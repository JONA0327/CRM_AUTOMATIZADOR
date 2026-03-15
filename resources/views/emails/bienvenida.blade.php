<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f5; padding: 40px 20px; }
        .wrapper { max-width: 520px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); padding: 36px 40px; text-align: center; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.6; margin-bottom: 16px; }
        .greeting { font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 12px; }
        .role-badge { display: inline-block; background: #eef2ff; color: #4f46e5; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; text-transform: capitalize; }
        .credentials-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
        .credentials-box h3 { font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 14px; }
        .cred-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .cred-row:last-child { margin-bottom: 0; }
        .cred-label { font-size: 12px; color: #9ca3af; width: 80px; flex-shrink: 0; }
        .cred-value { font-family: 'SFMono-Regular', Consolas, 'Courier New', monospace; font-size: 14px; color: #111827; font-weight: 500; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 10px; flex: 1; word-break: break-all; }
        .cred-value.password { background: #fff7ed; border-color: #fed7aa; color: #92400e; font-weight: 700; letter-spacing: 1px; }
        .warning-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; display: flex; gap: 12px; align-items: flex-start; margin-bottom: 24px; }
        .warning-icon { font-size: 18px; line-height: 1; flex-shrink: 0; }
        .warning-box p { color: #92400e; font-size: 13px; margin: 0; }
        .btn { display: block; text-align: center; background: #4f46e5; color: #fff; font-size: 14px; font-weight: 600; padding: 13px 24px; border-radius: 10px; text-decoration: none; margin: 0 0 24px; }
        .btn:hover { background: #4338ca; }
        .footer { padding: 20px 40px 32px; text-align: center; }
        .footer p { color: #9ca3af; font-size: 12px; line-height: 1.5; }
        .divider { height: 1px; background: #f3f4f6; margin: 0 40px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        {{-- Header --}}
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Plataforma de automatización con WhatsApp</p>
        </div>

        {{-- Body --}}
        <div class="body">
            <p class="greeting">¡Hola, {{ $user->name }}! 👋</p>

            <p>
                Has sido registrado en <strong>{{ config('app.name') }}</strong> con el rol
                <span class="role-badge">{{ $rol }}</span>.
                A continuación encontrarás tus credenciales de acceso:
            </p>

            <div class="credentials-box">
                <h3>Tus credenciales</h3>
                <div class="cred-row">
                    <span class="cred-label">Email</span>
                    <span class="cred-value">{{ $user->email }}</span>
                </div>
                <div class="cred-row">
                    <span class="cred-label">Contraseña</span>
                    <span class="cred-value password">{{ $plainPassword }}</span>
                </div>
            </div>

            <div class="warning-box">
                <span class="warning-icon">⚠️</span>
                <p>
                    Esta es una <strong>contraseña temporal</strong>. Por seguridad, debes
                    cambiarla la primera vez que inicies sesión. Dirígete a
                    <strong>Mi Perfil → Seguridad</strong> para actualizarla.
                </p>
            </div>

            <a href="{{ url('/login') }}" class="btn">Iniciar sesión ahora →</a>

            <p style="font-size:13px; color:#6b7280;">
                Si no esperabas este correo o tienes alguna duda, contáctanos respondiendo a este mensaje.
            </p>
        </div>

        <div class="divider"></div>

        {{-- Footer --}}
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }} · Este mensaje fue generado automáticamente.</p>
        </div>

    </div>
</div>
</body>
</html>
