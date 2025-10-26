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
            body {
                font-family: 'Inter', sans-serif;
                width: 100vw !important;
                max-width: 100vw !important;
                overflow-x: hidden;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .main-content-desktop {
                width: calc(100vw - 240px) !important;
                max-width: calc(100vw - 240px) !important;
            }
            
            main {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .w-full {
                width: 100% !important;
            }
            
            .max-w-7xl, .max-w-6xl, .max-w-5xl, .max-w-4xl {
                max-width: none !important;
                width: 100% !important;
            }
            
            .px-1, .px-2, .px-4, .px-6, .px-8, .sm\\:px-6, .lg\\:px-8 {
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }
            
            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0.25rem !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex w-full">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content Area -->
            <div class="main-content main-content-desktop flex-1 min-h-screen w-full">
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-gray-200 shadow-sm">
                        <div class="py-4 px-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    {{ $header }}
                                </div>
                                <div class="flex items-center space-x-3 text-sm text-gray-500">
                                    <!-- Current Time -->
                                    <span class="hidden sm:block">
                                        {{ now()->format('d/M/Y H:i') }}
                                    </span>
                                    <!-- Notifications -->
                                    <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition duration-150 ease-in-out">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10 3L4 21l7-3 7 3L10 3z"></path>
                                        </svg>
                                        <span class="absolute -top-1 -right-1 block h-3 w-3 rounded-full bg-red-400"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 w-full overflow-x-hidden p-4">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
