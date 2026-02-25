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

        return $msg
            ?? data_get($body, 'error')
            ?? data_get($body, 'response.message')
            ?? ("HTTP {$response->status()}: " . $response->body());
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
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->get("{$this->apiUrl}/instance/connectionState/{$instancia}");

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
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->get("{$this->apiUrl}/instance/connect/{$instancia}");

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
     * Elimina una instancia de Evolution API.
     */
    public function eliminarInstancia(string $instancia)
    {
        try {
            Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(10)
                ->delete("{$this->apiUrl}/instance/delete/{$instancia}");
        } catch (\Exception) {
            // Si falla la petición aún así redirigimos con error
            return redirect()->route('bot.index')
                ->with('error', 'Error al conectar con Evolution API.');
        }

        return redirect()->route('bot.index')
            ->with('success', "Instancia «{$instancia}» eliminada correctamente.");
    }
}
