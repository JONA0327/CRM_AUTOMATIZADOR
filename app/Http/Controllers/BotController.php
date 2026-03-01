<?php

namespace App\Http\Controllers;

use App\Events\MensajeBot;
use App\Models\Configuracion;
use App\Models\Conversation;
use App\Services\ExternalDbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        // Prioridad: BD (cifrado) → .env → vacío
        $this->apiUrl = rtrim(
            Configuracion::get('evolution_url', config('services.evolution.url', '')),
            '/'
        );
        $this->apiKey = Configuracion::get('evolution_key', config('services.evolution.key', ''));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // VISTAS / ADMIN
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lista todas las instancias conectadas.
     */
    public function index()
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/fetchInstances");

            $instancias = $response->successful() ? collect($response->json()) : collect();
        } catch (\Exception) {
            $instancias = collect();
        }

        $botActivo = Configuracion::get('bot_activo', '0') === '1';

        return view('bot.index', compact('instancias', 'botActivo'));
    }

    /**
     * Activa o desactiva el bot.
     */
    public function toggleBot()
    {
        $actual = Configuracion::get('bot_activo', '0') === '1';
        $nuevo  = $actual ? '0' : '1';

        Configuracion::set('bot_activo', $nuevo, 'bot', 'Estado del bot (1=activo, 0=inactivo)');

        $msg = $nuevo === '1' ? 'Bot activado correctamente.' : 'Bot desactivado correctamente.';

        return redirect()->route('bot.index')->with('success', $msg);
    }

    /**
     * Página para conectar un número nuevo.
     */
    public function conectar()
    {
        return view('bot.conectar');
    }

    /**
     * Muestra los logs recientes del bot en la vista de conversaciones.
     */
    public function conversaciones()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        if (file_exists($logPath) && filesize($logPath) > 0) {
            $lines = file($logPath);
            // Filtra solo logs del bot (que contienen [Bot])
            $botLogs = array_filter($lines, function($line) {
                return str_contains($line, '[Bot]');
            });
            $logs = array_slice(array_reverse(array_values($botLogs)), 0, 200);
        }

        $botActivo = Configuracion::get('bot_activo', '0') === '1';

        return view('bot.conversaciones', compact('logs', 'botActivo'));
    }

    /**
     * Diagnóstico: prueba la conexión con Evolution API (solo en local/debug).
     */
    public function diagnostico()
    {
        abort_unless(config('app.debug'), 403);

        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/fetchInstances");

            return response()->json([
                'url'     => $this->apiUrl,
                'status'  => $response->status(),
                'body'    => $response->json() ?? $response->body(),
                'headers' => $response->headers(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['url' => $this->apiUrl, 'error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // WEBHOOK PRINCIPAL
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Recibe eventos de Evolution API para una instancia específica.
     * URL: POST /webhook/whatsapp/{instancia}
     */
    public function recibirWebhook(Request $request, string $instancia)
    {
        $event = $request->input('event');
        $data  = $request->input('data', []);

        Log::info("[Bot] Webhook recibido — instancia={$instancia} evento={$event}", [
            'fromMe'    => data_get($data, 'key.fromMe'),
            'remoteJid' => data_get($data, 'key.remoteJid'),
            'tipo'      => array_keys(data_get($data, 'message', []) ?: []),
        ]);

        // Solo procesamos mensajes nuevos
        // Evolution API puede enviar el evento como 'MESSAGES_UPSERT' o 'messages.upsert'
        $eventNorm = strtolower(str_replace(['_', '.'], '', $event ?? ''));
        if ($eventNorm !== 'messagesupsert') {
            return response()->json(['status' => 'ignored']);
        }

        // El bot debe estar activo
        if (Configuracion::get('bot_activo', '0') !== '1') {
            return response()->json(['status' => 'bot_off']);
        }

        // Ignorar mensajes propios
        if (data_get($data, 'key.fromMe', false)) {
            return response()->json(['status' => 'own_message']);
        }

        $remoteJid = data_get($data, 'key.remoteJid', '');

        // Ignorar grupos
        if (str_contains($remoteJid, '@g.us')) {
            return response()->json(['status' => 'group_ignored']);
        }

        // Extraer texto del mensaje
        $texto = data_get($data, 'message.conversation')
            ?? data_get($data, 'message.extendedTextMessage.text')
            ?? data_get($data, 'message.imageMessage.caption')
            ?? '';

        if (empty(trim($texto))) {
            return response()->json(['status' => 'no_text']);
        }

        // Número limpio (sin @s.whatsapp.net)
        $telefono = preg_replace('/@.*/', '', $remoteJid);

        // Leer configuración del bot
        $proveedor = Configuracion::get('bot_ia_proveedor', 'openai');
        $prompt    = Configuracion::get('system_prompt', 'Eres un asistente útil. Responde siempre en español.');

        // Construir contexto desde la BD externa configurada
        $contexto = $this->construirContexto($telefono, []);

        // Llamar a la IA seleccionada
        $respuestaTexto = $this->llamarIA($proveedor, $prompt, $contexto, $texto);

        if ($respuestaTexto === null) {
            Log::error("[Bot] La IA no respondió — instancia={$instancia} proveedor={$proveedor}");
            return response()->json(['status' => 'ai_error']);
        }

        // Guardar conversación en BD y emitir evento en tiempo real
        $clienteNombre = null;  // El lookup de nombre se hace desde catálogos dinámicos
        $conv = Conversation::create([
            'phone'        => $telefono,
            'instancia'    => $instancia,
            'contact_name' => $clienteNombre,
            'user_message' => $texto,
            'bot_response' => $respuestaTexto,
            'status'       => 'ok',
        ]);
        broadcast(new MensajeBot($conv));

        // Enviar respuesta de texto
        $this->enviarTexto($instancia, $remoteJid, $respuestaTexto);

        return response()->json(['status' => 'ok']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INSTANCIAS — EVOLUTION API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crea una instancia en Evolution API y devuelve el QR (JSON).
     * El webhook se configura automáticamente con la URL única de la instancia.
     */
    public function crearInstancia(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras, números, guiones y guiones bajos.',
        ]);

        $nombre = $request->nombre;

        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/instance/create", [
                    'instanceName' => $nombre,
                    'qrcode'       => true,
                    'integration'  => 'WHATSAPP-BAILEYS',
                ]);

            // Si la instancia ya existe (409), la eliminamos y reintentamos
            if ($response->status() === 409) {
                Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(10)
                    ->delete("{$this->apiUrl}/instance/delete/{$nombre}");

                $response = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(15)
                    ->post("{$this->apiUrl}/instance/create", [
                        'instanceName' => $nombre,
                        'qrcode'       => true,
                        'integration'  => 'WHATSAPP-BAILEYS',
                    ]);
            }

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->extraerMensajeError($response),
                ], 422);
            }

            $data = $response->json();

            // Evolution v2: el QR puede venir dentro de 'qrcode.base64'
            $qr = data_get($data, 'qrcode.base64');

            if (! $qr) {
                sleep(1);
                $qrResponse = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(15)
                    ->get("{$this->apiUrl}/instance/connect/{$nombre}");

                if ($qrResponse->successful()) {
                    $qr = data_get($qrResponse->json(), 'base64')
                        ?? data_get($qrResponse->json(), 'qrcode.base64')
                        ?? data_get($qrResponse->json(), 'qrcode');
                }
            }

            // ── Configurar webhook único por instancia ─────────────────────
            $webhookUrl = route('webhook.whatsapp', ['instancia' => $nombre]);

            try {
                Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(10)
                    ->post("{$this->apiUrl}/webhook/set/{$nombre}", [
                        'webhook' => [
                            'enabled'  => true,
                            'url'      => $webhookUrl,
                            'byEvents' => false,
                            'base64'   => false,
                            'events'   => [
                                'MESSAGES_UPSERT',
                                'CONNECTION_UPDATE',
                                'MESSAGES_UPDATE',
                            ],
                        ],
                    ]);

                Log::info("[Bot] Webhook configurado para {$nombre}: {$webhookUrl}");
            } catch (\Exception $e) {
                Log::warning("[Bot] No se pudo configurar el webhook para {$nombre}: " . $e->getMessage());
            }

            return response()->json([
                'success'     => true,
                'qr'          => $qr,
                'instancia'   => $nombre,
                'webhook_url' => $webhookUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar con Evolution API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve el estado de conexión de una instancia (para polling).
     */
    public function estadoConexion(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/connectionState/{$enc}");

            if ($response->successful()) {
                $data  = $response->json();
                $state = data_get($data, 'instance.state')
                    ?? data_get($data, 'instance.connectionStatus')
                    ?? data_get($data, 'connectionStatus')
                    ?? data_get($data, 'state')
                    ?? 'unknown';

                return response()->json([
                    'success'   => true,
                    'state'     => $state,
                    'conectado' => strtolower($state) === 'open',
                ]);
            }

            return response()->json(['success' => false, 'state' => 'unknown', 'conectado' => false]);
        } catch (\Exception) {
            return response()->json(['success' => false, 'state' => 'error', 'conectado' => false]);
        }
    }

    /**
     * Refresca el QR de una instancia existente (JSON).
     */
    public function refrescarQr(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->get("{$this->apiUrl}/instance/connect/{$enc}");

            if ($response->successful()) {
                $qr = data_get($response->json(), 'base64')
                    ?? data_get($response->json(), 'qrcode.base64');

                return response()->json(['success' => true, 'qr' => $qr]);
            }

            return response()->json(['success' => false, 'message' => 'No se pudo refrescar el QR.'], 422);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'Error de conexión con Evolution API.'], 500);
        }
    }

    /**
     * Obtiene la configuración actual de webhook y settings de una instancia (JSON).
     */
    public function getConfig(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            $webhookRes  = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/webhook/find/{$enc}");

            $settingsRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/settings/find/{$enc}");

            // Siempre devolvemos la URL predefinida (no la que tenga Evolution guardada)
            $webhookPredefinido = route('webhook.whatsapp', ['instancia' => $instancia]);

            $webhookData = $webhookRes->successful() ? $webhookRes->json() : [];
            $webhookData['url'] = $webhookPredefinido; // sobrescribir con la URL correcta

            return response()->json([
                'success'      => true,
                'webhook'      => $webhookData,
                'settings'     => $settingsRes->successful() ? $settingsRes->json() : null,
                'webhook_url'  => $webhookPredefinido,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Guarda la configuración de webhook y settings de una instancia (JSON).
     * El webhook URL siempre se fuerza al predefinido de la instancia.
     */
    public function setConfig(Request $request, string $instancia)
    {
        $request->validate([
            'events'            => ['nullable', 'array'],
            'events.*'          => ['string'],
            'reject_call'       => ['nullable'],
            'groups_ignore'     => ['nullable'],
            'always_online'     => ['nullable'],
            'read_messages'     => ['nullable'],
            'read_status'       => ['nullable'],
            'sync_full_history' => ['nullable'],
        ]);

        $enc    = rawurlencode($instancia);
        $errors = [];

        // ── Webhook: siempre usamos la URL predefinida de la instancia ───────
        $webhookUrl = route('webhook.whatsapp', ['instancia' => $instancia]);
        try {
            // Evolution API v2 requiere el payload dentro de la clave "webhook"
            $wRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->apiUrl}/webhook/set/{$enc}", [
                    'webhook' => [
                        'enabled'  => true,
                        'url'      => $webhookUrl,
                        'byEvents' => false,
                        'base64'   => false,
                        'events'   => $request->input('events', []),
                    ],
                ]);

            if (! $wRes->successful()) {
                $errors[] = 'Webhook: ' . $this->extraerMensajeError($wRes);
            }
        } catch (\Exception $e) {
            $errors[] = 'Webhook: ' . $e->getMessage();
        }

        // ── Settings — Evolution API v2 usa camelCase ────────────────────────
        try {
            $rejectCall      = $request->boolean('reject_call');
            $settingsPayload = [
                'rejectCall'      => $rejectCall,
                'msgCall'         => $rejectCall ? ($request->input('msg_call', '') ?: 'Llamadas no disponibles.') : '',
                'groupsIgnore'    => $request->boolean('groups_ignore'),
                'alwaysOnline'    => $request->boolean('always_online'),
                'readMessages'    => $request->boolean('read_messages'),
                'readStatus'      => $request->boolean('read_status'),
                'syncFullHistory' => $request->boolean('sync_full_history'),
            ];

            $sRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->apiUrl}/settings/set/{$enc}", $settingsPayload);

            if (! $sRes->successful()) {
                $errors[] = 'Settings: ' . $this->extraerMensajeError($sRes);
            }
        } catch (\Exception $e) {
            $errors[] = 'Settings: ' . $e->getMessage();
        }

        if ($errors) {
            return response()->json(['success' => false, 'message' => implode(' | ', $errors)], 422);
        }

        return response()->json(['success' => true, 'webhook_url' => $webhookUrl]);
    }

    /**
     * Elimina una instancia de Evolution API.
     */
    public function eliminarInstancia(string $instancia)
    {
        $enc = rawurlencode($instancia);
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->delete("{$this->apiUrl}/instance/delete/{$enc}");
        } catch (\Exception) {
            return redirect()->route('bot.index')
                ->with('error', 'Error al conectar con Evolution API.');
        }

        return redirect()->route('bot.index')
            ->with('success', "Instancia «{$instancia}» eliminada correctamente.");
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS — LÓGICA DEL BOT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Construye el contexto para la IA leyendo todas las BDs externas configuradas en ext_dbs.
     */
    private function construirContexto(string $telefono, array $recursos): string
    {
        try {
            return (new ExternalDbService())->construirContextoMultiple();
        } catch (\Exception $e) {
            Log::warning('[Bot] Error al construir contexto con BD externa: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Llama al proveedor de IA seleccionado y devuelve el texto de respuesta.
     */
    private function llamarIA(string $proveedor, string $prompt, string $contexto, string $mensaje): ?string
    {
        $systemContent = $prompt;

        if ($contexto) {
            $systemContent .= "\n\n--- INFORMACIÓN CONTEXTUAL ---\n" . $contexto;
        }

        try {
            return match ($proveedor) {
                'deepseek' => $this->llamarDeepSeek($systemContent, $mensaje),
                'gemini'   => $this->llamarGemini($systemContent, $mensaje),
                default    => $this->llamarOpenAI($systemContent, $mensaje),
            };
        } catch (\Exception $e) {
            Log::error("[Bot] Error en IA ({$proveedor}): " . $e->getMessage());
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PROVEEDORES DE IA
    // ──────────────────────────────────────────────────────────────────────────

    private function llamarOpenAI(string $system, string $user): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        $model  = Configuracion::get('openai_model', 'gpt-4o-mini');

        if (! $apiKey) {
            Log::warning('[Bot] OpenAI: API Key no configurada.');
            return null;
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $user],
                ],
            ]);

        if (! $response->successful()) {
            Log::error('[Bot] OpenAI error HTTP ' . $response->status(), [
                'model' => $model,
                'body'  => $response->json() ?? $response->body(),
            ]);
            return null;
        }

        return data_get($response->json(), 'choices.0.message.content');
    }

    private function llamarDeepSeek(string $system, string $user): ?string
    {
        $apiKey = Configuracion::get('deepseek_key');
        $model  = Configuracion::get('deepseek_model', 'deepseek-chat');

        if (! $apiKey) {
            Log::warning('[Bot] DeepSeek: API Key no configurada.');
            return null;
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model'    => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $user],
                ],
            ]);

        if (! $response->successful()) {
            Log::error('[Bot] DeepSeek error HTTP ' . $response->status(), [
                'model' => $model,
                'body'  => $response->json() ?? $response->body(),
            ]);
            return null;
        }

        return data_get($response->json(), 'choices.0.message.content');
    }

    private function llamarGemini(string $system, string $user): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');

        if (! $apiKey) {
            Log::warning('[Bot] Gemini: API Key no configurada.');
            return null;
        }

        $response = Http::timeout(30)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'system_instruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'contents' => [
                        ['parts' => [['text' => $user]]],
                    ],
                ]
            );

        return $response->successful()
            ? data_get($response->json(), 'candidates.0.content.parts.0.text')
            : null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS — EVOLUTION API (ENVÍO)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Envía un mensaje de texto por WhatsApp.
     */
    private function enviarTexto(string $instancia, string $remoteJid, string $texto): void
    {
        $enc = rawurlencode($instancia);
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/message/sendText/{$enc}", [
                    'number' => $remoteJid,
                    'text'   => $texto,
                ]);
        } catch (\Exception $e) {
            Log::error("[Bot] Error al enviar texto a {$remoteJid}: " . $e->getMessage());
        }
    }

    /**
     * Envía una imagen o video por WhatsApp.
     */
    private function enviarMedia(string $instancia, string $remoteJid, string $tipo, string $url, string $caption = ''): void
    {
        $enc = rawurlencode($instancia);
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(20)
                ->post("{$this->apiUrl}/message/sendMedia/{$enc}", [
                    'number'    => $remoteJid,
                    'mediatype' => $tipo,   // 'image' | 'video'
                    'media'     => $url,
                    'caption'   => $caption,
                ]);
        } catch (\Exception $e) {
            Log::error("[Bot] Error al enviar {$tipo} a {$remoteJid}: " . $e->getMessage());
        }
    }

    /**
     * Extrae un mensaje legible de una respuesta de error de Evolution API.
     */
    private function extraerMensajeError(\Illuminate\Http\Client\Response $response): string
    {
        $body = $response->json();

        $msg = data_get($body, 'message');
        if (is_array($msg)) {
            $msg = implode('. ', $msg);
        }
        $msg = $msg
            ?? data_get($body, 'error')
            ?? data_get($body, 'response.message')
            ?? null;

        $rawBody = $response->body();
        $detail  = $rawBody !== '' ? " | Respuesta: {$rawBody}" : '';

        return ($msg ?? "HTTP {$response->status()}") . $detail;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API — CONVERSACIONES EN TIEMPO REAL
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lista los contactos únicos con su último mensaje (para el panel izquierdo).
     */
    public function listarContactos(): JsonResponse
    {
        $contactos = Conversation::selectRaw('phone, contact_name, instancia, MAX(created_at) as ultimo, COUNT(*) as total')
            ->groupBy('phone', 'contact_name', 'instancia')
            ->orderByDesc('ultimo')
            ->get();

        return response()->json($contactos);
    }

    /**
     * Devuelve los últimos 50 mensajes de un contacto específico.
     */
    public function mensajesPorTelefono(string $phone): JsonResponse
    {
        $mensajes = Conversation::where('phone', $phone)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($mensajes);
    }
}
