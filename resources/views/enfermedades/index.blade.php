<x-admin-layout title="Enfermedades">

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

    <div x-data="enfermedadesApp()" x-init="init()">

        {{-- ── HEADER ── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Enfermedades</h2>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ $enfermedades->total() }} enfermedad{{ $enfermedades->total() !== 1 ? 'es' : '' }} en el catálogo
                </p>
            </div>
            <button @click="abrirCrear()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Añadir Enfermedad
            </button>
        </div>

        {{-- ── BARRA DE BÚSQUEDA Y FILTROS ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden">
            <form method="GET" action="{{ route('enfermedades.index') }}"
                  class="flex items-stretch divide-x divide-gray-100">

                <label class="flex items-center gap-3 flex-1 px-4 py-3 cursor-text focus-within:bg-blue-50/40 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="Buscar por nombre, descripción o síntomas..."
                           class="flex-1 text-sm text-gray-700 placeholder-gray-400 bg-transparent border-0 outline-none focus:ring-0 p-0"/>
                    @if (request('buscar'))
                        <a href="{{ route('enfermedades.index', array_merge(request()->except('buscar'), ['categoria' => request('categoria')])) }}"
                           class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </label>

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

                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
            </form>

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
                    <a href="{{ route('enfermedades.index') }}"
                       class="ml-auto font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                        Limpiar filtros
                    </a>
                </div>
            @endif
        </div>

        {{-- ── GRID / VACÍO ── --}}
        @if ($enfermedades->isEmpty())
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 py-24 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-50 rounded-2xl flex items-center justify-center mb-5 shadow-sm">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-gray-800 font-semibold text-lg mb-1.5">
                    {{ request()->hasAny(['buscar','categoria']) ? 'Sin resultados' : 'Sin enfermedades aún' }}
                </p>
                <p class="text-gray-400 text-sm mb-7 max-w-xs">
                    {{ request()->hasAny(['buscar','categoria'])
                        ? 'Ninguna enfermedad coincide con tu búsqueda.'
                        : 'Comienza añadiendo enfermedades para que el bot pueda sugerir productos.' }}
                </p>
                @unless (request()->hasAny(['buscar','categoria']))
                    <button @click="abrirCrear()"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Añadir primera enfermedad
                    </button>
                @endunless
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($enfermedades as $enfermedad)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

                        {{-- Imagen --}}
                        <div class="relative h-40 bg-gray-50 flex-shrink-0 overflow-hidden">
                            @if ($enfermedad->image_url)
                                <img src="{{ $enfermedad->image_url }}" alt="{{ $enfermedad->name }}"
                                     class="w-full h-full object-cover"/>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-50 to-blue-50">
                                    <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Categoría badge --}}
                            <div class="absolute inset-x-0 top-0 flex items-start p-2">
                                @if ($enfermedad->category)
                                    <span class="px-2 py-0.5 bg-white/90 backdrop-blur-sm text-xs font-semibold text-gray-700 rounded-md shadow-sm truncate max-w-[80%]">
                                        {{ $enfermedad->category }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Cuerpo --}}
                        <div class="flex flex-col flex-1 p-4">
                            <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 mb-1">
                                {{ $enfermedad->name }}
                            </h3>
                            @if ($enfermedad->description)
                                <p class="text-xs text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $enfermedad->description }}
                                </p>
                            @endif

                            {{-- Síntomas chips --}}
                            @if ($enfermedad->symptoms)
                                @php
                                    $sintTags = array_filter(array_map('trim', explode(',', $enfermedad->symptoms)));
                                    $sintVisible = array_slice($sintTags, 0, 2);
                                    $sintExtra   = count($sintTags) - count($sintVisible);
                                @endphp
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach ($sintVisible as $s)
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-100">
                                            {{ $s }}
                                        </span>
                                    @endforeach
                                    @if ($sintExtra > 0)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-full">
                                            +{{ $sintExtra }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Productos sugeridos chips --}}
                            @if ($enfermedad->suggested)
                                @php
                                    $sugTags = array_filter(array_map('trim', explode(',', $enfermedad->suggested)));
                                    $sugVisible = array_slice($sugTags, 0, 2);
                                    $sugExtra   = count($sugTags) - count($sugVisible);
                                @endphp
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach ($sugVisible as $s)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full border border-emerald-100">
                                            {{ $s }}
                                        </span>
                                    @endforeach
                                    @if ($sugExtra > 0)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-full"
                                              title="{{ $enfermedad->suggested }}">+{{ $sugExtra }} más</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="px-4 pb-4 flex gap-2">
                            <button @click="abrirEditar({{ json_encode([
                                'id'          => $enfermedad->id,
                                'name'        => $enfermedad->name,
                                'description' => $enfermedad->description,
                                'category'    => $enfermedad->category,
                                'symptoms'    => $enfermedad->symptoms,
                                'treatment'   => $enfermedad->treatment,
                                'prevention'  => $enfermedad->prevention,
                                'suggested'   => $enfermedad->suggested,
                                'image_url'   => $enfermedad->image_url,
                                'update_url'  => route('enfermedades.update', $enfermedad),
                            ]) }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </button>
                            <form method="POST" action="{{ route('enfermedades.destroy', $enfermedad) }}"
                                  onsubmit="return confirm('¿Eliminar «{{ addslashes($enfermedad->name) }}»?')">
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

            @if ($enfermedades->hasPages())
                <div class="mt-6">{{ $enfermedades->links() }}</div>
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

                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cerrar()"></div>

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
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-900"
                                x-text="modo === 'crear' ? 'Añadir Enfermedad' : 'Editar Enfermedad'"></h3>
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
                                           placeholder="Nombre de la enfermedad..."
                                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"/>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Categoría</label>
                                    <select name="category" x-model="form.category"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                        <option value="">— Selecciona una categoría —</option>
                                        <option value="Sistema Inmunológico">Sistema Inmunológico</option>
                                        <option value="Sistema Cardiovascular">Sistema Cardiovascular</option>
                                        <option value="Sistema Digestivo">Sistema Digestivo</option>
                                        <option value="Sistema Nervioso">Sistema Nervioso</option>
                                        <option value="Sistema Endocrino">Sistema Endocrino</option>
                                        <option value="Enfermedades Infecciosas">Enfermedades Infecciosas</option>
                                        <option value="Enfermedades Autoinmunes">Enfermedades Autoinmunes</option>
                                        <option value="Oncología">Oncología</option>
                                        <option value="Control de Peso">Control de Peso</option>
                                        <option value="Piel y Tegumentos">Piel y Tegumentos</option>
                                        <option value="Salud Mental">Salud Mental</option>
                                        <option value="General">General</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Descripción</label>
                                <textarea name="description" x-model="form.description" rows="3"
                                          placeholder="Descripción general de la enfermedad..."
                                          class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
                            </div>

                            {{-- Síntomas (tags) --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                                        Síntomas
                                    </span>
                                </label>
                                <input type="hidden" name="symptoms" :value="form.symptoms"/>
                                <div class="min-h-[42px] w-full px-2.5 py-1.5 border border-gray-200 rounded-lg
                                            focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500
                                            transition bg-white flex flex-wrap gap-1.5 items-center cursor-text"
                                     @click="$refs.symptomsInput.focus()">
                                    <template x-for="(tag, i) in symptomsTags" :key="i">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full flex-shrink-0">
                                            <span x-text="tag"></span>
                                            <button type="button" @click.stop="removeSymptom(i)"
                                                    class="text-amber-500 hover:text-amber-800 leading-none ml-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <input x-ref="symptomsInput"
                                           x-model="symptomsInput"
                                           type="text"
                                           @keydown="handleTagKeydown($event, 'symptoms')"
                                           @blur="flushTag('symptoms')"
                                           @paste.prevent="pasteTag($event, 'symptoms')"
                                           placeholder="Escribe un síntoma..."
                                           class="flex-1 min-w-[140px] text-sm border-0 outline-none focus:ring-0 p-0 bg-transparent placeholder-gray-400 py-1"/>
                                </div>
                                <p class="mt-1.5 text-xs text-gray-400">
                                    Presiona <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono text-[10px]">,</kbd>
                                    o <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono text-[10px]">Enter</kbd>
                                    para añadir cada síntoma.
                                </p>
                            </div>

                            {{-- Tratamiento --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                                        Tratamiento
                                    </span>
                                </label>
                                <textarea name="treatment" x-model="form.treatment" rows="2"
                                          placeholder="Indicaciones de tratamiento..."
                                          class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
                            </div>

                            {{-- Prevención --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span>
                                        Prevención
                                    </span>
                                </label>
                                <textarea name="prevention" x-model="form.prevention" rows="2"
                                          placeholder="Medidas de prevención..."
                                          class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
                            </div>

                            {{-- Productos sugeridos (tags) --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                                        Productos sugeridos
                                    </span>
                                </label>
                                <input type="hidden" name="suggested" :value="form.suggested"/>
                                <div class="min-h-[42px] w-full px-2.5 py-1.5 border border-gray-200 rounded-lg
                                            focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500
                                            transition bg-white flex flex-wrap gap-1.5 items-center cursor-text"
                                     @click="$refs.suggestedInput.focus()">
                                    <template x-for="(tag, i) in suggestedTags" :key="i">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full flex-shrink-0">
                                            <span x-text="tag"></span>
                                            <button type="button" @click.stop="removeSuggested(i)"
                                                    class="text-emerald-500 hover:text-emerald-800 leading-none ml-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <input x-ref="suggestedInput"
                                           x-model="suggestedInput"
                                           type="text"
                                           list="productos-sugeridos-lista"
                                           @keydown="handleTagKeydown($event, 'suggested')"
                                           @blur="flushTag('suggested')"
                                           @paste.prevent="pasteTag($event, 'suggested')"
                                           placeholder="Escribe un producto..."
                                           class="flex-1 min-w-[140px] text-sm border-0 outline-none focus:ring-0 p-0 bg-transparent placeholder-gray-400 py-1"/>
                                    <datalist id="productos-sugeridos-lista">
                                        @foreach ($productos as $producto)
                                            <option value="{{ $producto }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <p class="mt-1.5 text-xs text-gray-400">
                                    Presiona <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono text-[10px]">,</kbd>
                                    o <kbd class="px-1 py-0.5 bg-gray-100 rounded text-gray-600 font-mono text-[10px]">Enter</kbd>
                                    para añadir cada producto. También puedes pegar varios separados por comas.
                                </p>
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
                                <span x-text="modo === 'crear' ? 'Guardar enfermedad' : 'Actualizar enfermedad'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>

    <script>
    function enfermedadesApp() {
        return {
            open: false,
            modo: 'crear',
            formAction: '{{ route('enfermedades.store') }}',
            imagenPreview: null,
            // Tags state per field
            symptomsTags: [],
            symptomsInput: '',
            suggestedTags: [],
            suggestedInput: '',
            form: { name:'', description:'', category:'', symptoms:'', treatment:'', prevention:'', suggested:'' },

            // Split on commas outside parentheses
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

            // Generic tag handlers (field = 'symptoms' | 'suggested')
            handleTagKeydown(e, field) {
                const inputKey = field + 'Input';
                const tagsKey  = field + 'Tags';
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = this[inputKey].trim();
                    if (val) { this[tagsKey].push(val); this.form[field] = this[tagsKey].join(', '); }
                    this[inputKey] = '';
                } else if (e.key === ',') {
                    const opens  = (this[inputKey].match(/\(/g) || []).length;
                    const closes = (this[inputKey].match(/\)/g) || []).length;
                    if (opens <= closes) {
                        e.preventDefault();
                        const val = this[inputKey].trim();
                        if (val) { this[tagsKey].push(val); this.form[field] = this[tagsKey].join(', '); }
                        this[inputKey] = '';
                    }
                } else if (e.key === 'Backspace' && !this[inputKey] && this[tagsKey].length) {
                    this[tagsKey].pop();
                    this.form[field] = this[tagsKey].join(', ');
                }
            },

            flushTag(field) {
                const inputKey = field + 'Input';
                const tagsKey  = field + 'Tags';
                const raw = this[inputKey].trim();
                if (!raw) return;
                const parts = this.splitTags(raw);
                this[tagsKey].push(...parts);
                this.form[field] = this[tagsKey].join(', ');
                this[inputKey] = '';
            },

            pasteTag(e, field) {
                const inputKey = field + 'Input';
                const tagsKey  = field + 'Tags';
                const text = e.clipboardData.getData('text');
                const parts = this.splitTags(text);
                if (parts.length > 1) {
                    this[tagsKey].push(...parts);
                    this.form[field] = this[tagsKey].join(', ');
                    this[inputKey] = '';
                } else {
                    this[inputKey] += text;
                }
            },

            removeSymptom(i) {
                this.symptomsTags.splice(i, 1);
                this.form.symptoms = this.symptomsTags.join(', ');
            },

            removeSuggested(i) {
                this.suggestedTags.splice(i, 1);
                this.form.suggested = this.suggestedTags.join(', ');
            },

            init() {
                @if ($errors->any())
                    this.abrirCrear();
                    this.form.name        = @json(old('name', ''));
                    this.form.description = @json(old('description', ''));
                    this.form.category    = @json(old('category', ''));
                    this.form.treatment   = @json(old('treatment', ''));
                    this.form.prevention  = @json(old('prevention', ''));
                    this.form.symptoms    = @json(old('symptoms', ''));
                    this.form.suggested   = @json(old('suggested', ''));
                    this.symptomsTags  = this.splitTags(this.form.symptoms);
                    this.suggestedTags = this.splitTags(this.form.suggested);
                @endif
            },

            abrirCrear() {
                this.modo = 'crear';
                this.formAction = '{{ route('enfermedades.store') }}';
                this.imagenPreview = null;
                this.symptomsTags = [];  this.symptomsInput = '';
                this.suggestedTags = []; this.suggestedInput = '';
                this.form = { name:'', description:'', category:'', symptoms:'', treatment:'', prevention:'', suggested:'' };
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            abrirEditar(e) {
                this.modo = 'editar';
                this.formAction = e.update_url;
                this.imagenPreview = e.image_url ?? null;
                this.symptomsTags  = this.splitTags(e.symptoms ?? '');
                this.symptomsInput = '';
                this.suggestedTags = this.splitTags(e.suggested ?? '');
                this.suggestedInput = '';
                this.form = {
                    name:        e.name ?? '',
                    description: e.description ?? '',
                    category:    e.category ?? '',
                    symptoms:    e.symptoms ?? '',
                    treatment:   e.treatment ?? '',
                    prevention:  e.prevention ?? '',
                    suggested:   e.suggested ?? '',
                };
                this.open = true;
                document.body.style.overflow = 'hidden';
            },

            cerrar() {
                this.open = false;
                document.body.style.overflow = '';
            },

            verImagen(ev) {
                const f = ev.target.files[0];
                if (!f) return;
                const r = new FileReader();
                r.onload = e => this.imagenPreview = e.target.result;
                r.readAsDataURL(f);
            },
        };
    }
    </script>

</x-admin-layout>
