<x-admin-layout :title="($modulo->icono ?? '📋') . ' ' . $modulo->nombre">

    <div x-data="catalogoCrud(@js([
        'module'                  => $modulo->slug,
        'campos'                  => $fields->toArray(),
        'records'                 => $records->items(),
        'tienePromptVerificacion' => $tienePromptVerificacion,
    ]))" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Notificación flash --}}
            <div x-show="flash.msg" x-cloak
                 :class="flash.ok ? 'bg-green-100 border-green-400 text-green-800' : 'bg-red-100 border-red-400 text-red-800'"
                 class="mb-4 border px-4 py-3 rounded-lg flex justify-between items-center">
                <span x-text="flash.msg"></span>
                <button @click="flash.msg=''" class="ml-4 font-bold">✕</button>
            </div>

            {{-- Errores de validación --}}
            <div x-show="errores.length > 0" x-cloak class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    <template x-for="err in errores" :key="err">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>

            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">{{ $records->total() }} registro(s)</span>
                    {{-- Toggle tabla / cards --}}
                    <div class="flex items-center bg-gray-100 rounded-lg p-0.5">
                        <button @click="setVista('tabla')" title="Vista tabla"
                                :class="vista === 'tabla' ? 'bg-white shadow text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
                                class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                            </svg>
                        </button>
                        <button @click="setVista('cards')" title="Vista tarjetas"
                                :class="vista === 'cards' ? 'bg-white shadow text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
                                class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                    </div>
                    {{-- Gear: configure cards (visible only in cards view) --}}
                    <button x-show="vista === 'cards'" @click="openCardConfig()"
                            title="Configurar tarjetas"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
                <button @click="abrirModal(null)"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    + Nuevo registro
                </button>
            </div>

            {{-- Tabla --}}
            <div x-show="vista === 'tabla'" class="bg-indigo-50 dark:bg-indigo-900/40 rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-800/60">
                            <tr>
                                @foreach($fields as $field)
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ $field->nombre }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($records as $record)
                                <tr class="hover:bg-white/[0.03] transition">
                                    @foreach($fields as $field)
                                        <td class="px-4 py-3 text-gray-300 align-top">
                                            @php $val = $record->datos[$field->slug] ?? null; @endphp

                                            @if($field->tipo === 'id' && $val)
                                                <code class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded font-mono">
                                                    {{ $val }}
                                                </code>

                                            @elseif($field->tipo === 'date' && $val)
                                                {{ \Carbon\Carbon::parse($val)->format('d/m/Y') }}

                                            @elseif($field->tipo === 'multiselect' && $val)
                                                <div class="flex flex-wrap gap-1 max-h-28 overflow-y-auto">
                                                    @foreach((array)$val as $item)
                                                        <span class="inline-block bg-blue-900/40 border border-blue-700/40 text-blue-300 text-xs px-2 py-0.5 rounded-full">
                                                            {{ $item }}
                                                        </span>
                                                    @endforeach
                                                </div>

                                            @elseif($field->tipo === 'category_select' && $val)
                                                @php $cs = is_array($val) ? $val : []; @endphp
                                                @if(!empty($cs['categoria']))
                                                    <div class="flex flex-wrap items-center gap-1">
                                                        <span class="text-xs text-gray-500 font-medium">{{ $cs['categoria'] }}:</span>
                                                        @foreach($cs['items'] ?? [] as $item)
                                                            <span class="inline-block bg-indigo-900/40 border border-indigo-700/50 text-indigo-300 text-xs px-2 py-0.5 rounded-full">{{ $item }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                            @elseif($field->tipo === 'tags' && $val)
                                                <div class="flex flex-wrap gap-1 max-h-28 overflow-y-auto">
                                                    @foreach((array)$val as $tag)
                                                        <span class="inline-block bg-purple-900/40 border border-purple-700/40 text-purple-300 text-xs px-2 py-0.5 rounded-full">
                                                            {{ $tag }}
                                                        </span>
                                                    @endforeach
                                                </div>

                                            @elseif($field->tipo === 'relation' && $val)
                                                @if(is_array($val))
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($val as $rid)
                                                            <span class="inline-block bg-indigo-900/40 border border-indigo-700/40 text-indigo-300 text-xs px-2 py-0.5 rounded-full">
                                                                ID: {{ $rid }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="inline-block bg-indigo-900/40 border border-indigo-700/40 text-indigo-300 text-xs px-2 py-0.5 rounded-full">
                                                        ID: {{ $val }}
                                                    </span>
                                                @endif

                                            @elseif($field->tipo === 'phone' && $val)
                                                <span>{{ $val }}</span>

                                            @elseif($field->tipo === 'url' && $val)
                                                <a href="{{ $val }}" target="_blank" rel="noopener"
                                                   class="text-blue-400 hover:text-blue-300 hover:underline text-xs flex items-center gap-1">
                                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                    {{ Str::limit($val, 40) }}
                                                </a>

                                            @elseif($field->tipo === 'file' && $val)
                                                @php
                                                    $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
                                                    $fileUrl = (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/'))
                                                        ? $val
                                                        : asset('storage/' . $val);
                                                @endphp
                                                @if(in_array($ext, ['jpg','jpeg','png','gif','webp','svg','bmp']))
                                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener">
                                                        <img src="{{ $fileUrl }}"
                                                             class="h-12 w-12 object-cover rounded border border-white/10 hover:opacity-80 transition"
                                                             loading="lazy" alt="{{ $field->nombre }}">
                                                    </a>
                                                @elseif(in_array($ext, ['mp4','webm','mov','avi','mkv']))
                                                    <video src="{{ $fileUrl }}"
                                                           class="h-12 w-20 object-cover rounded border border-white/10"
                                                           controls muted playsinline></video>
                                                @else
                                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener"
                                                       class="text-indigo-400 hover:text-indigo-300 hover:underline text-xs flex items-center gap-1">
                                                        📎 Descargar
                                                    </a>
                                                @endif

                                            @else
                                                {{ is_array($val) ? implode(', ', $val) : (Str::limit($val, 60) ?? '—') }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @if($campoPhone)
                                            <button @click="verificarWhatsapp({{ $record->id }}, '{{ $campoPhone->slug }}')"
                                                    title="Enviar verificación por WhatsApp"
                                                    class="inline-flex items-center gap-1 text-green-600 hover:text-green-800 text-xs font-medium mr-3 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                                </svg>
                                                Verificar
                                            </button>
                                        @endif
                                        <button @click="abrirModal({{ $record->id }}, {{ json_encode($record->datos) }})"
                                                class="text-indigo-400 hover:text-indigo-300 text-xs font-medium mr-3">
                                            Editar
                                        </button>
                                        <button @click="eliminar({{ $record->id }})"
                                                class="text-red-500 hover:text-red-700 text-xs font-medium">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $fields->count() + 2 }}" class="px-4 py-12 text-center text-gray-400">
                                        <div class="text-4xl mb-2">{{ $modulo->icono ?? '📋' }}</div>
                                        <p>No hay registros aún. Crea el primero.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($records->hasPages())
                    <div class="px-4 py-3 border-t dark:border-gray-700">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ VISTA: Cards ═══ --}}
        <div x-show="vista === 'cards'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Grid de cards --}}
            <div x-show="records.length > 0"
                 class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="record in records" :key="record.id">
                    <div @click="abrirDetalle(record)"
                         @keydown.enter="abrirDetalle(record)"
                         @keydown.space.prevent="abrirDetalle(record)"
                         tabindex="0"
                         role="button"
                         :aria-label="'Ver detalle de ' + getCardTitle(record)"
                         class="bg-indigo-50 dark:bg-indigo-900/40 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none">

                        {{-- Media (imagen o video) --}}
                        <template x-if="getCoverMedia(record) && getCoverMedia(record).tipo === 'imagen'">
                            <img :src="getCoverMedia(record).url"
                                 :alt="getCardTitle(record)"
                                 class="w-full h-44 object-cover flex-shrink-0"
                                 loading="lazy">
                        </template>
                        <template x-if="getCoverMedia(record) && getCoverMedia(record).tipo === 'video'">
                            <video :src="getCoverMedia(record).url"
                                   class="w-full h-44 object-cover flex-shrink-0"
                                   muted playsinline preload="metadata"></video>
                        </template>
                        <template x-if="!getCoverMedia(record)">
                            <div class="w-full h-24 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-10 h-10 text-indigo-200 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </template>

                        {{-- Contenido --}}
                        <div class="p-4 flex-1 space-y-1.5">
                            <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate"
                               x-text="getCardTitle(record)"></p>
                            <template x-for="campo in getCardFields(record)" :key="campo.slug">
                                <div class="flex gap-1 leading-tight"
                                     :class="campo.cardSize === 'base' ? 'text-sm' : campo.cardSize === 'xs' ? 'text-[10px]' : 'text-xs'">
                                    <span class="text-gray-400 flex-shrink-0" x-text="campo.nombre + ':'"></span>
                                    <span class="text-gray-600 dark:text-gray-300 truncate"
                                          x-text="getFieldDisplay(record, campo)"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Acciones --}}
                        <div class="px-4 pb-3 flex items-center gap-2 border-t border-gray-50 dark:border-gray-700 pt-2" @click.stop>
                            @if($campoPhone)
                                <button @click="verificarWhatsapp(record.id, '{{ $campoPhone->slug }}')"
                                        title="Verificar WhatsApp"
                                        class="p-1.5 rounded-lg text-green-500 hover:bg-green-50 hover:text-green-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                    </svg>
                                </button>
                            @endif
                            <button @click="abrirModal(record.id, record.datos)"
                                    title="Editar"
                                    class="p-1.5 rounded-lg text-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="eliminar(record.id)"
                                    title="Eliminar"
                                    class="p-1.5 rounded-lg text-red-300 hover:bg-red-50 hover:text-red-600 transition-colors ml-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Cards vacías --}}
            <div x-show="records.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow py-16 text-center text-gray-400">
                <div class="text-5xl mb-3">{{ $modulo->icono ?? '📋' }}</div>
                <p>No hay registros aún. Crea el primero.</p>
            </div>

            {{-- Paginación en vista cards --}}
            @if($records->hasPages())
                <div class="mt-4">{{ $records->links() }}</div>
            @endif
        </div>

        {{-- ═══ MODAL: Configuración de tarjetas ═══ --}}
        <div x-show="modalCardConfig" x-cloak
             class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4"
             @click.self="modalCardConfig = false">
            <div class="bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[85vh] flex flex-col border border-white/10"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-between items-center px-5 py-4 border-b border-white/10 flex-shrink-0">
                    <h3 class="text-base font-semibold text-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Configurar tarjetas
                    </h3>
                    <button @click="modalCardConfig = false" class="text-gray-400 hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-5 space-y-5">

                    {{-- Campo título --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Campo de título</label>
                        <select x-model="cardConfig.titleSlug" @change="saveCardConfig()"
                                class="w-full bg-gray-700 border border-white/10 rounded-lg px-3 py-2 text-sm text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">— Auto (primer campo de texto) —</option>
                            @foreach($fields as $f)
                                @if(!in_array($f->tipo, ['file','id']))
                                    <option value="{{ $f->slug }}">{{ $f->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Campos visibles --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Campos visibles</label>
                        <p class="text-xs text-gray-500 mb-3">Activa, reordena y elige el tamaño de cada campo.</p>
                        <div class="space-y-2">
                            <template x-for="(cf, idx) in cardConfig.fields" :key="cf.slug">
                                <div class="flex items-center gap-2 bg-gray-700/50 rounded-lg px-3 py-2 border border-white/5"
                                     :class="!cf.show ? 'opacity-50' : ''">

                                    {{-- Reorder arrows --}}
                                    <div class="flex flex-col gap-0.5 flex-shrink-0">
                                        <button @click="moveCardField(idx, -1)" :disabled="idx === 0"
                                                class="text-gray-500 hover:text-gray-200 disabled:opacity-20 leading-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        </button>
                                        <button @click="moveCardField(idx, 1)" :disabled="idx === cardConfig.fields.length - 1"
                                                class="text-gray-500 hover:text-gray-200 disabled:opacity-20 leading-none">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Checkbox show/hide --}}
                                    <input type="checkbox" :checked="cf.show"
                                           @change="updateCardFieldProp(cf.slug, 'show', $event.target.checked)"
                                           class="rounded border-gray-500 text-indigo-600 focus:ring-indigo-500 flex-shrink-0">

                                    {{-- Field name --}}
                                    <span class="flex-1 text-sm text-gray-200 truncate"
                                          x-text="campos.find(c => c.slug === cf.slug)?.nombre || cf.slug"></span>

                                    {{-- Font size selector --}}
                                    <select :value="cf.size"
                                            @change="updateCardFieldProp(cf.slug, 'size', $event.target.value)"
                                            :disabled="!cf.show"
                                            class="bg-gray-600 border border-white/10 rounded px-2 py-1 text-xs text-gray-200 outline-none disabled:opacity-40 w-24 flex-shrink-0">
                                        <option value="xs">Pequeño</option>
                                        <option value="sm">Normal</option>
                                        <option value="base">Grande</option>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                <div class="px-5 py-4 border-t border-white/10 flex justify-between items-center flex-shrink-0">
                    <button @click="cardConfig = { titleSlug: '', fields: campos.filter(c => c.tipo !== 'file' && c.tipo !== 'id').map(c => ({ slug: c.slug, show: true, size: 'sm' })) }; saveCardConfig()"
                            class="text-xs text-gray-400 hover:text-gray-200 transition-colors">
                        Restaurar por defecto
                    </button>
                    <button @click="modalCardConfig = false"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Listo
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ MODAL: Detalle de card ═══ --}}
        <div x-show="modalDetalle" x-cloak
             class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
             @click.self="modalDetalle = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-between items-center px-6 py-4 border-b dark:border-gray-700 flex-shrink-0">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 truncate pr-4"
                        x-text="recordDetalle ? getCardTitle(recordDetalle) : ''"></h3>
                    <button @click="modalDetalle = false"
                            aria-label="Cerrar detalle"
                            class="text-gray-400 hover:text-gray-600 flex-shrink-0 focus-visible:ring-2 focus-visible:ring-gray-400 rounded">
                        <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    <template x-if="recordDetalle">
                        <div class="space-y-4">
                            {{-- Media --}}
                            <template x-if="getCoverMedia(recordDetalle) && getCoverMedia(recordDetalle).tipo === 'imagen'">
                                <img :src="getCoverMedia(recordDetalle).url"
                                     :alt="getCardTitle(recordDetalle)"
                                     class="w-full max-h-64 object-contain rounded-xl border border-gray-100">
                            </template>
                            <template x-if="getCoverMedia(recordDetalle) && getCoverMedia(recordDetalle).tipo === 'video'">
                                <video :src="getCoverMedia(recordDetalle).url"
                                       class="w-full max-h-64 rounded-xl border border-gray-100"
                                       controls></video>
                            </template>

                            {{-- Todos los campos --}}
                            <template x-for="campo in campos" :key="campo.slug">
                                <div x-show="(campo.tipo !== 'file' || recordDetalle?.datos?.[campo.slug]) && campo.tipo !== 'id'"
                                     class="grid grid-cols-3 gap-2 text-sm items-start">
                                    <span class="text-gray-400 font-medium col-span-1 pt-0.5" x-text="campo.nombre"></span>
                                    <div class="col-span-2">
                                        {{-- Tipo archivo: mostrar imagen, video o enlace --}}
                                        <template x-if="campo.tipo === 'file' && recordDetalle?.datos?.[campo.slug]">
                                            <div>
                                                <template x-if="esImagen(recordDetalle.datos[campo.slug])">
                                                    <a :href="urlArchivo(recordDetalle.datos[campo.slug])" target="_blank">
                                                        <img :src="urlArchivo(recordDetalle.datos[campo.slug])"
                                                             class="max-h-48 w-auto rounded-lg border border-gray-200 dark:border-gray-600 object-contain hover:opacity-90 transition">
                                                    </a>
                                                </template>
                                                <template x-if="esVideo(recordDetalle.datos[campo.slug])">
                                                    <video :src="urlArchivo(recordDetalle.datos[campo.slug])"
                                                           class="max-w-full max-h-48 rounded-lg border border-gray-200 dark:border-gray-600"
                                                           controls></video>
                                                </template>
                                                <template x-if="!esImagen(recordDetalle.datos[campo.slug]) && !esVideo(recordDetalle.datos[campo.slug])">
                                                    <a :href="urlArchivo(recordDetalle.datos[campo.slug])" target="_blank"
                                                       class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                                                        <span>📎</span>
                                                        <span x-text="String(recordDetalle.datos[campo.slug]).split('/').pop()"></span>
                                                    </a>
                                                </template>
                                            </div>
                                        </template>
                                        {{-- Category select: chips --}}
                                        <template x-if="campo.tipo === 'category_select'">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <template x-if="recordDetalle?.datos?.[campo.slug]?.categoria">
                                                    <span class="text-xs text-gray-400 font-medium" x-text="recordDetalle.datos[campo.slug].categoria + ':'"></span>
                                                </template>
                                                <template x-for="item in (recordDetalle?.datos?.[campo.slug]?.items || [])" :key="item">
                                                    <span class="inline-block bg-indigo-900/40 border border-indigo-700/50 text-indigo-300 text-xs px-2 py-0.5 rounded-full" x-text="item"></span>
                                                </template>
                                                <template x-if="!recordDetalle?.datos?.[campo.slug]?.categoria">
                                                    <span class="text-gray-500">—</span>
                                                </template>
                                            </div>
                                        </template>
                                        {{-- Resto de tipos: texto --}}
                                        <template x-if="campo.tipo !== 'file' && campo.tipo !== 'category_select'">
                                            <span class="text-gray-800 dark:text-gray-200 break-words"
                                                  x-text="getFieldDisplay(recordDetalle, campo)"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 border-t dark:border-gray-700 flex items-center gap-3 flex-shrink-0">
                    @if($campoPhone)
                        <button @click="verificarWhatsapp(recordDetalle.id, '{{ $campoPhone->slug }}'); modalDetalle = false"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-green-600 border border-green-200 rounded-lg hover:bg-green-50 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                            Verificar
                        </button>
                    @endif
                    <button @click="abrirModal(recordDetalle.id, recordDetalle.datos); modalDetalle = false"
                            class="flex-1 px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                        Editar registro
                    </button>
                    <button @click="eliminar(recordDetalle.id); modalDetalle = false"
                            class="px-4 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ MODAL: Formulario dinámico ═══ --}}
        <div x-show="modal" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
             @click.self="modal=false">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">

                <div class="flex justify-between items-center p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200"
                        x-text="editId ? 'Editar registro' : 'Nuevo registro'"></h3>
                    <button @click="modal=false" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>

                <div class="overflow-y-auto p-5 space-y-4">
                    @foreach($fields as $field)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ $field->nombre }}
                                @if($field->obligatorio)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if($field->tipo === 'textarea')
                                <textarea x-model="form['{{ $field->slug }}']" rows="3"
                                          class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                          placeholder="{{ $field->nombre }}..."></textarea>

                            @elseif($field->tipo === 'select')
                                <select x-model="form['{{ $field->slug }}']"
                                        class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">-- Selecciona --</option>
                                    @foreach($field->opciones ?? [] as $opcion)
                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                    @endforeach
                                </select>

                            @elseif($field->tipo === 'category_select')
                                {{-- Paso 1: categoría; Paso 2: ítems de esa categoría (multi) --}}
                                <div class="space-y-2">
                                    <select @change="setCatFor('{{ $field->slug }}', $event.target.value)"
                                            :value="form['{{ $field->slug }}']?.categoria || ''"
                                            class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                                        <option value="">-- Selecciona categoría --</option>
                                        @foreach(array_keys($field->opciones ?? []) as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                    @foreach($field->opciones ?? [] as $cat => $items)
                                        <div x-show="form['{{ $field->slug }}']?.categoria === '{{ $cat }}'"
                                             class="border border-white/10 rounded-lg p-3 max-h-44 overflow-y-auto space-y-1.5 bg-gray-800/50">
                                            @foreach($items as $item)
                                                <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-white/5 px-1 py-0.5 rounded">
                                                    <input type="checkbox"
                                                           value="{{ $item }}"
                                                           :checked="(form['{{ $field->slug }}']?.items || []).includes('{{ $item }}')"
                                                           @change="toggleCategoryItem('{{ $field->slug }}', '{{ $cat }}', '{{ $item }}')"
                                                           class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-gray-200">{{ $item }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <p x-show="(form['{{ $field->slug }}']?.items || []).length > 0"
                                       class="text-xs text-indigo-400"
                                       x-text="(form['{{ $field->slug }}']?.items || []).length + ' seleccionado(s)'"></p>
                                </div>

                            @elseif($field->tipo === 'multiselect')
                                {{-- Lista de opciones con multi-selección por checkboxes --}}
                                <div class="border dark:border-gray-600 rounded-lg p-3 max-h-44 overflow-y-auto space-y-1.5 dark:bg-gray-700">
                                    @forelse($field->opciones ?? [] as $opcion)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 px-1 py-0.5 rounded">
                                            <input type="checkbox"
                                                   value="{{ $opcion }}"
                                                   :checked="(form['{{ $field->slug }}'] || []).includes('{{ $opcion }}')"
                                                   @change="toggleMultiselect('{{ $field->slug }}', '{{ $opcion }}')"
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="dark:text-gray-200">{{ $opcion }}</span>
                                        </label>
                                    @empty
                                        <p class="text-xs text-gray-400">Sin opciones definidas.</p>
                                    @endforelse
                                </div>
                                <p x-show="(form['{{ $field->slug }}'] || []).length > 0"
                                   class="text-xs text-blue-600 mt-1"
                                   x-text="(form['{{ $field->slug }}'] || []).length + ' seleccionado(s)'"></p>

                            @elseif($field->tipo === 'relation' && ($field->meta['multiple'] ?? false))
                                {{-- Relación múltiple: checkboxes --}}
                                <div x-init="cargarRelacion('{{ $field->slug }}', '{{ $field->modulo_relacion }}')"
                                     class="border dark:border-gray-600 rounded-lg p-3 max-h-44 overflow-y-auto space-y-1.5 dark:bg-gray-700">
                                    <template x-if="!relaciones['{{ $field->slug }}']">
                                        <p class="text-xs text-gray-400">Cargando opciones...</p>
                                    </template>
                                    <template x-for="opt in relaciones['{{ $field->slug }}'] || []" :key="opt.id">
                                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 px-1 rounded">
                                            <input type="checkbox"
                                                   :value="opt.id"
                                                   :checked="(form['{{ $field->slug }}'] || []).map(Number).includes(opt.id)"
                                                   @change="toggleRelacion('{{ $field->slug }}', opt.id)"
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span x-text="opt.label" class="dark:text-gray-200"></span>
                                        </label>
                                    </template>
                                    <p x-show="(relaciones['{{ $field->slug }}'] || []).length === 0 && relaciones['{{ $field->slug }}'] !== undefined"
                                       class="text-xs text-gray-400">Sin registros disponibles.</p>
                                </div>
                                {{-- Resumen de seleccionados --}}
                                <p x-show="(form['{{ $field->slug }}'] || []).length > 0"
                                   class="text-xs text-indigo-600 mt-1"
                                   x-text="(form['{{ $field->slug }}'] || []).length + ' seleccionado(s)'"></p>

                            @elseif($field->tipo === 'relation')
                                <select x-model="form['{{ $field->slug }}']"
                                        x-init="cargarRelacion('{{ $field->slug }}', '{{ $field->modulo_relacion }}')"
                                        class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">-- Cargando... --</option>
                                    <template x-for="opt in relaciones['{{ $field->slug }}'] || []" :key="opt.id">
                                        <option :value="opt.id" x-text="opt.label"></option>
                                    </template>
                                </select>

                            @elseif($field->tipo === 'tags')
                                {{-- Input de texto para las etiquetas (smart comma parsing) --}}
                                <input type="text"
                                       x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="Etiqueta1, Etiqueta2, Etiqueta3...">
                                {{-- Vista previa de chips --}}
                                <template x-if="parseTags(form['{{ $field->slug }}']).length > 0">
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        <template x-for="tag in parseTags(form['{{ $field->slug }}'])" :key="tag">
                                            <span class="inline-block bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full" x-text="tag"></span>
                                        </template>
                                    </div>
                                </template>
                                <p class="text-xs text-gray-400 mt-1">
                                    Separa las etiquetas con comas. Las comas dentro de paréntesis o corchetes no se cuentan como separadores.
                                </p>

                            @elseif($field->tipo === 'date')
                                <input type="date" x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">

                            @elseif($field->tipo === 'number')
                                <input type="number" x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="{{ $field->nombre }}">

                            @elseif($field->tipo === 'email')
                                <input type="email" x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="correo@ejemplo.com">

                            @elseif($field->tipo === 'phone')
                                <input type="tel" x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="+52 55 1234 5678">

                            @elseif($field->tipo === 'url')
                                <input type="url" x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="https://ejemplo.com">
                                {{-- Vista previa de URL --}}
                                <template x-if="form['{{ $field->slug }}']">
                                    <a :href="form['{{ $field->slug }}']" target="_blank" rel="noopener"
                                       class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        Abrir enlace
                                    </a>
                                </template>

                            @elseif($field->tipo === 'file')
                                {{-- Preview local (mientras sube) --}}
                                <template x-if="localPreviews['{{ $field->slug }}']">
                                    <div class="mb-2 p-2 bg-gray-800/50 rounded-lg border border-white/10">
                                        <template x-if="localPreviews['{{ $field->slug }}'].isImg">
                                            <img :src="localPreviews['{{ $field->slug }}'].url"
                                                 class="max-h-48 w-auto rounded object-contain mx-auto" alt="Preview">
                                        </template>
                                        <template x-if="localPreviews['{{ $field->slug }}'].isVid">
                                            <video :src="localPreviews['{{ $field->slug }}'].url"
                                                   class="max-h-48 w-full rounded" controls muted></video>
                                        </template>
                                    </div>
                                </template>
                                {{-- Preview del servidor (archivo ya guardado) --}}
                                <template x-if="form['{{ $field->slug }}'] && !localPreviews['{{ $field->slug }}']">
                                    <div class="mb-2 p-2 bg-gray-800/50 rounded-lg border border-white/10">
                                        <template x-if="esImagen(form['{{ $field->slug }}'])">
                                            <img :src="urlArchivo(form['{{ $field->slug }}'])"
                                                 class="max-h-48 w-auto rounded object-contain mx-auto"
                                                 alt="{{ $field->nombre }}">
                                        </template>
                                        <template x-if="esVideo(form['{{ $field->slug }}'])">
                                            <video :src="urlArchivo(form['{{ $field->slug }}'])"
                                                   class="max-h-48 w-full rounded" controls muted></video>
                                        </template>
                                        <template x-if="!esImagen(form['{{ $field->slug }}']) && !esVideo(form['{{ $field->slug }}'])">
                                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                                <span class="text-2xl">📎</span>
                                                <a :href="urlArchivo(form['{{ $field->slug }}'])" target="_blank"
                                                   class="text-indigo-400 hover:underline text-sm">Ver archivo actual</a>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Input de subida --}}
                                <div class="relative">
                                    @php
                                        $acceptAttr = ($field->meta['accept'] ?? 'all') === 'image'
                                            ? 'image/*'
                                            : (($field->meta['accept'] ?? 'all') === 'video'
                                                ? 'video/*'
                                                : 'image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip');
                                    @endphp
                                    <input type="file"
                                           accept="{{ $acceptAttr }}"
                                           @change="subirArchivo('{{ $field->slug }}', $event)"
                                           :disabled="subiendo['{{ $field->slug }}']"
                                           class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm
                                                  file:mr-3 file:py-1 file:px-3 file:rounded file:border-0
                                                  file:text-sm file:bg-indigo-50 file:text-indigo-700
                                                  hover:file:bg-indigo-100 cursor-pointer
                                                  disabled:opacity-50 disabled:cursor-not-allowed">
                                    <div x-show="subiendo['{{ $field->slug }}']"
                                         class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 rounded-lg flex items-center justify-center">
                                        <span class="text-sm text-indigo-600 font-medium flex items-center gap-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            Subiendo...
                                        </span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">
                                    Máx: {{ ($field->meta['max_mb'] ?? 10) }} MB ·
                                    @php
                                        $acceptLabel = ($field->meta['accept'] ?? 'all') === 'image'
                                            ? 'Solo imágenes'
                                            : (($field->meta['accept'] ?? 'all') === 'video'
                                                ? 'Solo videos'
                                                : 'Imágenes, videos y documentos');
                                    @endphp
                                    {{ $acceptLabel }}
                                </p>

                            @elseif($field->tipo === 'id')
                                {{-- Auto-generado — solo lectura --}}
                                <div class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700/50 font-mono flex items-center gap-2 min-h-[38px]">
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                    </svg>
                                    <span x-show="!form['{{ $field->slug }}']" class="text-gray-400 italic text-xs not-italic" style="font-style:italic">
                                        Se generará automáticamente
                                    </span>
                                    <span x-show="form['{{ $field->slug }}']"
                                          x-text="form['{{ $field->slug }}']"
                                          class="text-gray-700 dark:text-gray-200"></span>
                                </div>

                            @else
                                <input type="text" x-model="form['{{ $field->slug }}']"
                                       class="w-full border dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="{{ $field->nombre }}...">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="p-5 border-t dark:border-gray-700 flex justify-end gap-3">
                    <button @click="modal=false"
                            class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">
                        Cancelar
                    </button>
                    <button @click="guardar()" :disabled="Object.values(subiendo).some(v => v)"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-60">
                        <span x-text="editId ? 'Guardar cambios' : 'Crear registro'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function catalogoCrud(config) {
        return {
            module:                  config.module,
            campos:                  config.campos,
            records:                 config.records,
            tienePromptVerificacion: config.tienePromptVerificacion,
            modal:   false,
            editId:  null,
            form:    {},
            relaciones:    {},
            subiendo:      {},
            localPreviews: {},
            flash:   { msg: '', ok: true },
            errores: [],

            // ── Vista tabla / cards ───────────────────────────────────────
            vista: localStorage.getItem('catalogo_vista_' + config.module) || 'tabla',

            setVista(v) {
                this.vista = v;
                localStorage.setItem('catalogo_vista_' + this.module, v);
            },

            // ── Card config ───────────────────────────────────────────────
            modalCardConfig: false,
            cardConfig: { titleSlug: '', fields: [] },

            loadCardConfig() {
                const saved = localStorage.getItem('catalogo_card_cfg_' + this.module);
                if (saved) {
                    try { this.cardConfig = JSON.parse(saved); return; } catch {}
                }
                this.cardConfig = {
                    titleSlug: '',
                    fields: this.campos
                        .filter(c => c.tipo !== 'file' && c.tipo !== 'id')
                        .map(c => ({ slug: c.slug, show: true, size: 'sm' })),
                };
            },

            saveCardConfig() {
                localStorage.setItem('catalogo_card_cfg_' + this.module, JSON.stringify(this.cardConfig));
            },

            openCardConfig() {
                // Sync: add any new campos not yet in config, keep existing settings
                const existing = {};
                (this.cardConfig.fields || []).forEach(f => existing[f.slug] = f);
                const synced = this.campos
                    .filter(c => c.tipo !== 'file' && c.tipo !== 'id')
                    .map(c => existing[c.slug] || { slug: c.slug, show: true, size: 'sm' });
                this.cardConfig = { ...this.cardConfig, fields: synced };
                this.modalCardConfig = true;
            },

            moveCardField(idx, dir) {
                const fields = [...this.cardConfig.fields];
                const target = idx + dir;
                if (target < 0 || target >= fields.length) return;
                [fields[idx], fields[target]] = [fields[target], fields[idx]];
                this.cardConfig = { ...this.cardConfig, fields };
                this.saveCardConfig();
            },

            updateCardFieldProp(slug, prop, val) {
                const fields = this.cardConfig.fields.map(f =>
                    f.slug === slug ? { ...f, [prop]: val } : f
                );
                this.cardConfig = { ...this.cardConfig, fields };
                this.saveCardConfig();
            },

            // ── Modal detalle (cards) ─────────────────────────────────────
            modalDetalle:  false,
            recordDetalle: null,

            abrirDetalle(record) {
                this.recordDetalle = record;
                this.modalDetalle  = true;
            },

            // ── Helpers para cards ────────────────────────────────────────
            getCoverMedia(record) {
                for (const campo of this.campos) {
                    if (campo.tipo !== 'file') continue;
                    const val = record.datos?.[campo.slug];
                    if (!val) continue;
                    if (this.esImagen(val)) return { tipo: 'imagen', url: this.urlArchivo(val) };
                    if (this.esVideo(val))  return { tipo: 'video',  url: this.urlArchivo(val) };
                }
                return null;
            },

            getCardTitle(record) {
                // Use configured title field first
                if (this.cardConfig.titleSlug) {
                    const campo = this.campos.find(c => c.slug === this.cardConfig.titleSlug);
                    if (campo) {
                        const display = this.getFieldDisplay(record, campo);
                        return (display && display !== '—') ? display : '#' + record.id;
                    }
                }
                // Auto: first text/email/phone field (never id)
                const first = this.campos.find(c =>
                    ['text', 'email', 'phone'].includes(c.tipo) && record.datos?.[c.slug]
                );
                return first ? String(record.datos[first.slug]) : '#' + record.id;
            },

            getCardFields(record) {
                if (this.cardConfig.fields && this.cardConfig.fields.length) {
                    return this.cardConfig.fields
                        .filter(f => f.show && f.slug !== this.cardConfig.titleSlug)
                        .map(f => {
                            const c = this.campos.find(c => c.slug === f.slug);
                            return c ? { ...c, cardSize: f.size || 'sm' } : null;
                        })
                        .filter(Boolean);
                }
                return this.campos.filter(c => c.tipo !== 'file' && c.tipo !== 'id');
            },

            getFieldDisplay(record, campo) {
                const val = record.datos?.[campo.slug];
                if (val === null || val === undefined || val === '') return '—';
                if (campo.tipo === 'category_select') {
                    if (!val || typeof val !== 'object' || !val.categoria) return '—';
                    const items = Array.isArray(val.items) && val.items.length ? ': ' + val.items.join(', ') : '';
                    return val.categoria + items;
                }
                if (Array.isArray(val)) return val.join(', ');
                if (campo.tipo === 'date' && val) {
                    try { return new Date(val).toLocaleDateString('es-MX'); } catch { /* fallthrough */ }
                }
                if (campo.tipo === 'file') {
                    // Solo mostrar nombre del archivo, no la ruta completa
                    return String(val).split('/').pop();
                }
                return String(val);
            },

            init() {
                this.loadCardConfig();
                this.resetForm();
            },

            resetForm() {
                this.form    = {};
                this.subiendo = {};
                this.campos.forEach(c => {
                    if ((c.tipo === 'relation' && c.meta?.multiple) || c.tipo === 'multiselect') {
                        this.form[c.slug] = [];
                    } else if (c.tipo === 'category_select') {
                        this.form[c.slug] = { categoria: '', items: [] };
                    } else {
                        this.form[c.slug] = '';
                    }
                });
            },

            abrirModal(id, datos = null) {
                this.editId  = id;
                this.errores = [];
                this.subiendo = {};
                this.form    = {};
                if (datos) {
                    this.campos.forEach(c => {
                        if (c.tipo === 'tags' && Array.isArray(datos[c.slug])) {
                            this.form[c.slug] = datos[c.slug].join(', ');
                        } else if (c.tipo === 'multiselect' && Array.isArray(datos[c.slug])) {
                            this.form[c.slug] = datos[c.slug];
                        } else if (c.tipo === 'relation' && c.meta?.multiple && Array.isArray(datos[c.slug])) {
                            this.form[c.slug] = (datos[c.slug] || []).map(Number);
                        } else if (c.tipo === 'category_select') {
                            this.form[c.slug] = datos[c.slug] && datos[c.slug].categoria
                                ? { categoria: datos[c.slug].categoria, items: datos[c.slug].items || [] }
                                : { categoria: '', items: [] };
                        } else {
                            this.form[c.slug] = datos[c.slug] ?? '';
                        }
                    });
                } else {
                    this.campos.forEach(c => {
                        if ((c.tipo === 'relation' && c.meta?.multiple) || c.tipo === 'multiselect') {
                            this.form[c.slug] = [];
                        } else if (c.tipo === 'category_select') {
                            this.form[c.slug] = { categoria: '', items: [] };
                        } else {
                            this.form[c.slug] = '';
                        }
                    });
                }
                this.modal = true;
            },

            async cargarRelacion(slug, moduloRelacion) {
                if (!moduloRelacion || this.relaciones[slug] !== undefined) return;
                // Mark as loading (undefined → null means "in progress")
                this.relaciones = { ...this.relaciones, [slug]: null };
                const res = await fetch(
                    `/catalogo/${this.module}/opciones-relation?modulo_relacion=${encodeURIComponent(moduloRelacion)}`,
                    { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
                );
                if (res.ok) {
                    const data = await res.json();
                    this.relaciones = { ...this.relaciones, [slug]: data };
                }
            },

            // ── Relation multiple ─────────────────────────────────────────
            toggleRelacion(slug, id) {
                const arr = Array.isArray(this.form[slug]) ? [...this.form[slug]] : [];
                const idx = arr.indexOf(id);
                if (idx === -1) arr.push(id);
                else arr.splice(idx, 1);
                this.form = { ...this.form, [slug]: arr };
            },

            // ── Category select ───────────────────────────────────────────
            setCatFor(slug, categoria) {
                this.form = { ...this.form, [slug]: { categoria, items: [] } };
            },
            toggleCategoryItem(slug, categoria, item) {
                const current = this.form[slug] || { categoria, items: [] };
                const items   = [...(current.items || [])];
                const idx     = items.indexOf(item);
                if (idx === -1) items.push(item);
                else items.splice(idx, 1);
                this.form = { ...this.form, [slug]: { categoria, items } };
            },

            // ── Multiselect (opciones fijas) ──────────────────────────────
            toggleMultiselect(slug, value) {
                const arr = Array.isArray(this.form[slug]) ? [...this.form[slug]] : [];
                const idx = arr.indexOf(value);
                if (idx === -1) arr.push(value);
                else arr.splice(idx, 1);
                this.form = { ...this.form, [slug]: arr };
            },

            // ── Tags parsing ─────────────────────────────────────────────
            /**
             * Split by comma, ignoring commas inside (), [], "", ''
             */
            parseTags(text) {
                if (!text || typeof text !== 'string') return [];
                const result = [];
                let current  = '';
                let depth    = 0;
                let inSingle = false;
                let inDouble = false;
                for (const ch of text) {
                    if      (ch === "'" && !inDouble) { inSingle = !inSingle; current += ch; }
                    else if (ch === '"' && !inSingle) { inDouble = !inDouble; current += ch; }
                    else if (!inSingle && !inDouble) {
                        if      (ch === '(' || ch === '[') { depth++; current += ch; }
                        else if (ch === ')' || ch === ']') { depth--; current += ch; }
                        else if (ch === ',' && depth === 0) {
                            const tag = current.trim();
                            if (tag) result.push(tag);
                            current = '';
                        } else { current += ch; }
                    } else { current += ch; }
                }
                const last = current.trim();
                if (last) result.push(last);
                return result;
            },

            // ── Helpers de archivo ───────────────────────────────────────
            urlArchivo(path) {
                if (!path) return null;
                if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) return path;
                return '/storage/' + path;
            },
            esImagen(path) {
                return path && /\.(jpg|jpeg|png|gif|webp|svg|bmp)(\?.*)?$/i.test(path);
            },
            esVideo(path) {
                return path && /\.(mp4|webm|mov|avi|mkv)(\?.*)?$/i.test(path);
            },

            async subirArchivo(slug, event) {
                const file = event.target.files[0];
                if (!file) return;

                // Preview local inmediato (blob URL)
                const localUrl = URL.createObjectURL(file);
                this.localPreviews = { ...this.localPreviews, [slug]: { url: localUrl, isImg: file.type.startsWith('image/'), isVid: file.type.startsWith('video/') } };

                this.subiendo = { ...this.subiendo, [slug]: true };
                const fd = new FormData();
                fd.append('file', file);
                fd.append('field_slug', slug);
                fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                try {
                    const res  = await fetch(`/catalogo/${this.module}/upload-file`, {
                        method:  'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body:    fd,
                    });
                    const data = await res.json();
                    if (!res.ok) { this.errores = [data.message || 'Error al subir el archivo.']; return; }
                    this.form = { ...this.form, [slug]: data.path };
                    // Liberar blob URL ya que tenemos la URL del servidor
                    URL.revokeObjectURL(localUrl);
                    const p = { ...this.localPreviews }; delete p[slug]; this.localPreviews = p;
                } catch (e) {
                    this.errores = ['Error al subir: ' + e.message];
                } finally {
                    this.subiendo = { ...this.subiendo, [slug]: false };
                }
            },

            // ── WhatsApp verify ─────────────────────────────────────────
            async verificarWhatsapp(recordId, fieldSlug) {
                if (!confirm('¿Enviar mensaje de verificación por WhatsApp al número registrado?')) return;
                const res = await fetch(`/catalogo/${this.module}/${recordId}/whatsapp-verify/${fieldSlug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept':       'application/json',
                    },
                });
                const data = await res.json();
                this.mostrarFlash(data.message || (res.ok ? 'Mensaje enviado.' : 'Error al enviar.'), res.ok);
            },

            // ── CRUD ─────────────────────────────────────────────────────
            async guardar() {
                this.errores = [];
                // Prepare payload: convert tags text → array
                const sendForm = { ...this.form };
                this.campos.forEach(c => {
                    if (c.tipo === 'tags' && typeof sendForm[c.slug] === 'string') {
                        sendForm[c.slug] = this.parseTags(sendForm[c.slug]);
                    }
                });

                const url    = this.editId ? `/catalogo/${this.module}/${this.editId}` : `/catalogo/${this.module}`;
                const method = this.editId ? 'PUT' : 'POST';
                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept':       'application/json',
                        },
                        body: JSON.stringify({ datos: sendForm }),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        this.errores = json.errors
                            ? Object.values(json.errors).flat()
                            : [json.message || 'Error al guardar.'];
                        return;
                    }
                    this.modal = false;
                    this.mostrarFlash(this.editId ? 'Registro actualizado.' : 'Registro creado.', true);
                    setTimeout(() => window.location.reload(), 800);
                } catch (e) {
                    this.errores = ['Error inesperado: ' + e.message];
                }
            },

            async eliminar(id) {
                if (!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')) return;
                const res = await fetch(`/catalogo/${this.module}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept':       'application/json',
                    },
                });
                if (res.ok) {
                    this.mostrarFlash('Registro eliminado.', true);
                    setTimeout(() => window.location.reload(), 800);
                }
            },

            mostrarFlash(msg, ok) {
                this.flash = { msg, ok };
                setTimeout(() => this.flash.msg = '', 4000);
            },
        };
    }
    </script>
    @endpush
</x-admin-layout>
