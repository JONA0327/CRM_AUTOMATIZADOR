<x-admin-layout title="Números Conectados">

{{-- ══ MODAL CONFIGURACIÓN DE INSTANCIA ══ --}}
<template x-teleport="body">
    <div x-data="instanciaConfig()"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cerrar()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Configurar instancia</h3>
                        <p class="text-xs text-gray-400" x-text="instancia"></p>
                    </div>
                </div>
                <button @click="cerrar()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Cuerpo --}}
            <div class="px-6 py-5 space-y-6">

                {{-- Alerta de error --}}
                <div x-show="errorMsg" x-transition
                     class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="errorMsg"></span>
                </div>

                {{-- Alerta de éxito --}}
                <div x-show="okMsg" x-transition
                     class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="okMsg"></span>
                </div>

                {{-- Skeleton de carga --}}
                <template x-if="cargando">
                    <div class="space-y-4 animate-pulse">
                        <div class="h-4 bg-gray-100 rounded w-1/3"></div>
                        <div class="h-10 bg-gray-100 rounded"></div>
                        <div class="h-4 bg-gray-100 rounded w-1/4 mt-4"></div>
                        <div class="grid grid-cols-2 gap-3">
                            <template x-for="i in 6"><div class="h-8 bg-gray-100 rounded"></div></template>
                        </div>
                    </div>
                </template>

                <template x-if="!cargando">
                    <div class="space-y-6">

                        {{-- ── WEBHOOK ── --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Webhook
                            </h4>
                            {{-- URL predefinida (solo lectura) --}}
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">URL del webhook (asignada automáticamente)</label>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 flex items-center gap-2 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-mono text-gray-600 truncate" x-text="webhookUrl"></span>
                                </div>
                                <button type="button"
                                        @click="navigator.clipboard.writeText(webhookUrl).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                        class="flex-shrink-0 px-3 py-2.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-lg transition-colors">
                                    <span x-show="!copied">Copiar</span>
                                    <span x-show="copied">✓ Copiado</span>
                                </button>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">
                                Esta URL es única para esta instancia. Se configura automáticamente al guardar.
                            </p>
                        </div>

                        {{-- ── EVENTOS ── --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                Eventos a escuchar
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="ev in eventosDisponibles" :key="ev.value">
                                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                                           :class="form.events.includes(ev.value) ? 'border-blue-200 bg-blue-50/50' : ''">
                                        <input type="checkbox"
                                               :value="ev.value"
                                               :checked="form.events.includes(ev.value)"
                                               @change="toggleEvento(ev.value)"
                                               class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"/>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-700" x-text="ev.label"></p>
                                            <p class="text-xs text-gray-400" x-text="ev.desc"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- ── SETTINGS ── --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                Comportamiento de la instancia
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="s in settingsDisponibles" :key="s.key">
                                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg border border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                                           :class="form[s.key] ? 'border-emerald-200 bg-emerald-50/50' : ''">
                                        <input type="checkbox"
                                               :checked="form[s.key]"
                                               @change="form[s.key] = !form[s.key]"
                                               class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500"/>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-700" x-text="s.label"></p>
                                            <p class="text-xs text-gray-400" x-text="s.desc"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>

            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-b-2xl">
                <button @click="seleccionarTodos()"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
                    Seleccionar todos los eventos
                </button>
                <div class="flex items-center gap-3">
                    <button @click="cerrar()" type="button"
                            class="px-5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button @click="guardar()" :disabled="guardando"
                            class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <template x-if="guardando">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </template>
                        <template x-if="!guardando">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                        </template>
                        <span x-text="guardando ? 'Guardando...' : 'Guardar configuración'"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Números Conectados</h2>
            <p class="text-sm text-gray-500 mt-0.5">Instancias de WhatsApp activas en Evolution API</p>
        </div>
        <div class="flex items-center gap-3">

            {{-- Toggle Bot --}}
            <form method="POST" action="{{ route('bot.toggle') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all
                               {{ $botActivo
                                   ? 'bg-green-500 hover:bg-green-600 text-white'
                                   : 'bg-gray-200 hover:bg-gray-300 text-gray-700' }}">
                    {{-- Pill switch visual --}}
                    <span class="relative inline-flex w-9 h-5 flex-shrink-0">
                        <span class="block w-full h-full rounded-full transition-colors
                                     {{ $botActivo ? 'bg-white/30' : 'bg-gray-400/40' }}"></span>
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform
                                     {{ $botActivo ? 'translate-x-4' : 'translate-x-0' }}"></span>
                    </span>
                    {{ $botActivo ? 'Bot Encendido' : 'Bot Apagado' }}
                </button>
            </form>

            <a href="{{ route('bot.conectar') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Conectar Número
            </a>
        </div>
    </div>

    {{-- Tabla de instancias --}}
    @if ($instancias->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <h3 class="text-gray-700 font-semibold text-lg mb-2">Sin números conectados</h3>
            <p class="text-gray-400 text-sm mb-6">Escanea un código QR para vincular tu primer número de WhatsApp.</p>
            <a href="{{ route('bot.conectar') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Conectar primer número
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Instancia</th>
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Número / Perfil</th>
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Estado</th>
                        <th class="text-right px-6 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($instancias as $inst)
                        @php
                            // Evolution API v2: campos planos en la raíz del objeto
                            $nombre = data_get($inst, 'name')
                                   ?? data_get($inst, 'instance.instanceName')
                                   ?? data_get($inst, 'instanceName')
                                   ?? '—';

                            // Número: ownerJid (v2) o owner (v1)
                            $owner = data_get($inst, 'ownerJid')
                                  ?? data_get($inst, 'instance.owner')
                                  ?? data_get($inst, 'owner')
                                  ?? null;

                            // Nombre de perfil de WhatsApp
                            $perfil = data_get($inst, 'profileName')
                                   ?? data_get($inst, 'instance.profileName')
                                   ?? null;

                            // Estado: connectionStatus (v2) o status/state (v1)
                            $status = data_get($inst, 'connectionStatus')
                                   ?? data_get($inst, 'instance.connectionStatus')
                                   ?? data_get($inst, 'instance.status')
                                   ?? data_get($inst, 'instance.state')
                                   ?? data_get($inst, 'status')
                                   ?? 'unknown';

                            $conectado  = strtolower($status) === 'open';
                            $conectando = strtolower($status) === 'connecting';

                            $statusLabels = [
                                'open'       => 'Conectado',
                                'close'      => 'Desconectado',
                                'connecting' => 'Conectando...',
                                'unknown'    => 'Desconocido',
                            ];
                            $statusLabel = $statusLabels[strtolower($status)] ?? ucfirst($status);

                            // Número limpio (quita @s.whatsapp.net y similares)
                            $numero = $owner ? preg_replace('/@.*/', '', $owner) : null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Nombre de instancia --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 {{ $conectado ? 'bg-green-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 {{ $conectado ? 'text-green-600' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.122 1.532 5.852L0 24l6.335-1.54A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.65-.515-5.16-1.41l-.37-.22-3.76.914.949-3.659-.242-.376A10 10 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                        </svg>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $nombre }}</span>
                                </div>
                            </td>

                            {{-- Número / Perfil --}}
                            <td class="px-6 py-4">
                                @if ($numero)
                                    <p class="font-semibold text-gray-800">+{{ $numero }}</p>
                                @endif
                                @if ($perfil)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $perfil }}</p>
                                @endif
                                @if (! $numero && ! $perfil)
                                    <span class="text-gray-400 text-sm">Sin información</span>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="px-6 py-4">
                                @if ($conectado)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        Conectado
                                    </span>
                                @elseif ($conectando)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                                        Conectando...
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                        {{ $statusLabel }}
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Botón configurar --}}
                                    <button
                                        onclick="window.dispatchEvent(new CustomEvent('abrir-config', { detail: '{{ $nombre }}' }))"
                                        title="Configurar instancia"
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-500 bg-gray-100 hover:bg-blue-100 hover:text-blue-600 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </button>

                                    {{-- Botón eliminar --}}
                                    <form method="POST"
                                          action="{{ route('bot.eliminar', $nombre) }}"
                                          onsubmit="return confirm('¿Eliminar la instancia «{{ $nombre }}»?\nEsta acción desconectará el número de WhatsApp.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif


<script>
function instanciaConfig() {
    return {
        open:       false,
        instancia:  '',
        cargando:   false,
        guardando:  false,
        errorMsg:   '',
        okMsg:      '',
        webhookUrl: '',
        copied:     false,

        form: {
            events:            [],
            reject_call:       false,
            groups_ignore:     false,
            always_online:     false,
            read_messages:     false,
            read_status:       false,
            sync_full_history: false,
        },

        eventosDisponibles: [
            { value: 'MESSAGES_UPSERT',          label: 'Mensajes nuevos',         desc: 'Cuando llega un mensaje' },
            { value: 'MESSAGES_UPDATE',           label: 'Mensajes actualizados',   desc: 'Leídos, entregados, etc.' },
            { value: 'MESSAGES_DELETE',           label: 'Mensajes eliminados',     desc: 'Cuando se borra un mensaje' },
            { value: 'SEND_MESSAGE',              label: 'Mensajes enviados',       desc: 'Confirmación de envío' },
            { value: 'CONNECTION_UPDATE',         label: 'Estado de conexión',      desc: 'Conectado, desconectado...' },
            { value: 'QRCODE_UPDATED',            label: 'QR actualizado',          desc: 'Nuevo código QR generado' },
            { value: 'CONTACTS_UPSERT',           label: 'Contactos nuevos',        desc: 'Cuando se añade un contacto' },
            { value: 'CONTACTS_UPDATE',           label: 'Contactos actualizados',  desc: 'Cambios en un contacto' },
            { value: 'CHATS_UPSERT',              label: 'Chats nuevos',            desc: 'Cuando se abre un chat nuevo' },
            { value: 'CHATS_UPDATE',              label: 'Chats actualizados',      desc: 'Cambios en un chat existente' },
            { value: 'GROUPS_UPSERT',             label: 'Grupos nuevos',           desc: 'Cuando se une a un grupo' },
            { value: 'GROUP_PARTICIPANTS_UPDATE', label: 'Participantes de grupo',  desc: 'Entradas y salidas de grupo' },
            { value: 'PRESENCE_UPDATE',           label: 'Presencia',               desc: 'En línea / escribiendo...' },
            { value: 'CALL',                      label: 'Llamadas',                desc: 'Cuando recibe una llamada' },
        ],

        settingsDisponibles: [
            { key: 'reject_call',       label: 'Rechazar llamadas',            desc: 'Rechaza automáticamente las llamadas entrantes' },
            { key: 'groups_ignore',     label: 'Ignorar grupos',               desc: 'No procesa mensajes de grupos' },
            { key: 'always_online',     label: 'Siempre en línea',             desc: 'Mantiene el estado como activo' },
            { key: 'read_messages',     label: 'Leer mensajes automáticamente',desc: 'Marca como leído al recibir' },
            { key: 'read_status',       label: 'Leer estados',                 desc: 'Visualiza los estados/historias' },
            { key: 'sync_full_history', label: 'Sincronizar historial',        desc: 'Descarga el historial completo' },
        ],

        init() {
            window.addEventListener('abrir-config', (e) => this.abrir(e.detail));
        },

        async abrir(nombre) {
            this.instancia = nombre;
            this.errorMsg  = '';
            this.okMsg     = '';
            this.cargando  = true;
            this.open      = true;
            document.body.style.overflow = 'hidden';

            try {
                const res  = await fetch(`{{ url('/bot/config') }}/${encodeURIComponent(nombre)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.success) {
                    // URL predefinida del webhook
                    this.webhookUrl  = data.webhook_url ?? data.webhook?.url ?? '';
                    // Evolution v2 devuelve events dentro de webhook.webhook.events o webhook.events
                    this.form.events = data.webhook?.webhook?.events ?? data.webhook?.events ?? [];

                    // Settings — Evolution v2 devuelve camelCase
                    const s = data.settings ?? {};
                    this.form.reject_call       = s.rejectCall      ?? s.reject_call       ?? false;
                    this.form.groups_ignore     = s.groupsIgnore    ?? s.groups_ignore     ?? false;
                    this.form.always_online     = s.alwaysOnline    ?? s.always_online     ?? false;
                    this.form.read_messages     = s.readMessages    ?? s.read_messages     ?? false;
                    this.form.read_status       = s.readStatus      ?? s.read_status       ?? false;
                    this.form.sync_full_history = s.syncFullHistory ?? s.sync_full_history ?? false;
                }
            } catch (e) {
                this.errorMsg = 'No se pudo cargar la configuración actual.';
            } finally {
                this.cargando = false;
            }
        },

        toggleEvento(valor) {
            const idx = this.form.events.indexOf(valor);
            if (idx === -1) this.form.events.push(valor);
            else this.form.events.splice(idx, 1);
        },

        seleccionarTodos() {
            const todos = this.eventosDisponibles.map(e => e.value);
            this.form.events = this.form.events.length === todos.length ? [] : [...todos];
        },

        async guardar() {
            this.guardando = true;
            this.errorMsg  = '';
            this.okMsg     = '';

            try {
                const res  = await fetch(`{{ url('/bot/config') }}/${encodeURIComponent(this.instancia)}`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();

                if (data.success) {
                    this.okMsg = 'Configuración guardada correctamente.';
                    setTimeout(() => this.cerrar(), 1500);
                } else {
                    this.errorMsg = data.message ?? 'Error al guardar la configuración.';
                }
            } catch (e) {
                this.errorMsg = 'Error de red. Verifica tu conexión.';
            } finally {
                this.guardando = false;
            }
        },

        cerrar() {
            this.open = false;
            document.body.style.overflow = '';
        },
    };
}
</script>

</x-admin-layout>
