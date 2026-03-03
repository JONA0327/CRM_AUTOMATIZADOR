<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleAuthController extends Controller
{
    // Scopes solicitados: Calendar (lectura/escritura) + Drive (lectura/escritura de archivos)
    private const SCOPES = [
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/userinfo.email',
        'openid',
    ];

    /**
     * Redirige al usuario a la pantalla de autorización de Google.
     * GET /configuracion/google/redirect
     */
    public function redirect()
    {
        $clientId = Configuracion::get('google_client_id');

        if (! $clientId) {
            return redirect()->route('configuracion.index')
                ->with('error', 'Primero guarda el Client ID de Google antes de conectar.');
        }

        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => route('configuracion.google.callback'),
            'response_type' => 'code',
            'scope'         => implode(' ', self::SCOPES),
            'access_type'   => 'offline',   // necesario para obtener refresh_token
            'prompt'        => 'consent',   // fuerza mostrar pantalla de consentimiento siempre
        ];

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }

    /**
     * Recibe el callback de Google con el código de autorización.
     * GET /configuracion/google/callback
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('configuracion.index')
                ->with('error', 'Autorización de Google cancelada: ' . $request->error);
        }

        $code = $request->code;
        if (! $code) {
            return redirect()->route('configuracion.index')
                ->with('error', 'No se recibió el código de autorización de Google.');
        }

        $clientId     = Configuracion::get('google_client_id');
        $clientSecret = Configuracion::get('google_client_secret');

        // Intercambiar código por tokens
        $response = Http::timeout(15)->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => route('configuracion.google.callback'),
            'grant_type'    => 'authorization_code',
        ]);

        if (! $response->successful()) {
            $err = $response->json('error_description') ?? $response->body();
            return redirect()->route('configuracion.index')
                ->with('error', 'Error al obtener tokens de Google: ' . $err);
        }

        $data = $response->json();

        // Guardar tokens en la configuración del tenant
        if (! empty($data['refresh_token'])) {
            Configuracion::set('google_refresh_token', $data['refresh_token'], 'google', 'Refresh token OAuth2 de Google');
        }
        Configuracion::set('google_access_token', $data['access_token'], 'google', 'Access token OAuth2 de Google (caduca)');

        // Obtener el email de la cuenta conectada desde el id_token (JWT sin verificar — solo lectura)
        if (! empty($data['id_token'])) {
            try {
                $parts   = explode('.', $data['id_token']);
                $payload = json_decode(
                    base64_decode(str_pad($parts[1], strlen($parts[1]) + (4 - strlen($parts[1]) % 4) % 4, '=')),
                    true
                );
                $email = $payload['email'] ?? null;
                if ($email) {
                    Configuracion::set('google_account_email', $email, 'google', 'Email de la cuenta Google conectada');
                }
            } catch (\Throwable) {
                // No fatal — el token se guardó igual
            }
        }

        return redirect()->route('configuracion.index')
            ->with('success', 'Google conectado correctamente. Ya puedes usar Calendar y Drive.');
    }

    /**
     * Revoca el token y elimina la conexión con Google.
     * DELETE /configuracion/google/revoke
     */
    public function revoke()
    {
        $token = Configuracion::get('google_refresh_token')
            ?? Configuracion::get('google_access_token');

        if ($token) {
            try {
                Http::timeout(10)->get('https://oauth2.googleapis.com/revoke?token=' . urlencode($token));
            } catch (\Throwable) {
                // Ignorar errores de red al revocar — igual limpiamos la BD
            }
        }

        Configuracion::set('google_refresh_token',  null, 'google');
        Configuracion::set('google_access_token',   null, 'google');
        Configuracion::set('google_account_email',  null, 'google');

        return redirect()->route('configuracion.index')
            ->with('success', 'Cuenta de Google desconectada correctamente.');
    }
}
