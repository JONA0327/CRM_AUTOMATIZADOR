<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BotAutomate') }} — Acceso</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-950 text-gray-100 min-h-screen">
        <div class="min-h-screen flex">

            {{-- ── Panel izquierdo — Marketing ── --}}
            <div class="hidden lg:flex lg:w-1/2 relative flex-col items-center justify-center p-12 overflow-hidden
                        bg-gradient-to-br from-indigo-950 via-gray-900 to-gray-950">

                {{-- Patrón de fondo --}}
                <div class="absolute inset-0 opacity-5"
                     style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0);
                            background-size: 40px 40px;">
                </div>

                {{-- Glows decorativos --}}
                <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Contenido centrado --}}
                <div class="relative z-10 flex flex-col items-center text-center max-w-md">

                    {{-- Logo --}}
                    <div class="relative mb-6">
                        <div class="absolute inset-0 -m-10 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                        <img src="{{ asset('Gemini_Generated_Image_a8lgzta8lgzta8lg-removebg-preview.png') }}"
                             alt="{{ config('app.name') }}"
                             class="relative w-64 h-64 object-contain drop-shadow-[0_0_40px_rgba(129,140,248,0.7)]">
                    </div>

                    {{-- Tagline --}}
                    <p class="text-indigo-300 text-base mb-10">
                        Automatiza tu atención al cliente con IA
                    </p>

                    {{-- Características en grid 2 columnas --}}
                    <div class="grid grid-cols-2 gap-3 w-full text-left">
                        <div class="flex items-start gap-3 bg-white/5 border border-white/5 rounded-xl p-3">
                            <div class="flex-shrink-0 w-7 h-7 bg-indigo-500/20 rounded-lg flex items-center justify-center mt-0.5">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-xs">No-code</p>
                                <p class="text-gray-500 text-xs mt-0.5">Catálogos dinámicos</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white/5 border border-white/5 rounded-xl p-3">
                            <div class="flex-shrink-0 w-7 h-7 bg-indigo-500/20 rounded-lg flex items-center justify-center mt-0.5">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-xs">Bot 24/7</p>
                                <p class="text-gray-500 text-xs mt-0.5">WhatsApp con IA</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white/5 border border-white/5 rounded-xl p-3">
                            <div class="flex-shrink-0 w-7 h-7 bg-indigo-500/20 rounded-lg flex items-center justify-center mt-0.5">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-xs">Multi-tenant</p>
                                <p class="text-gray-500 text-xs mt-0.5">BD aislada por negocio</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 bg-white/5 border border-white/5 rounded-xl p-3">
                            <div class="flex-shrink-0 w-7 h-7 bg-indigo-500/20 rounded-lg flex items-center justify-center mt-0.5">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-xs">Tiempo real</p>
                                <p class="text-gray-500 text-xs mt-0.5">WebSockets Reverb</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Copyright --}}
                <p class="absolute bottom-5 text-gray-700 text-xs z-10">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </p>
            </div>

            {{-- ── Panel derecho — Login ── --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-950">
                <div class="w-full max-w-sm">

                    {{-- Logo móvil --}}
                    <div class="lg:hidden flex justify-center mb-10">
                        <div class="relative inline-block">
                            <div class="absolute inset-0 -m-4 bg-indigo-500/20 rounded-full blur-xl pointer-events-none"></div>
                            <img src="{{ asset('Gemini_Generated_Image_a8lgzta8lgzta8lg-removebg-preview.png') }}"
                                 alt="{{ config('app.name') }}"
                                 class="relative w-44 h-44 object-contain drop-shadow-[0_0_24px_rgba(129,140,248,0.6)]">
                        </div>
                    </div>

                    @auth
                        {{-- Ya autenticado --}}
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-500/15 border border-green-500/30 rounded-2xl flex items-center justify-center mx-auto mb-5">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-white mb-2">Sesión activa</h2>
                            <p class="text-gray-400 text-sm mb-8">Ya tienes sesión iniciada en el sistema.</p>
                            <a href="{{ url('/dashboard') }}"
                               class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-900/40 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Ir al Dashboard
                            </a>
                        </div>
                    @else
                        {{-- Formulario de login --}}

                        {{-- Estado de sesión --}}
                        @if (session('status'))
                            <div class="mb-5 text-sm text-green-400 bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-white mb-1">Bienvenido</h2>
                            <p class="text-gray-500 text-sm">Inicia sesión para acceder al panel</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                                    Correo electrónico
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="nombre@ejemplo.com"
                                        class="w-full pl-10 pr-4 py-2.5 bg-gray-800 border border-white/10 rounded-xl text-sm text-gray-100 placeholder-gray-600
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition
                                               @error('email') border-red-500/50 bg-red-500/5 @enderror"
                                    >
                                </div>
                                @error('email')
                                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Contraseña --}}
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-sm font-medium text-gray-300">
                                        Contraseña
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}"
                                           class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                        class="w-full pl-10 pr-4 py-2.5 bg-gray-800 border border-white/10 rounded-xl text-sm text-gray-100 placeholder-gray-600
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition
                                               @error('password') border-red-500/50 bg-red-500/5 @enderror"
                                    >
                                </div>
                                @error('password')
                                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Recuérdame --}}
                            <div class="flex items-center gap-2">
                                <input
                                    id="remember_me"
                                    name="remember"
                                    type="checkbox"
                                    class="rounded border-gray-600 bg-gray-800 text-indigo-600 shadow-sm focus:ring-indigo-500 focus:ring-offset-gray-950"
                                >
                                <label for="remember_me" class="text-sm text-gray-400">Mantener sesión iniciada</label>
                            </div>

                            {{-- Botón submit --}}
                            <button
                                type="submit"
                                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-900/40
                                       transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-950"
                            >
                                Iniciar sesión
                            </button>
                        </form>

                        {{-- Nota --}}
                        <p class="text-center text-xs text-gray-600 mt-8">
                            El acceso es únicamente para cuentas autorizadas.<br>
                            Contacta al administrador si necesitas acceso.
                        </p>
                    @endauth

                </div>
            </div>

        </div>
    </body>
</html>
