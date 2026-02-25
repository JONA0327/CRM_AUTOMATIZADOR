<x-admin-layout title="Productos">

    {{-- Flash success --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3.5 text-sm">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3.5 text-sm">
            <p class="font-semibold mb-1">Corrige los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div x-data="productosApp()" x-init="init()">

        {{-- ── HEADER ── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Productos</h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ $productos->total() }} producto{{ $productos->total() !== 1 ? 's' : '' }} en el catálogo
                </p>
            </div>
            <button @click="abrirCrear()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Añadir Producto
            </button>
        </div>

        {{-- ── BARRA DE BÚSQUEDA Y FILTROS ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden">
            <form method="GET" action="{{ route('productos.index') }}"
                  class="flex items-stretch divide-x divide-gray-100">

                {{-- Campo de búsqueda con ícono flex --}}
                <label class="flex items-center gap-3 flex-1 px-4 py-3 cursor-text
                               focus-within:bg-blue-50/40 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por nombre, categoría o descripción..."
                           class="flex-1 text-sm text-gray-700 placeholder-gray-400 bg-transparent border-0 outline-none focus:ring-0 p-0"/>
                    @if (request('buscar'))
                        <a href="{{ route('productos.index', array_merge(request()->except('buscar'), ['categoria' => request('categoria')])) }}"
                           class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </label>

                {{-- Selector de categoría --}}
                @if ($categorias->isNotEmpty())
                    <div class="flex items-center px-4 py-3">
                        <select name="categoria"
                                class="text-sm text-gray-600 bg-transparent border-0 outline-none focus:ring-0 cursor-pointer pr-6 appearance-none">
                            <option value="">Todas las categorías</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat }}" @selected(request('categoria') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Botón buscar --}}
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>

            </form>

            {{-- Filtros activos --}}
            @if (request()->hasAny(['buscar', 'categoria']))
                <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 border-t border-blue-100 text-xs text-blue-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filtros activos:</span>
                    @if (request('buscar'))
                        <span class="px-2 py-0.5 bg-blue-200/60 rounded-full font-medium">"{{ request('buscar') }}"</span>
                    @endif
                    @if (request('categoria'))
                        <span class="px-2 py-0.5 bg-blue-200/60 rounded-full font-medium">{{ request('categoria') }}</span>
                    @endif
                    <a href="{{ route('productos.index') }}"
                       class="ml-auto font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </div>

        {{-- ── GRID / VACÍO ── --}}
        @if ($productos->isEmpty())
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 py-24 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mb-5 shadow-sm">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <p class="text-gray-800 font-semibold text-lg mb-1.5">
                    {{ request()->hasAny(['buscar','categoria']) ? 'Sin resultados' : 'Sin productos aún' }}
                </p>
                <p class="text-gray-400 text-sm mb-7 max-w-xs">
                    {{ request()->hasAny(['buscar','categoria'])
                        ? 'Ningún producto coincide con tu búsqueda. Prueba con otros términos.'
                        : 'Comienza añadiendo tu primer producto al catálogo para que el bot pueda recomendarlo.' }}
                </p>
                @unless (request()->hasAny(['buscar','categoria']))
                    <button @click="abrirCrear()"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir primer producto
                    </button>
                @endunless
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($productos as $producto)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

                        {{-- Imagen --}}
                        <div class="relative h-40 bg-gray-50 flex-shrink-0 overflow-hidden">
                            @if ($producto->image_url)
                                <img src="{{ $producto->image_url }}" alt="{{ $producto->name }}"
                                     class="w-full h-full object-cover"/>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Badges encima de la imagen --}}
                            <div class="absolute inset-x-0 top-0 flex items-start justify-between p-2 gap-2">
                                @if ($producto->category)
                                    <span class="px-2 py-0.5 bg-white/90 backdrop-blur-sm text-xs font-semibold text-gray-700 rounded-md shadow-sm truncate max-w-[60%]">
                                        {{ $producto->category }}
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold shadow-sm flex-shrink-0
                                             {{ $producto->available ? 'bg-green-500 text-white' : 'bg-red-400 text-white' }}">
                                    {{ $producto->available ? 'Disponible' : 'No disp.' }}
                                </span>
                            </div>
                        </div>

                        {{-- Cuerpo --}}
                        <div class="flex flex-col flex-1 p-4">
                            <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 mb-1">
                                {{ $producto->name }}
                            </h3>
                            @if ($producto->description)
                                <p class="text-xs text-gray-400 line-clamp-2 leading-relaxed flex-1">
                                    {{ $producto->description }}
                                </p>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50">
                                <span class="text-base font-bold text-blue-700">
                                    ${{ number_format($producto->price, 2) }}
                                </span>
                                @if ($producto->video_url)
                                    <a href="{{ $producto->video_url }}" target="_blank"
                                       class="p-1.5 rounded-md transition-colors flex items-center gap-1
                                              {{ $producto->video_es_archivo
                                                  ? 'text-gray-400 hover:text-blue-500 hover:bg-blue-50'
                                                  : 'text-gray-400 hover:text-red-500 hover:bg-red-50' }}"
                                       title="{{ $producto->video_es_archivo ? 'Reproducir video' : 'Ver en YouTube/externo' }}">
                                        @if ($producto->video_es_archivo)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05A6.34 6.34 0 003.15 15.3a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.26 8.26 0 004.84 1.56V6.8a4.85 4.85 0 01-1.07-.11z"/>
                                            </svg>
                                        @endif
                                    </a>
                                @endif
                            </div>

                            {{-- Sugerido: chips --}}
                            @if ($producto->suggested)
                                @php
                                    $tags = array_filter(array_map('trim', explode(',', $producto->suggested)));
                                    $visible = array_slice($tags, 0, 3);
                                    $extra   = count($tags) - count($visible);
                                @endphp
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach ($visible as $tag)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full border border-emerald-100">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                    @if ($extra > 0)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-full"
                                              title="{{ $producto->suggested }}">
                                            +{{ $extra }} más
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="px-4 pb-4 flex gap-2">
                            <button @click="abrirEditar({{ json_encode([
                                'id'             => $producto->id,
                                'name'           => $producto->name,
                                'description'    => $producto->description,
                                'price'          => $producto->price,
                                'category'       => $producto->category,
                                'available'      => $producto->available,
                                'suggested'      => $producto->suggested,
                                'video'          => $producto->video,
                                'video_es_archivo' => $producto->video_es_archivo,
                                'image_url'      => $producto->image_url,
                                'update_url'     => route('productos.update', $producto),
                            ]) }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </button>
                            <form method="POST" action="{{ route('productos.destroy', $producto) }}"
                                  onsubmit="return confirm('¿Eliminar «{{ addslashes($producto->name) }}»?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($productos->hasPages())
                <div class="mt-6">{{ $productos->links() }}</div>
            @endif
        @endif

        {{-- ══ MODAL ══ --}}
        <template x-teleport="body">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display:none">

                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cerrar()"></div>

                {{-- Panel --}}
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop>

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900"
                                x-text="modo === 'crear' ? 'Añadir Producto' : 'Editar Producto'"></h3>
                        </div>
                        <button @click="cerrar()"
                                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Form --}}
                    <form :action="formAction" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" :value="modo === 'editar' ? 'PUT' : 'POST'">

                        <div class="px-6 py-5 space-y-4">

                            {{-- Nombre + Categoría --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        Nombre <span class="text-red-500 normal-case font-normal">*</span>
                                    </label>
                                    <input type="text" name="name" x-model="form.name" required
                                           list="productos-catalogo"
                                           placeholder="Buscar o escribir nombre..."
                                           @change="autoCategoria()"
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
                                    <datalist id="productos-catalogo">
                                        <option value="4Life® Plus">
                                        <option value="4Life Avanzado®">
                                        <option value="4Life Masticable®">
                                        <option value="4Life TF Boost®">
                                        <option value="4Life Renewal®">
                                        <option value="4Life RioVida Jugo®">
                                        <option value="4Life RioVida Burst®">
                                        <option value="4Life RioVida Stix®">
                                        <option value="4Life NutraStart®">
                                        <option value="4Life BCV®">
                                        <option value="4Life Belle Vie®">
                                        <option value="4Life GL-Coach®">
                                        <option value="4Life Vistari®">
                                        <option value="4Life Collagen®">
                                        <option value="4Life TF AG-Pro®">
                                        <option value="4Life Reflexion®">
                                        <option value="4Life Glutamine Prime®">
                                        <option value="4Life Respari®">
                                        <option value="4Life BioEFA®">
                                        <option value="4Life Tea4Life®">
                                        <option value="4Life Pre/O Biotics®">
                                        <option value="4Life Aloe Vera Stix Tropical®">
                                        <option value="4Life PRO-TF Chocolate®/Vainilla®">
                                        <option value="4Life Prezoom®">
                                        <option value="4Life Renuvo®">
                                        <option value="4Life TFORM Man®">
                                        <option value="4Life TFORM Woman®">
                                        <option value="4LIFE TFORM SHPRITE®">
                                        <option value="Limpiador con aceite de efecto espumoso">
                                        <option value="Tónico 4 en 1">
                                        <option value="Mascarilla de Barro Volcánico">
                                        <option value="Suero para uso facial de Vitaminas">
                                        <option value="Crema para el contorno de ojos">
                                        <option value="Crema Humectante">
                                        <option value="Mascarilla Hidratante en Velo">
                                        <option value="Sistema Completo AKwa">
                                        <option value="Enummi Pasta Dental®">
                                        <option value="Enummi Fórmula Concentrada®">
                                    </datalist>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Categoría</label>
                                    <select name="category" x-model="form.category"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                        <option value="">— Selecciona una categoría —</option>
                                        <option value="Sistema Inmunológico">Sistema Inmunológico</option>
                                        <option value="Productos Especializados">Productos Especializados</option>
                                        <option value="Salud Digestiva">Salud Digestiva</option>
                                        <option value="Control de Peso">Control de Peso</option>
                                        <option value="Salud y Belleza de la Piel">Salud y Belleza de la Piel</option>
                                        <option value="Higiene y Cuidado Personal">Higiene y Cuidado Personal</option>
                                    </select>
                                    <p x-show="form.category" class="mt-1 text-xs text-blue-600 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span x-text="form.category"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Descripción</label>
                                <textarea name="description" x-model="form.description" rows="3"
                                          placeholder="Descripción del producto, modo de uso, beneficios..."
                                          class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
                            </div>

                            {{-- Precio --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                        Precio <span class="text-red-500 normal-case font-normal">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                        <input type="number" name="price" x-model="form.price"
                                               required min="0" step="0.01" placeholder="0.00"
                                               class="w-full pl-7 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
                                    </div>
                                </div>
                                {{-- empty col for grid balance --}}
                                <div></div>
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Sugerido para</label>
                                    <input type="hidden" name="suggested" :value="form.suggested"/>
                                    {{-- Tag input box --}}
                                    <div class="min-h-[42px] w-full px-2.5 py-1.5 border border-gray-200 rounded-lg
                                                focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500
                                                transition bg-white flex flex-wrap gap-1.5 items-center cursor-text"
                                         @click="$refs.tagInput.focus()">
                                        <template x-for="(tag, i) in tags" :key="i">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full flex-shrink-0">
                                                <span x-text="tag"></span>
                                                <button type="button" @click.stop="removeTag(i)"
                                                        class="text-emerald-500 hover:text-emerald-800 leading-none ml-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>
                                        <input x-ref="tagInput"
                                               x-model="tagInput"
                                               type="text"
                                               @keydown="handleTagInput($event)"
                                               @blur="flushTagInput()"
                                               @paste.prevent="
                                                   const text = $event.clipboardData.getData('text');
                                                   const parts = splitTags(text);
                                                   if (parts.length > 1) {
                                                       tags.push(...parts);
                                                       form.suggested = tags.join(', ');
                                                       tagInput = '';
                                                   } else {
                                                       tagInput += text;
                                                   }
                                               "
                                               placeholder="Escribe una condición..."
                                               class="flex-1 min-w-[140px] text-sm border-0 outline-none focus:ring-0 p-0 bg-transparent placeholder-gray-400 py-1"/>
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-400">
                                        Presiona <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono text-[10px]">,</kbd>
                                        o <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono text-[10px]">Enter</kbd>
                                        para añadir cada condición. También puedes pegar varias separadas por comas.
                                    </p>
                                </div>
                            </div>

                            {{-- Imagen --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Imagen</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                                        <template x-if="imagenPreview">
                                            <img :src="imagenPreview" class="w-full h-full object-cover rounded-xl"/>
                                        </template>
                                        <template x-if="!imagenPreview">
                                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <label class="flex-1 flex items-center gap-3 px-4 py-3 border border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-colors">
                                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-gray-600 font-medium">Seleccionar imagen</p>
                                            <p class="text-xs text-gray-400">PNG, JPG, WEBP — máx. 5 MB</p>
                                        </div>
                                        <input type="file" name="image" accept="image/*" class="hidden" @change="verImagen($event)"/>
                                    </label>
                                </div>
                            </div>

                            {{-- Video --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Video</label>

                                {{-- Toggle URL / Archivo --}}
                                <div class="flex rounded-lg border border-gray-200 overflow-hidden mb-3 w-fit">
                                    <button type="button"
                                            @click="videoTipo = 'url'"
                                            :class="videoTipo === 'url'
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-gray-600 hover:bg-gray-50'"
                                            class="flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        URL externa
                                    </button>
                                    <button type="button"
                                            @click="videoTipo = 'archivo'"
                                            :class="videoTipo === 'archivo'
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-gray-600 hover:bg-gray-50'"
                                            class="flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold transition-colors border-l border-gray-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        Subir archivo
                                    </button>
                                </div>

                                {{-- Opción URL --}}
                                <div x-show="videoTipo === 'url'">
                                    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition">
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <input type="text" name="video" x-model="form.video"
                                               placeholder="https://youtube.com/... o cualquier URL de video"
                                               class="flex-1 py-2.5 text-sm bg-transparent border-0 outline-none focus:ring-0 p-0"/>
                                    </div>
                                </div>

                                {{-- Opción Archivo --}}
                                <div x-show="videoTipo === 'archivo'">
                                    <label class="flex items-center gap-4 p-4 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition-colors group">
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                            <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <template x-if="!videoNombre">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-700">Seleccionar video</p>
                                                    <p class="text-xs text-gray-400 mt-0.5">MP4, WebM, MOV, AVI — máx. 200 MB</p>
                                                </div>
                                            </template>
                                            <template x-if="videoNombre">
                                                <div>
                                                    <p class="text-sm font-semibold text-blue-700 truncate" x-text="videoNombre"></p>
                                                    <p class="text-xs text-gray-400 mt-0.5">Video seleccionado — haz clic para cambiar</p>
                                                </div>
                                            </template>
                                        </div>
                                        <input type="file" name="video_file"
                                               accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska,video/ogg"
                                               class="hidden"
                                               @change="videoNombre = $event.target.files[0]?.name ?? null"/>
                                    </label>
                                    <p x-show="modo === 'editar' && videoNombre === null && form.video && !form.video.startsWith('http')"
                                       class="mt-1.5 text-xs text-blue-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Ya tiene un video subido. Selecciona un nuevo archivo solo si quieres reemplazarlo.
                                    </p>
                                </div>
                            </div>

                            {{-- Disponible --}}
                            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Disponible para venta</p>
                                    <p class="text-xs text-gray-400 mt-0.5">El bot podrá recomendar este producto</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="available" class="sr-only peer"
                                           :checked="form.available"
                                           @change="form.available = $event.target.checked"/>
                                    <div class="w-10 h-5 bg-gray-300 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer
                                                peer-checked:after:translate-x-5 peer-checked:after:border-white
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:bg-white after:border after:rounded-full
                                                after:h-4 after:w-4 after:transition-all
                                                peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/50 rounded-b-2xl">
                            <button type="button" @click="cerrar()"
                                    class="px-5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                <span x-text="modo === 'crear' ? 'Guardar producto' : 'Actualizar producto'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>

    <script>
    function productosApp() {
        return {
            open: false,
            modo: 'crear',
            formAction: '{{ route('productos.store') }}',
            imagenPreview: null,
            videoTipo: 'url',   // 'url' | 'archivo'
            videoNombre: null,  // nombre del archivo de video seleccionado
            tags: [],           // array of suggested condition strings
            tagInput: '',       // current text being typed in tag input
            form: { name:'', description:'', price:'', category:'', available:true, suggested:'', video:'' },

            // Split by commas, but ignore commas inside parentheses
            // e.g. "herpes (simple, ocular, vaginal)" → one single tag
            splitTags(str) {
                if (!str) return [];
                const result = [];
                let current = '';
                let depth = 0;
                for (const ch of str) {
                    if (ch === '(') { depth++; current += ch; }
                    else if (ch === ')') { depth = Math.max(0, depth - 1); current += ch; }
                    else if (ch === ',' && depth === 0) {
                        const t = current.trim();
                        if (t) result.push(t);
                        current = '';
                    } else { current += ch; }
                }
                const t = current.trim();
                if (t) result.push(t);
                return result;
            },

            parseTags(str) {
                return this.splitTags(str);
            },

            handleTagInput(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = this.tagInput.trim();
                    if (val) { this.tags.push(val); this.form.suggested = this.tags.join(', '); }
                    this.tagInput = '';
                } else if (e.key === ',') {
                    // Only treat comma as separator when we're NOT inside open parentheses
                    const opens  = (this.tagInput.match(/\(/g) || []).length;
                    const closes = (this.tagInput.match(/\)/g) || []).length;
                    if (opens <= closes) {
                        e.preventDefault();
                        const val = this.tagInput.trim();
                        if (val) { this.tags.push(val); this.form.suggested = this.tags.join(', '); }
                        this.tagInput = '';
                    }
                    // else: inside parentheses → allow comma to be typed normally
                } else if (e.key === 'Backspace' && !this.tagInput && this.tags.length) {
                    this.tags.pop();
                    this.form.suggested = this.tags.join(', ');
                }
            },

            flushTagInput() {
                const raw = this.tagInput.trim();
                if (!raw) return;
                const parts = this.splitTags(raw);
                this.tags.push(...parts);
                this.form.suggested = this.tags.join(', ');
                this.tagInput = '';
            },

            removeTag(i) {
                this.tags.splice(i, 1);
                this.form.suggested = this.tags.join(', ');
            },

            init() {
                @if ($errors->any())
                    this.abrirCrear();
                    this.form.name        = @json(old('name', ''));
                    this.form.description = @json(old('description', ''));
                    this.form.price       = @json(old('price', ''));
                    this.form.category    = @json(old('category', ''));
                    this.form.available   = @json(old('available', true));
                    this.form.suggested   = @json(old('suggested', ''));
                    this.form.video       = @json(old('video', ''));
                    this.tags = this.parseTags(this.form.suggested);
                @endif
            },

            abrirCrear() {
                this.modo = 'crear';
                this.formAction = '{{ route('productos.store') }}';
                this.imagenPreview = null;
                this.videoTipo = 'url';
                this.videoNombre = null;
                this.tags = [];
                this.tagInput = '';
                this.form = { name:'', description:'', price:'', category:'', available:true, suggested:'', video:'' };
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            abrirEditar(p) {
                this.modo = 'editar';
                this.formAction = p.update_url;
                this.imagenPreview = p.image_url ?? null;
                this.videoTipo = p.video_es_archivo ? 'archivo' : 'url';
                this.videoNombre = null;
                this.tags = this.parseTags(p.suggested ?? '');
                this.tagInput = '';
                this.form = {
                    name: p.name ?? '', description: p.description ?? '',
                    price: p.price ?? '', category: p.category ?? '',
                    available: p.available ?? true, suggested: p.suggested ?? '',
                    video: p.video_es_archivo ? '' : (p.video ?? ''),
                };
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            cerrar() {
                this.open = false;
                document.body.style.overflow = '';
            },

            autoCategoria() {
                const mapa = {
                    // Sistema Inmunológico
                    '4Life® Plus':              'Sistema Inmunológico',
                    '4Life Avanzado®':          'Sistema Inmunológico',
                    '4Life Masticable®':        'Sistema Inmunológico',
                    '4Life TF Boost®':          'Sistema Inmunológico',
                    '4Life Renewal®':           'Sistema Inmunológico',
                    '4Life RioVida Jugo®':      'Sistema Inmunológico',
                    '4Life RioVida Burst®':     'Sistema Inmunológico',
                    '4Life RioVida Stix®':      'Sistema Inmunológico',
                    '4Life NutraStart®':        'Sistema Inmunológico',
                    // Productos Especializados
                    '4Life BCV®':               'Productos Especializados',
                    '4Life Belle Vie®':         'Productos Especializados',
                    '4Life GL-Coach®':          'Productos Especializados',
                    '4Life Vistari®':           'Productos Especializados',
                    '4Life Collagen®':          'Productos Especializados',
                    '4Life TF AG-Pro®':         'Productos Especializados',
                    '4Life Reflexion®':         'Productos Especializados',
                    '4Life Glutamine Prime®':   'Productos Especializados',
                    '4Life Respari®':           'Productos Especializados',
                    // Salud Digestiva
                    '4Life BioEFA®':            'Salud Digestiva',
                    '4Life Tea4Life®':          'Salud Digestiva',
                    '4Life Pre/O Biotics®':     'Salud Digestiva',
                    '4Life Aloe Vera Stix Tropical®': 'Salud Digestiva',
                    // Control de Peso
                    '4Life PRO-TF Chocolate®/Vainilla®': 'Control de Peso',
                    '4Life Prezoom®':           'Control de Peso',
                    '4Life Renuvo®':            'Control de Peso',
                    '4Life TFORM Man®':         'Control de Peso',
                    '4Life TFORM Woman®':       'Control de Peso',
                    '4LIFE TFORM SHPRITE®':     'Control de Peso',
                    // Salud y Belleza de la Piel
                    'Limpiador con aceite de efecto espumoso': 'Salud y Belleza de la Piel',
                    'Tónico 4 en 1':            'Salud y Belleza de la Piel',
                    'Mascarilla de Barro Volcánico': 'Salud y Belleza de la Piel',
                    'Suero para uso facial de Vitaminas': 'Salud y Belleza de la Piel',
                    'Crema para el contorno de ojos': 'Salud y Belleza de la Piel',
                    'Crema Humectante':         'Salud y Belleza de la Piel',
                    'Mascarilla Hidratante en Velo': 'Salud y Belleza de la Piel',
                    'Sistema Completo AKwa':    'Salud y Belleza de la Piel',
                    // Higiene y Cuidado Personal
                    'Enummi Pasta Dental®':     'Higiene y Cuidado Personal',
                    'Enummi Fórmula Concentrada®': 'Higiene y Cuidado Personal',
                };
                const cat = mapa[this.form.name];
                if (cat) this.form.category = cat;
            },

            verImagen(e) {
                const f = e.target.files[0];
                if (!f) return;
                const r = new FileReader();
                r.onload = ev => this.imagenPreview = ev.target.result;
                r.readAsDataURL(f);
            },
        };
    }
    </script>

</x-admin-layout>
