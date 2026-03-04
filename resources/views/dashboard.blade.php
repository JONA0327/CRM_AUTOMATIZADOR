<x-admin-layout title="Dashboard">
@php
    try {
        $tenancyActiva = tenancy()->tenant !== null
            && \Illuminate\Support\Facades\Schema::hasTable('catalog_modules');
    } catch (\Throwable) {
        $tenancyActiva = false;
    }
@endphp

    {{-- ── Mensajes flash ── --}}
    @if(session('error'))
        <div role="alert" aria-live="assertive" class="mb-6 bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl flex justify-between items-center">
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div role="status" aria-live="polite" class="mb-6 bg-blue-500/10 border border-blue-500/30 text-blue-400 px-4 py-3 rounded-xl">
            {{ session('info') }}
        </div>
    @endif

    {{-- ── Tarjetas de estadísticas ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- Módulos --}}
        <div class="bg-gray-900 rounded-xl border border-white/5 p-6 hover:border-indigo-500/30 transition-colors">
            <div class="w-10 h-10 bg-indigo-500/15 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z
                             M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z
                             M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z
                             M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-white">{{ $tenancyActiva ? $nav_modulos->count() : '—' }}</p>
            <p class="text-sm text-gray-500 mt-1">Módulos activos</p>
        </div>

        {{-- Registros totales --}}
        <div class="bg-gray-900 rounded-xl border border-white/5 p-6 hover:border-blue-500/30 transition-colors">
            <div class="w-10 h-10 bg-blue-500/15 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-white">
                {{ $tenancyActiva ? \App\Models\CatalogRecord::count() : '—' }}
            </p>
            <p class="text-sm text-gray-500 mt-1">Registros en catálogos</p>
        </div>

        {{-- Conversaciones --}}
        <div class="bg-gray-900 rounded-xl border border-white/5 p-6 hover:border-purple-500/30 transition-colors">
            <div class="w-10 h-10 bg-purple-500/15 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-white">
                {{ $tenancyActiva ? \App\Models\Conversation::count() : '—' }}
            </p>
            <p class="text-sm text-gray-500 mt-1">Conversaciones</p>
        </div>

        {{-- Estado del Bot --}}
        <div class="bg-gray-900 rounded-xl border border-white/5 p-6 hover:border-orange-500/30 transition-colors">
            <div class="w-10 h-10 bg-orange-500/15 rounded-lg flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-1"/>
                </svg>
            </div>
            @php $botActivo = $tenancyActiva && \App\Models\Configuracion::get('bot_activo', '0') === '1'; @endphp
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $botActivo ? 'bg-green-400 animate-pulse' : 'bg-red-500' }}"></div>
                <p class="text-sm font-semibold text-gray-200">{{ $botActivo ? 'Activo' : 'Inactivo' }}</p>
            </div>
            <p class="text-sm text-gray-500 mt-1">Estado del Bot</p>
        </div>

    </div>

    {{-- ── Accesos Rápidos ── --}}
    <div class="bg-gray-900 rounded-xl border border-white/5 p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-5">Accesos Rápidos</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

            <a href="{{ route('admin.modulos.index') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 hover:border-indigo-500/40 transition-colors group">
                <span class="text-2xl group-hover:scale-110 transition-transform">⚙️</span>
                <span class="text-xs font-semibold text-indigo-400 text-center leading-tight">Módulos</span>
            </a>

            <a href="{{ route('bot.conversaciones') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/20 hover:border-purple-500/40 transition-colors group">
                <span class="text-2xl group-hover:scale-110 transition-transform">💬</span>
                <span class="text-xs font-semibold text-purple-400 text-center leading-tight">Conversaciones</span>
            </a>

            <a href="{{ route('bot.index') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-xl bg-orange-500/10 hover:bg-orange-500/20 border border-orange-500/20 hover:border-orange-500/40 transition-colors group">
                <span class="text-2xl group-hover:scale-110 transition-transform">🤖</span>
                <span class="text-xs font-semibold text-orange-400 text-center leading-tight">Bot</span>
            </a>

            <a href="{{ route('bot.conectar') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-xl bg-teal-500/10 hover:bg-teal-500/20 border border-teal-500/20 hover:border-teal-500/40 transition-colors group">
                <span class="text-2xl group-hover:scale-110 transition-transform">📱</span>
                <span class="text-xs font-semibold text-teal-400 text-center leading-tight">Conectar Número</span>
            </a>

            <a href="{{ route('configuracion.index') }}"
               class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-800 hover:bg-gray-700 border border-white/5 hover:border-white/10 transition-colors group">
                <span class="text-2xl group-hover:scale-110 transition-transform">🔧</span>
                <span class="text-xs font-semibold text-gray-400 text-center leading-tight">Configuración</span>
            </a>

        </div>
    </div>

    {{-- ── Módulos del Catálogo ── --}}
    @if($tenancyActiva && $nav_modulos->count() > 0)
    <div class="bg-gray-900 rounded-xl border border-white/5 p-6">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Catálogos</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($nav_modulos as $mod)
            <a href="{{ route('catalogo.index', $mod->slug) }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-white/5 hover:border-indigo-500/30 hover:bg-indigo-500/5 transition-colors group">
                <span class="text-2xl">{{ $mod->icono ?? '📋' }}</span>
                <span class="text-sm font-medium text-gray-300 group-hover:text-indigo-300">{{ $mod->nombre }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @elseif($tenancyActiva)
    <div class="bg-gray-900 rounded-xl border border-dashed border-white/10 p-10 text-center">
        <div class="text-4xl mb-3">📋</div>
        <p class="text-gray-500 text-sm">Aún no hay módulos creados.</p>
        <a href="{{ route('admin.modulos.index') }}"
           class="mt-3 inline-block text-sm text-indigo-400 hover:text-indigo-300 font-medium">
            Crear primer módulo →
        </a>
    </div>
    @else
    <div class="bg-gray-900 rounded-xl border border-dashed border-white/10 p-10 text-center">
        <div class="text-4xl mb-3">🏢</div>
        <p class="text-gray-500 text-sm">Sin tenant activo. Usa el panel de administración global para crear uno.</p>
    </div>
    @endif

</x-admin-layout>
