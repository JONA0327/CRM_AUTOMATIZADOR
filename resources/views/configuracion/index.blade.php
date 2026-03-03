<x-admin-layout title="Configuración">

    {{-- Flash success --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion.update') }}"
          x-data="configPage()" @submit="preparar($event)">
        @csrf

        <div class="flex gap-6 items-start">

            {{-- ═══ SIDEBAR DE TABS ═══ --}}
            <div class="w-56 flex-shrink-0 sticky top-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Configuración</p>
                    </div>
                    <nav class="p-2 space-y-0.5">
                        @foreach([
                            ['id' => 'bot',       'icon' => 'bot',      'label' => 'Bot & Prompts',    'color' => 'purple'],
                            ['id' => 'whatsapp',  'icon' => 'wa',       'label' => 'WhatsApp',         'color' => 'green'],
                            ['id' => 'ia',        'icon' => 'brain',    'label' => 'Modelos de IA',    'color' => 'blue'],
                            ['id' => 'servicios', 'icon' => 'plug',     'label' => 'Servicios',        'color' => 'orange'],
                            ['id' => 'dbs',       'icon' => 'db',       'label' => 'Bases de Datos',   'color' => 'indigo'],
                        ] as $tab)
                        <button type="button"
                                @click="activeTab = '{{ $tab['id'] }}'"
                                :class="activeTab === '{{ $tab['id'] }}'
                                    ? 'bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-semibold'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-colors text-left">

                            {{-- Íconos por tab --}}
                            @if($tab['icon'] === 'bot')
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            @elseif($tab['icon'] === 'wa')
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                            @elseif($tab['icon'] === 'brain')
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            @elseif($tab['icon'] === 'plug')
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                            @endif

                            {{ $tab['label'] }}

                            {{-- Badge de estado --}}
                            @if($tab['id'] === 'bot')
                                @if($systemPrompt)
                                <span class="ml-auto w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>
                                @endif
                            @elseif($tab['id'] === 'whatsapp')
                                <span class="ml-auto w-2 h-2 rounded-full {{ ($estado['evolution_url'] && $estado['evolution_key']) ? 'bg-green-400' : 'bg-amber-400' }} flex-shrink-0"></span>
                            @elseif($tab['id'] === 'ia')
                                <span class="ml-auto w-2 h-2 rounded-full {{ $estado['openai_key'] || $estado['deepseek_key'] || $estado['gemini_key'] ? 'bg-green-400' : 'bg-amber-400' }} flex-shrink-0"></span>
                            @endif
                        </button>
                        @endforeach
                    </nav>

                    <div class="px-3 pb-3 pt-1">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Guardar
                        </button>
                    </div>
                </div>

                <p class="mt-3 px-1 text-xs text-gray-400 leading-relaxed">
                    Las API Keys se guardan <strong class="text-gray-500">cifradas</strong>. Deja un campo vacío para mantener el valor actual.
                </p>
            </div>

            {{-- ═══ CONTENIDO DEL TAB ACTIVO ═══ --}}
            <div class="flex-1 min-w-0 space-y-5">

                {{-- ─── TAB: BOT & PROMPTS ─── --}}
                <div x-show="activeTab === 'bot'" x-cloak>

                    {{-- ═══ DIAGRAMA DE NODOS ═══ --}}
                    @php
                        $apis   = collect($availableTags)->where('tipo', 'api')->values();
                        $datos  = collect($availableTags)->whereIn('tipo', ['catalogo', 'db_ext'])->values();
                        $nApis  = $apis->count();
                        $nDatos = $datos->count();
                        // 60px per node + 80px padding top/bottom → no overlap, no legend crowding
                        $svgH   = max(420, max($nApis, $nDatos) * 62 + 80);
                        $botX   = 350; $botY = $svgH / 2; $botR = 44;
                        // Distribute nodes evenly with 40px top/bottom margin
                        $apiYs  = collect(range(0, max(0, $nApis - 1)))->map(fn($i) =>
                            $nApis > 1 ? 40 + ($i / ($nApis - 1)) * ($svgH - 80) : $svgH / 2
                        );
                        $datYs  = collect(range(0, max(0, $nDatos - 1)))->map(fn($i) =>
                            $nDatos > 1 ? 40 + ($i / ($nDatos - 1)) * ($svgH - 80) : $svgH / 2
                        );
                        $nodeW  = 168;
                        $apiX   = 10;
                        $datX   = 700 - 10 - $nodeW;   // = 522
                        // SVG line anchors
                        $apiLineX = $apiX + $nodeW + 4; // 182
                        $botLX    = $botX - $botR - 4;  // 302
                        $botRX    = $botX + $botR + 4;  // 398
                        $datLineX = $datX - 4;           // 518
                    @endphp

                    <div class="mb-2 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700"
                         style="background: radial-gradient(ellipse at 30% 50%, #eef2ff 0%, #f8fafc 60%, #faf5ff 100%);">
                        <div class="relative dark:hidden" style="width:700px; height:{{ $svgH }}px;">

                            {{-- SVG: líneas + fondo punteado (light) --}}
                            <svg class="absolute inset-0" width="700" height="{{ $svgH }}" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="dotsL" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
                                        <circle cx="12" cy="12" r="1" fill="#c7d2fe" opacity="0.6"/>
                                    </pattern>
                                </defs>
                                <rect width="700" height="{{ $svgH }}" fill="url(#dotsL)"/>

                                {{-- Líneas API → Bot --}}
                                @foreach($apis as $i => $node)
                                    @php
                                        $ny    = $apiYs[$i];
                                        $color = $node['activo'] ? '#6366f1' : '#d1d5db';
                                        $sw    = $node['activo'] ? '1.5' : '1';
                                        $dash  = $node['activo'] ? 'none' : '5,5';
                                        $op    = $node['activo'] ? '0.85' : '0.35';
                                    @endphp
                                    <line x1="{{ $apiLineX }}" y1="{{ $ny }}"
                                          x2="{{ $botLX }}" y2="{{ $botY }}"
                                          stroke="{{ $color }}" stroke-width="{{ $sw }}"
                                          stroke-dasharray="{{ $dash }}" opacity="{{ $op }}"/>
                                    @if($node['activo'])
                                        <circle cx="{{ $apiLineX }}" cy="{{ $ny }}" r="3.5" fill="#6366f1" opacity="0.7"/>
                                        <circle cx="{{ $botLX }}" cy="{{ $botY }}" r="3.5" fill="#6366f1" opacity="0.7"/>
                                    @endif
                                @endforeach

                                {{-- Líneas Bot → Datos --}}
                                @foreach($datos as $i => $node)
                                    @php
                                        $ny    = $datYs[$i];
                                        $color = $node['tipo'] === 'catalogo' ? '#7c3aed' : '#d97706';
                                        $dot   = $node['tipo'] === 'catalogo' ? '#7c3aed' : '#d97706';
                                    @endphp
                                    <line x1="{{ $botRX }}" y1="{{ $botY }}"
                                          x2="{{ $datLineX }}" y2="{{ $ny }}"
                                          stroke="{{ $color }}" stroke-width="1.5" opacity="0.85"/>
                                    <circle cx="{{ $botRX }}" cy="{{ $botY }}" r="3.5" fill="{{ $dot }}" opacity="0.7"/>
                                    <circle cx="{{ $datLineX }}" cy="{{ $ny }}" r="3.5" fill="{{ $dot }}" opacity="0.7"/>
                                @endforeach

                                {{-- Sección labels en SVG --}}
                                <text x="14" y="20" font-size="9" font-weight="700" fill="#818cf8"
                                      letter-spacing="1.5" font-family="ui-sans-serif,sans-serif">APIs</text>
                                <text x="686" y="20" font-size="9" font-weight="700" fill="#a78bfa"
                                      letter-spacing="1.5" text-anchor="end" font-family="ui-sans-serif,sans-serif">DATOS</text>
                            </svg>

                            {{-- Nodo central BOT --}}
                            <div class="absolute z-10 flex flex-col items-center"
                                 style="left:{{ $botX }}px; top:{{ $botY }}px; transform:translate(-50%,-50%);">
                                <div class="rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-2xl border-4 border-white flex items-center justify-center"
                                     style="width:88px;height:88px;">
                                    <span class="text-4xl leading-none select-none">🤖</span>
                                </div>
                                <span class="mt-2 px-3 py-0.5 text-xs font-extrabold tracking-widest text-indigo-700 bg-white rounded-full shadow border border-indigo-100">
                                    BOT
                                </span>
                            </div>

                            {{-- Nodos API (izquierda) --}}
                            @foreach($apis as $i => $node)
                                <div class="absolute z-10"
                                     style="left:{{ $apiX }}px; top:{{ $apiYs[$i] }}px; transform:translateY(-50%); width:{{ $nodeW }}px;">
                                    <button type="button"
                                            {{ $node['activo'] ? "onclick=\"insertarTag('{$node['tag']}')\"" : '' }}
                                            {{ $node['activo'] ? '' : 'disabled' }}
                                            title="{{ $node['activo'] ? $node['preview'] : 'Configura esta API en Modelos de IA o Servicios' }}"
                                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-medium shadow-sm transition-all
                                                   {{ $node['activo']
                                                       ? 'border-indigo-300 bg-white text-indigo-700 hover:border-indigo-500 hover:shadow-md cursor-pointer'
                                                       : 'border-gray-200 bg-white/80 text-gray-400 cursor-not-allowed opacity-60' }}">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $node['activo'] ? 'bg-emerald-400' : 'bg-gray-300' }}"></span>
                                        <span class="truncate text-left leading-tight">{{ $node['label'] }}</span>
                                    </button>
                                </div>
                            @endforeach

                            {{-- Nodos Datos (derecha) --}}
                            @foreach($datos as $i => $node)
                                <div class="absolute z-10"
                                     style="left:{{ $datX }}px; top:{{ $datYs[$i] }}px; transform:translateY(-50%); width:{{ $nodeW }}px;">
                                    <button type="button"
                                            onclick="insertarTag('{{ $node['tag'] }}')"
                                            title="{{ $node['preview'] }}"
                                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-medium shadow-sm transition-all cursor-pointer
                                                   {{ $node['tipo'] === 'catalogo'
                                                       ? 'border-purple-300 bg-white text-purple-700 hover:border-purple-500 hover:shadow-md'
                                                       : 'border-amber-300 bg-white text-amber-700 hover:border-amber-500 hover:shadow-md' }}">
                                        <span class="truncate text-left leading-tight flex-1">{{ $node['label'] }}</span>
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $node['tipo'] === 'catalogo' ? 'bg-purple-400' : 'bg-amber-400' }}"></span>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Dark mode version --}}
                        <div class="relative hidden dark:block" style="width:700px; height:{{ $svgH }}px; background: radial-gradient(ellipse at 30% 50%, #1e1b4b 0%, #111827 60%, #1a0a2e 100%);">
                            <svg class="absolute inset-0" width="700" height="{{ $svgH }}" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="dotsD" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
                                        <circle cx="12" cy="12" r="1" fill="#4f46e5" opacity="0.3"/>
                                    </pattern>
                                </defs>
                                <rect width="700" height="{{ $svgH }}" fill="url(#dotsD)"/>
                                @foreach($apis as $i => $node)
                                    @php
                                        $ny    = $apiYs[$i];
                                        $color = $node['activo'] ? '#818cf8' : '#374151';
                                        $dash  = $node['activo'] ? 'none' : '5,5';
                                        $op    = $node['activo'] ? '0.8' : '0.4';
                                    @endphp
                                    <line x1="{{ $apiLineX }}" y1="{{ $ny }}" x2="{{ $botLX }}" y2="{{ $botY }}"
                                          stroke="{{ $color }}" stroke-width="{{ $node['activo'] ? '1.5' : '1' }}"
                                          stroke-dasharray="{{ $dash }}" opacity="{{ $op }}"/>
                                    @if($node['activo'])
                                        <circle cx="{{ $apiLineX }}" cy="{{ $ny }}" r="3.5" fill="#818cf8" opacity="0.7"/>
                                    @endif
                                @endforeach
                                @foreach($datos as $i => $node)
                                    @php $color = $node['tipo'] === 'catalogo' ? '#a78bfa' : '#fbbf24'; @endphp
                                    <line x1="{{ $botRX }}" y1="{{ $botY }}" x2="{{ $datLineX }}" y2="{{ $datYs[$i] }}"
                                          stroke="{{ $color }}" stroke-width="1.5" opacity="0.8"/>
                                    <circle cx="{{ $datLineX }}" cy="{{ $datYs[$i] }}" r="3.5" fill="{{ $color }}" opacity="0.7"/>
                                @endforeach
                                <text x="14" y="20" font-size="9" font-weight="700" fill="#6366f1"
                                      letter-spacing="1.5" font-family="ui-sans-serif,sans-serif">APIs</text>
                                <text x="686" y="20" font-size="9" font-weight="700" fill="#7c3aed"
                                      letter-spacing="1.5" text-anchor="end" font-family="ui-sans-serif,sans-serif">DATOS</text>
                            </svg>
                            {{-- Bot node dark --}}
                            <div class="absolute z-10 flex flex-col items-center"
                                 style="left:{{ $botX }}px; top:{{ $botY }}px; transform:translate(-50%,-50%);">
                                <div class="rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-2xl border-4 border-gray-800 flex items-center justify-center"
                                     style="width:88px;height:88px;">
                                    <span class="text-4xl leading-none select-none">🤖</span>
                                </div>
                                <span class="mt-2 px-3 py-0.5 text-xs font-extrabold tracking-widest text-indigo-300 bg-gray-800 rounded-full shadow border border-indigo-800">BOT</span>
                            </div>
                            {{-- API nodes dark --}}
                            @foreach($apis as $i => $node)
                                <div class="absolute z-10"
                                     style="left:{{ $apiX }}px; top:{{ $apiYs[$i] }}px; transform:translateY(-50%); width:{{ $nodeW }}px;">
                                    <button type="button"
                                            {{ $node['activo'] ? "onclick=\"insertarTag('{$node['tag']}')\"" : '' }}
                                            {{ $node['activo'] ? '' : 'disabled' }}
                                            title="{{ $node['activo'] ? $node['preview'] : 'Configura esta API en Modelos de IA o Servicios' }}"
                                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-medium shadow-sm transition-all
                                                   {{ $node['activo']
                                                       ? 'border-indigo-700 bg-gray-800 text-indigo-300 hover:border-indigo-500 hover:shadow-md cursor-pointer'
                                                       : 'border-gray-700 bg-gray-800/60 text-gray-500 cursor-not-allowed opacity-50' }}">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $node['activo'] ? 'bg-emerald-400' : 'bg-gray-600' }}"></span>
                                        <span class="truncate text-left leading-tight">{{ $node['label'] }}</span>
                                    </button>
                                </div>
                            @endforeach
                            {{-- Data nodes dark --}}
                            @foreach($datos as $i => $node)
                                <div class="absolute z-10"
                                     style="left:{{ $datX }}px; top:{{ $datYs[$i] }}px; transform:translateY(-50%); width:{{ $nodeW }}px;">
                                    <button type="button"
                                            onclick="insertarTag('{{ $node['tag'] }}')"
                                            title="{{ $node['preview'] }}"
                                            class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-medium shadow-sm cursor-pointer transition-all
                                                   {{ $node['tipo'] === 'catalogo'
                                                       ? 'border-purple-700 bg-gray-800 text-purple-300 hover:border-purple-500 hover:shadow-md'
                                                       : 'border-amber-700 bg-gray-800 text-amber-300 hover:border-amber-500 hover:shadow-md' }}">
                                        <span class="truncate text-left leading-tight flex-1">{{ $node['label'] }}</span>
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $node['tipo'] === 'catalogo' ? 'bg-purple-400' : 'bg-amber-400' }}"></span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Leyenda (fuera del diagrama para no superponerse) --}}
                    <div class="mb-5 mt-2.5 flex flex-wrap items-center justify-center gap-4 text-xs text-gray-400 dark:text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>Activo
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>Sin configurar
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-400 inline-block"></span>Catálogo
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>BD externa
                        </span>
                        <span class="text-indigo-400 dark:text-indigo-500 font-medium">← clic en un nodo activo para insertar su etiqueta →</span>
                    </div>

                    {{-- Selector de proveedor --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Proveedor de IA activo</p>
                                <p class="text-xs text-gray-400">El bot usará este modelo para generar respuestas</p>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="grid grid-cols-3 gap-3">
                                @foreach([
                                    ['value' => 'openai',   'label' => 'ChatGPT',    'sub' => 'GPT-4o, GPT-4o-mini',   'color' => 'teal'],
                                    ['value' => 'deepseek', 'label' => 'DeepSeek',   'sub' => 'deepseek-chat',          'color' => 'blue'],
                                    ['value' => 'gemini',   'label' => 'Gemini',     'sub' => 'gemini-1.5-pro/flash',   'color' => 'orange'],
                                ] as $prov)
                                <label class="relative flex flex-col gap-1 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                                              {{ $botProveedor === $prov['value'] ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300' }}">
                                    <input type="radio" name="bot_ia_proveedor" value="{{ $prov['value'] }}"
                                           {{ $botProveedor === $prov['value'] ? 'checked' : '' }}
                                           class="sr-only"/>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $prov['label'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $prov['sub'] }}</p>
                                    @if ($botProveedor === $prov['value'])
                                        <svg class="absolute top-2.5 right-2.5 w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </label>
                                @endforeach
                            </div>
                            <p class="mt-2.5 text-xs text-gray-400">Configura la API Key del proveedor seleccionado en la pestaña "Modelos de IA".</p>
                        </div>
                    </div>

                    {{-- System Prompt --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-5"
                         x-data="savedPromptsManager()"
                         x-init="init()">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Prompt del sistema</p>
                                    <p class="text-xs text-gray-400">Instrucciones base del bot en cada conversación</p>
                                </div>
                            </div>
                            <x-config-badge :configured="(bool)$systemPrompt"/>
                        </div>

                        {{-- ── Prompts guardados ── --}}
                        <div class="px-5 pt-4 pb-0">
                            {{-- Lista de prompts guardados --}}
                            <template x-if="prompts.length > 0">
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Prompts guardados</p>
                                        <span class="text-xs text-gray-400" x-text="prompts.length + ' prompt(s)'"></span>
                                    </div>
                                    <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                        <template x-for="p in prompts" :key="p.id">
                                            <div class="group flex items-center gap-2 px-3 py-2 rounded-lg border transition-all cursor-pointer"
                                                 :class="promptActivo === p.id
                                                     ? 'border-purple-400 bg-purple-50 dark:bg-purple-900/20'
                                                     : 'border-gray-200 dark:border-gray-600 hover:border-purple-300 hover:bg-purple-50/50'">
                                                <button type="button"
                                                        @click="cargarPrompt(p)"
                                                        class="flex-1 text-left min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 flex-shrink-0"
                                                             :class="promptActivo === p.id ? 'text-purple-600' : 'text-gray-400'"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="p.nombre"></span>
                                                        <template x-if="promptActivo === p.id">
                                                            <span class="text-xs text-purple-600 font-semibold flex-shrink-0">● activo</span>
                                                        </template>
                                                    </div>
                                                    <p class="text-xs text-gray-400 truncate mt-0.5 ml-5.5" x-text="p.contenido.substring(0,80) + (p.contenido.length > 80 ? '…' : '')"></p>
                                                </button>
                                                <button type="button"
                                                        @click="eliminarPrompt(p)"
                                                        title="Eliminar prompt"
                                                        class="opacity-0 group-hover:opacity-100 flex-shrink-0 p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Guardar prompt actual con nombre --}}
                            <div class="mb-3 flex items-center gap-2">
                                <input type="text" x-model="nuevoNombre" placeholder="Nombre del prompt…"
                                       class="flex-1 text-xs px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition"
                                       @keydown.enter.prevent="guardarPrompt()">
                                <button type="button"
                                        @click="guardarPrompt()"
                                        :disabled="!nuevoNombre.trim() || guardando"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors disabled:opacity-50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    Guardar prompt
                                </button>
                            </div>
                        </div>

                        <div class="px-5 pb-4">
                            <textarea id="system_prompt" name="system_prompt" rows="10" maxlength="8000"
                                x-ref="textarea"
                                @input="chars = $event.target.value.length"
                                placeholder="Eres un asistente de ventas especializado en... Responde siempre en español, de forma clara y amable..."
                                class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm font-mono leading-relaxed focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition resize-y"
                            >{{ $systemPrompt }}</textarea>
                            <div class="mt-1.5 flex items-center justify-between">
                                <p class="text-xs text-gray-400">Usa <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">[ETIQUETA]</code> para inyectar datos al enviar. Clic en el diagrama o en los chips para insertar.</p>
                                <span class="text-xs font-mono" :class="chars >= max * 0.9 ? 'text-red-500 font-semibold' : 'text-gray-400'">
                                    <span x-text="chars"></span>/<span x-text="max"></span>
                                </span>
                            </div>

                            {{-- Paleta de etiquetas disponibles --}}
                            @php
                                $tagsActivos  = collect($availableTags)->where('activo', true);
                                $tagsApis     = $tagsActivos->where('tipo', 'api');
                                $tagsCatalogo = $tagsActivos->where('tipo', 'catalogo');
                                $tagsDbExt    = $tagsActivos->where('tipo', 'db_ext');
                            @endphp
                            @if($tagsActivos->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 space-y-2">
                                @if($tagsApis->isNotEmpty())
                                <div class="flex items-start gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 w-16 mt-1 flex-shrink-0">APIs</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($tagsApis as $tag)
                                        <button type="button" onclick="insertarTag('{{ $tag['tag'] }}')"
                                                title="{{ $tag['preview'] }}"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700 text-xs font-mono hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-colors cursor-pointer">
                                            [{{ $tag['tag'] }}]
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($tagsCatalogo->isNotEmpty())
                                <div class="flex items-start gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 w-16 mt-1 flex-shrink-0">Catálogos</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($tagsCatalogo as $tag)
                                        <button type="button" onclick="insertarTag('{{ $tag['tag'] }}')"
                                                title="{{ $tag['preview'] }}"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700 text-xs font-mono hover:bg-purple-100 transition-colors cursor-pointer">
                                            [{{ $tag['tag'] }}]
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($tagsDbExt->isNotEmpty())
                                <div class="flex items-start gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 w-16 mt-1 flex-shrink-0">BDs ext.</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($tagsDbExt as $tag)
                                        <button type="button" onclick="insertarTag('{{ $tag['tag'] }}')"
                                                title="{{ $tag['preview'] }}"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 text-xs font-mono hover:bg-amber-100 transition-colors cursor-pointer">
                                            [{{ $tag['tag'] }}]
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @else
                            <p class="mt-3 text-xs text-gray-400 italic">
                                Configura APIs o crea catálogos para ver etiquetas disponibles aquí.
                            </p>
                            @endif
                        </div>
                    </div>

                    {{-- Prompt verificación WhatsApp --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mt-5"
                         x-data="{ chars: {{ strlen($promptVerificacion ?? '') }}, max: 1000 }">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Mensaje de verificación WhatsApp</p>
                                    <p class="text-xs text-gray-400">Enviado al pulsar el ícono WhatsApp en campos de teléfono</p>
                                </div>
                            </div>
                            <x-config-badge :configured="(bool)$promptVerificacion"/>
                        </div>
                        <div class="px-5 py-4">
                            <textarea name="bot_prompt_verificacion" rows="4" maxlength="1000"
                                @input="chars = $event.target.value.length"
                                placeholder="Hola, te contactamos para verificar tu número de WhatsApp. ¿Confirmas que este número te pertenece?"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm leading-relaxed focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-y"
                            >{{ $promptVerificacion }}</textarea>
                            <div class="mt-1.5 flex items-center justify-between">
                                <p class="text-xs text-gray-400">Requiere Evolution API configurada e instancia activa.</p>
                                <span class="text-xs font-mono" :class="chars >= max * 0.9 ? 'text-red-500 font-semibold' : 'text-gray-400'">
                                    <span x-text="chars"></span>/<span x-text="max"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── TAB: WHATSAPP / EVOLUTION ─── --}}
                <div x-show="activeTab === 'whatsapp'" x-cloak>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Evolution API</p>
                                    <p class="text-xs text-gray-400">Gestiona instancias y mensajes de WhatsApp</p>
                                </div>
                            </div>
                            <x-config-badge :configured="$estado['evolution_url'] && $estado['evolution_key']"/>
                        </div>
                        <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-config-field name="evolution_url" label="URL del servidor" tipo="text"
                                placeholder="https://tu-evolution-api.com"
                                :configured="$estado['evolution_url']"/>
                            <x-config-field name="evolution_key" label="API Key global" tipo="password"
                                placeholder="Tu API Key de Evolution"
                                :configured="$estado['evolution_key']"/>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">¿Cómo conectar WhatsApp?</p>
                                <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
                                    Configura la URL y API Key de tu Evolution API, luego ve a <strong>Bot → Conectar</strong> para escanear el código QR y vincular tu número de WhatsApp.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── TAB: MODELOS DE IA ─── --}}
                <div x-show="activeTab === 'ia'" x-cloak class="space-y-4">

                    {{-- Info banner --}}
                    <div class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-blue-800">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Selecciona el modelo de cada proveedor. Las capacidades de <strong>audio</strong> e <strong>imágenes</strong> solo se muestran cuando el modelo o proveedor las soporta.</span>
                    </div>

                    {{-- ════ OPENAI ════ --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                         x-data="{
                             open:        {{ $estado['openai_key'] ? 'true' : 'false' }},
                             modelo:      @js($iaModelos['openai']),
                             customOn:    false,
                             customVal:   '',
                             whisper:     {{ $iaToggles['openai_whisper'] ? 'true' : 'false' }},
                             imagen:      {{ $iaToggles['openai_imagen']  ? 'true' : 'false' }},
                             modelos: [
                                 { id: 'gpt-4o',        label: 'GPT-4o',         tag: 'Recomendado' },
                                 { id: 'gpt-4o-mini',   label: 'GPT-4o Mini',    tag: 'Rápido'      },
                                 { id: 'gpt-4-turbo',   label: 'GPT-4 Turbo',    tag: null          },
                                 { id: 'gpt-3.5-turbo', label: 'GPT-3.5 Turbo',  tag: 'Económico'   },
                                 { id: 'o1',            label: 'o1',             tag: 'Razonamiento'},
                                 { id: 'o3-mini',       label: 'o3-mini',        tag: null          },
                             ],
                             init() {
                                 const found = this.modelos.find(m => m.id === this.modelo);
                                 if (!found && this.modelo) { this.customOn = true; this.customVal = this.modelo; }
                             },
                             elegir(id) {
                                 if (id === '__custom__') { this.customOn = true; this.modelo = ''; }
                                 else { this.customOn = false; this.modelo = id; }
                             },
                             get modeloFinal() { return this.customOn ? this.customVal : this.modelo; },
                         }"
                         x-init="init()">

                        {{-- Header --}}
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-teal-700" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0L4.01 14.2A4.501 4.501 0 0 1 2.34 7.896zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.808 2.768a4.504 4.504 0 0 1-.689 8.122V12.57a.79.79 0 0 0-.412-.719zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.806-2.767a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">ChatGPT (OpenAI)</p>
                                    <p class="text-xs text-gray-400" x-text="modeloFinal || 'GPT-4o · DALL-E 3 · Whisper'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-config-badge :configured="$estado['openai_key']"/>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                            <div class="px-5 py-5 space-y-5">

                                {{-- API Key --}}
                                <x-config-field name="openai_key" label="API Key" tipo="password" placeholder="sk-..." :configured="$estado['openai_key']"/>

                                {{-- Selector de modelo --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2.5">Modelo de chat</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-2">
                                        <template x-for="m in modelos" :key="m.id">
                                            <button type="button" @click="elegir(m.id)"
                                                    :class="modelo === m.id && !customOn
                                                        ? 'ring-2 ring-teal-500 bg-teal-50 border-teal-200'
                                                        : 'border-gray-200 hover:border-teal-300 hover:bg-gray-50'"
                                                    class="relative flex flex-col items-start px-3 py-2.5 border rounded-lg transition-all text-left">
                                                <span class="text-xs font-semibold text-gray-800 leading-tight" x-text="m.label"></span>
                                                <span x-show="m.tag" class="text-[10px] text-teal-600 font-medium mt-0.5" x-text="m.tag"></span>
                                                <span x-show="modelo === m.id && !customOn"
                                                      class="absolute top-1.5 right-1.5 w-3.5 h-3.5 bg-teal-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 12 12">
                                                        <polyline points="2,6 5,9 10,3"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        </template>
                                        {{-- Personalizado --}}
                                        <button type="button" @click="elegir('__custom__')"
                                                :class="customOn ? 'ring-2 ring-teal-500 bg-teal-50 border-teal-200' : 'border-dashed border-gray-300 hover:border-teal-300'"
                                                class="flex flex-col items-start px-3 py-2.5 border rounded-lg transition-all text-left">
                                            <span class="text-xs font-semibold text-gray-600">Personalizado</span>
                                            <span class="text-[10px] text-gray-400 mt-0.5">Escribe el ID del modelo</span>
                                        </button>
                                    </div>
                                    <div x-show="customOn" x-transition>
                                        <input type="text" x-model="customVal" placeholder="ej: gpt-4o-2024-11-20"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"/>
                                    </div>
                                    <input type="hidden" name="openai_model" :value="modeloFinal">
                                </div>

                                {{-- Capacidades adicionales (Whisper + DALL-E — siempre disponibles con key OpenAI) --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2.5">Capacidades adicionales</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                        {{-- Whisper --}}
                                        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                                     :class="whisper ? 'bg-teal-100' : 'bg-gray-200'">
                                                    <svg class="w-4 h-4" :class="whisper ? 'text-teal-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-800">Whisper</p>
                                                    <p class="text-[10px] text-gray-400">Transcripción de voz a texto</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="whisper = !whisper"
                                                    :class="whisper ? 'bg-teal-500' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none">
                                                <span :class="whisper ? 'translate-x-5' : 'translate-x-0'"
                                                      class="inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200"></span>
                                            </button>
                                            <input type="hidden" name="openai_whisper_activo" :value="whisper ? '1' : '0'">
                                        </div>

                                        {{-- DALL-E 3 --}}
                                        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                                     :class="imagen ? 'bg-purple-100' : 'bg-gray-200'">
                                                    <svg class="w-4 h-4" :class="imagen ? 'text-purple-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-800">DALL-E 3</p>
                                                    <p class="text-[10px] text-gray-400">Generación de imágenes con IA</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="imagen = !imagen"
                                                    :class="imagen ? 'bg-purple-500' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none">
                                                <span :class="imagen ? 'translate-x-5' : 'translate-x-0'"
                                                      class="inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200"></span>
                                            </button>
                                            <input type="hidden" name="openai_imagen_activo" :value="imagen ? '1' : '0'">
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ════ DEEPSEEK ════ --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                         x-data="{
                             open:     {{ $estado['deepseek_key'] ? 'true' : 'false' }},
                             modelo:   @js($iaModelos['deepseek']),
                             customOn: false,
                             customVal:'',
                             modelos: [
                                 { id: 'deepseek-chat',     label: 'DeepSeek Chat',     tag: 'Recomendado'   },
                                 { id: 'deepseek-reasoner', label: 'DeepSeek Reasoner', tag: 'R1 · Avanzado' },
                                 { id: 'deepseek-coder',    label: 'DeepSeek Coder',    tag: 'Programación'  },
                             ],
                             init() {
                                 const found = this.modelos.find(m => m.id === this.modelo);
                                 if (!found && this.modelo) { this.customOn = true; this.customVal = this.modelo; }
                             },
                             elegir(id) {
                                 if (id === '__custom__') { this.customOn = true; this.modelo = ''; }
                                 else { this.customOn = false; this.modelo = id; }
                             },
                             get modeloFinal() { return this.customOn ? this.customVal : this.modelo; },
                         }"
                         x-init="init()">

                        {{-- Header --}}
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">DeepSeek</p>
                                    <p class="text-xs text-gray-400" x-text="modeloFinal || 'deepseek-chat · deepseek-reasoner'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-config-badge :configured="$estado['deepseek_key']"/>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                            <div class="px-5 py-5 space-y-5">

                                {{-- API Key --}}
                                <x-config-field name="deepseek_key" label="API Key" tipo="password" placeholder="sk-..." :configured="$estado['deepseek_key']"/>

                                {{-- Selector de modelo --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2.5">Modelo de chat</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-2">
                                        <template x-for="m in modelos" :key="m.id">
                                            <button type="button" @click="elegir(m.id)"
                                                    :class="modelo === m.id && !customOn
                                                        ? 'ring-2 ring-blue-500 bg-blue-50 border-blue-200'
                                                        : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'"
                                                    class="relative flex flex-col items-start px-3 py-2.5 border rounded-lg transition-all text-left">
                                                <span class="text-xs font-semibold text-gray-800 leading-tight" x-text="m.label"></span>
                                                <span x-show="m.tag" class="text-[10px] text-blue-600 font-medium mt-0.5" x-text="m.tag"></span>
                                                <span x-show="modelo === m.id && !customOn"
                                                      class="absolute top-1.5 right-1.5 w-3.5 h-3.5 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 12 12">
                                                        <polyline points="2,6 5,9 10,3"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        </template>
                                        <button type="button" @click="elegir('__custom__')"
                                                :class="customOn ? 'ring-2 ring-blue-500 bg-blue-50 border-blue-200' : 'border-dashed border-gray-300 hover:border-blue-300'"
                                                class="flex flex-col items-start px-3 py-2.5 border rounded-lg transition-all text-left">
                                            <span class="text-xs font-semibold text-gray-600">Personalizado</span>
                                            <span class="text-[10px] text-gray-400 mt-0.5">Escribe el ID del modelo</span>
                                        </button>
                                    </div>
                                    <div x-show="customOn" x-transition>
                                        <input type="text" x-model="customVal" placeholder="ej: deepseek-v3"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
                                    </div>
                                    <input type="hidden" name="deepseek_model" :value="modeloFinal">
                                </div>

                                {{-- Sin capacidades adicionales --}}
                                <div class="flex items-center gap-2 py-2 text-xs text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    DeepSeek no incluye transcripción de audio ni generación de imágenes.
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ════ GEMINI ════ --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                         x-data="{
                             open:     {{ $estado['gemini_key'] ? 'true' : 'false' }},
                             modelo:   @js($iaModelos['gemini']),
                             customOn: false,
                             customVal:'',
                             audio:    {{ $iaToggles['gemini_audio'] ? 'true' : 'false' }},
                             modelos: [
                                 { id: 'gemini-2.0-flash-exp', label: 'Gemini 2.0 Flash',  tag: 'Más rápido',   audio: true  },
                                 { id: 'gemini-2.0-flash',     label: 'Gemini 2.0 Flash',  tag: 'Estable',      audio: true  },
                                 { id: 'gemini-1.5-flash',     label: 'Gemini 1.5 Flash',  tag: 'Rápido',       audio: true  },
                                 { id: 'gemini-1.5-flash-8b',  label: 'Flash 1.5 8B',      tag: 'Ultra rápido', audio: true  },
                                 { id: 'gemini-1.5-pro',       label: 'Gemini 1.5 Pro',    tag: 'Más potente',  audio: true  },
                                 { id: 'gemini-1.0-pro',       label: 'Gemini 1.0 Pro',    tag: 'Básico',       audio: false },
                             ],
                             init() {
                                 const found = this.modelos.find(m => m.id === this.modelo);
                                 if (!found && this.modelo) { this.customOn = true; this.customVal = this.modelo; }
                             },
                             elegir(id) {
                                 if (id === '__custom__') { this.customOn = true; this.modelo = ''; }
                                 else {
                                     this.customOn = false;
                                     this.modelo   = id;
                                     // Apagar audio automáticamente si el modelo no lo soporta
                                     const m = this.modelos.find(m => m.id === id);
                                     if (m && !m.audio) this.audio = false;
                                 }
                             },
                             get modeloFinal() { return this.customOn ? this.customVal : this.modelo; },
                             get soportaAudio() {
                                 if (this.customOn) return true;
                                 const m = this.modelos.find(m => m.id === this.modelo);
                                 return m ? m.audio : true;
                             },
                         }"
                         x-init="init()">

                        {{-- Header --}}
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-orange-600" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2a2 2 0 00-1.82 1.17L3.1 17.47A2 2 0 005 20h14a2 2 0 001.9-2.53L13.82 3.17A2 2 0 0012 2z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Google Gemini</p>
                                    <p class="text-xs text-gray-400" x-text="modeloFinal || 'gemini-1.5-flash · gemini-1.5-pro'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-config-badge :configured="$estado['gemini_key']"/>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                            <div class="px-5 py-5 space-y-5">

                                {{-- API Key --}}
                                <x-config-field name="gemini_key" label="API Key" tipo="password" placeholder="AIza..." :configured="$estado['gemini_key']"/>

                                {{-- Selector de modelo --}}
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2.5">Modelo de chat</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-2">
                                        <template x-for="m in modelos" :key="m.id">
                                            <button type="button" @click="elegir(m.id)"
                                                    :class="modelo === m.id && !customOn
                                                        ? 'ring-2 ring-orange-500 bg-orange-50 border-orange-200'
                                                        : 'border-gray-200 hover:border-orange-300 hover:bg-gray-50'"
                                                    class="relative flex flex-col items-start px-3 py-2.5 border rounded-lg transition-all text-left">
                                                <div class="flex items-center gap-1 w-full">
                                                    <span class="text-xs font-semibold text-gray-800 leading-tight flex-1" x-text="m.label"></span>
                                                    {{-- Ícono audio para modelos que lo soportan --}}
                                                    <svg x-show="m.audio" class="w-3 h-3 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                    </svg>
                                                </div>
                                                <span x-show="m.tag" class="text-[10px] text-orange-600 font-medium mt-0.5" x-text="m.tag"></span>
                                                <span x-show="modelo === m.id && !customOn"
                                                      class="absolute top-1.5 right-1.5 w-3.5 h-3.5 bg-orange-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 12 12">
                                                        <polyline points="2,6 5,9 10,3"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        </template>
                                        <button type="button" @click="elegir('__custom__')"
                                                :class="customOn ? 'ring-2 ring-orange-500 bg-orange-50 border-orange-200' : 'border-dashed border-gray-300 hover:border-orange-300'"
                                                class="flex flex-col items-start px-3 py-2.5 border rounded-lg transition-all text-left">
                                            <span class="text-xs font-semibold text-gray-600">Personalizado</span>
                                            <span class="text-[10px] text-gray-400 mt-0.5">Escribe el ID del modelo</span>
                                        </button>
                                    </div>
                                    <div x-show="customOn" x-transition>
                                        <input type="text" x-model="customVal" placeholder="ej: gemini-2.0-flash-thinking-exp"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"/>
                                    </div>
                                    <input type="hidden" name="gemini_model" :value="modeloFinal">
                                </div>

                                {{-- Capacidades adicionales (solo si el modelo las soporta) --}}
                                <div x-show="soportaAudio" x-transition>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2.5">Capacidades adicionales</p>
                                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                                 :class="audio ? 'bg-orange-100' : 'bg-gray-200'">
                                                <svg class="w-4 h-4" :class="audio ? 'text-orange-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-gray-800">Audio nativo</p>
                                                <p class="text-[10px] text-gray-400">Transcripción multimodal integrada en el modelo</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="audio = !audio"
                                                :class="audio ? 'bg-orange-500' : 'bg-gray-300'"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none">
                                            <span :class="audio ? 'translate-x-5' : 'translate-x-0'"
                                                  class="inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200"></span>
                                        </button>
                                    </div>
                                    <input type="hidden" name="gemini_audio_activo" :value="audio ? '1' : '0'">
                                </div>

                                {{-- Aviso cuando el modelo NO soporta audio --}}
                                <div x-show="!soportaAudio" x-transition class="flex items-center gap-2 py-1 text-xs text-gray-400">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Este modelo no soporta transcripción de audio nativa.
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                {{-- ─── TAB: SERVICIOS ─── --}}
                <div x-show="activeTab === 'servicios'" x-cloak class="space-y-4">

                    {{-- Google --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                         x-data="{ open: {{ ($estado['google_client_id'] || $estado['google_client_secret'] || $googleConectado) ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Google (Calendar & Drive)</p>
                                    @if($googleConectado)
                                        <p class="text-xs text-green-600 font-medium flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block"></span>
                                            Conectado: {{ $googleEmail }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400">OAuth2 para Calendar y Drive — Sin conectar</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-config-badge :configured="$googleConectado"/>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>
                        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                            <div class="px-5 py-5 space-y-5">

                                {{-- Paso 1: Credenciales OAuth2 --}}
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">
                                        Paso 1 — Credenciales OAuth2 (Google Cloud Console)
                                    </p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <x-config-field name="google_client_id" label="Client ID" tipo="password" placeholder="12345-abc.apps.googleusercontent.com" :configured="$estado['google_client_id']"/>
                                        <x-config-field name="google_client_secret" label="Client Secret" tipo="password" placeholder="GOCSPX-..." :configured="$estado['google_client_secret']"/>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-400">
                                        Crea las credenciales en
                                        <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-blue-500 hover:underline">
                                            Google Cloud Console
                                        </a>
                                        como «Aplicación web». Agrega
                                        <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded text-xs font-mono">{{ route('configuracion.google.callback') }}</code>
                                        como URI de redireccionamiento autorizado.
                                    </p>
                                </div>

                                {{-- Paso 2: Autorizar cuenta --}}
                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">
                                        Paso 2 — Autorizar cuenta de Google
                                    </p>

                                    @if($googleConectado)
                                        {{-- Conectado: mostrar cuenta y botón desconectar --}}
                                        <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">Cuenta conectada</p>
                                                    <p class="text-xs text-green-600 dark:text-green-400">{{ $googleEmail }}</p>
                                                </div>
                                            </div>
                                            <form method="POST" action="{{ route('configuracion.google.revoke') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('¿Desconectar la cuenta de Google? El bot no podrá acceder a Calendar ni Drive.')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    Desconectar
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        {{-- No conectado: instrucciones + botón --}}
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-4 flex items-center justify-between gap-4">
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                <p class="font-medium text-gray-700 dark:text-gray-200 mb-0.5">Sin cuenta conectada</p>
                                                <p class="text-xs text-gray-400">
                                                    Guarda primero el Client ID y Client Secret, luego haz clic en «Conectar con Google» para autorizar el acceso a Calendar y Drive.
                                                </p>
                                            </div>
                                            @if($estado['google_client_id'] && $estado['google_client_secret'])
                                                <a href="{{ route('configuracion.google.redirect') }}"
                                                   class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg border border-gray-300 shadow-sm transition-colors">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                                    </svg>
                                                    Conectar con Google
                                                </a>
                                            @else
                                                <span class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400 text-sm font-semibold rounded-lg border border-gray-200 cursor-not-allowed select-none"
                                                      title="Guarda primero el Client ID y Client Secret">
                                                    Conectar con Google
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- AssemblyAI --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                         x-data="{ open: {{ $estado['assembly_key'] ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">AssemblyAI</p>
                                    <p class="text-xs text-gray-400">Transcripción de audio y voz a texto</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-config-badge :configured="$estado['assembly_key']"/>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>
                        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                            <div class="px-5 py-4">
                                <x-config-field name="assembly_key" label="API Key" tipo="password" placeholder="Tu API Key de AssemblyAI" :configured="$estado['assembly_key']"/>
                            </div>
                        </div>
                    </div>

                    {{-- Zoom --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                         x-data="{ open: {{ ($estado['zoom_account_id'] || $estado['zoom_client_id']) ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-sky-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zM6.5 8.5A1.5 1.5 0 005 10v4.5A2.5 2.5 0 007.5 17h7A1.5 1.5 0 0016 15.5V11a2.5 2.5 0 00-2.5-2.5H6.5zm10.25 1.45l-2.5 1.786V12.3l2.5 1.786c.32.228.75.005.75-.393v-3.35c0-.398-.43-.62-.75-.393z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Zoom</p>
                                    <p class="text-xs text-gray-400">Integración con reuniones y webinars</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-config-badge :configured="$estado['zoom_account_id'] && $estado['zoom_client_id'] && $estado['zoom_client_secret']"/>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>
                        <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-700">
                            <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <x-config-field name="zoom_account_id" label="Account ID" tipo="password" placeholder="Tu Account ID" :configured="$estado['zoom_account_id']"/>
                                <x-config-field name="zoom_client_id" label="Client ID" tipo="password" placeholder="Tu Client ID" :configured="$estado['zoom_client_id']"/>
                                <x-config-field name="zoom_client_secret" label="Client Secret" tipo="password" placeholder="Tu Client Secret" :configured="$estado['zoom_client_secret']"/>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── TAB: BASES DE DATOS ─── --}}
                <div x-show="activeTab === 'dbs'" x-cloak x-data="extDbManager()">

                    <input type="hidden" name="ext_dbs" :value="jsonFinal"/>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Bases de Datos Externas</p>
                                    <p class="text-xs text-gray-400">El bot usará los datos de estas BDs como contexto</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full"
                                  :class="conexiones.length > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'">
                                <span x-text="conexiones.length"></span> conexión(es)
                            </span>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-if="conexiones.length === 0">
                                <div class="py-10 text-center">
                                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                    </svg>
                                    <p class="text-sm text-gray-400">Sin conexiones configuradas.</p>
                                    <p class="text-xs text-gray-400 mt-1">Agrega una para que el bot acceda a tus datos.</p>
                                </div>
                            </template>

                            <template x-for="(conn, idx) in conexiones" :key="conn.id">
                                <div>
                                    <div class="flex items-center gap-3 px-5 py-3 bg-gray-50 dark:bg-gray-750">
                                        <input type="text" x-model="conn.nombre"
                                               placeholder="✏ Escribe un nombre para esta BD…"
                                               class="flex-1 text-sm font-semibold text-gray-800 dark:text-gray-200 bg-transparent border-b border-transparent hover:border-gray-300 focus:border-indigo-400 focus:outline-none focus:ring-0 placeholder-gray-400 transition-colors"/>
                                        <span class="px-2 py-0.5 rounded text-xs font-mono bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 flex-shrink-0"
                                              x-text="conn.driver.toUpperCase()"></span>
                                        <button type="button" @click="conn._expandido = !conn._expandido"
                                                class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                                            <svg class="w-4 h-4 transition-transform" :class="conn._expandido ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="eliminar(idx)"
                                                class="text-red-400 hover:text-red-600 flex-shrink-0" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div x-show="conn._expandido" class="px-5 py-4 space-y-4">
                                        {{-- Nombre --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nombre de esta conexión <span class="text-red-400">*</span></label>
                                            <input type="text" x-model="conn.nombre" placeholder="Ej: BD Clientes, ERP, CRM…"
                                                   class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition">
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tipo de BD</label>
                                                <select x-model="conn.driver"
                                                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition">
                                                    <option value="mysql">MySQL</option>
                                                    <option value="pgsql">PostgreSQL</option>
                                                    <option value="mongodb">MongoDB</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Host</label>
                                                <input type="text" x-model="conn.host" placeholder="127.0.0.1"
                                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Puerto</label>
                                                <input type="text" x-model="conn.port"
                                                       :placeholder="conn.driver === 'pgsql' ? '5432' : conn.driver === 'mongodb' ? '27017' : '3306'"
                                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Base de datos</label>
                                                <input type="text" x-model="conn.database" placeholder="nombre_bd"
                                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Usuario</label>
                                                <input type="text" x-model="conn.username" placeholder="root"
                                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Contraseña</label>
                                                <input type="password" x-model="conn.password" autocomplete="new-password"
                                                       :placeholder="conn.has_password ? '(guardada)' : '(vacía)'"
                                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3 flex-wrap">
                                            <button type="button" @click="probar(idx)" :disabled="conn._probando"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg shadow-sm transition">
                                                <svg x-show="!conn._probando" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                                <svg x-show="conn._probando" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                                <span x-text="conn._probando ? 'Probando…' : 'Probar conexión'"></span>
                                            </button>
                                            <template x-if="conn._mensaje">
                                                <p class="text-sm font-medium" :class="conn._error ? 'text-red-600' : 'text-green-700'" x-text="conn._mensaje"></p>
                                            </template>
                                        </div>

                                        {{-- Tablas disponibles (post-test) --}}
                                        <template x-if="conn._tablas_disponibles.length > 0">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Selecciona las tablas que el bot usará como contexto:
                                                </p>
                                                <div class="rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                                                    <template x-for="tabla in conn._tablas_disponibles" :key="tabla">
                                                        <div>
                                                            {{-- Fila principal de la tabla --}}
                                                            <div class="flex items-center gap-3 px-3 py-2.5 transition-colors"
                                                                 :class="conn.tablas.includes(tabla)
                                                                     ? 'bg-indigo-50 dark:bg-indigo-900/25'
                                                                     : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750'">
                                                                <input type="checkbox"
                                                                       :checked="conn.tablas.includes(tabla)"
                                                                       @change="toggleTabla(idx, tabla)"
                                                                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 flex-shrink-0 cursor-pointer"/>
                                                                <span class="font-mono text-sm text-gray-800 dark:text-gray-200 flex-1 select-none cursor-pointer"
                                                                      @click="toggleTabla(idx, tabla)"
                                                                      x-text="tabla"></span>
                                                                {{-- Contador de columnas --}}
                                                                <span class="text-xs text-gray-400 dark:text-gray-500 font-mono flex-shrink-0"
                                                                      x-show="columnasDe(conn, tabla).length > 0"
                                                                      x-text="columnasDe(conn, tabla).length + ' cols'"></span>
                                                                {{-- Botón expandir schema --}}
                                                                <button type="button"
                                                                        x-show="columnasDe(conn, tabla).length > 0"
                                                                        @click="toggleEsquema(idx, tabla)"
                                                                        class="p-1 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex-shrink-0"
                                                                        :title="conn._esquemaVisible[tabla] ? 'Ocultar columnas' : 'Ver columnas'">
                                                                    <svg class="w-3.5 h-3.5 transition-transform"
                                                                         :class="conn._esquemaVisible[tabla] ? 'rotate-180 text-indigo-500' : ''"
                                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                            {{-- Panel de columnas expandible --}}
                                                            <div x-show="conn._esquemaVisible[tabla]"
                                                                 class="px-4 pb-3 pt-2 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700">
                                                                <div class="flex items-center justify-between mb-2">
                                                                    <p class="text-xs text-gray-500 font-medium">Columnas para el bot:</p>
                                                                    <div class="flex gap-2">
                                                                        <button type="button" @click="seleccionarTodasCols(idx, tabla)" class="text-[10px] text-indigo-600 hover:underline">Todas</button>
                                                                        <button type="button" @click="limpiarCols(idx, tabla)" class="text-[10px] text-gray-400 hover:underline">Ninguna</button>
                                                                    </div>
                                                                </div>
                                                                <div class="flex flex-wrap gap-1.5">
                                                                    <template x-for="col in columnasDe(conn, tabla)" :key="col">
                                                                        <label class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-xs font-mono cursor-pointer transition-colors"
                                                                               :class="colSeleccionada(conn, tabla, col) ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-500'">
                                                                            <input type="checkbox" :checked="colSeleccionada(conn, tabla, col)"
                                                                                   @change="toggleColumna(idx, tabla, col)"
                                                                                   class="w-3 h-3 text-indigo-600 rounded cursor-pointer">
                                                                            <span x-text="col"></span>
                                                                        </label>
                                                                    </template>
                                                                </div>
                                                                <p class="mt-1.5 text-[10px] text-gray-400"
                                                                   x-show="colsSeleccionadas(conn, tabla).length > 0"
                                                                   x-text="colsSeleccionadas(conn, tabla).length + ' / ' + columnasDe(conn, tabla).length + ' cols seleccionadas'"></p>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                                {{-- Resumen de selección --}}
                                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500"
                                                   x-show="conn.tablas.length > 0">
                                                    <span class="font-semibold text-indigo-600 dark:text-indigo-400" x-text="conn.tablas.length"></span>
                                                    tabla(s) seleccionada(s) para el bot.
                                                </p>
                                            </div>
                                        </template>

                                        {{-- Tablas guardadas (sin re-test) --}}
                                        <template x-if="conn._tablas_disponibles.length === 0 && conn.tablas.length > 0">
                                            <div class="rounded-lg border border-indigo-100 dark:border-indigo-800 overflow-hidden divide-y divide-indigo-50 dark:divide-indigo-900">
                                                <div class="px-3 py-2 bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-between">
                                                    <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                                                        Tablas guardadas
                                                    </p>
                                                    <p class="text-xs text-indigo-500 dark:text-indigo-400">
                                                        Prueba la conexión para ver todas las tablas disponibles
                                                    </p>
                                                </div>
                                                <template x-for="t in conn.tablas" :key="t">
                                                    <div>
                                                        <div class="flex items-center gap-3 px-3 py-2.5 bg-white dark:bg-gray-800">
                                                            <span class="w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                                                            <span class="font-mono text-sm text-gray-800 dark:text-gray-200 flex-1" x-text="t"></span>
                                                            <span class="text-xs text-gray-400 font-mono flex-shrink-0"
                                                                  x-show="(conn._esquemas?.[t] ?? []).length > 0"
                                                                  x-text="(conn._esquemas?.[t] ?? []).length + ' cols'"></span>
                                                            <button type="button"
                                                                    x-show="(conn._esquemas?.[t] ?? []).length > 0"
                                                                    @click="toggleEsquema(idx, t)"
                                                                    class="p-1 text-gray-400 hover:text-indigo-600 transition-colors flex-shrink-0">
                                                                <svg class="w-3.5 h-3.5 transition-transform"
                                                                     :class="conn._esquemaVisible[t] ? 'rotate-180 text-indigo-500' : ''"
                                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                        <div x-show="conn._esquemaVisible[t]"
                                                             class="px-4 pb-3 pt-2 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700">
                                                            <div class="flex items-center justify-between mb-2">
                                                                <p class="text-xs text-gray-500 font-medium">Columnas para el bot:</p>
                                                                <div class="flex gap-2">
                                                                    <button type="button" @click="seleccionarTodasCols(idx, t)" class="text-[10px] text-indigo-600 hover:underline">Todas</button>
                                                                    <button type="button" @click="limpiarCols(idx, t)" class="text-[10px] text-gray-400 hover:underline">Ninguna</button>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-wrap gap-1.5">
                                                                <template x-for="col in (conn._esquemas?.[t] ?? [])" :key="col">
                                                                    <label class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-xs font-mono cursor-pointer transition-colors"
                                                                           :class="colSeleccionada(conn, t, col) ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-500'">
                                                                        <input type="checkbox" :checked="colSeleccionada(conn, t, col)"
                                                                               @change="toggleColumna(idx, t, col)"
                                                                               class="w-3 h-3 text-indigo-600 rounded cursor-pointer">
                                                                        <span x-text="col"></span>
                                                                    </label>
                                                                </template>
                                                            </div>
                                                            <p class="mt-1.5 text-[10px] text-gray-400"
                                                               x-show="colsSeleccionadas(conn, t).length > 0"
                                                               x-text="colsSeleccionadas(conn, t).length + ' / ' + (conn._esquemas?.[t] ?? []).length + ' cols seleccionadas'"></p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="agregar()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 border-2 border-dashed border-indigo-300 hover:border-indigo-500 text-indigo-600 hover:text-indigo-700 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Agregar conexión
                            </button>
                        </div>
                    </div>
                </div>

            </div>{{-- /content --}}
        </div>

    </form>

<script>
function configPage() {
    return {
        activeTab: 'bot',
        preparar(e) {
            // Nada especial — el form envía todo normalmente
        },
    };
}

/**
 * Inserta [TAG] en el textarea del system_prompt en la posición actual del cursor.
 * También destellea el textarea para feedback visual.
 */
function insertarTag(tag) {
    const ta = document.getElementById('system_prompt');
    if (!ta) return;
    const inicio   = ta.selectionStart ?? ta.value.length;
    const fin      = ta.selectionEnd   ?? ta.value.length;
    const etiqueta = '[' + tag + ']';
    ta.value = ta.value.slice(0, inicio) + etiqueta + ta.value.slice(fin);
    ta.selectionStart = ta.selectionEnd = inicio + etiqueta.length;
    // Dispara el evento input para actualizar el contador de Alpine.js
    ta.dispatchEvent(new Event('input'));
    ta.focus();
    // Flash visual de confirmación
    ta.style.outline = '2px solid #6366f1';
    ta.style.outlineOffset = '2px';
    setTimeout(() => { ta.style.outline = ''; ta.style.outlineOffset = ''; }, 600);
}

function extDbManager() {
    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content ?? '';
    const testUrl   = '{{ route("configuracion.test-db") }}';
    const seed = @json($extDbs);

    return {
        conexiones: seed.map(c => ({
            ...c,
            _tablas_disponibles: [],
            _esquemas:      c.tablas_schema ?? {},
            _esquemaVisible: {},
            _probando: false,
            _mensaje: '',
            _error: false,
            _expandido: true,
            tablas_columnas: c.tablas_columnas ?? {},
        })),

        agregar() {
            this.conexiones.push({
                id: 'new_' + Date.now(),
                nombre: '',
                driver: 'mysql',
                host: '',
                port: '',
                database: '',
                username: '',
                password: '',
                has_password: false,
                tablas: [],
                tablas_schema: {},
                tablas_columnas: {},
                _tablas_disponibles: [],
                _esquemas: {},
                _esquemaVisible: {},
                _probando: false,
                _mensaje: '',
                _error: false,
                _expandido: true,
            });
        },

        eliminar(idx) {
            this.conexiones.splice(idx, 1);
        },

        async probar(idx) {
            const conn = this.conexiones[idx];
            conn._probando = true;
            conn._mensaje  = '';
            conn._error    = false;
            try {
                const res  = await fetch(testUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ driver: conn.driver, host: conn.host, port: conn.port || null, database: conn.database, username: conn.username || null, password: conn.password || null }),
                });
                const text = await res.text();
                let json;
                try { json = JSON.parse(text); }
                catch { conn._mensaje = 'Error del servidor (' + res.status + '): ' + text.slice(0, 200); conn._error = true; return; }
                conn._mensaje = json.mensaje;
                conn._error   = !json.success;
                if (json.success) {
                    conn._tablas_disponibles = json.tablas ?? [];
                    const newEsquemas = json.esquemas ?? {};
                    conn._esquemas = newEsquemas;
                    // Auto-seleccionar todas las columnas para tablas ya seleccionadas si aún no tienen selección
                    conn.tablas.forEach(t => {
                        if (!conn.tablas_columnas[t] && newEsquemas[t]) {
                            conn.tablas_columnas[t] = [...newEsquemas[t]];
                        }
                    });
                }
            } catch (e) {
                conn._mensaje = 'Error de red: ' + e.message;
                conn._error   = true;
            } finally {
                conn._probando = false;
            }
        },

        toggleTabla(idx, tabla) {
            const conn = this.conexiones[idx];
            const i = conn.tablas.indexOf(tabla);
            if (i === -1) {
                conn.tablas.push(tabla);
                // Auto-seleccionar todas las columnas al añadir la tabla
                const cols = this.columnasDe(conn, tabla);
                if (cols.length > 0) conn.tablas_columnas[tabla] = [...cols];
            } else {
                conn.tablas.splice(i, 1);
                delete conn.tablas_columnas[tabla];
            }
        },

        toggleColumna(idx, tabla, col) {
            const conn = this.conexiones[idx];
            if (!conn.tablas_columnas[tabla]) conn.tablas_columnas[tabla] = [];
            const i = conn.tablas_columnas[tabla].indexOf(col);
            if (i === -1) conn.tablas_columnas[tabla].push(col);
            else          conn.tablas_columnas[tabla].splice(i, 1);
        },

        seleccionarTodasCols(idx, tabla) {
            const conn = this.conexiones[idx];
            conn.tablas_columnas[tabla] = [...this.columnasDe(conn, tabla)];
        },

        limpiarCols(idx, tabla) {
            this.conexiones[idx].tablas_columnas[tabla] = [];
        },

        colSeleccionada(conn, tabla, col) {
            const sel = conn.tablas_columnas?.[tabla];
            if (!sel || sel.length === 0) return false;
            return sel.includes(col);
        },

        colsSeleccionadas(conn, tabla) {
            return conn.tablas_columnas?.[tabla] ?? [];
        },

        toggleEsquema(idx, tabla) {
            const vis = this.conexiones[idx]._esquemaVisible;
            vis[tabla] = !vis[tabla];
        },

        columnasDe(conn, tabla) {
            return conn._esquemas?.[tabla] ?? [];
        },

        get jsonFinal() {
            return JSON.stringify(this.conexiones.map(c => {
                const tablas_schema   = {};
                const tablas_columnas = {};
                (c.tablas ?? []).forEach(t => {
                    const cols = c._esquemas?.[t] ?? c.tablas_schema?.[t];
                    if (cols && cols.length) tablas_schema[t] = cols;
                    const sel = c.tablas_columnas?.[t];
                    if (sel && sel.length) tablas_columnas[t] = sel;
                });
                return {
                    id: c.id, nombre: c.nombre, driver: c.driver,
                    host: c.host, port: c.port, database: c.database,
                    username: c.username, password: c.password,
                    has_password: c.has_password || c.password !== '',
                    tablas: c.tablas,
                    tablas_schema,
                    tablas_columnas,
                };
            }));
        },
    };
}

/**
 * Gestión de prompts guardados (Sistema de prompts multi-perfil).
 */
function savedPromptsManager() {
    return {
        chars: {{ strlen($systemPrompt ?? '') }},
        max: 8000,
        prompts: @json($savedPrompts->values()),
        nuevoNombre: '',
        guardando: false,
        promptActivo: null,

        init() {
            // Determinar el prompt activo comparando contenido con el system_prompt actual
            const current = document.getElementById('system_prompt')?.value ?? '';
            const match = this.prompts.find(p => p.contenido === current);
            this.promptActivo = match ? match.id : null;
        },

        cargarPrompt(p) {
            const ta = document.getElementById('system_prompt');
            if (!ta) return;
            ta.value = p.contenido;
            this.chars = p.contenido.length;
            this.promptActivo = p.id;
            // Flash visual
            ta.classList.add('ring-2', 'ring-purple-400');
            setTimeout(() => ta.classList.remove('ring-2', 'ring-purple-400'), 1200);
        },

        async guardarPrompt() {
            const nombre = this.nuevoNombre.trim();
            if (!nombre) return;
            const ta = document.getElementById('system_prompt');
            const contenido = ta?.value ?? '';
            if (!contenido.trim()) {
                alert('El textarea del prompt está vacío. Escribe un prompt primero.');
                return;
            }
            this.guardando = true;
            try {
                const res = await axios.post('{{ route("configuracion.prompts.store") }}', { nombre, contenido }, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (res.data.success) {
                    this.prompts.push(res.data.prompt);
                    this.promptActivo = res.data.prompt.id;
                    this.nuevoNombre = '';
                    // Ordenar por nombre
                    this.prompts.sort((a, b) => a.nombre.localeCompare(b.nombre));
                }
            } catch (e) {
                alert('No se pudo guardar el prompt.');
            } finally {
                this.guardando = false;
            }
        },

        async eliminarPrompt(p) {
            if (!confirm(`¿Eliminar el prompt "${p.nombre}"?`)) return;
            try {
                await axios.delete(`{{ url('/configuracion/prompts') }}/${p.id}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                this.prompts = this.prompts.filter(x => x.id !== p.id);
                if (this.promptActivo === p.id) this.promptActivo = null;
            } catch (e) {
                alert('No se pudo eliminar el prompt.');
            }
        },
    };
}
</script>

</x-admin-layout>
