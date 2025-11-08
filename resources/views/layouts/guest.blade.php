<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>CRM_AUTOMATIZADOR</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Heroicons for modern icons -->
        <script src="https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/layout-optimized.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="guest-body font-sans antialiased">
        <div class="guest-body__background" aria-hidden="true">
            <div class="molecule molecule--one">
                <svg viewBox="0 0 260 260" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="molecule__ring" cx="130" cy="130" r="82" />
                    <line class="molecule__link" x1="52" y1="120" x2="115" y2="70" />
                    <line class="molecule__link" x1="150" y1="200" x2="210" y2="150" />
                    <circle class="molecule__node" cx="45" cy="125" r="14" />
                    <circle class="molecule__node" cx="115" cy="65" r="11" />
                    <circle class="molecule__node" cx="152" cy="205" r="13" />
                    <circle class="molecule__node" cx="212" cy="148" r="10" />
                    <circle class="molecule__core" cx="130" cy="130" r="24" />
                </svg>
            </div>
            <div class="molecule molecule--two">
                <svg viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="molecule__ring" cx="120" cy="120" r="90" />
                    <line class="molecule__link" x1="70" y1="60" x2="130" y2="120" />
                    <line class="molecule__link" x1="180" y1="175" x2="120" y2="120" />
                    <circle class="molecule__node" cx="65" cy="58" r="10" />
                    <circle class="molecule__node" cx="185" cy="180" r="12" />
                    <circle class="molecule__node" cx="120" cy="120" r="14" />
                </svg>
            </div>
            <div class="molecule molecule--three">
                <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="molecule__ring" cx="100" cy="100" r="70" />
                    <line class="molecule__link" x1="45" y1="150" x2="100" y2="100" />
                    <line class="molecule__link" x1="155" y1="55" x2="100" y2="100" />
                    <circle class="molecule__node" cx="42" cy="152" r="11" />
                    <circle class="molecule__node" cx="158" cy="52" r="9" />
                    <circle class="molecule__core" cx="100" cy="100" r="18" />
                </svg>
            </div>
        </div>
        <div class="guest-wrapper">
            <!-- Logo y Título -->
            <div class="guest-brand">
                <div class="guest-brand__logo">
                    <svg class="guest-brand__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="guest-brand__copy">
                    <h1 class="guest-brand__title">CRM_AUTOMATIZADOR</h1>
                    <p class="guest-brand__subtitle">Sistema inteligente para impulsar tus relaciones con clientes</p>
                </div>
            </div>

            <!-- Contenedor del formulario -->
            <div class="guest-card">
                <div class="guest-card__surface">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="guest-footer">
                © 2025 CRM_AUTOMATIZADOR. Todos los derechos reservados.
            </div>
        </div>
    </body>
</html>
