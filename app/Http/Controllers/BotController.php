<?php

namespace App\Http\Controllers;

use App\Events\MensajeBot;
use App\Models\Configuracion;
use App\Models\Conversation;
use App\Models\Tenant;
use App\Models\TenantInstance;
use App\Services\ExternalDbService;
use App\Services\PromptTagResolverService;
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

        // Instancias registradas en BD (ligadas al tenant actual)
        $user = auth()->user();
        $dbInstances = TenantInstance::where('tenant_id', $user->tenant_id)
            ->get()
            ->keyBy('instance_name');

        // Límite de instancias del plan
        $limitAlcanzado = false;
        $maxInstancias   = null;
        $totalInstancias = $dbInstances->count();
        if ($user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant && $tenant->max_instances) {
                $maxInstancias   = $tenant->max_instances;
                $limitAlcanzado  = $totalInstancias >= $maxInstancias;
            }
        }

        return view('bot.index', compact('instancias', 'botActivo', 'dbInstances', 'limitAlcanzado', 'maxInstancias', 'totalInstancias'));
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
            $instances = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/fetchInstances");

            // Intentar leer versión del servidor
            $versionRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(5)
                ->get("{$this->apiUrl}/");

            return response()->json([
                'url'              => $this->apiUrl,
                'instances_status' => $instances->status(),
                'instances_body'   => $instances->json() ?? $instances->body(),
                'version_status'   => $versionRes->status(),
                'version_body'     => $versionRes->json() ?? $versionRes->body(),
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

        // El bot debe estar activo (interruptor global)
        if (Configuracion::get('bot_activo', '0') !== '1') {
            return response()->json(['status' => 'bot_off']);
        }

        // Esta instancia específica debe estar activa
        $instObj = TenantInstance::where('instance_name', $instancia)->first();
        if ($instObj && !$instObj->activo) {
            return response()->json(['status' => 'instance_off']);
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

        // Texto que se guardará en BD (puede incluir prefijo de audio 🎙)
        $mensajeParaGuardar = $texto;

        // Si no hay texto, intentar transcribir si es un mensaje de audio/PTT
        if (empty(trim($texto))) {
            $messageKeys = array_keys(data_get($data, 'message', []) ?: []);
            $esAudio = in_array('audioMessage', $messageKeys) || in_array('pttMessage', $messageKeys);

            if ($esAudio) {
                $transcripcion = $this->transcribirAudio($instancia, $data);
                if ($transcripcion !== null) {
                    $texto              = $transcripcion;
                    $mensajeParaGuardar = '🎙 ' . $transcripcion;
                    Log::info("[Bot] Audio transcrito — instancia={$instancia}", [
                        'chars' => strlen($transcripcion),
                        'preview' => substr($transcripcion, 0, 80),
                    ]);
                } else {
                    Log::info("[Bot] Audio recibido sin servicio de transcripción activo — instancia={$instancia}");
                    return response()->json(['status' => 'audio_no_transcripcion']);
                }
            }
        }

        if (empty(trim($texto))) {
            return response()->json(['status' => 'no_text']);
        }

        // Número limpio (sin @s.whatsapp.net)
        $telefono = preg_replace('/@.*/', '', $remoteJid);

        // Leer configuración del bot
        $proveedor = Configuracion::get('bot_ia_proveedor', 'openai');
        $prompt    = Configuracion::get('system_prompt', 'Eres un asistente útil. Responde siempre en español.');

        // Resolver etiquetas [TAG] en el system_prompt (e.g. [CATALOGO_AGENDA], [API_ZOOM])
        $prompt = app(PromptTagResolverService::class)->resolve($prompt);

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
            'user_message' => $mensajeParaGuardar,   // incluye 🎙 si fue audio
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
        $usarQr = $request->input('metodo', 'qr') !== 'phone';

        // Verificar límite de instancias del plan
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant && $tenant->max_instances) {
                $count = TenantInstance::where('tenant_id', $user->tenant_id)->count();
                if ($count >= $tenant->max_instances) {
                    return response()->json([
                        'success' => false,
                        'message' => "Has alcanzado el límite de {$tenant->max_instances} instancia(s) para tu plan. Contacta al administrador para ampliar tu límite.",
                    ], 422);
                }
            }
        }

        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/instance/create", [
                    'instanceName' => $nombre,
                    'qrcode'       => $usarQr,
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
                        'qrcode'       => $usarQr,
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

            // Para modo QR: extraer el código del response o pedirlo vía connect
            $qr = null;
            if ($usarQr) {
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

            // Registrar la instancia en BD ligada al tenant actual
            $tenantId = auth()->user()->tenant_id;
            if ($tenantId && !TenantInstance::where('instance_name', $nombre)->exists()) {
                $esLaPrimera = !TenantInstance::where('tenant_id', $tenantId)->exists();
                TenantInstance::create([
                    'tenant_id'     => $tenantId,
                    'instance_name' => $nombre,
                    'activo'        => true,
                    'is_default'    => $esLaPrimera,
                ]);
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

        // Eliminar también el registro en BD
        TenantInstance::where('instance_name', $instancia)->delete();

        return redirect()->route('bot.index')
            ->with('success', "Instancia «{$instancia}» eliminada correctamente.");
    }

    /**
     * Registra una instancia de Evolution API que ya existe pero no está ligada al tenant.
     */
    public function registrarInstancia(Request $request): JsonResponse
    {
        $request->validate(['instancia' => ['required', 'string', 'max:100']]);

        $instancia = $request->instancia;
        $tenantId  = auth()->user()->tenant_id;

        if (!$tenantId) {
            return response()->json(['success' => false, 'message' => 'Sin tenant asignado.'], 422);
        }

        if (TenantInstance::where('instance_name', $instancia)->exists()) {
            return response()->json(['success' => false, 'message' => 'Esta instancia ya está registrada.'], 422);
        }

        $esLaPrimera = !TenantInstance::where('tenant_id', $tenantId)->exists();
        TenantInstance::create([
            'tenant_id'     => $tenantId,
            'instance_name' => $instancia,
            'activo'        => true,
            'is_default'    => $esLaPrimera,
        ]);

        return response()->json(['success' => true, 'is_default' => $esLaPrimera]);
    }

    /**
     * Activa o desactiva una instancia individual (sin afectar el interruptor global del bot).
     */
    public function toggleInstance(Request $request): JsonResponse
    {
        $request->validate(['instancia' => ['required', 'string']]);

        $inst = TenantInstance::where('instance_name', $request->instancia)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $inst->update(['activo' => !$inst->activo]);

        return response()->json(['success' => true, 'activo' => $inst->activo]);
    }

    /**
     * Marca una instancia como predeterminada (y desmarca las demás del tenant).
     */
    public function setDefault(Request $request): JsonResponse
    {
        $request->validate(['instancia' => ['required', 'string']]);

        $tenantId = auth()->user()->tenant_id;

        TenantInstance::where('tenant_id', $tenantId)->update(['is_default' => false]);
        TenantInstance::where('instance_name', $request->instancia)
            ->where('tenant_id', $tenantId)
            ->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Devuelve logs recientes filtrados para esta instancia + estado de conexión actual.
     */
    public function getLogs(string $instancia): JsonResponse
    {
        $entries = [];
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            $lines = $this->tailLines($logFile, 1500);

            $currentEntry = null;
            foreach ($lines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.*)$/', $line, $m)) {
                    if ($currentEntry !== null) {
                        $entries[] = $currentEntry;
                    }
                    $currentEntry = [
                        'timestamp' => $m[1],
                        'level'     => strtolower($m[2]),
                        'message'   => $m[3],
                    ];
                } elseif ($currentEntry !== null && trim($line) !== '') {
                    $currentEntry['message'] .= "\n" . $line;
                }
            }
            if ($currentEntry !== null) {
                $entries[] = $currentEntry;
            }

            // Filtrar: solo entradas que mencionen la instancia o sean errores del bot
            $entries = array_values(array_filter($entries, function ($e) use ($instancia) {
                $msg = $e['message'];
                return str_contains($msg, $instancia)
                    || str_contains($msg, '[Bot]')
                    || str_contains($msg, '[BotController]')
                    || str_contains($msg, '[ExternalDb]')
                    || in_array($e['level'], ['error', 'critical', 'emergency', 'alert']);
            }));

            // Últimas 150 entradas relevantes, más recientes primero
            $entries = array_reverse(array_slice($entries, -150));
        }

        // Estado actual de la instancia en Evolution API
        $estado   = null;
        $estadoOk = false;
        try {
            $enc = rawurlencode($instancia);
            $res = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(5)
                ->get("{$this->apiUrl}/instance/connectionState/{$enc}");
            if ($res->successful()) {
                $estadoOk = true;
                $estado   = $res->json();
            } else {
                $estado = ['error' => 'HTTP ' . $res->status() . ': ' . $res->body()];
            }
        } catch (\Exception $e) {
            $estado = ['error' => $e->getMessage()];
        }

        return response()->json([
            'success'      => true,
            'logs'         => $entries,
            'estado'       => $estado,
            'estado_ok'    => $estadoOk,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'instancia'    => $instancia,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS — LÓGICA DEL BOT
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Lee los últimos bytes del archivo de log y devuelve las líneas resultantes.
     */
    private function tailLines(string $file, int $maxLines = 1500): array
    {
        $handle = @fopen($file, 'r');
        if (!$handle) return [];

        $size     = filesize($file);
        if ($size === 0) {
            fclose($handle);
            return [];
        }
        $readSize = min(512 * 1024, $size); // Últimos 512 KB
        fseek($handle, max(0, $size - $readSize));
        $content = fread($handle, $readSize);
        fclose($handle);

        $lines = explode("\n", $content);
        return array_slice(
            array_filter($lines, fn($l) => trim($l) !== ''),
            -$maxLines
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TRANSCRIPCIÓN DE AUDIO
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Intenta transcribir un mensaje de audio/PTT usando Whisper (OpenAI) o Gemini nativo.
     * Descarga el audio de Evolution API en base64 y lo pasa al servicio configurado.
     *
     * Prioridad: Whisper (si activo) → Gemini nativo (si activo)
     */
    private function transcribirAudio(string $instancia, array $data): ?string
    {
        $whisperActivo     = Configuracion::get('openai_whisper_activo', '0') === '1';
        $geminiAudioActivo = Configuracion::get('gemini_audio_activo',   '0') === '1';

        $usarWhisper = $whisperActivo && Configuracion::get('openai_key');
        $usarGemini  = $geminiAudioActivo && Configuracion::get('gemini_key');

        if (!$usarWhisper && !$usarGemini) {
            return null;
        }

        // Descargar audio desde Evolution API → base64
        $enc = rawurlencode($instancia);
        try {
            $res = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(25)
                ->post("{$this->apiUrl}/chat/getBase64FromMediaMessage/{$enc}", [
                    'message' => [
                        'key'     => data_get($data, 'key'),
                        'message' => data_get($data, 'message'),
                    ],
                    'convertToMp4' => false,
                ]);

            if (!$res->successful()) {
                Log::warning("[Bot] No se pudo descargar audio — instancia={$instancia}: HTTP {$res->status()}");
                return null;
            }

            $base64   = data_get($res->json(), 'base64');
            $mimeType = data_get($res->json(), 'mediaType')
                ?? data_get($data, 'message.audioMessage.mimetype')
                ?? data_get($data, 'message.pttMessage.mimetype')
                ?? 'audio/ogg';

            if (!$base64) {
                Log::warning("[Bot] Respuesta de descarga sin base64 — instancia={$instancia}");
                return null;
            }
        } catch (\Exception $e) {
            Log::warning("[Bot] Error al descargar audio — instancia={$instancia}: " . $e->getMessage());
            return null;
        }

        // Transcribir: Whisper primero, Gemini como fallback
        if ($usarWhisper) {
            $resultado = $this->transcribirConWhisper($base64, $mimeType);
            if ($resultado !== null) return $resultado;
        }

        if ($usarGemini) {
            return $this->transcribirConGeminiNativo($base64, $mimeType);
        }

        return null;
    }

    /**
     * Transcribe audio usando la API de Whisper de OpenAI.
     * Soporta OGG, MP3, MP4, WAV, WEBM (formatos aceptados por Whisper).
     */
    private function transcribirConWhisper(string $base64Audio, string $mimeType): ?string
    {
        $apiKey = Configuracion::get('openai_key');
        if (!$apiKey) return null;

        $audioContent = base64_decode($base64Audio);
        if (!$audioContent) return null;

        // Extensión basada en el mimetype
        $ext = match(true) {
            str_contains($mimeType, 'ogg')  => 'ogg',
            str_contains($mimeType, 'mpeg') => 'mp3',
            str_contains($mimeType, 'mp4')  => 'mp4',
            str_contains($mimeType, 'wav')  => 'wav',
            str_contains($mimeType, 'webm') => 'webm',
            str_contains($mimeType, 'm4a')  => 'm4a',
            default                          => 'ogg',
        };

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->attach('file', $audioContent, "audio.{$ext}")
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model'           => 'whisper-1',
                    'response_format' => 'text',
                    'language'        => 'es',
                ]);

            if (!$response->successful()) {
                Log::error('[Bot] Whisper error HTTP ' . $response->status(), [
                    'body' => $response->json() ?? $response->body(),
                ]);
                return null;
            }

            $transcripcion = trim($response->body());
            Log::info('[Bot] Whisper OK: ' . substr($transcripcion, 0, 100));
            return $transcripcion ?: null;
        } catch (\Exception $e) {
            Log::error('[Bot] Whisper excepción: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Transcribe audio usando la capacidad multimodal nativa de Gemini (Flash/Pro 1.5+).
     * Envía el audio como inline_data directamente en la petición.
     */
    private function transcribirConGeminiNativo(string $base64Audio, string $mimeType): ?string
    {
        $apiKey = Configuracion::get('gemini_key');
        $model  = Configuracion::get('gemini_model', 'gemini-1.5-flash');

        if (!$apiKey) return null;

        try {
            $response = Http::timeout(60)
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'contents' => [[
                            'parts' => [
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data'      => $base64Audio,
                                    ],
                                ],
                                [
                                    'text' => 'Transcribe exactamente lo que dice este audio. Devuelve únicamente la transcripción, sin comentarios ni explicaciones adicionales.',
                                ],
                            ],
                        ]],
                    ]
                );

            if (!$response->successful()) {
                Log::error('[Bot] Gemini audio error HTTP ' . $response->status(), [
                    'body' => $response->json() ?? $response->body(),
                ]);
                return null;
            }

            $transcripcion = trim(data_get($response->json(), 'candidates.0.content.parts.0.text') ?? '');
            Log::info('[Bot] Gemini audio OK: ' . substr($transcripcion, 0, 100));
            return $transcripcion ?: null;
        } catch (\Exception $e) {
            Log::error('[Bot] Gemini audio excepción: ' . $e->getMessage());
            return null;
        }
    }

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
            if ($proveedor === 'deepseek') return $this->llamarDeepSeek($systemContent, $mensaje);
            if ($proveedor === 'gemini')   return $this->llamarGemini($systemContent, $mensaje);
            return $this->llamarOpenAI($systemContent, $mensaje);
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
     * Genera un código de emparejamiento de 8 dígitos para vincular sin QR.
     * La instancia debe existir previamente. POST /bot/pairing-code/{instancia}
     */
    public function pairingCode(Request $request, string $instancia): JsonResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:20']]);

        // Solo dígitos con código de país, ej: 5215512345678
        $phone = preg_replace('/\D/', '', $request->phone);

        $enc = rawurlencode($instancia);
        try {
            // ── Intento 1: endpoint estándar Evolution API v2.1+ ──────────────
            // Primero aseguramos estado "connecting" con un GET /connect
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/connect/{$enc}");

            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->apiUrl}/instance/pairingCode/{$enc}", [
                    'phoneNumber' => $phone,
                ]);

            if ($response->successful()) {
                $code = data_get($response->json(), 'code')
                    ?? data_get($response->json(), 'pairingCode');
                if ($code) {
                    return response()->json(['success' => true, 'code' => $code]);
                }
            }

            // ── Intento 2: PUT (algunas builds usan PUT en vez de POST) ──────
            $response2 = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->put("{$this->apiUrl}/instance/pairingCode/{$enc}", [
                    'phoneNumber' => $phone,
                ]);

            if ($response2->successful()) {
                $code = data_get($response2->json(), 'code')
                    ?? data_get($response2->json(), 'pairingCode');
                if ($code) {
                    return response()->json(['success' => true, 'code' => $code]);
                }
            }

            // ── Intento 3: connect con number param (Evolution API < v2.1) ───
            $response3 = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->get("{$this->apiUrl}/instance/connect/{$enc}", [
                    'number' => $phone,
                ]);

            if ($response3->successful()) {
                $code = data_get($response3->json(), 'pairingCode')
                    ?? data_get($response3->json(), 'code');
                if ($code) {
                    return response()->json(['success' => true, 'code' => $code]);
                }
            }

            // ── Ningún intento devolvió código ────────────────────────────────
            return response()->json([
                'success' => false,
                'message' => 'Tu versión de Evolution API no soporta código de emparejamiento vía REST. '
                    . 'Actualiza Evolution API a v2.1.0+ o conecta mediante código QR.',
            ], 422);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina todas las conversaciones de un contacto específico.
     * DELETE /bot/conversaciones/{phone}
     */
    public function eliminarConversacionesPorTelefono(string $phone): JsonResponse
    {
        $deleted = Conversation::where('phone', $phone)->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * Elimina TODAS las conversaciones del tenant actual.
     * DELETE /bot/conversaciones
     */
    public function eliminarTodasConversaciones(): JsonResponse
    {
        $deleted = Conversation::query()->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /**
     * Trunca el archivo de log de Laravel. Solo accesible por super_admin.
     * DELETE /admin/logs
     */
    public function clearLogs(): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return response()->json(['success' => true]);
    }

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
