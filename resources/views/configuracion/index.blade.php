<x-admin-layout title="Configuración">

    {{-- Flash success --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             role="status" aria-live="polite"
             class="mb-5 flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Flash error --}}
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
             role="alert" aria-live="assertive"
             class="mb-5 flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-300 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion.update') }}"
          x-data="configPage()" @submit="preparar($event)">
        @csrf

        <div class="flex gap-6 items-start">

            {{-- ═══ SIDEBAR DE TABS ═══ --}}
            <div class="w-56 flex-shrink-0 sticky top-6">
                <div class="bg-indigo-900/30 rounded-xl shadow-sm border border-white/5 overflow-hidden">
                    <div class="px-4 py-3 border-b border-white/5">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Configuración</p>
                    </div>
                    <nav class="p-2 space-y-0.5" role="tablist" aria-label="Secciones de configuración" aria-orientation="vertical">
                        @foreach([
                            ['id' => 'bot',       'icon' => 'bot',      'label' => 'Bot & Prompts',    'color' => 'purple'],
                            ['id' => 'apis',      'icon' => 'link',     'label' => 'Conectar APIs',    'color' => 'emerald'],
                            ['id' => 'dbs',       'icon' => 'db',       'label' => 'Bases de Datos',   'color' => 'indigo'],
                        ] as $tab)
                        <button type="button"
                                role="tab"
                                id="tab-{{ $tab['id'] }}"
                                aria-controls="panel-{{ $tab['id'] }}"
                                @click="activeTab = '{{ $tab['id'] }}'"
                                :aria-selected="(activeTab === '{{ $tab['id'] }}').toString()"
                                :tabindex="activeTab === '{{ $tab['id'] }}' ? 0 : -1"
                                :class="activeTab === '{{ $tab['id'] }}'
                                    ? 'bg-indigo-900/30 text-indigo-700 text-indigo-300 font-semibold'
                                    : 'text-gray-400 hover:bg-gray-700 hover:text-gray-100'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-colors text-left focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none">

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
                            @elseif($tab['icon'] === 'link')
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
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
                            @elseif($tab['id'] === 'apis')
                                @php $anyApi = $estado['evolution_url'] || $estado['openai_key'] || $estado['deepseek_key'] || $estado['gemini_key'] || $googleConectado; @endphp
                                <span class="ml-auto w-2 h-2 rounded-full {{ $anyApi ? 'bg-green-400' : 'bg-amber-400' }} flex-shrink-0"></span>
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

                {{-- ─── TAB: BOT & PROMPTS (N8N Canvas) ─── --}}
                {{-- PHP data for canvas --}}
                @php
                    $canvasCatalogs = collect($availableTags)->where('tipo', 'catalogo')->values();
                    $catalogsWithFiles = $modulosConArchivos->map(fn($mod) => 'CATALOGO_' . strtoupper(preg_replace('/[^A-Z0-9]/i', '_', $mod->slug)))->values()->all();
                    $canvasDbs      = collect($availableTags)->where('tipo', 'db_ext')->values();
                    $canvasApis     = collect($availableTags)->whereIn('tipo', ['api'])->where('activo', true)->values();
                    $canvasTimezone = $botTimezone ?? '';
                    $botCanvasLayout = json_decode(
                        \App\Models\Configuracion::get('bot_canvas_layout', ''),
                        true
                    ) ?: null;
                @endphp

                <div x-show="activeTab === 'bot'" x-cloak
                     id="panel-bot" role="tabpanel" aria-labelledby="tab-bot"
                     x-data="botCanvas()"
                     x-init="init()">

                    {{-- ═══ LIENZO N8N ═══ --}}
                    {{-- Layout: palette left + canvas center --}}
                    <div class="flex gap-0 rounded-xl border border-white/10 overflow-hidden bg-gray-900" style="height:600px">

                        {{-- ── PALETA ── --}}
                        <div class="w-44 flex-shrink-0 border-r border-white/10 bg-gray-850 flex flex-col overflow-y-auto"
                             style="background:#111827">
                            <p class="px-3 pt-3 pb-1.5 text-[10px] font-bold text-gray-500 uppercase tracking-widest">Nodos</p>

                            <template x-for="item in palette" :key="item.type">
                                <div draggable="true"
                                     @dragstart="startPaletteDrag(item.type, $event)"
                                     class="mx-2 mb-1.5 flex items-center gap-2 px-2.5 py-2 rounded-lg border border-white/10 cursor-grab hover:border-indigo-500/60 hover:bg-indigo-500/10 transition-colors select-none"
                                     :title="item.label">
                                    <span class="text-base leading-none" x-text="item.icon"></span>
                                    <span class="text-xs text-gray-300 leading-tight" x-text="item.label"></span>
                                </div>
                            </template>

                            <div class="mt-auto px-3 pb-3 pt-2 border-t border-white/5 text-[9px] text-gray-600 leading-relaxed">
                                Arrastra nodos al lienzo. Conecta el punto <span class="text-indigo-400">●</span> al nodo Bot para activar.
                            </div>
                        </div>

                        {{-- ── CANVAS ── --}}
                        <div class="relative flex-1 overflow-hidden"
                             style="background: radial-gradient(circle at 1px 1px, rgba(255,255,255,.04) 1px, transparent 0); background-size:28px 28px;"
                             @dragover.prevent
                             @drop="onCanvasDrop($event)"
                             @mousemove.window="onMouseMove($event)"
                             @mouseup.window="onMouseUp($event)">

                            {{-- SVG overlay for edges --}}
                            {{-- x-for/x-if cannot be used inside <svg> (browser parses <template> as SVGElement, not HTMLTemplateElement).
                                 Use x-html on a <g> for edges + x-show on a <path> for the temp connection. --}}
                            <svg class="absolute inset-0 w-full h-full pointer-events-none" style="z-index:1"
                                 @click="onSvgEdgeClick($event)">
                                {{-- Existing edges rendered via innerHTML to avoid SVG template issue --}}
                                <g x-html="edgesHtml"></g>
                                {{-- Temp connection being drawn --}}
                                <path x-show="connecting"
                                      :d="connecting ? tempEdgePath() : ''"
                                      fill="none"
                                      stroke="#a5b4fc"
                                      stroke-width="2"
                                      stroke-dasharray="6 3"
                                      opacity="0.8"/>
                            </svg>

                            {{-- Nodes --}}
                            <template x-for="node in nodes" :key="node.id">
                                <div class="absolute select-none"
                                     :style="nodeCardStyle(node)"
                                     style="z-index:2">

                                    {{-- Node card --}}
                                    <div class="rounded-xl border-2 shadow-lg transition-all cursor-pointer"
                                         :class="{
                                             'border-indigo-500 ring-2 ring-indigo-500/40 bg-indigo-900/40': selectedNode && selectedNode.id === node.id,
                                             'border-green-500/60 bg-gray-800': node.type === 'bot' && !(selectedNode && selectedNode.id === node.id),
                                             'border-white/20 bg-gray-800': node.type !== 'bot' && !(selectedNode && selectedNode.id === node.id),
                                             'opacity-50': node.type !== 'bot' && node.type !== 'memoria' && !isConnectedToBot(node.id),
                                         }"
                                         style="min-width:132px;max-width:160px"
                                         @mousedown.stop="startDrag(node.id, $event)"
                                         @click.stop="openModal(node.id)">

                                        <div class="px-3 py-2.5 flex items-center gap-2">
                                            <span class="text-lg leading-none flex-shrink-0"
                                                  x-text="nodeTypes[node.type]?.icon ?? '📦'"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-100 leading-tight truncate"
                                                   x-text="nodeTypes[node.type]?.label ?? node.type"></p>
                                                <p class="text-[9px] text-gray-400 leading-tight mt-0.5 truncate"
                                                   x-text="node.type === 'bot' ? (modeLabels[botMode] ?? 'Cerebro central') : (nodeTypes[node.type]?.sub ?? '')"></p>
                                            </div>
                                        </div>

                                        {{-- Connected indicator --}}
                                        <div x-show="isConnectedToBot(node.id) && node.type !== 'bot'"
                                             class="px-2 pb-1.5">
                                            <span class="text-[9px] text-green-400 font-semibold">● Conectado</span>
                                        </div>

                                        {{-- CRUD permission badges (catalog nodes only) --}}
                                        <template x-if="node.type?.startsWith('catalogo_') && node.permisos">
                                            <div class="px-2 pb-1.5 flex flex-wrap gap-1">
                                                <span x-show="node.permisos?.consultar" class="text-[8px] px-1.5 py-0.5 rounded bg-blue-900/50 text-blue-300">👁 Ver</span>
                                                <span x-show="node.permisos?.crear"    class="text-[8px] px-1.5 py-0.5 rounded bg-green-900/50 text-green-300">✚ Crear</span>
                                                <span x-show="node.permisos?.editar"   class="text-[8px] px-1.5 py-0.5 rounded bg-amber-900/50 text-amber-300">✏ Editar</span>
                                                <span x-show="node.permisos?.borrar"        class="text-[8px] px-1.5 py-0.5 rounded bg-red-900/50 text-red-300">🗑 Borrar</span>
                                                <span x-show="node.permisos?.media_enviar" class="text-[8px] px-1.5 py-0.5 rounded bg-purple-900/50 text-purple-300">📤 Enviar</span>
                                                <span x-show="node.permisos?.media_guardar" class="text-[8px] px-1.5 py-0.5 rounded bg-violet-900/50 text-violet-300">💾 Guardar</span>
                                            </div>
                                        </template>

                                        {{-- WhatsApp node badges --}}
                                        <template x-if="node.type === 'whatsapp' && node.config">
                                            <div class="px-2 pb-1.5 flex flex-wrap gap-1">
                                                <span x-show="Object.values(node.config?.mensajeria ?? {}).some(v=>v)" class="text-[8px] px-1.5 py-0.5 rounded bg-green-900/50 text-green-300">💬 Msg</span>
                                                <span x-show="Object.values(node.config?.grupos ?? {}).some(v=>v)" class="text-[8px] px-1.5 py-0.5 rounded bg-blue-900/50 text-blue-300">👥 Grupos</span>
                                                <span x-show="Object.values(node.config?.contactos ?? {}).some(v=>v)" class="text-[8px] px-1.5 py-0.5 rounded bg-cyan-900/50 text-cyan-300">👤 Cont.</span>
                                            </div>
                                        </template>

                                        {{-- Google Calendar badges --}}
                                        <template x-if="node.type === 'google-calendar' && node.config">
                                            <div class="px-2 pb-1.5 flex flex-wrap gap-1">
                                                <span x-show="node.config?.operaciones?.listar" class="text-[8px] px-1.5 py-0.5 rounded bg-blue-900/50 text-blue-300">📅 Ver</span>
                                                <span x-show="node.config?.operaciones?.crear" class="text-[8px] px-1.5 py-0.5 rounded bg-green-900/50 text-green-300">➕ Crear</span>
                                                <span x-show="node.config?.operaciones?.actualizar" class="text-[8px] px-1.5 py-0.5 rounded bg-amber-900/50 text-amber-300">✏ Editar</span>
                                                <span x-show="node.config?.operaciones?.eliminar" class="text-[8px] px-1.5 py-0.5 rounded bg-red-900/50 text-red-300">🗑 Borrar</span>
                                            </div>
                                        </template>

                                        {{-- Google Drive badges --}}
                                        <template x-if="node.type === 'google-drive' && node.config">
                                            <div class="px-2 pb-1.5 flex flex-wrap gap-1">
                                                <span x-show="node.config?.operaciones?.listar" class="text-[8px] px-1.5 py-0.5 rounded bg-yellow-900/50 text-yellow-300">📂 Ver</span>
                                                <span x-show="node.config?.operaciones?.subir" class="text-[8px] px-1.5 py-0.5 rounded bg-green-900/50 text-green-300">⬆ Subir</span>
                                                <span x-show="node.config?.operaciones?.descargar" class="text-[8px] px-1.5 py-0.5 rounded bg-blue-900/50 text-blue-300">⬇ Bajar</span>
                                                <span x-show="node.config?.operaciones?.eliminar" class="text-[8px] px-1.5 py-0.5 rounded bg-red-900/50 text-red-300">🗑 Borrar</span>
                                            </div>
                                        </template>

                                        {{-- External DB node badges --}}
                                        <template x-if="['db-mysql','db-mongodb','db-postgresql'].includes(node.type) && node.config?.permisos">
                                            <div class="px-2 pb-1.5 flex flex-wrap gap-1">
                                                <span x-show="node.config?.permisos?.consultar" class="text-[8px] px-1.5 py-0.5 rounded bg-blue-900/50 text-blue-300">👁 Ver</span>
                                                <span x-show="node.config?.permisos?.crear" class="text-[8px] px-1.5 py-0.5 rounded bg-green-900/50 text-green-300">✚ Crear</span>
                                                <span x-show="node.config?.permisos?.editar" class="text-[8px] px-1.5 py-0.5 rounded bg-amber-900/50 text-amber-300">✏ Editar</span>
                                                <span x-show="node.config?.permisos?.borrar" class="text-[8px] px-1.5 py-0.5 rounded bg-red-900/50 text-red-300">🗑 Borrar</span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Delete button (non-bot, non-memoria) --}}
                                    <button x-show="node.type !== 'bot' && node.type !== 'memoria'"
                                            type="button"
                                            @click.stop="deleteNode(node.id)"
                                            class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-gray-700 border border-white/20 text-gray-400 hover:text-red-400 hover:bg-red-900/40 flex items-center justify-center text-[10px] z-10 transition-colors"
                                            title="Quitar nodo">
                                        ✕
                                    </button>

                                    {{-- Output port (right side, for non-bot nodes) --}}
                                    <div x-show="node.type !== 'bot'"
                                         class="absolute top-1/2 -translate-y-1/2 -right-3 w-5 h-5 rounded-full border-2 bg-gray-900 cursor-crosshair hover:bg-indigo-500 hover:border-indigo-400 transition-colors z-10 flex items-center justify-center"
                                         :class="isConnectedToBot(node.id) ? 'border-green-400 bg-green-900/40' : 'border-white/30'"
                                         @mousedown.stop.prevent="startConnect(node.id, $event)"
                                         title="Arrastrar para conectar">
                                        <span class="w-2 h-2 rounded-full"
                                              :class="isConnectedToBot(node.id) ? 'bg-green-400' : 'bg-gray-500'"></span>
                                    </div>

                                    {{-- Input port (left side, bot only) --}}
                                    <div x-show="node.type === 'bot'"
                                         class="absolute top-1/2 -translate-y-1/2 -left-3 w-5 h-5 rounded-full border-2 border-green-400 bg-green-900/40 z-10 flex items-center justify-center"
                                         @mouseup.stop="endConnect(node.id)"
                                         title="Soltar aquí para conectar">
                                        <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                    </div>
                                </div>
                            </template>

                            {{-- Empty state --}}
                            <div x-show="nodes.length <= 2"
                                 class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="text-center">
                                    <p class="text-4xl mb-3 opacity-30">⬅</p>
                                    <p class="text-sm text-gray-500">Arrastra nodos desde la paleta al lienzo</p>
                                    <p class="text-xs text-gray-600 mt-1">Luego conecta su puerto al nodo Bot para activarlos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Canvas layout hidden input (serialized on form submit) --}}
                    <input type="hidden" name="bot_canvas_layout" id="bot_canvas_layout_input"
                           :value="getCanvasJson()">

                    {{-- ═══ MODAL DE CONFIGURACIÓN DE NODO ═══ --}}
                    <div x-show="modalOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="fixed inset-0 z-50 flex items-center justify-center p-4"
                         style="background:rgba(0,0,0,.6)"
                         @click.self="modalOpen = false">

                        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-gray-800 border border-white/10 shadow-2xl"
                             @click.stop>

                            {{-- Modal Header --}}
                            <div class="sticky top-0 z-10 flex items-center justify-between px-5 py-4 border-b border-white/10 bg-gray-800">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl" x-text="selectedNode ? (nodeTypes[selectedNode.type]?.icon ?? '📦') : ''"></span>
                                    <div>
                                        <p class="text-sm font-bold text-gray-100"
                                           x-text="selectedNode ? (nodeTypes[selectedNode.type]?.label ?? selectedNode.type) : ''"></p>
                                        <p class="text-xs text-gray-400"
                                           x-text="selectedNode ? (nodeTypes[selectedNode.type]?.desc ?? '') : ''"></p>
                                    </div>
                                </div>
                                <button type="button" @click="modalOpen = false"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-100 hover:bg-white/10 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="px-5 py-5 space-y-5">

                                {{-- ── NODO BOT ── --}}
                                <template x-if="selectedNode?.type === 'bot'">
                                    <div class="space-y-5">

                                        {{-- Modo respuesta --}}
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Modo de respuesta</p>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach([
                                                    ['value' => 'ia',      'label' => 'Solo IA',   'icon' => '🤖', 'desc' => 'Siempre usa el prompt'],
                                                    ['value' => 'pasos',   'label' => 'Por pasos', 'icon' => '📋', 'desc' => 'Solo el flujo definido'],
                                                    ['value' => 'hibrido', 'label' => 'Híbrido',   'icon' => '⚡', 'desc' => 'Flujo → IA como fallback'],
                                                ] as $modo)
                                                <label @click="botMode = '{{ $modo['value'] }}'"
                                                       :class="botMode === '{{ $modo['value'] }}' ? 'bg-indigo-500/15 border-indigo-500/60 ring-1 ring-indigo-500/30' : 'border-white/10 hover:border-indigo-400/40'"
                                                       class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 cursor-pointer transition-all text-center">
                                                    <input type="radio" name="bot_modo_respuesta" value="{{ $modo['value'] }}" class="sr-only"
                                                           :checked="botMode === '{{ $modo['value'] }}'">
                                                    <span class="text-xl">{{ $modo['icon'] }}</span>
                                                    <p class="text-xs font-semibold text-gray-200">{{ $modo['label'] }}</p>
                                                    <p class="text-[10px] text-gray-400">{{ $modo['desc'] }}</p>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Template preview según modo --}}
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Ejemplo de funcionamiento</p>

                                            {{-- Solo IA --}}
                                            <div x-show="botMode === 'ia'" class="rounded-xl bg-gray-900 border border-white/8 p-4 space-y-2 text-xs">
                                                <p class="font-semibold text-indigo-300 mb-1">🤖 Flujo: Solo IA</p>
                                                <div class="flex items-start gap-2">
                                                    <span class="text-green-400 font-mono mt-0.5">👤</span>
                                                    <div class="bg-gray-800 rounded-lg px-3 py-2 text-gray-300">¿Cuánto cuesta el producto X?</div>
                                                </div>
                                                <div class="flex items-start gap-2 justify-end">
                                                    <div class="bg-indigo-900/60 rounded-lg px-3 py-2 text-indigo-200">El System Prompt guía la respuesta → IA genera texto libre.</div>
                                                    <span class="text-indigo-400 font-mono mt-0.5">🤖</span>
                                                </div>
                                                <p class="text-gray-500 pt-1">Agrega nodos <strong class="text-indigo-400">IA</strong> y <strong class="text-purple-400">System Prompt</strong> al lienzo y conéctalos al Bot.</p>
                                            </div>

                                            {{-- Por pasos --}}
                                            <div x-show="botMode === 'pasos'" class="rounded-xl bg-gray-900 border border-white/8 p-4 space-y-2 text-xs">
                                                <p class="font-semibold text-amber-300 mb-1">📋 Flujo: Por pasos</p>
                                                <div class="flex items-start gap-2">
                                                    <span class="text-green-400 font-mono mt-0.5">👤</span>
                                                    <div class="bg-gray-800 rounded-lg px-3 py-2 text-gray-300">Hola</div>
                                                </div>
                                                <div class="flex items-start gap-2 justify-end">
                                                    <div class="bg-amber-900/40 rounded-lg px-3 py-2 text-amber-200">El nodo <em>Flujo por Pasos</em> devuelve el mensaje del paso "inicio".</div>
                                                    <span class="text-amber-400 font-mono mt-0.5">🤖</span>
                                                </div>
                                                <p class="text-gray-500 pt-1">Agrega el nodo <strong class="text-amber-400">Flujo por Pasos</strong> y conéctalo al Bot.</p>
                                            </div>

                                            {{-- Híbrido --}}
                                            <div x-show="botMode === 'hibrido'" class="rounded-xl bg-gray-900 border border-white/8 p-4 space-y-2 text-xs">
                                                <p class="font-semibold text-emerald-300 mb-1">⚡ Flujo: Híbrido</p>
                                                <div class="flex items-start gap-2">
                                                    <span class="text-green-400 font-mono mt-0.5">👤</span>
                                                    <div class="bg-gray-800 rounded-lg px-3 py-2 text-gray-300">Quiero saber más sobre sus servicios</div>
                                                </div>
                                                <div class="flex items-start gap-2 justify-end">
                                                    <div class="bg-emerald-900/40 rounded-lg px-3 py-2 text-emerald-200">① El flujo por pasos intenta responder → si no hay match, ② la IA toma el control.</div>
                                                    <span class="text-emerald-400 font-mono mt-0.5">🤖</span>
                                                </div>
                                                <p class="text-gray-500 pt-1">Necesitas ambos nodos: <strong class="text-amber-400">Flujo por Pasos</strong> + <strong class="text-indigo-400">IA</strong> conectados al Bot.</p>
                                            </div>
                                        </div>

                                        {{-- Zona horaria --}}
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Zona horaria <span class="font-mono text-cyan-400 normal-case">[HORA_ACTUAL]</span></p>
                                            <select name="bot_timezone"
                                                    class="w-full bg-gray-900 border border-white/10 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-cyan-500">
                                                <option value="">— Sin zona horaria —</option>
                                                <optgroup label="México">
                                                    <option value="America/Mexico_City"  {{ ($botTimezone??'') === 'America/Mexico_City'  ? 'selected' : '' }}>Ciudad de México (CST/CDT)</option>
                                                    <option value="America/Monterrey"    {{ ($botTimezone??'') === 'America/Monterrey'    ? 'selected' : '' }}>Monterrey (CST/CDT)</option>
                                                    <option value="America/Tijuana"      {{ ($botTimezone??'') === 'America/Tijuana'      ? 'selected' : '' }}>Tijuana (PST/PDT)</option>
                                                    <option value="America/Chihuahua"    {{ ($botTimezone??'') === 'America/Chihuahua'    ? 'selected' : '' }}>Chihuahua (MST/MDT)</option>
                                                    <option value="America/Hermosillo"   {{ ($botTimezone??'') === 'America/Hermosillo'   ? 'selected' : '' }}>Hermosillo (MST)</option>
                                                </optgroup>
                                                <optgroup label="Latinoamérica">
                                                    <option value="America/Bogota"                 {{ ($botTimezone??'') === 'America/Bogota'                 ? 'selected' : '' }}>Colombia (COT)</option>
                                                    <option value="America/Lima"                   {{ ($botTimezone??'') === 'America/Lima'                   ? 'selected' : '' }}>Perú (PET)</option>
                                                    <option value="America/Santiago"               {{ ($botTimezone??'') === 'America/Santiago'               ? 'selected' : '' }}>Chile (CLT/CLST)</option>
                                                    <option value="America/Argentina/Buenos_Aires" {{ ($botTimezone??'') === 'America/Argentina/Buenos_Aires' ? 'selected' : '' }}>Argentina (ART)</option>
                                                    <option value="America/Sao_Paulo"              {{ ($botTimezone??'') === 'America/Sao_Paulo'              ? 'selected' : '' }}>Brasil / São Paulo (BRT)</option>
                                                    <option value="America/Caracas"                {{ ($botTimezone??'') === 'America/Caracas'                ? 'selected' : '' }}>Venezuela (VET)</option>
                                                    <option value="America/Guayaquil"              {{ ($botTimezone??'') === 'America/Guayaquil'              ? 'selected' : '' }}>Ecuador (ECT)</option>
                                                    <option value="America/La_Paz"                 {{ ($botTimezone??'') === 'America/La_Paz'                 ? 'selected' : '' }}>Bolivia (BOT)</option>
                                                    <option value="America/Asuncion"               {{ ($botTimezone??'') === 'America/Asuncion'               ? 'selected' : '' }}>Paraguay (PYT)</option>
                                                    <option value="America/Montevideo"             {{ ($botTimezone??'') === 'America/Montevideo'             ? 'selected' : '' }}>Uruguay (UYT)</option>
                                                    <option value="America/Santo_Domingo"          {{ ($botTimezone??'') === 'America/Santo_Domingo'          ? 'selected' : '' }}>Rep. Dominicana (AST)</option>
                                                    <option value="America/Guatemala"              {{ ($botTimezone??'') === 'America/Guatemala'              ? 'selected' : '' }}>Guatemala (CST)</option>
                                                    <option value="America/Panama"                 {{ ($botTimezone??'') === 'America/Panama'                 ? 'selected' : '' }}>Panamá (EST)</option>
                                                </optgroup>
                                                <optgroup label="Estados Unidos &amp; Canadá">
                                                    <option value="America/New_York"    {{ ($botTimezone??'') === 'America/New_York'    ? 'selected' : '' }}>Nueva York (EST/EDT)</option>
                                                    <option value="America/Chicago"     {{ ($botTimezone??'') === 'America/Chicago'     ? 'selected' : '' }}>Chicago (CST/CDT)</option>
                                                    <option value="America/Denver"      {{ ($botTimezone??'') === 'America/Denver'      ? 'selected' : '' }}>Denver (MST/MDT)</option>
                                                    <option value="America/Los_Angeles" {{ ($botTimezone??'') === 'America/Los_Angeles' ? 'selected' : '' }}>Los Ángeles (PST/PDT)</option>
                                                    <option value="America/Phoenix"     {{ ($botTimezone??'') === 'America/Phoenix'     ? 'selected' : '' }}>Phoenix (MST)</option>
                                                    <option value="America/Toronto"     {{ ($botTimezone??'') === 'America/Toronto'     ? 'selected' : '' }}>Toronto (EST/EDT)</option>
                                                </optgroup>
                                                <optgroup label="Europa">
                                                    <option value="Europe/Madrid" {{ ($botTimezone??'') === 'Europe/Madrid' ? 'selected' : '' }}>España (CET/CEST)</option>
                                                    <option value="Europe/London" {{ ($botTimezone??'') === 'Europe/London' ? 'selected' : '' }}>Reino Unido (GMT/BST)</option>
                                                    <option value="Europe/Paris"  {{ ($botTimezone??'') === 'Europe/Paris'  ? 'selected' : '' }}>Francia (CET/CEST)</option>
                                                </optgroup>
                                                <optgroup label="Universal">
                                                    <option value="UTC" {{ ($botTimezone??'') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                                </optgroup>
                                            </select>
                                        </div>

                                        {{-- Prompt verificación WhatsApp --}}
                                        @if($hasPhoneField)
                                        <div x-data="{ chars: {{ strlen($promptVerificacion ?? '') }}, max: 1000 }">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Mensaje verificación WhatsApp</p>
                                            <textarea name="bot_prompt_verificacion" rows="3" maxlength="1000"
                                                @input="chars = $event.target.value.length"
                                                placeholder="Hola, te contactamos para verificar tu número de WhatsApp…"
                                                class="w-full px-3 py-2 border border-white/10 bg-gray-700 text-white rounded-lg text-xs leading-relaxed focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-y"
                                            >{{ $promptVerificacion }}</textarea>
                                            <div class="flex justify-end mt-1">
                                                <span class="text-xs font-mono text-gray-500"><span x-text="chars"></span>/<span x-text="max"></span></span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </template>

                                {{-- ── NODO SYSTEM PROMPT ── --}}
                                <template x-if="selectedNode?.type === 'system-prompt'">
                                    <div x-data="savedPromptsManager()" x-init="init()" class="space-y-4">

                                        {{-- Tag palette (only connected nodes) --}}
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Etiquetas disponibles</p>
                                            <div x-show="connectedTags().length > 0" class="flex flex-wrap gap-1.5">
                                                <template x-for="tag in connectedTags()" :key="tag.tag">
                                                    <button type="button"
                                                            @click="insertarTag(tag.tag)"
                                                            :title="tag.preview"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-900/40 border border-indigo-700/60 text-indigo-300 text-xs font-mono hover:bg-indigo-900/70 hover:border-indigo-500 transition-colors cursor-pointer select-none">
                                                        <span class="text-indigo-500 text-[10px]">{}</span>
                                                        <span x-text="tag.tag"></span>
                                                    </button>
                                                </template>
                                            </div>
                                            <p x-show="connectedTags().length === 0" class="text-xs text-gray-500 italic">
                                                Conecta nodos al Bot para que aparezcan sus etiquetas aquí.
                                            </p>
                                        </div>

                                        {{-- Prompts guardados --}}
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Prompts guardados</p>
                                                <span class="text-xs text-gray-500" x-text="prompts.length + ' guardado(s)'"></span>
                                            </div>
                                            <template x-if="prompts.length > 0">
                                                <div class="space-y-1 max-h-32 overflow-y-auto pr-1 mb-3">
                                                    <template x-for="p in prompts" :key="p.id">
                                                        <div class="group flex items-center gap-2 px-3 py-1.5 rounded-lg border transition-all"
                                                             :class="promptActivo === p.id ? 'border-purple-500/60 bg-purple-500/15' : 'border-white/10 hover:border-white/20'">
                                                            <button type="button" @click="togglePrompt(p)"
                                                                    :class="promptActivo === p.id ? 'bg-purple-500' : 'bg-gray-600'"
                                                                    class="relative inline-flex h-4 w-8 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none">
                                                                <span :class="promptActivo === p.id ? 'translate-x-4' : 'translate-x-0'"
                                                                      class="inline-block h-3 w-3 rounded-full bg-white shadow transition-transform duration-200"></span>
                                                            </button>
                                                            <button type="button" @click="cargarPrompt(p)" class="flex-1 text-left min-w-0">
                                                                <span class="text-xs font-medium truncate"
                                                                      :class="promptActivo === p.id ? 'text-purple-300' : 'text-gray-200'"
                                                                      x-text="p.nombre"></span>
                                                            </button>
                                                            <button type="button" @click="editarPrompt(p)"
                                                                    class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-purple-400 rounded transition-all">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            </button>
                                                            <button type="button" @click="eliminarPrompt(p)"
                                                                    class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-400 rounded transition-all">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            <div class="flex items-center gap-2">
                                                <input type="text" x-model="nuevoNombre" placeholder="Nombre del prompt…"
                                                       class="flex-1 text-xs px-2.5 py-1.5 border border-white/10 bg-gray-700 text-gray-100 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition"
                                                       @keydown.enter.prevent="editandoId ? actualizarPrompt() : guardarPrompt()">
                                                <template x-if="editandoId">
                                                    <button type="button" @click="cancelarEdicion()"
                                                            class="px-2 py-1.5 text-xs text-gray-400 hover:text-gray-200 border border-white/10 rounded-lg">✕</button>
                                                </template>
                                                <button type="button"
                                                        @click="editandoId ? actualizarPrompt() : guardarPrompt()"
                                                        :disabled="!nuevoNombre.trim() || guardando"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-purple-300 bg-purple-500/20 hover:bg-purple-500/30 border border-purple-500/30 rounded-lg transition-colors disabled:opacity-50"
                                                        x-text="editandoId ? 'Actualizar' : 'Guardar'">
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Textarea --}}
                                        <div x-data="{ chars: {{ strlen($systemPrompt ?? '') }}, max: 8000 }">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Contenido del prompt</p>
                                            <textarea id="system_prompt" name="system_prompt" rows="12" maxlength="8000"
                                                @input="chars = $event.target.value.length"
                                                placeholder="Eres un asistente de ventas especializado en… Responde siempre en español, de forma clara y amable…"
                                                class="w-full px-3 py-2.5 border border-white/10 bg-gray-700 text-white rounded-lg text-sm font-mono leading-relaxed focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition resize-y"
                                            >{{ $systemPrompt }}</textarea>
                                            <div class="flex justify-between mt-1">
                                                <p class="text-xs text-gray-500">Usa etiquetas <span class="font-mono text-indigo-400">[TAG]</span> para inyectar datos dinámicos.</p>
                                                <span class="text-xs font-mono" :class="chars >= max * 0.9 ? 'text-red-500 font-semibold' : 'text-gray-500'">
                                                    <span x-text="chars"></span>/<span x-text="max"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- ── NODO ETAPAS IA ── --}}
                                <template x-if="selectedNode?.type === 'etapas-ia'">
                                    <div class="space-y-4">
                                        <div x-data="{ ayuda: false, chars: {{ strlen($botPasosIA ?? '') }}, max: 8000 }">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Etapas de conversación (anti-ciclo)</p>
                                                <button type="button" @click="ayuda = !ayuda"
                                                        class="text-xs text-purple-400 hover:text-purple-200">¿Cómo funciona?</button>
                                            </div>
                                            <div x-show="ayuda" x-collapse class="mb-3 p-3 rounded-lg bg-purple-900/10 border border-white/5 text-xs text-gray-300 space-y-2">
                                                <p><strong class="text-purple-300">¿Para qué?</strong> Evita que la IA repita preguntas. Define instrucciones distintas por etapa.</p>
                                                <p><strong class="text-purple-300">Formato:</strong> Array JSON con <code class="bg-gray-700 px-1 rounded">desde</code>, <code class="bg-gray-700 px-1 rounded">hasta</code>, <code class="bg-gray-700 px-1 rounded">nombre</code>, <code class="bg-gray-700 px-1 rounded">instruccion</code>.</p>
                                                <pre class="bg-gray-900 rounded p-2 text-[10px] overflow-x-auto">[
  { "desde": 1, "hasta": 1, "nombre": "Bienvenida", "instruccion": "Saluda y pregunta." },
  { "desde": 2, "hasta": 9999, "nombre": "Cierre", "instruccion": "Resuelve o deriva." }
]</pre>
                                            </div>
                                            <textarea name="bot_pasos_ia" rows="12" maxlength="8000"
                                                @input="chars = $event.target.value.length"
                                                class="w-full px-3 py-2.5 border border-white/10 bg-gray-700 text-white rounded-lg text-xs font-mono leading-relaxed focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition resize-y"
                                                placeholder='[{"desde":1,"hasta":1,"nombre":"Bienvenida","instruccion":"..."}]'
                                            >{{ $botPasosIA }}</textarea>
                                            <div class="flex justify-end mt-1">
                                                <span class="text-xs font-mono" :class="chars >= max * 0.9 ? 'text-red-500 font-semibold' : 'text-gray-500'">
                                                    <span x-text="chars"></span>/<span x-text="max"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- ── NODO FLUJO POR PASOS ── --}}
                                <template x-if="selectedNode?.type === 'flujo-pasos'">
                                    <div x-data="{ chars: {{ strlen($botFlujoPasos ?? '') }}, max: 12000 }" class="space-y-3">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Flujo conversacional por pasos</p>
                                        <p class="text-xs text-gray-400">JSON con claves <code class="bg-gray-700 px-1 rounded text-xs">inicio</code> y <code class="bg-gray-700 px-1 rounded text-xs">steps</code>. Cada step puede tener <code class="bg-gray-700 px-1 rounded text-xs">mensaje</code> y <code class="bg-gray-700 px-1 rounded text-xs">opciones</code>.</p>
                                        <textarea name="bot_flujo_pasos" rows="15" maxlength="12000"
                                            @input="chars = $event.target.value.length"
                                            class="w-full px-3 py-2.5 border border-white/10 bg-gray-700 text-white rounded-lg text-xs font-mono leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-y"
                                            placeholder='{"inicio":"menu","steps":{"menu":{"mensaje":"Hola\n1) Ventas\n2) Soporte","opciones":{"1|ventas":"ventas","2|soporte":"soporte"}}}}'
                                        >{{ $botFlujoPasos }}</textarea>
                                        <div class="flex justify-end">
                                            <span class="text-xs font-mono" :class="chars >= max * 0.9 ? 'text-red-500 font-semibold' : 'text-gray-500'">
                                                <span x-text="chars"></span>/<span x-text="max"></span>
                                            </span>
                                        </div>
                                    </div>
                                </template>

                                {{-- ── NODO MEMORIA ── --}}
                                <template x-if="selectedNode?.type === 'memoria'">
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-3 p-3 rounded-lg bg-green-900/20 border border-green-500/30">
                                            <span class="text-2xl">💬</span>
                                            <div>
                                                <p class="text-sm font-semibold text-green-300">Memoria siempre activa</p>
                                                <p class="text-xs text-gray-400 mt-1">Los últimos <strong class="text-green-300">10 intercambios</strong> de cada conversación se inyectan automáticamente en el contexto de la IA. No requiere configuración.</p>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500">Este nodo no puede desconectarse del Bot.</p>
                                    </div>
                                </template>

                                {{-- ── NODO PIPELINE ── --}}
                                <template x-if="selectedNode?.type === 'pipeline'">
                                    <div x-data="mediaPipelineManager()" x-init="init()">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pipeline de medios</p>
                                            <button type="button" @click="guardar()"
                                                    :disabled="saving"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                                    :class="saved ? 'bg-green-600 text-white' : 'bg-indigo-600 hover:bg-indigo-500 text-white disabled:opacity-60'">
                                                <span x-text="saved ? '✓ Guardado' : (saving ? 'Guardando…' : 'Guardar pipeline')"></span>
                                            </button>
                                        </div>

                                        {{-- Tabs --}}
                                        <div class="flex border-b border-white/5 mb-4 overflow-x-auto">
                                            <template x-for="mt in mediaTypes" :key="mt.key">
                                                <button type="button"
                                                        @click="activeType = mt.key"
                                                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium whitespace-nowrap border-b-2 transition-colors"
                                                        :class="activeType === mt.key
                                                            ? 'border-indigo-500 text-indigo-400 bg-indigo-500/5'
                                                            : 'border-transparent text-gray-400 hover:text-gray-200'">
                                                    <span x-text="mt.icon"></span>
                                                    <span x-text="mt.label"></span>
                                                    <span x-show="pipeline[mt.key] && pipeline[mt.key].activo"
                                                          class="w-1.5 h-1.5 rounded-full bg-green-400 ml-0.5"></span>
                                                </button>
                                            </template>
                                        </div>

                                        {{-- Content --}}
                                        <template x-for="mt in mediaTypes" :key="mt.key">
                                            <div x-show="activeType === mt.key" class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-200" x-text="'Procesar ' + mt.label.toLowerCase() + ' entrante'"></p>
                                                        <p class="text-xs text-gray-400 mt-0.5" x-text="mt.desc"></p>
                                                    </div>
                                                    <button type="button" @click="pipeline[mt.key].activo = !pipeline[mt.key].activo"
                                                            :class="pipeline[mt.key].activo ? 'bg-indigo-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none">
                                                        <span :class="pipeline[mt.key].activo ? 'translate-x-5' : 'translate-x-0'"
                                                              class="inline-block h-5 w-5 rounded-full bg-gray-800 shadow ring-0 transition-transform duration-200"></span>
                                                    </button>
                                                </div>

                                                <div x-show="pipeline[mt.key].activo" x-transition class="space-y-2">
                                                    <template x-for="(paso, idx) in pipeline[mt.key].pasos" :key="idx">
                                                        <div class="bg-gray-900/60 border border-white/5 rounded-lg p-2.5 space-y-2">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-4 h-4 rounded-full bg-indigo-900/60 text-indigo-400 text-[9px] font-bold flex items-center justify-center flex-shrink-0" x-text="idx + 1"></span>
                                                                <select x-model="paso.tipo"
                                                                        class="flex-1 bg-gray-800 border border-white/10 text-gray-100 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-indigo-500">
                                                                    <template x-for="(info, key) in stepTypes" :key="key">
                                                                        <option :value="key" x-text="info.icon + ' ' + info.label"></option>
                                                                    </template>
                                                                </select>
                                                                <select x-model="paso.proveedor"
                                                                        class="w-28 bg-gray-800 border border-white/10 text-gray-100 text-xs rounded-md px-2 py-1 focus:outline-none focus:border-indigo-500">
                                                                    <option value="auto">Auto</option>
                                                                    <option value="openai">OpenAI</option>
                                                                    <option value="gemini">Gemini</option>
                                                                    <option value="whisper">Whisper</option>
                                                                </select>
                                                                <button type="button" @click="removerPaso(mt.key, idx)"
                                                                        x-show="pipeline[mt.key].pasos.length > 1"
                                                                        class="w-5 h-5 flex items-center justify-center rounded text-gray-500 hover:text-red-400 hover:bg-red-900/20 transition-colors flex-shrink-0">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </div>
                                                            <input x-model="paso.prompt" type="text" placeholder="Prompt personalizado (opcional)…"
                                                                   class="w-full bg-gray-800 border border-white/10 text-gray-100 text-xs rounded-md px-2 py-1 placeholder-gray-600 focus:outline-none focus:border-indigo-500">
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="agregarPaso(mt.key)"
                                                            class="w-full py-1.5 border-2 border-dashed border-white/10 hover:border-indigo-500/50 text-xs text-gray-500 hover:text-indigo-400 rounded-lg transition-colors">
                                                        + Agregar paso
                                                    </button>

                                                    {{-- Destino --}}
                                                    <div class="pt-2 border-t border-white/5">
                                                        <p class="text-xs font-semibold text-gray-500 mb-1.5">Destino del resultado</p>
                                                        <select x-model="pipeline[mt.key].destino"
                                                                class="w-full bg-gray-800 border border-white/10 text-gray-100 text-xs rounded-md px-2 py-1.5 focus:outline-none focus:border-indigo-500">
                                                            <template x-for="dest in destinosDisponibles" :key="dest.id">
                                                                <option :value="dest.id" x-text="dest.label"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <script>
                                        function mediaPipelineManager() {
                                            return {
                                                activeType: 'image',
                                                saving: false,
                                                saved: false,
                                                saveError: null,
                                                pipeline: @json($pipeline),
                                                destinosDisponibles: @json($destinosDisponibles),
                                                mediaTypes: [
                                                    { key: 'image',    icon: '🖼',  label: 'Imagen',    desc: 'Fotos y capturas enviadas por el usuario' },
                                                    { key: 'audio',    icon: '🎙',  label: 'Audio',     desc: 'Mensajes de voz y audios' },
                                                    { key: 'video',    icon: '🎬',  label: 'Video',     desc: 'Videos cortos' },
                                                    { key: 'document', icon: '📄',  label: 'Documento', desc: 'PDFs, Word, Excel y otros archivos' },
                                                ],
                                                stepTypes: @json($pasosDisponibles),

                                                init() {
                                                    const keys = ['image', 'audio', 'video', 'document'];
                                                    for (const key of keys) {
                                                        if (!this.pipeline[key]) {
                                                            this.pipeline[key] = { activo: false, pasos: [{ tipo: 'vision', proveedor: 'auto', prompt: '' }], destino: 'pasar_a_bot' };
                                                        }
                                                        if (!Array.isArray(this.pipeline[key].pasos) || this.pipeline[key].pasos.length === 0) {
                                                            this.pipeline[key].pasos = [{ tipo: 'vision', proveedor: 'auto', prompt: '' }];
                                                        }
                                                        if (!this.pipeline[key].destino) this.pipeline[key].destino = 'pasar_a_bot';
                                                    }
                                                },

                                                agregarPaso(tipo) {
                                                    this.pipeline[tipo].pasos.push({ tipo: 'vision', proveedor: 'auto', prompt: '' });
                                                },

                                                removerPaso(tipo, idx) {
                                                    this.pipeline[tipo].pasos.splice(idx, 1);
                                                },

                                                async guardar() {
                                                    this.saving = true;
                                                    this.saveError = null;
                                                    try {
                                                        const res = await fetch('{{ route("configuracion.pipeline.save") }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                                'Accept': 'application/json',
                                                            },
                                                            body: JSON.stringify({ pipeline: this.pipeline }),
                                                        });
                                                        if (res.ok) {
                                                            this.saved = true;
                                                            setTimeout(() => this.saved = false, 2500);
                                                        } else {
                                                            const data = await res.json().catch(() => ({}));
                                                            this.saveError = data.message || 'Error al guardar el pipeline.';
                                                        }
                                                    } catch (e) {
                                                        this.saveError = 'Error de conexión al guardar.';
                                                    } finally {
                                                        this.saving = false;
                                                    }
                                                },
                                            };
                                        }
                                        </script>
                                    </div>
                                </template>

                                {{-- ── NODO IA ── --}}
                                <template x-if="selectedNode?.type === 'ia'">
                                    <div x-data="iaNodeManager()" x-init="init()" class="space-y-5">

                                        <p class="text-xs text-gray-400">Selecciona el proveedor de IA que usará el bot y configura el modelo y capacidades.</p>

                                        {{-- Selector de proveedor --}}
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Proveedor</p>
                                            <div class="grid grid-cols-3 gap-2">
                                                <button type="button" @click="setProveedor('openai')"
                                                        :class="proveedor === 'openai' ? 'ring-2 ring-teal-500 border-teal-500/40 bg-teal-500/10' : 'border-white/10 hover:border-teal-400/50'"
                                                        class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all">
                                                    <svg class="w-6 h-6 text-teal-400" viewBox="0 0 24 24" fill="currentColor"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073z"/></svg>
                                                    <span class="text-xs font-semibold text-gray-200">ChatGPT</span>
                                                    <span class="text-[10px] text-teal-400" x-text="modeloActual('openai')"></span>
                                                </button>
                                                <button type="button" @click="setProveedor('deepseek')"
                                                        :class="proveedor === 'deepseek' ? 'ring-2 ring-blue-500 border-blue-500/40 bg-blue-500/10' : 'border-white/10 hover:border-blue-400/50'"
                                                        class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all">
                                                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                    <span class="text-xs font-semibold text-gray-200">DeepSeek</span>
                                                    <span class="text-[10px] text-blue-400" x-text="modeloActual('deepseek')"></span>
                                                </button>
                                                <button type="button" @click="setProveedor('gemini')"
                                                        :class="proveedor === 'gemini' ? 'ring-2 ring-orange-500 border-orange-500/40 bg-orange-500/10' : 'border-white/10 hover:border-orange-400/50'"
                                                        class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all">
                                                    <svg class="w-6 h-6 text-orange-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a2 2 0 00-1.82 1.17L3.1 17.47A2 2 0 005 20h14a2 2 0 001.9-2.53L13.82 3.17A2 2 0 0012 2z"/></svg>
                                                    <span class="text-xs font-semibold text-gray-200">Gemini</span>
                                                    <span class="text-[10px] text-orange-400" x-text="modeloActual('gemini')"></span>
                                                </button>
                                            </div>
                                            <input type="hidden" name="bot_ia_proveedor" :value="proveedor">
                                        </div>

                                        {{-- ── OpenAI config ── --}}
                                        <div x-show="proveedor === 'openai'" class="space-y-4">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Modelo</p>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <template x-for="m in openai.modelos" :key="m.id">
                                                        <button type="button" @click="openai.elegir(m.id)"
                                                                :class="openai.modelo === m.id && !openai.customOn ? 'ring-2 ring-teal-500 bg-teal-500/15 border-teal-500/40' : 'border-white/10 hover:border-teal-300 hover:bg-gray-800'"
                                                                class="relative flex flex-col items-start px-3 py-2 border rounded-lg transition-all text-left">
                                                            <span class="text-xs font-semibold text-gray-200 leading-tight" x-text="m.label"></span>
                                                            <span x-show="m.tag" class="text-[10px] text-teal-400 font-medium mt-0.5" x-text="m.tag"></span>
                                                            <span x-show="openai.modelo === m.id && !openai.customOn"
                                                                  class="absolute top-1.5 right-1.5 w-3 h-3 bg-teal-500 rounded-full flex items-center justify-center">
                                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                                                            </span>
                                                        </button>
                                                    </template>
                                                    <button type="button" @click="openai.elegir('__custom__')"
                                                            :class="openai.customOn ? 'ring-2 ring-teal-500 bg-teal-500/15 border-teal-500/40' : 'border-dashed border-white/10 hover:border-teal-300'"
                                                            class="flex flex-col items-start px-3 py-2 border rounded-lg transition-all text-left">
                                                        <span class="text-xs font-semibold text-gray-300">Personalizado</span>
                                                    </button>
                                                </div>
                                                <div x-show="openai.customOn" x-transition class="mt-2">
                                                    <input type="text" x-model="openai.customVal" placeholder="ej: gpt-4o-2024-11-20"
                                                           class="w-full px-3 py-1.5 text-xs border border-white/10 rounded-lg focus:ring-2 focus:ring-teal-500"/>
                                                </div>
                                                <input type="hidden" name="openai_model" :value="openai.modeloFinal">
                                            </div>
                                            {{-- Capacidades --}}
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Capacidades</p>
                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                        <div class="flex items-center gap-2.5">
                                                            <span class="text-base">🎙</span>
                                                            <div>
                                                                <p class="text-xs font-semibold text-gray-200">Whisper (voz → texto)</p>
                                                                <p class="text-[10px] text-gray-400">Transcripción de mensajes de voz</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="openai.whisper = !openai.whisper"
                                                                :class="openai.whisper ? 'bg-teal-500' : 'bg-gray-600'"
                                                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                            <span :class="openai.whisper ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                        </button>
                                                        <input type="hidden" name="openai_whisper_activo" :value="openai.whisper ? '1' : '0'">
                                                    </div>
                                                    <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                        <div class="flex items-center gap-2.5">
                                                            <span class="text-base">🖼</span>
                                                            <div>
                                                                <p class="text-xs font-semibold text-gray-200">Vision (leer imágenes)</p>
                                                                <p class="text-[10px] text-gray-400">El bot analiza fotos enviadas por el usuario</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="openai.imagen = !openai.imagen"
                                                                :class="openai.imagen ? 'bg-purple-500' : 'bg-gray-600'"
                                                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                            <span :class="openai.imagen ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                        </button>
                                                        <input type="hidden" name="openai_imagen_activo" :value="openai.imagen ? '1' : '0'">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ── DeepSeek config ── --}}
                                        <div x-show="proveedor === 'deepseek'" class="space-y-4">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Modelo</p>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <template x-for="m in deepseek.modelos" :key="m.id">
                                                        <button type="button" @click="deepseek.elegir(m.id)"
                                                                :class="deepseek.modelo === m.id && !deepseek.customOn ? 'ring-2 ring-blue-500 bg-blue-500/15 border-blue-500/40' : 'border-white/10 hover:border-blue-300 hover:bg-gray-800'"
                                                                class="relative flex flex-col items-start px-3 py-2 border rounded-lg transition-all text-left">
                                                            <span class="text-xs font-semibold text-gray-200 leading-tight" x-text="m.label"></span>
                                                            <span x-show="m.tag" class="text-[10px] text-blue-400 font-medium mt-0.5" x-text="m.tag"></span>
                                                            <span x-show="deepseek.modelo === m.id && !deepseek.customOn"
                                                                  class="absolute top-1.5 right-1.5 w-3 h-3 bg-blue-500 rounded-full flex items-center justify-center">
                                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                                                            </span>
                                                        </button>
                                                    </template>
                                                    <button type="button" @click="deepseek.elegir('__custom__')"
                                                            :class="deepseek.customOn ? 'ring-2 ring-blue-500 bg-blue-500/15 border-blue-500/40' : 'border-dashed border-white/10 hover:border-blue-300'"
                                                            class="flex flex-col items-start px-3 py-2 border rounded-lg transition-all text-left">
                                                        <span class="text-xs font-semibold text-gray-300">Personalizado</span>
                                                    </button>
                                                </div>
                                                <div x-show="deepseek.customOn" x-transition class="mt-2">
                                                    <input type="text" x-model="deepseek.customVal" placeholder="ej: deepseek-v3"
                                                           class="w-full px-3 py-1.5 text-xs border border-white/10 rounded-lg focus:ring-2 focus:ring-blue-500"/>
                                                </div>
                                                <input type="hidden" name="deepseek_model" :value="deepseek.modeloFinal">
                                            </div>
                                            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                DeepSeek no soporta lectura de imágenes ni audio nativo.
                                            </p>
                                        </div>

                                        {{-- ── Gemini config ── --}}
                                        <div x-show="proveedor === 'gemini'" class="space-y-4">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Modelo</p>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <template x-for="m in gemini.modelos" :key="m.id">
                                                        <button type="button" @click="gemini.elegir(m.id)"
                                                                :class="gemini.modelo === m.id && !gemini.customOn ? 'ring-2 ring-orange-500 bg-orange-500/15 border-orange-500/40' : 'border-white/10 hover:border-orange-300 hover:bg-gray-800'"
                                                                class="relative flex flex-col items-start px-3 py-2 border rounded-lg transition-all text-left">
                                                            <div class="flex items-center gap-1 w-full">
                                                                <span class="text-xs font-semibold text-gray-200 leading-tight flex-1" x-text="m.label"></span>
                                                                <svg x-show="m.audio" class="w-3 h-3 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                                                <svg x-show="m.vision" class="w-3 h-3 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            </div>
                                                            <span x-show="m.tag" class="text-[10px] text-orange-400 font-medium mt-0.5" x-text="m.tag"></span>
                                                            <span x-show="gemini.modelo === m.id && !gemini.customOn"
                                                                  class="absolute top-1.5 right-1.5 w-3 h-3 bg-orange-500 rounded-full flex items-center justify-center">
                                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                                                            </span>
                                                        </button>
                                                    </template>
                                                    <button type="button" @click="gemini.elegir('__custom__')"
                                                            :class="gemini.customOn ? 'ring-2 ring-orange-500 bg-orange-500/15 border-orange-500/40' : 'border-dashed border-white/10 hover:border-orange-300'"
                                                            class="flex flex-col items-start px-3 py-2 border rounded-lg transition-all text-left">
                                                        <span class="text-xs font-semibold text-gray-300">Personalizado</span>
                                                    </button>
                                                </div>
                                                <div class="flex flex-wrap gap-1.5 mt-1.5 text-[10px] text-gray-500">
                                                    <span class="flex items-center gap-1"><svg class="w-3 h-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg> soporta audio</span>
                                                    <span class="flex items-center gap-1"><svg class="w-3 h-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> soporta imágenes</span>
                                                </div>
                                                <div x-show="gemini.customOn" x-transition class="mt-2">
                                                    <input type="text" x-model="gemini.customVal" placeholder="ej: gemini-2.0-flash-thinking-exp"
                                                           class="w-full px-3 py-1.5 text-xs border border-white/10 rounded-lg focus:ring-2 focus:ring-orange-500"/>
                                                </div>
                                                <input type="hidden" name="gemini_model" :value="gemini.modeloFinal">
                                            </div>
                                            {{-- Capacidades Gemini --}}
                                            <div x-show="gemini.soportaAudio || gemini.soportaVision">
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Capacidades</p>
                                                <div class="space-y-2">
                                                    <div x-show="gemini.soportaAudio" class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                        <div class="flex items-center gap-2.5">
                                                            <span class="text-base">🎙</span>
                                                            <div>
                                                                <p class="text-xs font-semibold text-gray-200">Audio nativo</p>
                                                                <p class="text-[10px] text-gray-400">Transcripción multimodal integrada</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="gemini.audio = !gemini.audio"
                                                                :class="gemini.audio ? 'bg-orange-500' : 'bg-gray-600'"
                                                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                            <span :class="gemini.audio ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                        </button>
                                                        <input type="hidden" name="gemini_audio_activo" :value="gemini.audio ? '1' : '0'">
                                                    </div>
                                                    <div x-show="gemini.soportaVision" class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                        <div class="flex items-center gap-2.5">
                                                            <span class="text-base">🖼</span>
                                                            <div>
                                                                <p class="text-xs font-semibold text-gray-200">Vision (leer imágenes)</p>
                                                                <p class="text-[10px] text-gray-400">El bot analiza fotos enviadas por el usuario</p>
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="gemini.vision = !gemini.vision"
                                                                :class="gemini.vision ? 'bg-purple-500' : 'bg-gray-600'"
                                                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                            <span :class="gemini.vision ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                        </button>
                                                        <input type="hidden" name="gemini_vision_activo" :value="gemini.vision ? '1' : '0'">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-xs text-gray-500">
                                            Configura las API Keys en
                                            <button type="button" @click="modalOpen=false; $dispatch('set-tab', 'apis')" class="text-emerald-400 hover:underline">Conectar APIs</button>.
                                        </p>
                                    </div>
                                </template>

                                {{-- ── NODO CATÁLOGO (con permisos CRUD) ── --}}
                                <template x-if="selectedNode?.type?.startsWith('catalogo_')">
                                    <div class="space-y-4">

                                        {{-- Etiqueta de contexto --}}
                                        <div class="p-3 rounded-lg bg-gray-700/50 border border-white/10">
                                            <p class="text-xs font-bold text-gray-300 mb-1">Etiqueta de contexto</p>
                                            <template x-for="tag in (nodeTypes[selectedNode?.type]?.tags ?? [])" :key="tag.tag">
                                                <div class="flex items-start gap-2 mt-2">
                                                    <button type="button"
                                                            @click="insertarTag(tag.tag)"
                                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-indigo-900/40 border border-indigo-700/60 text-indigo-300 text-xs font-mono hover:bg-indigo-900/70 hover:border-indigo-500 transition-colors cursor-pointer">
                                                        <span class="text-indigo-500 text-[10px]">{}</span>
                                                        <span x-text="tag.tag"></span>
                                                    </button>
                                                    <p class="text-xs text-gray-400 mt-0.5" x-text="tag.preview"></p>
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Permisos CRUD --}}
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Permisos del bot sobre este catálogo</p>
                                            <p class="text-[10px] text-gray-500 mb-3">Define qué operaciones puede ejecutar el bot cuando el usuario lo solicite.</p>

                                            <div class="space-y-2">
                                                {{-- Consultar --}}
                                                <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="text-base">👁</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">Consultar</p>
                                                            <p class="text-[10px] text-gray-400">El bot puede leer y mostrar registros</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.permisos.consultar = !selectedNode.permisos.consultar"
                                                            :class="selectedNode.permisos?.consultar ? 'bg-blue-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.permisos?.consultar ? 'translate-x-4' : 'translate-x-0'"
                                                              class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>

                                                {{-- Crear --}}
                                                <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="text-base">➕</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">Crear</p>
                                                            <p class="text-[10px] text-gray-400">El bot puede agregar nuevos registros</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.permisos.crear = !selectedNode.permisos.crear"
                                                            :class="selectedNode.permisos?.crear ? 'bg-green-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.permisos?.crear ? 'translate-x-4' : 'translate-x-0'"
                                                              class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>

                                                {{-- Editar --}}
                                                <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="text-base">✏️</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">Editar</p>
                                                            <p class="text-[10px] text-gray-400">El bot puede modificar registros existentes</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.permisos.editar = !selectedNode.permisos.editar"
                                                            :class="selectedNode.permisos?.editar ? 'bg-amber-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.permisos?.editar ? 'translate-x-4' : 'translate-x-0'"
                                                              class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>

                                                {{-- Borrar --}}
                                                <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="text-base">🗑️</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">Borrar</p>
                                                            <p class="text-[10px] text-gray-400">El bot puede eliminar registros</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.permisos.borrar = !selectedNode.permisos.borrar"
                                                            :class="selectedNode.permisos?.borrar ? 'bg-red-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.permisos?.borrar ? 'translate-x-4' : 'translate-x-0'"
                                                              class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>

                                                {{-- Media adjunta (solo si el catálogo tiene campos de archivo) --}}
                                                <div x-show="nodeTypes[selectedNode?.type]?.hasFiles">
                                                    <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-purple-900/30">
                                                        <div class="flex items-center gap-2.5">
                                                            <span class="text-base">📎</span>
                                                            <div>
                                                                <p class="text-xs font-semibold text-gray-200">Media del catálogo</p>
                                                                <p class="text-[10px] text-gray-400">Activa opciones de envío y recepción de archivos</p>
                                                            </div>
                                                        </div>
                                                        <button type="button"
                                                                @click="selectedNode.permisos.media = !selectedNode.permisos.media"
                                                                :class="selectedNode.permisos?.media ? 'bg-purple-500' : 'bg-gray-600'"
                                                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                            <span :class="selectedNode.permisos?.media ? 'translate-x-4' : 'translate-x-0'"
                                                                  class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                        </button>
                                                    </div>
                                                    {{-- Sub-opciones --}}
                                                    <div x-show="selectedNode.permisos?.media" x-transition class="mt-2 ml-3 space-y-1.5">
                                                        <div class="flex items-center justify-between px-3 py-2 bg-gray-900/40 rounded-xl border border-white/5">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-sm">📤</span>
                                                                <div>
                                                                    <p class="text-xs font-medium text-gray-300">Enviar archivos al usuario</p>
                                                                    <p class="text-[10px] text-gray-500">El bot enviará imágenes, videos o documentos del catálogo</p>
                                                                </div>
                                                            </div>
                                                            <button type="button"
                                                                    @click="selectedNode.permisos.media_enviar = !selectedNode.permisos.media_enviar"
                                                                    :class="selectedNode.permisos?.media_enviar ? 'bg-purple-500' : 'bg-gray-600'"
                                                                    class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                                <span :class="selectedNode.permisos?.media_enviar ? 'translate-x-4' : 'translate-x-0'"
                                                                      class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                            </button>
                                                        </div>
                                                        <div class="flex items-center justify-between px-3 py-2 bg-gray-900/40 rounded-xl border border-white/5">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-sm">💾</span>
                                                                <div>
                                                                    <p class="text-xs font-medium text-gray-300">Guardar archivos recibidos</p>
                                                                    <p class="text-[10px] text-gray-500">Guarda fotos, videos o audios enviados por el usuario en el catálogo</p>
                                                                </div>
                                                            </div>
                                                            <button type="button"
                                                                    @click="selectedNode.permisos.media_guardar = !selectedNode.permisos.media_guardar"
                                                                    :class="selectedNode.permisos?.media_guardar ? 'bg-violet-500' : 'bg-gray-600'"
                                                                    class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                                <span :class="selectedNode.permisos?.media_guardar ? 'translate-x-4' : 'translate-x-0'"
                                                                      class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <p x-show="!isConnectedToBot(selectedNode?.id)"
                                           class="text-xs text-amber-400">
                                            ⚠ Conecta este nodo al Bot para activar sus permisos y etiquetas.
                                        </p>
                                        <p x-show="isConnectedToBot(selectedNode?.id)"
                                           class="text-xs text-green-400">
                                            ✓ Conectado — permisos y etiquetas activos en el bot.
                                        </p>
                                    </div>
                                </template>

                                {{-- ── NODO WHATSAPP ── --}}
                                <template x-if="selectedNode?.type === 'whatsapp'">
                                    <div class="space-y-4 max-h-[30rem] overflow-y-auto pr-1">

                                        {{-- Estado de conexión --}}
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-700/50 border border-white/10">
                                            <div class="w-8 h-8 rounded-lg bg-green-900/50 flex items-center justify-center flex-shrink-0">
                                                <span class="text-base">📱</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-200">Evolution API</p>
                                                <div class="flex flex-wrap gap-2 mt-0.5">
                                                    @if(($estado['evolution_url'] ?? false) && ($estado['evolution_key'] ?? false))
                                                    <span class="text-[10px] text-green-400 font-semibold">✔ URL + API Key configuradas</span>
                                                    @else
                                                    <span class="text-[10px] text-amber-400">⚠ Configura las credenciales en</span>
                                                    <button type="button" @click="modalOpen=false; $dispatch('set-tab','apis')" class="text-[10px] text-emerald-400 hover:underline">Conectar APIs ↗</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mensajería --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Mensajería — tipos que puede enviar el bot</p>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach([
                                                    ['k'=>'texto',     'e'=>'💬', 'l'=>'Texto'],
                                                    ['k'=>'imagen',    'e'=>'🖼',  'l'=>'Imagen'],
                                                    ['k'=>'video',     'e'=>'🎦', 'l'=>'Video'],
                                                    ['k'=>'audio',     'e'=>'🎤', 'l'=>'Audio'],
                                                    ['k'=>'documento', 'e'=>'📄', 'l'=>'Documento'],
                                                    ['k'=>'ubicacion', 'e'=>'📍', 'l'=>'Ubicación'],
                                                    ['k'=>'contacto',  'e'=>'👤', 'l'=>'Contacto vCard'],
                                                    ['k'=>'sticker',   'e'=>'🎭', 'l'=>'Sticker'],
                                                    ['k'=>'encuesta',  'e'=>'📊', 'l'=>'Encuesta/Poll'],
                                                    ['k'=>'lista',     'e'=>'📋', 'l'=>'Lista de opciones'],
                                                    ['k'=>'botones',   'e'=>'🔘', 'l'=>'Botones de acción'],
                                                ] as $wi)
                                                <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-900/60 rounded-lg border border-white/5">
                                                    <span class="text-xs text-gray-300">{{ $wi['e'] }} {{ $wi['l'] }}</span>
                                                    <button type="button"
                                                            @click="selectedNode.config.mensajeria.{{ $wi['k'] }} = !selectedNode.config.mensajeria.{{ $wi['k'] }}"
                                                            :class="selectedNode.config?.mensajeria?.{{ $wi['k'] }} ? 'bg-green-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-4 w-8 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.mensajeria?.{{ $wi['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-3 w-3 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Gestión de grupos --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Gestión de grupos</p>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach([
                                                    ['k'=>'listar',            'e'=>'📋', 'l'=>'Obtener grupos'],
                                                    ['k'=>'miembros',          'e'=>'👥', 'l'=>'Ver miembros'],
                                                    ['k'=>'crear',             'e'=>'➕', 'l'=>'Crear grupo'],
                                                    ['k'=>'addParticipantes',  'e'=>'👤+', 'l'=>'Agregar miembros'],
                                                    ['k'=>'removeParticipantes','e'=>'👤-','l'=>'Quitar miembros'],
                                                    ['k'=>'promoverAdmin',     'e'=>'⭐', 'l'=>'Promover admin'],
                                                    ['k'=>'destituirAdmin',    'e'=>'⬇', 'l'=>'Destituir admin'],
                                                    ['k'=>'actualizarNombre',  'e'=>'✏', 'l'=>'Cambiar nombre'],
                                                    ['k'=>'actualizarDesc',    'e'=>'📝', 'l'=>'Cambiar descripción'],
                                                    ['k'=>'salir',             'e'=>'🚶', 'l'=>'Salir del grupo'],
                                                ] as $gi)
                                                <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-900/60 rounded-lg border border-white/5">
                                                    <span class="text-xs text-gray-300">{{ $gi['e'] }} {{ $gi['l'] }}</span>
                                                    <button type="button"
                                                            @click="selectedNode.config.grupos.{{ $gi['k'] }} = !selectedNode.config.grupos.{{ $gi['k'] }}"
                                                            :class="selectedNode.config?.grupos?.{{ $gi['k'] }} ? 'bg-blue-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-4 w-8 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.grupos?.{{ $gi['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-3 w-3 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Mensajes y Chats --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Mensajes y chats</p>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach([
                                                    ['k'=>'eliminar',    'e'=>'🗑', 'l'=>'Eliminar mensaje'],
                                                    ['k'=>'reaccionar',  'e'=>'😀', 'l'=>'Reaccionar emoji'],
                                                    ['k'=>'marcarLeido', 'e'=>'✔✔', 'l'=>'Marcar leído'],
                                                    ['k'=>'obtener',     'e'=>'📥', 'l'=>'Obtener mensajes'],
                                                    ['k'=>'archivar',    'e'=>'📦', 'l'=>'Archivar chat'],
                                                ] as $mi)
                                                <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-900/60 rounded-lg border border-white/5">
                                                    <span class="text-xs text-gray-300">{{ $mi['e'] }} {{ $mi['l'] }}</span>
                                                    <button type="button"
                                                            @click="selectedNode.config.mensajes.{{ $mi['k'] }} = !selectedNode.config.mensajes.{{ $mi['k'] }}"
                                                            :class="selectedNode.config?.mensajes?.{{ $mi['k'] }} ? 'bg-amber-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-4 w-8 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.mensajes?.{{ $mi['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-3 w-3 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Contactos --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Contactos</p>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach([
                                                    ['k'=>'buscar',     'e'=>'🔍', 'l'=>'Buscar contactos'],
                                                    ['k'=>'verificar',  'e'=>'✔',  'l'=>'Verificar número'],
                                                    ['k'=>'fotoPerfil', 'e'=>'🖼',  'l'=>'Foto de perfil'],
                                                ] as $ci)
                                                <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-900/60 rounded-lg border border-white/5">
                                                    <span class="text-xs text-gray-300">{{ $ci['e'] }} {{ $ci['l'] }}</span>
                                                    <button type="button"
                                                            @click="selectedNode.config.contactos.{{ $ci['k'] }} = !selectedNode.config.contactos.{{ $ci['k'] }}"
                                                            :class="selectedNode.config?.contactos?.{{ $ci['k'] }} ? 'bg-cyan-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-4 w-8 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.contactos?.{{ $ci['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-3 w-3 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Eventos Webhook --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Eventos webhook a procesar</p>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                @foreach([
                                                    ['k'=>'message',           'l'=>'messages.upsert'],
                                                    ['k'=>'connectionUpdate',  'l'=>'connection.update'],
                                                    ['k'=>'sendMessage',       'l'=>'send.message'],
                                                    ['k'=>'contactsUpdate',    'l'=>'contacts.update'],
                                                    ['k'=>'presenceUpdate',    'l'=>'presence.update'],
                                                    ['k'=>'chatsUpdate',       'l'=>'chats.update'],
                                                    ['k'=>'groupUpdate',       'l'=>'groups.upsert'],
                                                    ['k'=>'groupParticipants', 'l'=>'group-participants'],
                                                    ['k'=>'qrcodeUpdated',     'l'=>'qrcode.updated'],
                                                    ['k'=>'labelsEdit',        'l'=>'labels.edit'],
                                                ] as $ei)
                                                <div class="flex items-center justify-between px-2.5 py-1.5 bg-gray-900/60 rounded-lg border border-white/5">
                                                    <span class="text-[10px] font-mono text-gray-400 truncate">{{ $ei['l'] }}</span>
                                                    <button type="button"
                                                            @click="selectedNode.config.eventos.{{ $ei['k'] }} = !selectedNode.config.eventos.{{ $ei['k'] }}"
                                                            :class="selectedNode.config?.eventos?.{{ $ei['k'] }} ? 'bg-purple-500' : 'bg-gray-600'"
                                                            class="ml-1 flex-shrink-0 relative inline-flex h-4 w-8 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.eventos?.{{ $ei['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-3 w-3 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <p x-show="!isConnectedToBot(selectedNode?.id)" class="text-xs text-amber-400">⚠ Conecta este nodo al Bot para activarlo.</p>
                                        <p x-show="isConnectedToBot(selectedNode?.id)" class="text-xs text-green-400">✓ Conectado — configuración activa en el bot.</p>
                                    </div>
                                </template>

                                {{-- ── NODO GOOGLE CALENDAR ── --}}
                                <template x-if="selectedNode?.type === 'google-calendar'">
                                    <div class="space-y-4">
                                        {{-- Estado --}}
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-700/50 border border-white/10">
                                            <span class="text-xl">📅</span>
                                            <div>
                                                <p class="text-xs font-semibold text-gray-200">Google Calendar</p>
                                                @if($googleConectado)
                                                <p class="text-[10px] text-green-400 font-semibold">✔ Google conectado{{ $googleEmail ? ' (' . $googleEmail . ')' : '' }}</p>
                                                @else
                                                <span class="text-[10px] text-amber-400">⚠ Conecta Google en </span>
                                                <button type="button" @click="modalOpen=false; $dispatch('set-tab','apis')" class="text-[10px] text-emerald-400 hover:underline">Conectar APIs ↗</button>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Calendar ID --}}
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">ID del calendario <span class="text-gray-600">(vacío = calendario principal)</span></label>
                                            <input type="text" x-model="selectedNode.config.calendarId" placeholder="example@group.calendar.google.com"
                                                   class="w-full px-3 py-2 bg-gray-900 border border-white/10 text-gray-100 text-xs rounded-lg focus:outline-none focus:border-blue-500">
                                        </div>
                                        {{-- Operaciones --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Permisos del bot en Google Calendar</p>
                                            <div class="space-y-1.5">
                                                @foreach([
                                                    ['k'=>'listar',     'e'=>'📋', 'l'=>'Listar eventos',           'd'=>'Consultar próximos eventos del calendario'],
                                                    ['k'=>'detalle',    'e'=>'🔍', 'l'=>'Ver detalle de evento',     'd'=>'Obtener información completa de un evento'],
                                                    ['k'=>'crear',      'e'=>'➕', 'l'=>'Crear evento',                 'd'=>'Agendar nuevas citas o eventos'],
                                                    ['k'=>'actualizar', 'e'=>'✏', 'l'=>'Actualizar evento',            'd'=>'Modificar fecha, hora o descripción'],
                                                    ['k'=>'eliminar',   'e'=>'🗑', 'l'=>'Eliminar evento',              'd'=>'Cancelar o borrar eventos'],
                                                    ['k'=>'invitar',    'e'=>'📧', 'l'=>'Invitar participantes',       'd'=>'Agregar asistentes al evento'],
                                                ] as $cali)
                                                <div class="flex items-center justify-between px-3 py-2 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-base">{{ $cali['e'] }}</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">{{ $cali['l'] }}</p>
                                                            <p class="text-[10px] text-gray-400">{{ $cali['d'] }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.config.operaciones.{{ $cali['k'] }} = !selectedNode.config.operaciones.{{ $cali['k'] }}"
                                                            :class="selectedNode.config?.operaciones?.{{ $cali['k'] }} ? 'bg-blue-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.operaciones?.{{ $cali['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <p x-show="!isConnectedToBot(selectedNode?.id)" class="text-xs text-amber-400">⚠ Conecta este nodo al Bot para activarlo.</p>
                                        <p x-show="isConnectedToBot(selectedNode?.id)" class="text-xs text-green-400">✓ Conectado — permisos activos.</p>
                                    </div>
                                </template>

                                {{-- ── NODO GOOGLE DRIVE ── --}}
                                <template x-if="selectedNode?.type === 'google-drive'">
                                    <div class="space-y-4">
                                        {{-- Estado --}}
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-700/50 border border-white/10">
                                            <span class="text-xl">📂</span>
                                            <div>
                                                <p class="text-xs font-semibold text-gray-200">Google Drive</p>
                                                @if($googleConectado)
                                                <p class="text-[10px] text-green-400 font-semibold">✔ Google conectado{{ $googleEmail ? ' (' . $googleEmail . ')' : '' }}</p>
                                                @else
                                                <span class="text-[10px] text-amber-400">⚠ Conecta Google en </span>
                                                <button type="button" @click="modalOpen=false; $dispatch('set-tab','apis')" class="text-[10px] text-emerald-400 hover:underline">Conectar APIs ↗</button>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Folder ID --}}
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">ID de carpeta raíz <span class="text-gray-600">(vacío = Mi Drive)</span></label>
                                            <input type="text" x-model="selectedNode.config.carpetaId" placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms"
                                                   class="w-full px-3 py-2 bg-gray-900 border border-white/10 text-gray-100 text-xs rounded-lg focus:outline-none focus:border-yellow-500">
                                        </div>
                                        {{-- Operaciones --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Permisos del bot en Google Drive</p>
                                            <div class="space-y-1.5">
                                                @foreach([
                                                    ['k'=>'listar',       'e'=>'📂', 'l'=>'Listar archivos',          'd'=>'Ver lista de archivos y carpetas'],
                                                    ['k'=>'detalle',      'e'=>'🔍', 'l'=>'Ver detalles',              'd'=>'Metadatos y URL de descarga'],
                                                    ['k'=>'subir',        'e'=>'⬆', 'l'=>'Subir archivos',              'd'=>'Guardar archivos en Drive'],
                                                    ['k'=>'descargar',    'e'=>'⬇', 'l'=>'Descargar / compartir',       'd'=>'Enlace de descarga o compartir con el usuario'],
                                                    ['k'=>'crearCarpeta', 'e'=>'📁', 'l'=>'Crear carpeta',              'd'=>'Organizar contenido creando carpetas'],
                                                    ['k'=>'compartir',    'e'=>'🔗', 'l'=>'Compartir archivo',          'd'=>'Generar enlace público o enviar por email'],
                                                    ['k'=>'renombrar',    'e'=>'✏', 'l'=>'Renombrar',                   'd'=>'Cambiar nombre de archivos o carpetas'],
                                                    ['k'=>'eliminar',     'e'=>'🗑', 'l'=>'Eliminar archivo',           'd'=>'Mover a papelera o eliminar permanentemente'],
                                                ] as $dri)
                                                <div class="flex items-center justify-between px-3 py-2 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-base">{{ $dri['e'] }}</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">{{ $dri['l'] }}</p>
                                                            <p class="text-[10px] text-gray-400">{{ $dri['d'] }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.config.operaciones.{{ $dri['k'] }} = !selectedNode.config.operaciones.{{ $dri['k'] }}"
                                                            :class="selectedNode.config?.operaciones?.{{ $dri['k'] }} ? 'bg-yellow-500' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.operaciones?.{{ $dri['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <p x-show="!isConnectedToBot(selectedNode?.id)" class="text-xs text-amber-400">⚠ Conecta este nodo al Bot para activarlo.</p>
                                        <p x-show="isConnectedToBot(selectedNode?.id)" class="text-xs text-green-400">✓ Conectado — permisos activos.</p>
                                    </div>
                                </template>

                                {{-- ── NODO BD EXTERNA (MySQL / MongoDB / PostgreSQL) ── --}}
                                <template x-if="selectedNode && ['db-mysql','db-mongodb','db-postgresql'].includes(selectedNode.type)">
                                    <div class="space-y-4">
                                        {{-- Header --}}
                                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-700/50 border border-white/10">
                                            <span class="text-xl" x-text="{'db-mysql':'🐬','db-mongodb':'🍃','db-postgresql':'🐘'}[selectedNode.type]"></span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-200" x-text="{'db-mysql':'MySQL','db-mongodb':'MongoDB','db-postgresql':'PostgreSQL'}[selectedNode.type] + ' — Base de datos externa'"></p>
                                                <p class="text-[10px] text-gray-400" x-text="dbConexiones(selectedNode.type).length + ' conexión(es) configurada(s)'"></p>
                                            </div>
                                            <template x-if="dbConexiones(selectedNode.type).length === 0">
                                                <button type="button" @click="modalOpen=false; $dispatch('set-tab','bds')" class="text-[10px] text-emerald-400 hover:underline whitespace-nowrap">Configurar BDs ↗</button>
                                            </template>
                                        </div>
                                        {{-- Selector de conexión --}}
                                        <template x-if="dbConexiones(selectedNode.type).length > 0">
                                            <div>
                                                <label class="block text-xs text-gray-400 mb-1">Conexión a usar</label>
                                                <select x-model="selectedNode.config.conexionNombre"
                                                        class="w-full bg-gray-900 border border-white/10 text-gray-100 text-xs rounded-lg px-3 py-2 focus:outline-none focus:border-cyan-500">
                                                    <option value="">— Seleccionar conexión —</option>
                                                    <template x-for="db in dbConexiones(selectedNode.type)" :key="db.nombre">
                                                        <option :value="db.nombre" x-text="db.nombre + (db.host ? ' (' + db.host + ')' : '')"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>
                                        {{-- Tabla / Colección --}}
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" x-text="selectedNode.type === 'db-mongodb' ? 'Colección (vacío = todas)' : 'Tabla / Vista (vacío = todas)'"></label>
                                            <input type="text" x-model="selectedNode.config.tabla"
                                                   :placeholder="selectedNode.type === 'db-mongodb' ? 'mi_coleccion' : 'mi_tabla'"
                                                   class="w-full px-3 py-2 bg-gray-900 border border-white/10 text-gray-100 text-xs rounded-lg focus:outline-none focus:border-cyan-500">
                                        </div>
                                        {{-- Permisos CRUD --}}
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Permisos del bot sobre esta BD</p>
                                            <div class="space-y-1.5">
                                                @foreach([
                                                    ['k'=>'consultar','e'=>'👁', 'l'=>'Consultar / Buscar', 'd'=>'El bot puede leer registros',               'c'=>'bg-blue-500'],
                                                    ['k'=>'crear',    'e'=>'➕', 'l'=>'Insertar registro',  'd'=>'El bot puede insertar nuevos registros',       'c'=>'bg-green-500'],
                                                    ['k'=>'editar',   'e'=>'✏', 'l'=>'Actualizar registro','d'=>'El bot puede modificar registros existentes',   'c'=>'bg-amber-500'],
                                                    ['k'=>'borrar',   'e'=>'🗑', 'l'=>'Eliminar registro',  'd'=>'El bot puede eliminar registros',              'c'=>'bg-red-500'],
                                                ] as $dbi)
                                                <div class="flex items-center justify-between px-3 py-2.5 bg-gray-900/60 rounded-xl border border-white/5">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="text-base">{{ $dbi['e'] }}</span>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-200">{{ $dbi['l'] }}</p>
                                                            <p class="text-[10px] text-gray-400">{{ $dbi['d'] }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button"
                                                            @click="selectedNode.config.permisos.{{ $dbi['k'] }} = !selectedNode.config.permisos.{{ $dbi['k'] }}"
                                                            :class="selectedNode.config?.permisos?.{{ $dbi['k'] }} ? '{{ $dbi['c'] }}' : 'bg-gray-600'"
                                                            class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors">
                                                        <span :class="selectedNode.config?.permisos?.{{ $dbi['k'] }} ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-4 w-4 rounded-full bg-gray-800 shadow transition-transform"></span>
                                                    </button>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <p x-show="!isConnectedToBot(selectedNode?.id)" class="text-xs text-amber-400">⚠ Conecta este nodo al Bot para activar sus permisos.</p>
                                        <p x-show="isConnectedToBot(selectedNode?.id)" class="text-xs text-green-400">✓ Conectado — permisos activos en el bot.</p>
                                    </div>
                                </template>

                                {{-- ── NODO DB / API (info only) ── --}}
                                <template x-if="selectedNode && ['db_', 'api_'].some(t => selectedNode.type.startsWith(t))">
                                    <div class="space-y-3">
                                        <div class="p-3 rounded-lg bg-gray-700/50 border border-white/10">
                                            <p class="text-xs font-bold text-gray-300 mb-1">Etiquetas de este nodo</p>
                                            <template x-for="tag in (nodeTypes[selectedNode?.type]?.tags ?? [])" :key="tag.tag">
                                                <div class="flex items-start gap-2 mt-2">
                                                    <button type="button"
                                                            @click="insertarTag(tag.tag)"
                                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-indigo-900/40 border border-indigo-700/60 text-indigo-300 text-xs font-mono hover:bg-indigo-900/70 hover:border-indigo-500 transition-colors cursor-pointer">
                                                        <span class="text-indigo-500 text-[10px]">{}</span>
                                                        <span x-text="tag.tag"></span>
                                                    </button>
                                                    <p class="text-xs text-gray-400 mt-0.5" x-text="tag.preview"></p>
                                                </div>
                                            </template>
                                        </div>
                                        <p x-show="!isConnectedToBot(selectedNode?.id)"
                                           class="text-xs text-amber-400">
                                            ⚠ Conecta este nodo al Bot para que sus etiquetas sean inyectadas en el contexto.
                                        </p>
                                        <p x-show="isConnectedToBot(selectedNode?.id)"
                                           class="text-xs text-green-400">
                                            ✓ Conectado — sus etiquetas estarán disponibles en el System Prompt.
                                        </p>
                                    </div>
                                </template>

                            </div>{{-- /modal body --}}
                        </div>{{-- /modal card --}}
                    </div>{{-- /modal backdrop --}}



                    {{-- Hidden inputs to preserve IA model values on form submit --}}
                    <input type="hidden" name="openai_model" value="{{ $iaModelos['openai'] }}">
                    <input type="hidden" name="deepseek_model" value="{{ $iaModelos['deepseek'] }}">
                    <input type="hidden" name="gemini_model" value="{{ $iaModelos['gemini'] }}">
                    <input type="hidden" name="openai_whisper_activo" value="{{ $iaToggles['openai_whisper'] ? '1' : '0' }}">
                    <input type="hidden" name="openai_imagen_activo" value="{{ $iaToggles['openai_imagen'] ? '1' : '0' }}">
                    <input type="hidden" name="gemini_audio_activo" value="{{ $iaToggles['gemini_audio'] ? '1' : '0' }}">
                </div>{{-- /panel-bot --}}

                {{-- ─── TAB: BASES DE DATOS ─── --}}
                <div x-show="activeTab === 'dbs'" x-cloak x-data="extDbManager()"
                     id="panel-dbs" role="tabpanel" aria-labelledby="tab-dbs">

                    <input type="hidden" name="ext_dbs" :value="jsonFinal"/>

                    <div class="bg-gray-800 rounded-xl shadow-sm border border-white/5 overflow-hidden">
                        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-100">Bases de Datos Externas</p>
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
                                    <div class="flex items-center gap-3 px-5 py-3 bg-gray-800 dark:bg-gray-750">
                                        <input type="text" x-model="conn.nombre"
                                               placeholder="✏ Escribe un nombre para esta BD…"
                                               class="flex-1 text-sm font-semibold text-gray-200 bg-transparent border-b border-transparent hover:border-white/10 focus:border-indigo-400 focus:outline-none focus:ring-0 placeholder-gray-400 transition-colors"/>
                                        <span class="px-2 py-0.5 rounded text-xs font-mono bg-indigo-100 dark:bg-indigo-900 text-indigo-700 text-indigo-300 flex-shrink-0"
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
                                            <label class="block text-xs font-medium text-gray-400 mb-1">Nombre de esta conexión <span class="text-red-400">*</span></label>
                                            <input type="text" x-model="conn.nombre" placeholder="Ej: BD Clientes, ERP, CRM…"
                                                   class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition">
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-1">Tipo de BD</label>
                                                <select x-model="conn.driver"
                                                        class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition">
                                                    <option value="mysql">MySQL</option>
                                                    <option value="pgsql">PostgreSQL</option>
                                                    <option value="mongodb">MongoDB</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-1">Host</label>
                                                <input type="text" x-model="conn.host" placeholder="127.0.0.1"
                                                       class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-1">Puerto</label>
                                                <input type="text" x-model="conn.port"
                                                       :placeholder="conn.driver === 'pgsql' ? '5432' : conn.driver === 'mongodb' ? '27017' : '3306'"
                                                       class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-1">Base de datos</label>
                                                <input type="text" x-model="conn.database" placeholder="nombre_bd"
                                                       class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-1">Usuario</label>
                                                <input type="text" x-model="conn.username" placeholder="root"
                                                       class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-400 mb-1">Contraseña</label>
                                                <input type="password" x-model="conn.password" autocomplete="new-password"
                                                       :placeholder="conn.has_password ? '(guardada)' : '(vacía)'"
                                                       class="w-full px-3 py-2 border border-white/10 border-white/10 bg-gray-700 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 transition"/>
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
                                                <p class="text-sm font-medium" :class="conn._error ? 'text-red-600' : 'text-green-400'" x-text="conn._mensaje"></p>
                                            </template>
                                        </div>

                                        {{-- Tablas disponibles (post-test) --}}
                                        <template x-if="conn._tablas_disponibles.length > 0">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-300 mb-2">
                                                    Selecciona las tablas que el bot usará como contexto:
                                                </p>
                                                <div class="rounded-lg border border-white/10 border-white/10 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                                                    <template x-for="tabla in conn._tablas_disponibles" :key="tabla">
                                                        <div>
                                                            {{-- Fila principal de la tabla --}}
                                                            <div class="flex items-center gap-3 px-3 py-2.5 transition-colors"
                                                                 :class="conn.tablas.includes(tabla)
                                                                     ? 'bg-indigo-900/30 dark:bg-indigo-900/25'
                                                                     : 'bg-gray-800 hover:bg-gray-800 dark:hover:bg-gray-750'">
                                                                <input type="checkbox"
                                                                       :checked="conn.tablas.includes(tabla)"
                                                                       @change="toggleTabla(idx, tabla)"
                                                                       class="w-4 h-4 text-indigo-600 border-white/10 rounded focus:ring-indigo-500 flex-shrink-0 cursor-pointer"/>
                                                                <span class="font-mono text-sm text-gray-200 flex-1 select-none cursor-pointer"
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
                                                                 class="px-4 pb-3 pt-2 bg-gray-800 bg-gray-900/40 border-t border-white/5">
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
                                                                               :class="colSeleccionada(conn, tabla, col) ? 'border-indigo-400 bg-indigo-900/30 text-indigo-700' : 'border-white/10 bg-gray-800 text-gray-500'">
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
                                                <div class="px-3 py-2 bg-indigo-900/30 dark:bg-indigo-900/30 flex items-center justify-between">
                                                    <p class="text-xs font-semibold text-indigo-700 text-indigo-300">
                                                        Tablas guardadas
                                                    </p>
                                                    <p class="text-xs text-indigo-500 dark:text-indigo-400">
                                                        Prueba la conexión para ver todas las tablas disponibles
                                                    </p>
                                                </div>
                                                <template x-for="t in conn.tablas" :key="t">
                                                    <div>
                                                        <div class="flex items-center gap-3 px-3 py-2.5 bg-gray-800">
                                                            <span class="w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                                                            <span class="font-mono text-sm text-gray-200 flex-1" x-text="t"></span>
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
                                                             class="px-4 pb-3 pt-2 bg-gray-800 bg-gray-900/40 border-t border-white/5">
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
                                                                           :class="colSeleccionada(conn, t, col) ? 'border-indigo-400 bg-indigo-900/30 text-indigo-700' : 'border-white/10 bg-gray-800 text-gray-500'">
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

                        <div class="px-5 py-4 border-t border-white/5">
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

                {{-- ─── TAB: CONECTAR APIs ─── --}}
                <div x-show="activeTab === 'apis'" x-cloak class="space-y-4"
                     id="panel-apis" role="tabpanel" aria-labelledby="tab-apis">

                    <div class="flex items-start gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3 text-sm text-emerald-300">
                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span>Administra aquí todas las <strong>claves de acceso</strong> y conexiones externas. La configuración de modelos, etapas y prompts está en sus secciones dedicadas.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- WhatsApp / Evolution API --}}
                        <div class="bg-gray-800 rounded-xl shadow-sm border border-white/5 overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-green-900/40 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 22c-5.523 0-10-4.477-10-10S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-100">WhatsApp — Evolution API</p>
                                    <p class="text-xs text-gray-400">Mensajería y webhooks</p>
                                </div>
                                <x-config-badge :configured="$estado['evolution_url'] && $estado['evolution_key']"/>
                            </div>
                            <div class="px-5 py-4 space-y-3">
                                <x-config-field name="evolution_url" label="URL del servidor" tipo="text" placeholder="https://tu-evolution-api.com" :configured="$estado['evolution_url']"/>
                                <x-config-field name="evolution_key" label="API Key global" tipo="password" placeholder="Tu API Key de Evolution" :configured="$estado['evolution_key']"/>
                            </div>
                        </div>

                        {{-- OpenAI --}}
                        <div class="bg-gray-800 rounded-xl shadow-sm border border-white/5 overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-900/40 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-teal-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0L4.01 14.2A4.501 4.501 0 0 1 2.34 7.896zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.808 2.768a4.504 4.504 0 0 1-.689 8.122V12.57a.79.79 0 0 0-.412-.719zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.806-2.767a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-100">OpenAI</p>
                                    <p class="text-xs text-gray-400">GPT · DALL-E · Whisper</p>
                                </div>
                                <x-config-badge :configured="$estado['openai_key']"/>
                            </div>
                            <div class="px-5 py-4 space-y-2">
                                <x-config-field name="openai_key" label="API Key" tipo="password" placeholder="sk-..." :configured="$estado['openai_key']"/>
                                <p class="text-xs text-gray-500">Modelos y capacidades → configura el nodo <strong class="text-teal-400">IA</strong> en el lienzo Bot.</p>
                            </div>
                        </div>

                        {{-- DeepSeek --}}
                        <div class="bg-gray-800 rounded-xl shadow-sm border border-white/5 overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-100">DeepSeek</p>
                                    <p class="text-xs text-gray-400">deepseek-chat · deepseek-reasoner</p>
                                </div>
                                <x-config-badge :configured="$estado['deepseek_key']"/>
                            </div>
                            <div class="px-5 py-4 space-y-2">
                                <x-config-field name="deepseek_key" label="API Key" tipo="password" placeholder="sk-..." :configured="$estado['deepseek_key']"/>
                                <p class="text-xs text-gray-500">Modelos y capacidades → configura el nodo <strong class="text-blue-400">IA</strong> en el lienzo Bot.</p>
                            </div>
                        </div>

                        {{-- Gemini --}}
                        <div class="bg-gray-800 rounded-xl shadow-sm border border-white/5 overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-orange-900/40 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-orange-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2a2 2 0 00-1.82 1.17L3.1 17.47A2 2 0 005 20h14a2 2 0 001.9-2.53L13.82 3.17A2 2 0 0012 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-100">Google Gemini</p>
                                    <p class="text-xs text-gray-400">gemini-1.5-flash · gemini-1.5-pro</p>
                                </div>
                                <x-config-badge :configured="$estado['gemini_key']"/>
                            </div>
                            <div class="px-5 py-4 space-y-2">
                                <x-config-field name="gemini_key" label="API Key" tipo="password" placeholder="AIza..." :configured="$estado['gemini_key']"/>
                                <p class="text-xs text-gray-500">Modelos y capacidades → configura el nodo <strong class="text-orange-400">IA</strong> en el lienzo Bot.</p>
                            </div>
                        </div>

                    </div>

                    {{-- Google Calendar & Drive (ancho completo por el flujo OAuth) --}}
                    <div class="bg-gray-800 rounded-xl shadow-sm border border-white/5 overflow-hidden"
                         x-data="{ open: {{ ($estado['google_client_id'] || $estado['google_client_secret'] || $googleConectado) ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-700/30 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-red-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-100">Google (Calendar & Drive)</p>
                                    @if($googleConectado)
                                        <p class="text-xs text-green-400 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block"></span>
                                            Conectado: {{ $googleEmail }}
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-400">OAuth2 — Sin conectar</p>
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
                        <div x-show="open" x-collapse class="border-t border-white/5">
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
                                        <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-blue-500 hover:underline">Google Cloud Console</a>
                                        como «Aplicación web». Agrega
                                        <code class="bg-gray-700 px-1 rounded text-xs font-mono">{{ route('configuracion.google.callback') }}</code>
                                        como URI de redireccionamiento autorizado.
                                    </p>
                                </div>
                                {{-- Paso 2: Autorizar cuenta --}}
                                <div class="border-t border-white/5 pt-4">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">
                                        Paso 2 — Autorizar cuenta de Google
                                    </p>
                                    @if($googleConectado)
                                        <div class="flex items-center justify-between bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-green-400">Cuenta conectada</p>
                                                    <p class="text-xs text-green-600">{{ $googleEmail }}</p>
                                                </div>
                                            </div>
                                            <button type="button"
                                                    onclick="if(confirm('¿Desconectar la cuenta de Google? El bot no podrá acceder a Calendar ni Drive.')){const f=document.createElement('form');f.method='POST';f.action='{{ route('configuracion.google.revoke') }}';const c=document.createElement('input');c.type='hidden';c.name='_token';c.value=document.querySelector('meta[name=csrf-token]').content;f.appendChild(c);const m=document.createElement('input');m.type='hidden';m.name='_method';m.value='DELETE';f.appendChild(m);document.body.appendChild(f);f.submit();}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-500/10 hover:bg-red-100 border border-red-500/30 rounded-lg transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                                Desconectar
                                            </button>
                                        </div>
                                    @else
                                        <div class="bg-gray-700/50 rounded-xl border border-white/10 px-4 py-4 flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-200 mb-0.5">Sin cuenta conectada</p>
                                                <p class="text-xs text-gray-400">Guarda primero el Client ID y Client Secret, luego haz clic en «Conectar con Google».</p>
                                            </div>
                                            @if($estado['google_client_id'] && $estado['google_client_secret'])
                                                <a href="{{ route('configuracion.google.redirect') }}"
                                                   class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-semibold rounded-lg border border-white/10 shadow-sm transition-colors">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                                    </svg>
                                                    Conectar con Google
                                                </a>
                                            @else
                                                <span class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400 text-sm font-semibold rounded-lg border border-white/10 cursor-not-allowed select-none"
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

                </div>

            </div>{{-- /content --}}
        </div>

    {{-- Prompt activo persistido en el form --}}
    <input type="hidden" name="bot_prompt_activo" id="bot_prompt_activo_input" value="{{ $promptActivoId ?? '' }}">

    </form>

<script>
function configPage() {
    return {
        activeTab: 'bot',
        preparar(e) {
            // Nada especial — el form envía todo normalmente
        },
        init() {
            // Listen for tab switch events from modals
            this.$el.addEventListener('set-tab', (e) => {
                this.activeTab = e.detail;
            });
        },
    };
}

/**
 * N8N-style node canvas for Bot configuration.
 * Manages palette → canvas drag/drop, connection drawing, node modals.
 */
function botCanvas() {
    // Node type registry
    const nodeTypes = {
        'bot': {
            icon: '🤖', label: 'Bot', sub: 'Cerebro central', desc: 'Configuración del cerebro del bot',
            singleton: true, fixed: true, tags: []
        },
        'memoria': {
            icon: '💬', label: 'Memoria BD', sub: 'Historial activo', desc: 'Historial de conversación (siempre activo)',
            singleton: true, fixed: true, tags: []
        },
        'system-prompt': {
            icon: '📝', label: 'System Prompt', sub: 'Instrucciones base', desc: 'Prompt del sistema y prompts guardados',
            singleton: true, fixed: false,
            tags: [{ tag: 'HORA_ACTUAL', preview: 'Fecha y hora actual según zona horaria configurada' }]
        },
        'etapas-ia': {
            icon: '🔄', label: 'Etapas IA', sub: 'Anti-ciclo', desc: 'Guía por etapas para la IA (anti-ciclo)',
            singleton: true, fixed: false, tags: []
        },
        'flujo-pasos': {
            icon: '📋', label: 'Flujo por Pasos', sub: 'Conversación guiada', desc: 'Flujo conversacional paso a paso',
            singleton: true, fixed: false, tags: []
        },
        'pipeline': {
            icon: '🎬', label: 'Pipeline Media', sub: 'Imagen/Audio/Video', desc: 'Procesamiento de medios entrantes',
            singleton: true, fixed: false, tags: []
        },
        'ia': {
            icon: '🧠', label: 'IA', sub: 'Proveedor · Modelo', desc: 'Configura el proveedor de IA, modelo y capacidades (voz/imagen)',
            singleton: true, fixed: false, tags: []
        },
        'whatsapp': {
            icon: '📱', label: 'WhatsApp', sub: 'Evolution API', desc: 'Mensajería, grupos y eventos de WhatsApp vía Evolution API',
            singleton: true, fixed: false, tags: []
        },
        'google-calendar': {
            icon: '📅', label: 'G. Calendar', sub: 'Agendar · Consultar', desc: 'Gestión de eventos y citas en Google Calendar',
            singleton: true, fixed: false, tags: []
        },
        'google-drive': {
            icon: '📂', label: 'G. Drive', sub: 'Archivos · Subir', desc: 'Gestión de archivos y carpetas en Google Drive',
            singleton: true, fixed: false, tags: []
        },
        'db-mysql': {
            icon: '🐬', label: 'MySQL', sub: 'Base de datos', desc: 'Conexión a base de datos MySQL externa',
            singleton: false, fixed: false, tags: []
        },
        'db-mongodb': {
            icon: '🍃', label: 'MongoDB', sub: 'NoSQL', desc: 'Conexión a base de datos MongoDB externa',
            singleton: false, fixed: false, tags: []
        },
        'db-postgresql': {
            icon: '🐘', label: 'PostgreSQL', sub: 'Base de datos', desc: 'Conexión a base de datos PostgreSQL externa',
            singleton: false, fixed: false, tags: []
        },
        // Catalog, DB, API types are added dynamically in init()
    };

    // PHP data injected from Blade
    const phpCatalogs = @json($canvasCatalogs);
    const phpCatalogsWithFiles = new Set(@json($catalogsWithFiles));
    const phpDbs      = @json($canvasDbs);
    const phpApis     = @json($canvasApis);
    const phpExtDbs   = @json($extDbs);
    const savedLayout = @json($botCanvasLayout);

    const modeLabels = { ia: 'Solo IA', pasos: 'Por pasos', hibrido: 'Híbrido' };

    return {
        nodes: [],
        edges: [],
        dragging: null,
        connecting: null,
        selectedNode: null,
        modalOpen: false,
        botMode: @js($botModoRespuesta ?? 'ia'),
        nodeTypes: nodeTypes,
        extDbs: phpExtDbs,

        init() {
            // Register dynamic node types from PHP
            phpCatalogs.forEach(t => {
                const key = 'catalogo_' + t.tag;
                nodeTypes[key] = {
                    icon: '📖', label: t.label || t.tag, sub: '[' + t.tag + ']',
                    desc: 'Catálogo: ' + (t.label || t.tag),
                    singleton: true, fixed: false,
                    hasFiles: phpCatalogsWithFiles.has(t.tag),
                    tags: [{ tag: t.tag, preview: t.preview || 'Datos del catálogo ' + (t.label || t.tag) }]
                };
            });
            phpDbs.forEach(t => {
                const key = 'db_' + t.tag;
                nodeTypes[key] = {
                    icon: '🗄', label: t.label || t.tag, sub: '[' + t.tag + ']',
                    desc: 'Base de datos: ' + (t.label || t.tag),
                    singleton: true, fixed: false,
                    tags: [{ tag: t.tag, preview: t.preview || 'Datos BD ' + (t.label || t.tag) }]
                };
            });
            phpApis.forEach(t => {
                const key = 'api_' + t.tag;
                nodeTypes[key] = {
                    icon: '⚡', label: t.label || t.tag, sub: '[' + t.tag + ']',
                    desc: 'API: ' + (t.label || t.tag),
                    singleton: true, fixed: false,
                    tags: [{ tag: t.tag, preview: t.preview || 'Datos API ' + (t.label || t.tag) }]
                };
            });

            // Restore saved layout OR set default layout
            if (savedLayout && savedLayout.nodes && savedLayout.nodes.length > 0) {
                this.nodes = savedLayout.nodes;
                this.edges = savedLayout.edges || [];
                // Ensure bot + memoria always exist
                if (!this.nodes.find(n => n.id === 'bot')) {
                    this.nodes.unshift({ id: 'bot', type: 'bot', x: 320, y: 220 });
                }
                if (!this.nodes.find(n => n.id === 'memoria')) {
                    this.nodes.push({ id: 'memoria', type: 'memoria', x: 320, y: 340 });
                }
                if (!this.edges.find(e => e.from === 'memoria' && e.to === 'bot')) {
                    this.edges.push({ id: 'e_memoria', from: 'memoria', to: 'bot' });
                }
                // Ensure catalog nodes that were saved before CRUD feature have default permisos
                this.nodes.forEach(n => {
                    if (n.type?.startsWith('catalogo_') && !n.permisos) {
                        n.permisos = { consultar: true, crear: false, editar: false, borrar: false, media: false, media_enviar: false, media_guardar: false };
                    } else if (n.type?.startsWith('catalogo_') && n.permisos && n.permisos.media_enviar === undefined) {
                        n.permisos.media_enviar  = false;
                        n.permisos.media_guardar = false;
                    }
                    if (n.type === 'whatsapp' && !n.config)        n.config = { mensajeria: { texto: true, imagen: true, video: false, audio: false, documento: false, ubicacion: false, contacto: false, sticker: false, encuesta: false, lista: false, botones: false }, grupos: { crear: false, listar: false, miembros: false, addParticipantes: false, removeParticipantes: false, promoverAdmin: false, destituirAdmin: false, actualizarNombre: false, actualizarDesc: false, salir: false }, mensajes: { eliminar: false, reaccionar: true, marcarLeido: true, obtener: false, archivar: false }, contactos: { buscar: false, verificar: false, fotoPerfil: false }, eventos: { message: true, connectionUpdate: true, sendMessage: false, contactsUpdate: false, presenceUpdate: false, chatsUpdate: false, groupUpdate: false, groupParticipants: false, qrcodeUpdated: true, labelsEdit: false } };
                    if (n.type === 'google-calendar' && !n.config) n.config = { calendarId: '', operaciones: { listar: true, detalle: false, crear: false, actualizar: false, eliminar: false, invitar: false } };
                    if (n.type === 'google-drive'    && !n.config) n.config = { carpetaId: '', operaciones: { listar: true, detalle: false, subir: false, descargar: false, crearCarpeta: false, compartir: false, eliminar: false, renombrar: false } };
                    if (['db-mysql','db-mongodb','db-postgresql'].includes(n.type) && !n.config) n.config = { conexionNombre: '', tabla: '', permisos: { consultar: true, crear: false, editar: false, borrar: false } };
                });
            } else {
                // Default: bot in center, system-prompt and memoria connected
                this.nodes = [
                    { id: 'bot',           type: 'bot',           x: 360, y: 200 },
                    { id: 'memoria',       type: 'memoria',       x: 180, y: 320 },
                    { id: 'system-prompt', type: 'system-prompt', x: 180, y: 100 },
                ];
                this.edges = [
                    { id: 'e_memoria', from: 'memoria', to: 'bot' },
                    { id: 'e_sysprompt', from: 'system-prompt', to: 'bot' },
                ];
            }

            // Watch botMode and auto-sync canvas nodes
            this.$watch('botMode', mode => this.applyModeNodes(mode));
        },

        // ─── Palette ───────────────────────────────────────────────────────

        // Auto-add/remove mode-specific nodes when Bot mode changes.
        // 'ia'     → needs: ia, system-prompt
        // 'pasos'  → needs: flujo-pasos
        // 'hibrido'→ needs: ia, system-prompt, flujo-pasos
        applyModeNodes(mode) {
            const want = {
                ia:      ['ia', 'system-prompt'],
                pasos:   ['flujo-pasos'],
                hibrido: ['ia', 'system-prompt', 'flujo-pasos'],
            };
            // All manageable types across all modes
            const allManaged = ['ia', 'system-prompt', 'flujo-pasos'];
            const desired    = want[mode] ?? [];

            // Remove nodes that belong to another mode (not desired) AND have NO user-set data
            // We only auto-remove types that we auto-added (all three managed types)
            const toRemove = allManaged.filter(t => !desired.includes(t));
            toRemove.forEach(type => {
                const idx = this.nodes.findIndex(n => n.type === type);
                if (idx !== -1) {
                    const nodeId = this.nodes[idx].id;
                    this.nodes.splice(idx, 1);
                    this.edges = this.edges.filter(e => e.from !== nodeId && e.to !== nodeId);
                }
            });

            // Add nodes that are desired but not yet on canvas
            const botNode = this.nodes.find(n => n.type === 'bot');
            const botX = botNode?.x ?? 360;
            const botY = botNode?.y ?? 200;

            // Layout offsets relative to bot node
            const offsets = {
                'ia':            { dx: -220, dy: -140 },
                'system-prompt': { dx: -220, dy:    0  },
                'flujo-pasos':   { dx: -220, dy:  140  },
            };

            desired.forEach(type => {
                if (!this.isOnCanvas(type)) {
                    const { dx, dy } = offsets[type] ?? { dx: -200, dy: 0 };
                    const id = type + '_auto';
                    this.nodes.push({ id, type, x: Math.max(0, botX + dx), y: Math.max(10, botY + dy) });
                    if (!this.edges.find(e => e.from === id && e.to === 'bot')) {
                        this.edges.push({ id: 'e_' + type, from: id, to: 'bot' });
                    }
                }
            });
        },

        get palette() {
            return Object.keys(this.nodeTypes)
                .filter(type => {
                    const t = this.nodeTypes[type];
                    if (t.fixed) return false;
                    if (t.singleton && this.isOnCanvas(type)) return false;
                    return true;
                })
                .map(type => ({ type, ...this.nodeTypes[type] }));
        },

        isOnCanvas(type) {
            return this.nodes.some(n => n.type === type);
        },

        // ─── Drag from palette ─────────────────────────────────────────────
        _paletteDragType: null,
        startPaletteDrag(type, event) {
            const nt = this.nodeTypes[type];
            if (nt?.singleton && this.isOnCanvas(type)) {
                event.preventDefault();
                return;
            }
            this._paletteDragType = type;
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/plain', type);
        },

        onCanvasDrop(event) {
            const type = event.dataTransfer.getData('text/plain') || this._paletteDragType;
            this._paletteDragType = null;
            if (!type || !this.nodeTypes[type]) return;
            if (this.nodeTypes[type].singleton && this.isOnCanvas(type)) return;
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX - rect.left - 66;
            const y = event.clientY - rect.top - 30;
            const id = type + '_' + Date.now();
            const newNode = { id, type, x: Math.max(0, x), y: Math.max(0, y) };
            if (type.startsWith('catalogo_')) {
                newNode.permisos = { consultar: true, crear: false, editar: false, borrar: false, media: false, media_enviar: false, media_guardar: false };
            }
            if (type === 'whatsapp')       newNode.config = { mensajeria: { texto: true, imagen: true, video: false, audio: false, documento: false, ubicacion: false, contacto: false, sticker: false, encuesta: false, lista: false, botones: false }, grupos: { crear: false, listar: false, miembros: false, addParticipantes: false, removeParticipantes: false, promoverAdmin: false, destituirAdmin: false, actualizarNombre: false, actualizarDesc: false, salir: false }, mensajes: { eliminar: false, reaccionar: true, marcarLeido: true, obtener: false, archivar: false }, contactos: { buscar: false, verificar: false, fotoPerfil: false }, eventos: { message: true, connectionUpdate: true, sendMessage: false, contactsUpdate: false, presenceUpdate: false, chatsUpdate: false, groupUpdate: false, groupParticipants: false, qrcodeUpdated: true, labelsEdit: false } };
            if (type === 'google-calendar') newNode.config = { calendarId: '', operaciones: { listar: true, detalle: false, crear: false, actualizar: false, eliminar: false, invitar: false } };
            if (type === 'google-drive')    newNode.config = { carpetaId: '', operaciones: { listar: true, detalle: false, subir: false, descargar: false, crearCarpeta: false, compartir: false, eliminar: false, renombrar: false } };
            if (['db-mysql','db-mongodb','db-postgresql'].includes(type)) newNode.config = { conexionNombre: '', tabla: '', permisos: { consultar: true, crear: false, editar: false, borrar: false } };
            this.nodes.push(newNode);
        },

        // ─── Node drag (reposition) ────────────────────────────────────────
        startDrag(nodeId, event) {
            if (this.connecting) return;
            const node = this.nodes.find(n => n.id === nodeId);
            if (!node) return;
            this.dragging = {
                id: nodeId,
                offsetX: event.clientX - node.x,
                offsetY: event.clientY - node.y,
            };
        },

        onMouseMove(event) {
            if (this.dragging) {
                const node = this.nodes.find(n => n.id === this.dragging.id);
                if (node) {
                    node.x = Math.max(0, event.clientX - this.dragging.offsetX);
                    node.y = Math.max(0, event.clientY - this.dragging.offsetY);
                }
            }
            if (this.connecting) {
                this.connecting.curX = event.clientX - this._canvasRect().left;
                this.connecting.curY = event.clientY - this._canvasRect().top;
            }
        },

        onMouseUp(event) {
            this.dragging = null;
            if (this.connecting && !event.target.closest('[\\@mouseup\\.stop]')) {
                this.connecting = null;
            }
        },

        _canvasEl() {
            return document.querySelector('#panel-bot .relative.flex-1');
        },

        _canvasRect() {
            const el = this._canvasEl();
            return el ? el.getBoundingClientRect() : { left: 0, top: 0 };
        },

        _nodeCenter(nodeId) {
            const node = this.nodes.find(n => n.id === nodeId);
            if (!node) return { x: 0, y: 0 };
            return { x: node.x + 66, y: node.y + 28 };
        },

        // ─── Connection drawing ────────────────────────────────────────────
        startConnect(fromId, event) {
            this.dragging = null; // cancel any drag
            const node = this.nodes.find(n => n.id === fromId);
            if (!node) return;
            const rect = this._canvasRect();
            const x1 = node.x + 132 + 3; // right port
            const y1 = node.y + 28;
            this.connecting = { fromId, x1, y1, curX: x1, curY: y1 };
        },

        endConnect(toId) {
            if (!this.connecting || this.connecting.fromId === toId) {
                this.connecting = null;
                return;
            }
            const fromId = this.connecting.fromId;
            this.connecting = null;
            // Only connect to bot
            if (toId !== 'bot') return;
            // Avoid duplicates
            if (this.edges.find(e => e.from === fromId && e.to === toId)) return;
            this.edges.push({ id: 'e_' + fromId + '_' + toId, from: fromId, to: toId });
        },

        // ─── Edge management ──────────────────────────────────────────────
        removeEdge(edgeId) {
            // Prevent removing the memoria connection
            const edge = this.edges.find(e => e.id === edgeId);
            if (edge && edge.from === 'memoria') return;
            this.edges = this.edges.filter(e => e.id !== edgeId);
        },

        isConnectedToBot(nodeId) {
            if (nodeId === 'bot') return true;
            return this.edges.some(e => e.from === nodeId && e.to === 'bot');
        },

        // ─── SVG edges rendered as HTML string (x-for inside SVG breaks; use x-html instead) ─────
        get edgesHtml() {
            return this.edges.map(edge => {
                const d   = this.edgePath(edge);
                const mid = this.edgeMid(edge);
                return `<path d="${d}" fill="none" stroke="#6366f1" stroke-width="2" stroke-dasharray="none" opacity="0.7"/>` +
                       `<circle cx="${mid.x}" cy="${mid.y}" r="7" fill="#ef4444" opacity="0.85" ` +
                       `class="pointer-events-auto cursor-pointer" data-edge-id="${edge.id}" title="Quitar conexión"/>` +
                       `<text x="${mid.x}" y="${mid.y + 4}" text-anchor="middle" font-size="9" ` +
                       `fill="white" class="pointer-events-none select-none">✕</text>`;
            }).join('\n');
        },

        onSvgEdgeClick(event) {
            const edgeId = event.target.getAttribute('data-edge-id');
            if (edgeId) this.removeEdge(edgeId);
        },

        // ─── SVG path helpers ──────────────────────────────────────────────
        edgePath(edge) {
            const from = this.nodes.find(n => n.id === edge.from);
            const to   = this.nodes.find(n => n.id === edge.to);
            if (!from || !to) return '';
            const x1 = from.x + 132 + 3;
            const y1 = from.y + 28;
            const x2 = to.x - 3;  // bot left port
            const y2 = to.y + 28;
            const cx1 = x1 + Math.abs(x2 - x1) * 0.5;
            const cx2 = x2 - Math.abs(x2 - x1) * 0.5;
            return `M ${x1},${y1} C ${cx1},${y1} ${cx2},${y2} ${x2},${y2}`;
        },

        tempEdgePath() {
            if (!this.connecting) return '';
            const { x1, y1, curX, curY } = this.connecting;
            const cx1 = x1 + Math.abs(curX - x1) * 0.5;
            const cx2 = curX - Math.abs(curX - x1) * 0.5;
            return `M ${x1},${y1} C ${cx1},${y1} ${cx2},${curY} ${curX},${curY}`;
        },

        edgeMid(edge) {
            const from = this.nodes.find(n => n.id === edge.from);
            const to   = this.nodes.find(n => n.id === edge.to);
            if (!from || !to) return { x: 0, y: 0 };
            return {
                x: (from.x + 132 + to.x) / 2,
                y: (from.y + to.y + 56) / 2,
            };
        },

        // ─── Node style ───────────────────────────────────────────────────
        nodeCardStyle(node) {
            return `left: ${node.x}px; top: ${node.y}px;`;
        },

        // ─── Modal ────────────────────────────────────────────────────────
        openModal(nodeId) {
            if (this.dragging) return; // don't open if we just dragged
            this.selectedNode = this.nodes.find(n => n.id === nodeId) || null;
            this.modalOpen = !!this.selectedNode;
        },

        deleteNode(nodeId) {
            if (nodeId === 'bot' || nodeId === 'memoria') return;
            this.nodes = this.nodes.filter(n => n.id !== nodeId);
            this.edges = this.edges.filter(e => e.from !== nodeId && e.to !== nodeId);
            if (this.selectedNode?.id === nodeId) {
                this.selectedNode = null;
                this.modalOpen = false;
            }
        },

        // ─── Connected tags (for System Prompt modal) ─────────────────────
        connectedTags() {
            const tags = [];
            // Always include hora_actual if system-prompt or bot nodes exist
            this.edges.forEach(edge => {
                if (edge.to !== 'bot') return;
                const node = this.nodes.find(n => n.id === edge.from);
                if (!node) return;
                const nt = this.nodeTypes[node.type];
                if (nt?.tags) tags.push(...nt.tags);
            });
            return tags;
        },

        // ─── DB connections filtered by driver ────────────────────────────
        dbConexiones(tipo) {
            const driverMap = { 'db-mysql': 'mysql', 'db-mongodb': 'mongodb', 'db-postgresql': 'pgsql' };
            const driver = driverMap[tipo] ?? '';
            return driver ? this.extDbs.filter(d => d.driver === driver) : this.extDbs;
        },

        // ─── Canvas serialization ─────────────────────────────────────────
        getCanvasJson() {
            return JSON.stringify({
                nodes: this.nodes.map(n => {
                    const data = { id: n.id, type: n.type, x: n.x, y: n.y };
                    if (n.type?.startsWith('catalogo_') && n.permisos) {
                        data.permisos = n.permisos;
                    }
                    if (['whatsapp','google-calendar','google-drive','db-mysql','db-mongodb','db-postgresql'].includes(n.type) && n.config) {
                        data.config = n.config;
                    }
                    return data;
                }),
                edges: this.edges.map(e => ({ id: e.id, from: e.from, to: e.to })),
            });
        },
    };
}

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
 * Alpine component for the IA canvas node modal.
 * Manages provider selection, model selection, and capability toggles.
 */
function iaNodeManager() {
    return {
        proveedor: @js($botProveedor ?? 'openai'),

        openai: {
            modelo:    @js($iaModelos['openai']   ?? 'gpt-4o'),
            customOn:  false,
            customVal: '',
            whisper:   {{ ($iaToggles['openai_whisper'] ?? false) ? 'true' : 'false' }},
            imagen:    {{ ($iaToggles['openai_imagen']  ?? false) ? 'true' : 'false' }},
            modelos: [
                { id: 'gpt-4o',        label: 'GPT-4o',        tag: 'Recomendado' },
                { id: 'gpt-4o-mini',   label: 'GPT-4o Mini',   tag: 'Rápido'      },
                { id: 'gpt-4-turbo',   label: 'GPT-4 Turbo',   tag: null          },
                { id: 'gpt-3.5-turbo', label: 'GPT-3.5 Turbo', tag: 'Económico'   },
                { id: 'o1',            label: 'o1',            tag: 'Razonamiento' },
                { id: 'o3-mini',       label: 'o3-mini',       tag: null           },
            ],
            elegir(id) {
                if (id === '__custom__') { this.customOn = true; this.modelo = ''; }
                else { this.customOn = false; this.modelo = id; }
            },
            get modeloFinal() { return this.customOn ? this.customVal : this.modelo; },
        },

        deepseek: {
            modelo:    @js($iaModelos['deepseek']  ?? 'deepseek-chat'),
            customOn:  false,
            customVal: '',
            modelos: [
                { id: 'deepseek-chat',     label: 'DeepSeek Chat',     tag: 'Recomendado'   },
                { id: 'deepseek-reasoner', label: 'DeepSeek Reasoner', tag: 'R1 · Avanzado' },
                { id: 'deepseek-coder',    label: 'DeepSeek Coder',    tag: 'Programación'  },
            ],
            elegir(id) {
                if (id === '__custom__') { this.customOn = true; this.modelo = ''; }
                else { this.customOn = false; this.modelo = id; }
            },
            get modeloFinal() { return this.customOn ? this.customVal : this.modelo; },
        },

        gemini: {
            modelo:    @js($iaModelos['gemini']    ?? 'gemini-1.5-flash'),
            customOn:  false,
            customVal: '',
            audio:     {{ ($iaToggles['gemini_audio']  ?? false) ? 'true' : 'false' }},
            vision:    {{ ($iaToggles['gemini_vision'] ?? false) ? 'true' : 'false' }},
            modelos: [
                { id: 'gemini-2.0-flash-exp', label: 'Gemini 2.0 Flash',  tag: 'Más rápido',   audio: true,  vision: true  },
                { id: 'gemini-2.0-flash',     label: 'Gemini 2.0 Flash',  tag: 'Estable',      audio: true,  vision: true  },
                { id: 'gemini-1.5-flash',     label: 'Gemini 1.5 Flash',  tag: 'Rápido',       audio: true,  vision: true  },
                { id: 'gemini-1.5-flash-8b',  label: 'Flash 1.5 8B',      tag: 'Ultra rápido', audio: true,  vision: true  },
                { id: 'gemini-1.5-pro',       label: 'Gemini 1.5 Pro',    tag: 'Más potente',  audio: true,  vision: true  },
                { id: 'gemini-1.0-pro',       label: 'Gemini 1.0 Pro',    tag: 'Básico',       audio: false, vision: false },
            ],
            elegir(id) {
                if (id === '__custom__') { this.customOn = true; this.modelo = ''; }
                else {
                    this.customOn = false;
                    this.modelo   = id;
                    const m = this.modelos.find(m => m.id === id);
                    if (m && !m.audio)  this.audio  = false;
                    if (m && !m.vision) this.vision = false;
                }
            },
            get modeloFinal()   { return this.customOn ? this.customVal : this.modelo; },
            get soportaAudio()  { if (this.customOn) return true; const m = this.modelos.find(m => m.id === this.modelo); return m ? m.audio  : true; },
            get soportaVision() { if (this.customOn) return true; const m = this.modelos.find(m => m.id === this.modelo); return m ? m.vision : true; },
        },

        init() {
            // Restore custom values from saved models
            const o = this.openai;
            if (!o.modelos.find(m => m.id === o.modelo) && o.modelo) { o.customOn = true; o.customVal = o.modelo; }
            const d = this.deepseek;
            if (!d.modelos.find(m => m.id === d.modelo) && d.modelo) { d.customOn = true; d.customVal = d.modelo; }
            const g = this.gemini;
            if (!g.modelos.find(m => m.id === g.modelo) && g.modelo) { g.customOn = true; g.customVal = g.modelo; }
        },

        setProveedor(p) { this.proveedor = p; },

        modeloActual(prov) {
            if (prov === 'openai')   return this.openai.modeloFinal   || 'Sin modelo';
            if (prov === 'deepseek') return this.deepseek.modeloFinal || 'Sin modelo';
            if (prov === 'gemini')   return this.gemini.modeloFinal   || 'Sin modelo';
            return '';
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
        editandoId: null,

        init() {
            // Leer el ID del prompt activo desde la configuración guardada
            const storedId = parseInt(document.getElementById('bot_prompt_activo_input')?.value ?? '0');
            this.promptActivo = storedId > 0 ? storedId : null;
        },

        togglePrompt(p) {
            if (this.promptActivo === p.id) {
                // Desactivar
                this.promptActivo = null;
                document.getElementById('bot_prompt_activo_input').value = '';
            } else {
                // Activar y cargar contenido
                this.cargarPrompt(p);
            }
        },

        cargarPrompt(p) {
            const ta = document.getElementById('system_prompt');
            if (!ta) return;
            ta.value = p.contenido;
            this.chars = p.contenido.length;
            this.promptActivo = p.id;
            document.getElementById('bot_prompt_activo_input').value = p.id;
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

        editarPrompt(p) {
            this.editandoId = p.id;
            this.nuevoNombre = p.nombre;
            // Cargar el contenido en el textarea para que el usuario lo edite
            const ta = document.getElementById('system_prompt');
            if (ta) {
                ta.value = p.contenido;
                this.chars = p.contenido.length;
                ta.dispatchEvent(new Event('input'));
            }
            this.promptActivo = p.id;
        },

        cancelarEdicion() {
            this.editandoId = null;
            this.nuevoNombre = '';
        },

        async actualizarPrompt() {
            const nombre = this.nuevoNombre.trim();
            if (!nombre || !this.editandoId) return;
            const ta = document.getElementById('system_prompt');
            const contenido = ta?.value ?? '';
            if (!contenido.trim()) {
                alert('El textarea del prompt está vacío.');
                return;
            }
            this.guardando = true;
            try {
                const res = await axios.put(`{{ url('/configuracion/prompts') }}/${this.editandoId}`, { nombre, contenido }, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (res.data.success) {
                    const idx = this.prompts.findIndex(p => p.id === this.editandoId);
                    if (idx !== -1) this.prompts[idx] = res.data.prompt;
                    this.prompts.sort((a, b) => a.nombre.localeCompare(b.nombre));
                    this.promptActivo = res.data.prompt.id;
                    this.editandoId = null;
                    this.nuevoNombre = '';
                }
            } catch (e) {
                alert('No se pudo actualizar el prompt.');
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

