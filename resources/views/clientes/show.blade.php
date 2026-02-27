<x-admin-layout title="Historial · {{ $cliente->name }}">

    <div x-data="historialApp()" x-init="init()">

        {{-- ── HEADER ── --}}
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-start gap-4">
                {{-- Avatar --}}
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md flex-shrink-0">
                    <span class="text-2xl font-bold text-white">
                        {{ mb_strtoupper(mb_substr($cliente->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $cliente->name }}</h2>
                        @if ($cliente->folio)
                            <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-mono font-bold rounded-lg border border-blue-100">
                                {{ $cliente->folio }}
                            </span>
                        @endif
                        @if ($cliente->status)
                            @php
                                $statusColors = [
                                    'Activo'     => 'bg-green-100 text-green-700 border-green-200',
                                    'Inactivo'   => 'bg-gray-100 text-gray-500 border-gray-200',
                                    'Prospecto'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Recurrente' => 'bg-blue-100 text-blue-700 border-blue-200',
                                ];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusColors[$cliente->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ $cliente->status }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-1.5 flex-wrap text-sm text-gray-400">
                        @if ($cliente->phone)
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $cliente->phone) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 text-green-600 hover:text-green-700 font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.524 5.847L0 24l6.335-1.509A11.933 11.933 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.8 9.8 0 01-5.003-1.368l-.36-.214-3.723.887.916-3.619-.234-.373A9.77 9.77 0 012.182 12C2.182 6.58 6.58 2.182 12 2.182S21.818 6.58 21.818 12 17.42 21.818 12 21.818z"/>
                                </svg>
                                {{ $cliente->phone }}
                            </a>
                        @endif
                        @if ($cliente->date)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $cliente->date->format('d/m/Y') }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $cliente->observations->count() }} {{ $cliente->observations->count() === 1 ? 'registro' : 'registros' }}
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ route('clientes.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-colors shadow-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>

        {{-- ── HISTORIAL ── --}}
        @if ($cliente->observations->isEmpty())
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 py-24 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-50 rounded-2xl flex items-center justify-center mb-5 shadow-sm">
                    <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <p class="text-gray-700 font-semibold text-lg mb-1.5">Sin registros aún</p>
                <p class="text-gray-400 text-sm max-w-xs">
                    Este cliente no tiene observaciones registradas. Puedes añadirlas desde la lista de clientes.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($cliente->observations as $index => $obs)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">

                        {{-- Card header --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 bg-gray-50/40">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-purple-600">
                                        {{ $cliente->observations->count() - $index }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        Registro #{{ $cliente->observations->count() - $index }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $obs->created_at->format('d/m/Y') }} · {{ $obs->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            {{-- Peso y Edad badges --}}
                            <div class="flex items-center gap-2">
                                @if ($obs->weight)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        </svg>
                                        {{ $obs->weight }} kg
                                    </span>
                                @endif
                                @if ($obs->age)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-full border border-indigo-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $obs->age }} años
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Cuerpo --}}
                        <div class="px-6 py-5">
                            {{-- Observación --}}
                            @if ($obs->observation)
                                <div class="mb-5">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Observaciones
                                    </p>
                                    <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                        {{ $obs->observation }}
                                    </p>
                                </div>
                            @endif

                            {{-- Productos sugeridos --}}
                            @if ($obs->suggested_products)
                                @php
                                    $prods = array_filter(array_map('trim', explode(',', $obs->suggested_products)));
                                @endphp
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        Productos sugeridos
                                    </p>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach ($prods as $nombre)
                                            <button type="button"
                                                    @click="abrirProducto({{ json_encode($nombre) }})"
                                                    class="group flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-xl shadow-sm
                                                           hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 text-left cursor-pointer">
                                                {{-- Imagen --}}
                                                @php
                                                    $prod = $productosMap->get($nombre);
                                                @endphp
                                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center flex-shrink-0 border border-gray-100">
                                                    @if ($prod && $prod['image_url'])
                                                        <img src="{{ $prod['image_url'] }}"
                                                             alt="{{ $nombre }}"
                                                             class="w-full h-full object-cover"/>
                                                    @else
                                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                {{-- Info --}}
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors truncate max-w-[140px]">
                                                        {{ $nombre }}
                                                    </p>
                                                    @if ($prod && $prod['category'])
                                                        <p class="text-xs text-gray-400 truncate max-w-[140px]">{{ $prod['category'] }}</p>
                                                    @endif
                                                    @if ($prod && $prod['price'])
                                                        <p class="text-xs font-semibold text-emerald-600 mt-0.5">${{ number_format($prod['price'], 2) }}</p>
                                                    @endif
                                                </div>
                                                {{-- Flecha --}}
                                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 flex-shrink-0 transition-colors ml-1"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Sin contenido --}}
                            @if (!$obs->observation && !$obs->suggested_products)
                                <p class="text-sm text-gray-400 italic">Sin datos registrados en este registro.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ══ MODAL DETALLE DE PRODUCTO ══ --}}
        <template x-teleport="body">
            <div x-show="productoModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">

                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="cerrarProducto()"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop>

                    {{-- Imagen principal --}}
                    <div class="relative w-full h-56 bg-gradient-to-br from-gray-100 to-gray-50 overflow-hidden rounded-t-2xl flex-shrink-0">
                        <template x-if="productoActual?.image_url">
                            <img :src="productoActual.image_url"
                                 :alt="productoActual.name"
                                 class="w-full h-full object-cover"/>
                        </template>
                        <template x-if="!productoActual?.image_url">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </template>

                        {{-- Botón cerrar sobre la imagen --}}
                        <button @click="cerrarProducto()"
                                class="absolute top-3 right-3 p-2 bg-black/40 hover:bg-black/60 text-white rounded-xl transition-colors backdrop-blur-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        {{-- Badge disponibilidad --}}
                        <div class="absolute bottom-3 left-3">
                            <template x-if="productoActual?.available">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500 text-white text-xs font-semibold rounded-full shadow-sm">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                    Disponible
                                </span>
                            </template>
                            <template x-if="productoActual && !productoActual.available">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full shadow-sm">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full opacity-50"></span>
                                    No disponible
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Info del producto --}}
                    <div class="p-6">
                        {{-- Nombre + Categoría --}}
                        <div class="mb-4">
                            <template x-if="productoActual?.category">
                                <span class="inline-flex items-center px-2.5 py-0.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-md mb-2"
                                      x-text="productoActual.category"></span>
                            </template>
                            <h3 class="text-xl font-bold text-gray-900" x-text="productoActual?.name ?? '—'"></h3>
                            <template x-if="productoActual?.price">
                                <p class="text-2xl font-bold text-emerald-600 mt-1">
                                    $<span x-text="parseFloat(productoActual.price).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                </p>
                            </template>
                        </div>

                        {{-- Descripción --}}
                        <template x-if="productoActual?.description">
                            <div class="mb-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Descripción</p>
                                <p class="text-sm text-gray-700 leading-relaxed" x-text="productoActual.description"></p>
                            </div>
                        </template>

                        {{-- Video --}}
                        <template x-if="productoActual?.video_url">
                            <div class="mb-5">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Video</p>
                                {{-- YouTube embed --}}
                                <template x-if="isYoutube(productoActual.video_url)">
                                    <div class="relative w-full" style="padding-top: 56.25%">
                                        <iframe :src="youtubeEmbed(productoActual.video_url)"
                                                class="absolute inset-0 w-full h-full rounded-xl"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen></iframe>
                                    </div>
                                </template>
                                {{-- Video local / otro --}}
                                <template x-if="!isYoutube(productoActual.video_url)">
                                    <video :src="productoActual.video_url"
                                           controls
                                           class="w-full rounded-xl border border-gray-100 max-h-56 bg-black"></video>
                                </template>
                            </div>
                        </template>

                        {{-- Sin información extra --}}
                        <template x-if="!productoActual?.description && !productoActual?.video_url">
                            <p class="text-sm text-gray-400 italic">Sin descripción disponible.</p>
                        </template>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <script>
    function historialApp() {
        return {
            productoModal: false,
            productoActual: null,
            productosMap: @json($productosMap),

            init() {},

            abrirProducto(nombre) {
                this.productoActual = this.productosMap[nombre] ?? { name: nombre, image_url: null, description: null, price: null, category: null, available: null, video_url: null };
                this.productoModal = true;
                document.body.style.overflow = 'hidden';
            },

            cerrarProducto() {
                this.productoModal = false;
                this.productoActual = null;
                document.body.style.overflow = '';
            },

            isYoutube(url) {
                if (!url) return false;
                return /youtube\.com|youtu\.be/.test(url);
            },

            youtubeEmbed(url) {
                if (!url) return '';
                const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
                return match ? `https://www.youtube.com/embed/${match[1]}` : url;
            },
        };
    }
    </script>

</x-admin-layout>
