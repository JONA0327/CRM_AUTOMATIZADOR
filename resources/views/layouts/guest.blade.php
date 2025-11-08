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
    <body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-blue-100 min-h-screen">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
            <!-- Logo y Título -->
            <div class="text-center mb-8">
                <!-- Icono CRM moderno -->
                <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">CRM_AUTOMATIZADOR</h1>
                <p class="text-gray-600 text-sm">Sistema de Gestión de Relaciones con Clientes</p>
            </div>

            <!-- Contenedor del formulario -->
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-6 text-sm text-gray-500">
                    © 2025 CRM_AUTOMATIZADOR. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </body>
</html>
