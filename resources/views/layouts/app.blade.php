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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/css/layout-optimized.css', 'resources/js/app.js', 'resources/js/sidebar.js'])

        <style>
            :root {
                color-scheme: light;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-sky-50 text-slate-900">
        <div class="app-shell">
            @include('layouts.sidebar')

            <div class="main-content-area">
                @isset($header)
                    <header class="page-header">
                        <div class="page-header-inner">
                            <div class="page-header-content">
                                {{ $header }}
                            </div>
                            <div class="page-header-meta">
                                <span class="page-header-date hidden sm:block">
                                    {{ now()->format('d M Y • H:i') }}
                                </span>
                                <button class="page-header-alert" type="button" aria-label="Abrir notificaciones">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10 3L4 21l7-3 7 3L10 3z"></path>
                                    </svg>
                                    <span class="page-header-alert-indicator"></span>
                                </button>
                            </div>
                        </div>
                    </header>
                @endisset

                <main class="page-container">
                    <div class="page-stack">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>

