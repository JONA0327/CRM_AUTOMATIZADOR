<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Consulta tu historial</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen antialiased text-gray-900">

<div x-data="consultaApp()" x-init="init()" x-cloak>

    {{-- ── HEADER ── --}}
    <div class="bg-white border-b border-gray-100 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-6">

            {{-- Marca --}}
            <div class="flex justify-center mb-6">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-gray-800 font-bold text-lg tracking-tight">{{ config('app.name') }}</span>
                </div>
            </div>

            {{-- Título --}}
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-1">Consulta tu historial</h1>
                <p class="text-sm text-gray-500">
                    Ingresa tu <strong class="text-gray-700 font-semibold">folio</strong> o
                    <strong class="text-gray-700 font-semibold">número de teléfono</strong>
                </p>
            </div>

            {{-- Búsqueda --}}
            <form method="GET" action="{{ route('consulta') }}"
                  class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex items-stretch divide-x divide-gray-100">

                <label class="flex items-center gap-3 flex-1 px-4 py-3 cursor-text focus-within:bg-blue-50/40 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           name="buscar"
                           value="{{ $buscar ?? '' }}"
                           autofocus
                           placeholder="CLI-0001 · 5512345678"
                           class="flex-1 text-sm text-gray-700 placeholder-gray-400 bg-transparent border-0 outline-none focus:ring-0 p-0"/>
                    @if ($buscar)
                        <a href="{{ route('consulta') }}"
                           class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </label>

                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                               text-white text-sm font-semibold transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
            </form>

        </div>
    </div>

    {{-- ── CONTENIDO ── --}}
    <div class="max-w-3xl mx-auto px-4 py-8 pb-16">

        {{-- Estado inicial --}}
        @if (!$buscar)
            <div class="flex flex-col items-center text-center py-16">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-4 border border-blue-100">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <p class="text-gray-700 font-semibold mb-1">Tu historial en un solo lugar</p>
                <p class="text-gray-400 text-sm max-w-xs leading-relaxed">
                    Consulta tus observaciones, peso, edad y los productos recomendados en cada visita.
                </p>
            </div>
        @endif

        {{-- Sin resultados --}}
        @if ($notFound)
            <div class="flex flex-col items-center text-center py-16">
                <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mb-4 border border-red-100">
                    <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-800 font-bold text-lg mb-1">No encontramos ningún registro</p>
                <p class="text-gray-400 text-sm max-w-xs">
                    Verifica que el folio, nombre o teléfono sean correctos e intenta de nuevo.
                </p>
            </div>
        @endif

        {{-- ── CLIENTE ENCONTRADO ── --}}
        @if ($cliente)

            {{-- Card del cliente --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm flex-shrink-0">
                    <span class="text-xl font-bold text-white">
                        {{ mb_strtoupper(mb_substr($cliente->name, 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-lg font-bold text-gray-900 truncate">{{ $cliente->name }}</h2>
                        @if ($cliente->folio)
                            <span class="inline-flex px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-mono font-bold rounded-lg border border-blue-100">
                                {{ $cliente->folio }}
                            </span>
                        @endif
                        @if ($cliente->status)
                            @php
                                $sc = [
                                    'Activo'     => 'bg-green-100 text-green-700 border-green-200',
                                    'Inactivo'   => 'bg-gray-100 text-gray-500 border-gray-200',
                                    'Prospecto'  => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Recurrente' => 'bg-blue-100 text-blue-700 border-blue-200',
                                ];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $sc[$cliente->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ $cliente->status }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-0.5 flex-wrap text-xs text-gray-400">
                        @if ($cliente->phone)
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $cliente->phone }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                            </svg>
                            {{ $cliente->observations->count() }} {{ $cliente->observations->count() === 1 ? 'registro' : 'registros' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Sin observaciones --}}
            @if ($cliente->observations->isEmpty())
                <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 py-14 flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                        </svg>
                    </div>
                    <p class="text-gray-600 font-semibold mb-1">Sin registros aún</p>
                    <p class="text-gray-400 text-sm">Tu historial está vacío por el momento.</p>
                </div>
            @else

                {{-- Divisor --}}
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-px flex-1 bg-gray-100"></div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-3">Historial de registros</span>
                    <div class="h-px flex-1 bg-gray-100"></div>
                </div>

                {{-- Observaciones --}}
                <div class="space-y-4">
                    @foreach ($cliente->observations as $index => $obs)
                        @php $num = $cliente->observations->count() - $index; @endphp
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                            {{-- Encabezado --}}
                            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50 bg-gray-50/60">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[10px] font-bold text-blue-600">{{ $num }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-700">Registro #{{ $num }}</p>
                                        <p class="text-[11px] text-gray-400">
                                            {{ $obs->created_at->format('d/m/Y') }} · {{ $obs->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($obs->weight)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full border border-blue-100">
                                            {{ $obs->weight }} kg
                                        </span>
                                    @endif
                                    @if ($obs->age)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full border border-gray-200">
                                            {{ $obs->age }} años
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Cuerpo --}}
                            <div class="px-5 py-4 space-y-4">

                                @if ($obs->observation)
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Observaciones</p>
                                        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                            {{ $obs->observation }}
                                        </p>
                                    </div>
                                @endif

                                @if ($obs->suggested_products)
                                    @php
                                        $prods = array_filter(array_map('trim', explode(',', $obs->suggested_products)));
                                    @endphp
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Productos sugeridos</p>
                                        <div class="flex flex-wrap gap-3">
                                            @foreach ($prods as $nombre)
                                                @php $prod = $productosMap->get($nombre); @endphp
                                                <button type="button"
                                                        @click="abrirProducto({{ json_encode($nombre) }})"
                                                        class="group flex items-center gap-3 p-3 bg-white border border-gray-100 rounded-xl shadow-sm
                                                               hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 text-left cursor-pointer">
                                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0">
                                                        @if ($prod && $prod['image_url'])
                                                            <img src="{{ $prod['image_url'] }}" alt="{{ $nombre }}" class="w-full h-full object-cover"/>
                                                        @else
                                                            <svg class="w-5 h-5 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors max-w-[130px] truncate">
                                                            {{ $nombre }}
                                                        </p>
                                                        @if ($prod && $prod['category'])
                                                            <p class="text-xs text-gray-400 truncate max-w-[130px]">{{ $prod['category'] }}</p>
                                                        @endif
                                                        @if ($prod && $prod['price'])
                                                            <p class="text-xs font-bold text-green-600 mt-0.5">${{ number_format($prod['price'], 2) }}</p>
                                                        @endif
                                                    </div>
                                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 flex-shrink-0 ml-1 transition-colors"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (!$obs->observation && !$obs->suggested_products)
                                    <p class="text-sm text-gray-400 italic">Sin detalles registrados.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

    </div>

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

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-y-auto"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop>

                {{-- Imagen --}}
                <div class="relative w-full h-60 bg-gray-50 overflow-hidden rounded-t-2xl">
                    <template x-if="productoActual?.image_url">
                        <img :src="productoActual.image_url" :alt="productoActual.name" class="w-full h-full object-cover"/>
                    </template>
                    <template x-if="!productoActual?.image_url">
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </template>

                    <button @click="cerrarProducto()"
                            class="absolute top-3 right-3 p-2 bg-black/40 hover:bg-black/60 text-white rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="absolute bottom-3 left-3">
                        <template x-if="productoActual?.available">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500 text-white text-xs font-semibold rounded-full shadow">
                                <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                                Disponible
                            </span>
                        </template>
                        <template x-if="productoActual && productoActual.available === false">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full shadow">
                                <span class="w-1.5 h-1.5 bg-white/50 rounded-full"></span>
                                No disponible
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Info --}}
                <div class="p-6">
                    <template x-if="productoActual?.category">
                        <span class="inline-flex items-center px-2.5 py-0.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-md mb-2"
                              x-text="productoActual.category"></span>
                    </template>
                    <h3 class="text-xl font-bold text-gray-900 mb-1" x-text="productoActual?.name ?? '—'"></h3>
                    <template x-if="productoActual?.price">
                        <p class="text-2xl font-bold text-green-600 mb-4">
                            $<span x-text="parseFloat(productoActual.price).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                        </p>
                    </template>

                    <template x-if="productoActual?.description">
                        <div class="mb-5">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Descripción</p>
                            <p class="text-sm text-gray-700 leading-relaxed" x-text="productoActual.description"></p>
                        </div>
                    </template>

                    <template x-if="productoActual?.video_url">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Video</p>
                            <template x-if="isYoutube(productoActual.video_url)">
                                <div class="relative w-full" style="padding-top: 56.25%">
                                    <iframe :src="youtubeEmbed(productoActual.video_url)"
                                            class="absolute inset-0 w-full h-full rounded-xl"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                </div>
                            </template>
                            <template x-if="!isYoutube(productoActual.video_url)">
                                <video :src="productoActual.video_url" controls
                                       class="w-full rounded-xl border border-gray-100 max-h-56 bg-black"></video>
                            </template>
                        </div>
                    </template>

                    <template x-if="!productoActual?.description && !productoActual?.video_url">
                        <p class="text-sm text-gray-400 italic">Sin descripción disponible.</p>
                    </template>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function consultaApp() {
    return {
        productoModal: false,
        productoActual: null,
        productosMap: @json($productosMap),

        init() {},

        abrirProducto(nombre) {
            this.productoActual = this.productosMap[nombre]
                ?? { name: nombre, image_url: null, description: null, price: null, category: null, available: null, video_url: null };
            this.productoModal = true;
            document.body.style.overflow = 'hidden';
        },

        cerrarProducto() {
            this.productoModal = false;
            this.productoActual = null;
            document.body.style.overflow = '';
        },

        isYoutube(url) {
            return url && /youtube\.com|youtu\.be/.test(url);
        },

        youtubeEmbed(url) {
            if (!url) return '';
            const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
            return match ? `https://www.youtube.com/embed/${match[1]}` : url;
        },
    };
}
</script>

</body>
</html>
