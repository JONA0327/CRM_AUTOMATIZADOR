<x-admin-layout title="Números Conectados">

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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-admin-layout>
