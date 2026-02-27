<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Configuracion;
use App\Models\Disease;
use App\Models\Product;
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

        Log::info("[Bot] Webhook recibido — instancia={$instancia} evento={$event}");

        // Solo procesamos mensajes nuevos
        if ($event !== 'MESSAGES_UPSERT') {
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
        $proveedor    = Configuracion::get('bot_ia_proveedor', 'openai');
        $recursosJson = Configuracion::get('bot_recursos', '["clientes","productos"]');
        $recursos     = json_decode($recursosJson, true) ?? ['clientes', 'productos'];
        $prompt       = Configuracion::get('system_prompt', 'Eres un asistente útil. Responde siempre en español.');

        // Construir contexto con los recursos habilitados
        $contexto = $this->construirContexto($telefono, $recursos);

        // Llamar a la IA seleccionada
        [$respuestaTexto, $productosDetectados] = $this->llamarIA(
            $proveedor, $prompt, $contexto, $texto, $recursos
        );

        if ($respuestaTexto === null) {
            Log::error("[Bot] La IA no respondió — instancia={$instancia} proveedor={$proveedor}");
            return response()->json(['status' => 'ai_error']);
        }

        // Enviar respuesta de texto
        $this->enviarTexto($instancia, $remoteJid, $respuestaTexto);

        // Enviar imagen y/o video de los productos mencionados
        foreach ($productosDetectados as $producto) {
            if ($producto->image_url) {
                $this->enviarMedia($instancia, $remoteJid, 'image', $producto->image_url, $producto->name);
            }
            if ($producto->video_url && !$producto->video_es_archivo) {
                // Solo enviamos videos externos (URLs), no archivos locales
                $this->enviarMedia($instancia, $remoteJid, 'video', $producto->video_url, $producto->name);
            }
        }

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
     * Construye el contexto que se inyecta al prompt del sistema
     * usando únicamente los recursos habilitados en la configuración.
     */
    private function construirContexto(string $telefono, array $recursos): string
    {
        $partes = [];

        // ── Clientes ──────────────────────────────────────────────────────────
        if (in_array('clientes', $recursos)) {
            $cliente = Client::with(['observations' => fn($q) => $q->latest()->limit(5)])
                ->where('phone', 'like', "%{$telefono}%")
                ->first();

            if ($cliente) {
                $partes[] = "=== DATOS DEL CLIENTE ===";
                $partes[] = "Nombre: {$cliente->name}";
                $partes[] = "Folio: {$cliente->folio}";
                if ($cliente->status) $partes[] = "Estado: {$cliente->status}";

                if ($cliente->observations->isNotEmpty()) {
                    $partes[] = "\nÚLTIMAS OBSERVACIONES (más recientes primero):";
                    foreach ($cliente->observations as $obs) {
                        $linea = "- [{$obs->created_at->format('d/m/Y')}]";
                        if ($obs->weight)            $linea .= " Peso: {$obs->weight} kg";
                        if ($obs->age)               $linea .= " Edad: {$obs->age} años";
                        if ($obs->observation)       $linea .= " | Obs: {$obs->observation}";
                        if ($obs->suggested_products) $linea .= " | Productos sugeridos: {$obs->suggested_products}";
                        $partes[] = $linea;
                    }
                } else {
                    $partes[] = "Sin observaciones registradas aún.";
                }
            } else {
                $partes[] = "=== CLIENTE NO REGISTRADO ===";
                $partes[] = "El número {$telefono} no está registrado en el sistema.";
            }
        }

        // ── Productos ─────────────────────────────────────────────────────────
        if (in_array('productos', $recursos)) {
            $productos = Product::where('available', true)->orderBy('name')->get();

            if ($productos->isNotEmpty()) {
                $partes[] = "\n=== CATÁLOGO DE PRODUCTOS DISPONIBLES ===";
                foreach ($productos as $p) {
                    $linea = "- {$p->name}";
                    if ($p->category)    $linea .= " [Categoría: {$p->category}]";
                    if ($p->price)       $linea .= " — Precio: \${$p->price}";
                    if ($p->description) $linea .= " — {$p->description}";
                    $partes[] = $linea;
                }
            }
        }

        // ── Enfermedades ──────────────────────────────────────────────────────
        if (in_array('enfermedades', $recursos)) {
            $enfermedades = Disease::orderBy('name')->get();

            if ($enfermedades->isNotEmpty()) {
                $partes[] = "\n=== ENFERMEDADES / PADECIMIENTOS ===";
                foreach ($enfermedades as $e) {
                    $linea = "- {$e->name}";
                    if ($e->category)   $linea .= " [Categoría: {$e->category}]";
                    if ($e->symptoms)   $linea .= " | Síntomas: {$e->symptoms}";
                    if ($e->treatment)  $linea .= " | Tratamiento: {$e->treatment}";
                    $partes[] = $linea;
                }
            }
        }

        return implode("\n", $partes);
    }

    /**
     * Llama al proveedor de IA seleccionado y devuelve [texto, productos_detectados].
     */
    private function llamarIA(string $proveedor, string $prompt, string $contexto, string $mensaje, array $recursos): array
    {
        // Construir el system prompt completo
        $systemContent = $prompt;

        if ($contexto) {
            $systemContent .= "\n\n--- INFORMACIÓN CONTEXTUAL ---\n" . $contexto;
        }

        if (in_array('productos', $recursos)) {
            $systemContent .= "\n\nIMPORTANTE: Cuando recomiendes un producto, escribe su nombre exactamente como aparece en el catálogo.";
        }

        try {
            $respuesta = match ($proveedor) {
                'deepseek' => $this->llamarDeepSeek($systemContent, $mensaje),
                'gemini'   => $this->llamarGemini($systemContent, $mensaje),
                default    => $this->llamarOpenAI($systemContent, $mensaje),
            };

            if ($respuesta === null) return [null, []];

            // Detectar qué productos se mencionan en la respuesta
            $productosDetectados = in_array('productos', $recursos)
                ? $this->detectarProductos($respuesta)
                : [];

            return [$respuesta, $productosDetectados];
        } catch (\Exception $e) {
            Log::error("[Bot] Error en IA ({$proveedor}): " . $e->getMessage());
            return [null, []];
        }
    }

    /**
     * Detecta productos del catálogo mencionados en el texto de respuesta de la IA.
     */
    private function detectarProductos(string $texto): array
    {
        $detectados = [];

        Product::where('available', true)->orderBy('name')->get()
            ->each(function (Product $p) use ($texto, &$detectados) {
                if (mb_stripos($texto, $p->name) !== false) {
                    $detectados[] = $p;
                }
            });

        return $detectados;
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

        return $response->successful()
            ? data_get($response->json(), 'choices.0.message.content')
            : null;
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

        return $response->successful()
            ? data_get($response->json(), 'choices.0.message.content')
            : null;
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
}
