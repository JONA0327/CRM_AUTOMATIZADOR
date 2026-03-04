<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="BotAutomate — {{ $title ?? 'Panel de administración' }}">
    <title>BotAutomate — {{ $title ?? 'Dashboard' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-950 text-gray-100" x-data="{ sidebarOpen: false }">

{{-- Skip link — accesibilidad teclado --}}
<a href="#main-content"
   class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[9999] focus:px-4 focus:py-2 focus:bg-white focus:text-blue-700 focus:font-semibold focus:rounded-lg focus:shadow-lg focus:ring-2 focus:ring-blue-500">
    Saltar al contenido principal
</a>

<div class="flex h-screen overflow-hidden">

    {{-- Overlay móvil --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-black/50 lg:hidden"
    ></div>

    {{-- ============================================================
        SIDEBAR
    ============================================================ --}}
    <aside
        id="sidebar"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col
               bg-gradient-to-b from-indigo-950 via-gray-900 to-gray-950
               border-r border-white/5
               transform transition-transform duration-300 ease-in-out
               lg:translate-x-0 lg:static lg:inset-0"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-white/5">
            <img src="{{ asset('Gemini_Generated_Image_a8lgzta8lgzta8lg-removebg-preview.png') }}"
                 alt="{{ config('app.name') }}"
                 class="w-16 h-16 object-contain flex-shrink-0 drop-shadow-[0_0_10px_rgba(129,140,248,0.6)]">
            <span class="text-white font-bold text-lg truncate">BotAutomate</span>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 px-3 py-5 overflow-y-auto space-y-0.5 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden" aria-label="Navegación principal">

            @php
                $authUser      = auth()->user();
                $isSuperAdmin  = $authUser?->hasRole('super_admin');
                $isAnfitrion   = $authUser?->hasRole('anfitrion') || $isSuperAdmin;
                $hasTenant     = tenancy()->tenant !== null;

                $sidebarModulos = collect();
                try {
                    if ($hasTenant && \Illuminate\Support\Facades\Schema::hasTable('catalog_modules')) {
                        $sidebarModulos = \App\Models\CatalogModule::where('activo', true)->orderBy('orden')->get(['id','nombre','slug','icono']);
                    }
                } catch (\Throwable) {}
            @endphp

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('dashboard') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z
                             M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z
                             M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z
                             M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Dashboard
            </a>

            {{-- ── Super Admin: Gestión de Negocios ── --}}
            @if($isSuperAdmin)
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest px-3 pt-5 pb-1.5">
                    Super Admin
                </p>

                <a href="{{ route('admin.negocios.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('admin.negocios.*') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Negocios
                </a>
            @endif

            {{-- ── Catálogos dinámicos — todos los roles con tenant ── --}}
            @if($hasTenant && $sidebarModulos->count() > 0)
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest px-3 pt-5 pb-1.5">
                    Catálogos
                </p>
                @foreach($sidebarModulos as $mod)
                    <a href="{{ route('catalogo.index', $mod->slug) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->is('catalogo/' . $mod->slug . '*') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                        <span class="text-base">{{ $mod->icono ?? '📋' }}</span>
                        {{ $mod->nombre }}
                    </a>
                @endforeach
            @endif

            {{-- ── Administración — solo anfitrion ── --}}
            @if($isAnfitrion && $hasTenant)
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest px-3 pt-5 pb-1.5">
                    Administración
                </p>

                <a href="{{ route('admin.modulos.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('admin.modulos.*') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Módulos (no-code)
                </a>

                <a href="{{ route('configuracion.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('configuracion.*') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    Configuración
                </a>

                <a href="{{ route('colaboradores.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('colaboradores.*') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Colaboradores
                </a>
            @endif

            {{-- ── WhatsApp Bot — todos los roles con tenant ── --}}
            @if($hasTenant)
                <p class="text-gray-500 text-[10px] font-semibold uppercase tracking-widest px-3 pt-5 pb-1.5">
                    WhatsApp Bot
                </p>

                <a href="{{ route('bot.conversaciones') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('bot.conversaciones') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Conversaciones
                </a>

                @if($isAnfitrion)
                    <a href="{{ route('bot.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('bot.index') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"/>
                        </svg>
                        Instancias Bot
                    </a>

                    <a href="{{ route('bot.conectar') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                              {{ request()->routeIs('bot.conectar') ? 'bg-indigo-600/20 text-indigo-300' : 'text-gray-400 hover:bg-white/5 hover:text-gray-300' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Conectar Número
                    </a>
                @endif
            @endif

        </nav>

        {{-- Versión --}}
        <div class="px-5 py-2 text-center">
            <span class="text-gray-600 text-[10px] font-medium tracking-widest">v4.1.2</span>
        </div>

        {{-- Usuario (parte inferior) --}}
        <div class="px-4 py-4 border-t border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-600/30 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-200 text-sm font-semibold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-100 text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        @php
                            if (Auth::user()->hasRole('super_admin')) {
                                $roleLabel = ['Super Admin', 'bg-yellow-400/30 text-yellow-200'];
                            } elseif (Auth::user()->hasRole('anfitrion')) {
                                $roleLabel = ['Anfitrión', 'bg-green-400/30 text-green-200'];
                            } elseif (Auth::user()->hasRole('colaborador')) {
                                $roleLabel = ['Colaborador', 'bg-blue-300/30 text-blue-100'];
                            } else {
                                $roleLabel = ['Sin rol', 'bg-white/10 text-blue-200'];
                            }
                        @endphp
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $roleLabel[1] }}">
                            {{ $roleLabel[0] }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" aria-label="Cerrar sesión"
                            class="text-gray-500 hover:text-gray-200 transition-colors p-1 rounded focus-visible:ring-2 focus-visible:ring-indigo-500">
                        <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-gray-900/80 backdrop-blur border-b border-white/5 flex-shrink-0">
            <div class="flex items-center justify-between h-16 px-6">

                {{-- Hamburger (móvil) + título --}}
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true"
                            aria-label="Abrir menú de navegación"
                            :aria-expanded="sidebarOpen.toString()"
                            aria-controls="sidebar"
                            class="lg:hidden text-gray-500 hover:text-gray-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded">
                        <svg class="w-6 h-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-100">{{ $title ?? 'Dashboard' }}</h1>
                </div>

                {{-- Dropdown de usuario --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-haspopup="menu"
                            aria-controls="user-dropdown"
                            class="flex items-center gap-2 text-sm text-gray-300 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg px-1">
                        <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center" aria-hidden="true">
                            <span class="text-white text-xs font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        </div>
                        <span class="hidden sm:block font-medium">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="user-dropdown"
                         role="menu"
                         x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-xl shadow-xl border border-white/10 py-1 z-50">
                        <a href="{{ route('profile.edit') }}"
                           role="menuitem"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-200 hover:bg-white/5 focus-visible:outline-none focus-visible:bg-white/5">
                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mi Perfil
                        </a>
                        <div class="h-px bg-white/10 my-1" role="separator"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    role="menuitem"
                                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 focus-visible:outline-none focus-visible:bg-red-500/10">
                                <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </header>

        {{-- Área de contenido scrollable --}}
        <main id="main-content" class="flex-1 overflow-y-auto bg-gray-950 p-6" tabindex="-1">
            {{ $slot }}
        </main>

    </div>
</div>

@stack('scripts')
</body>
</html>
