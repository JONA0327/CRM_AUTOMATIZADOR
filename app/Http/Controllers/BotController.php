<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
     * Activa o desactiva el bot (guarda el estado en BD).
     */
    public function toggleBot()
    {
        $actual = Configuracion::get('bot_activo', '0') === '1';
        $nuevo  = $actual ? '0' : '1';

        Configuracion::set(
            clave:       'bot_activo',
            valor:       $nuevo,
            grupo:       'bot',
            descripcion: 'Estado del bot (1=activo, 0=inactivo)',
        );

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
                'url'        => $this->apiUrl,
                'status'     => $response->status(),
                'body'       => $response->json() ?? $response->body(),
                'headers'    => $response->headers(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'url'   => $this->apiUrl,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extrae un mensaje legible de la respuesta de Evolution API.
     */
    private function extraerMensajeError(\Illuminate\Http\Client\Response $response): string
    {
        $body = $response->json();

        // message puede ser string o array
        $msg = data_get($body, 'message');
        if (is_array($msg)) {
            $msg = implode('. ', $msg);
        }

        $msg = $msg
            ?? data_get($body, 'error')
            ?? data_get($body, 'response.message')
            ?? null;

        // Siempre incluir el body completo para facilitar diagnóstico
        $rawBody = $response->body();
        $detail  = $rawBody !== '' ? " | Respuesta: {$rawBody}" : '';

        return ($msg ?? "HTTP {$response->status()}") . $detail;
    }

    /**
     * Recibe eventos de Evolution API (webhook público, sin auth).
     * Evolution API hace POST aquí cuando ocurre un evento en WhatsApp.
     */
    public function recibirWebhook(Request $request)
    {
        // Responde 200 inmediatamente para que Evolution API no reintente
        // La lógica del bot se procesará aquí más adelante
        $event    = $request->input('event');
        $instance = $request->input('instance');
        $data     = $request->input('data');

        // Por ahora solo registramos el evento recibido (logs de Laravel)
        \Log::info("Webhook Evolution API recibido: evento={$event} instancia={$instance}");

        return response()->json(['status' => 'ok']);
    }

    /**
     * Crea una instancia en Evolution API y devuelve el QR (JSON).
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

            // Si no vino en la respuesta de creación, lo pedimos explícitamente
            if (! $qr) {
                sleep(1); // breve espera para que la instancia se inicialice
                $qrResponse = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(15)
                    ->get("{$this->apiUrl}/instance/connect/{$nombre}");

                if ($qrResponse->successful()) {
                    $qr = data_get($qrResponse->json(), 'base64')
                        ?? data_get($qrResponse->json(), 'qrcode.base64')
                        ?? data_get($qrResponse->json(), 'qrcode');
                }
            }

            // ── Configurar webhook automáticamente ──────────────────────
            try {
                Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(10)
                    ->post("{$this->apiUrl}/webhook/set/{$nombre}", [
                        'url'              => route('webhook.whatsapp'),
                        'webhook_by_events'=> false,
                        'webhook_base64'   => false,
                        'events'           => [
                            'MESSAGES_UPSERT',
                            'CONNECTION_UPDATE',
                            'MESSAGES_UPDATE',
                        ],
                    ]);
            } catch (\Exception) {
                // No bloquea la creación si el webhook falla
            }

            return response()->json([
                'success'   => true,
                'qr'        => $qr,
                'instancia' => $nombre,
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

            return response()->json([
                'success'  => true,
                'webhook'  => $webhookRes->successful()  ? $webhookRes->json()  : null,
                'settings' => $settingsRes->successful() ? $settingsRes->json() : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Guarda la configuración de webhook y settings de una instancia (JSON).
     */
    public function setConfig(Request $request, string $instancia)
    {
        $request->validate([
            'webhook_url'       => ['nullable', 'string', 'max:500'],
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

        // ── Webhook (solo si se proporcionó una URL) ──────────────────────
        $webhookUrl = trim($request->input('webhook_url', ''));
        if ($webhookUrl !== '') {
            try {
                $webhookPayload = [
                    'url'              => $webhookUrl,
                    'webhook_by_events'=> false,
                    'webhook_base64'   => false,
                    'events'           => $request->input('events', []),
                ];

                $wRes = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(10)
                    ->post("{$this->apiUrl}/webhook/set/{$enc}", $webhookPayload);

                if (! $wRes->successful()) {
                    $errors[] = 'Webhook: ' . $this->extraerMensajeError($wRes);
                }
            } catch (\Exception $e) {
                $errors[] = 'Webhook: ' . $e->getMessage();
            }
        }

        // ── Settings ─────────────────────────────────────────────────────
        try {
            $rejectCall = $request->boolean('reject_call');
            $settingsPayload = [
                'reject_call'       => $rejectCall,
                'msg_call'          => $rejectCall ? ($request->input('msg_call', '') ?: 'Llamadas no disponibles.') : '',
                'groups_ignore'     => $request->boolean('groups_ignore'),
                'always_online'     => $request->boolean('always_online'),
                'read_messages'     => $request->boolean('read_messages'),
                'read_status'       => $request->boolean('read_status'),
                'sync_full_history' => $request->boolean('sync_full_history'),
            ];

            \Log::info('[setConfig] URL: ' . "{$this->apiUrl}/settings/set/{$enc}");
            \Log::info('[setConfig] Payload: ' . json_encode($settingsPayload));

            $sRes = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->post("{$this->apiUrl}/settings/set/{$enc}", $settingsPayload);

            \Log::info('[setConfig] Status: ' . $sRes->status() . ' Body: ' . $sRes->body());

            if (! $sRes->successful()) {
                $errors[] = 'Settings: ' . $this->extraerMensajeError($sRes);
            }
        } catch (\Exception $e) {
            \Log::error('[setConfig] Excepción: ' . $e->getMessage());
            $errors[] = 'Settings: ' . $e->getMessage();
        }

        if ($errors) {
            return response()->json(['success' => false, 'message' => implode(' | ', $errors)], 422);
        }

        return response()->json(['success' => true]);
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
            // Si falla la petición aún así redirigimos con error
            return redirect()->route('bot.index')
                ->with('error', 'Error al conectar con Evolution API.');
        }

        return redirect()->route('bot.index')
            ->with('success', "Instancia «{$instancia}» eliminada correctamente.");
    }
}
