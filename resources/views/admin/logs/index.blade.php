<x-admin-layout title="Logs del Sistema">

<div x-data="logsManager()" x-init="cargar(canalActivo)" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-100">Logs del sistema</h1>
            <p class="text-xs text-gray-400 mt-0.5">Monitoreo en tiempo real por canal</p>
        </div>
        <div class="flex items-center gap-2">
            <span x-show="tamano" class="text-xs text-gray-500" x-text="tamano + ' KB'"></span>
            <button @click="cargar(canalActivo)"
                    :disabled="cargando"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg transition-colors disabled:opacity-50">
                <svg class="w-3.5 h-3.5" :class="cargando ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualizar
            </button>
            <button @click="limpiar(canalActivo)"
                    :disabled="limpiando"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-red-900/40 hover:bg-red-800/60 text-red-300 border border-red-700/40 rounded-lg transition-colors disabled:opacity-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span x-text="limpiando ? 'Limpiando…' : 'Limpiar log'"></span>
            </button>
        </div>
    </div>

    {{-- Tabs de canal --}}
    <div class="flex gap-1 mb-4 bg-gray-800/50 rounded-xl p-1 w-fit">
        <template x-for="c in canales" :key="c.id">
            <button type="button"
                    @click="cambiarCanal(c.id)"
                    :class="canalActivo === c.id
                        ? 'bg-gray-700 text-gray-100 shadow-sm'
                        : 'text-gray-400 hover:text-gray-200'"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all">
                <span x-text="c.icono"></span>
                <span x-text="c.label"></span>
                <span x-show="canalActivo === c.id && entries.length > 0"
                      class="text-xs bg-gray-600 text-gray-300 px-1.5 py-0.5 rounded-full"
                      x-text="entries.length"></span>
            </button>
        </template>
    </div>

    {{-- Descripción del canal --}}
    <p class="text-xs text-gray-500 mb-3" x-text="canales.find(c => c.id === canalActivo)?.desc ?? ''"></p>

    {{-- Área de logs --}}
    <div class="bg-gray-900 rounded-xl border border-white/5 overflow-hidden">

        {{-- Sin entradas --}}
        <div x-show="!cargando && entries.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-500">
            <svg class="w-10 h-10 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm">Sin entradas en este log</p>
        </div>

        {{-- Spinner --}}
        <div x-show="cargando" class="flex items-center justify-center py-16 text-gray-500">
            <svg class="w-6 h-6 animate-spin mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Cargando…
        </div>

        {{-- Lista de entradas --}}
        <div x-show="!cargando && entries.length > 0"
             class="overflow-y-auto max-h-[62vh] divide-y divide-white/5 font-mono text-xs">
            <template x-for="(entry, i) in entries" :key="i">
                <div class="px-4 py-2.5 flex gap-3 hover:bg-white/5 transition-colors"
                     :class="{
                        'border-l-2 border-red-500':    ['error','critical','emergency','alert'].includes(entry.level),
                        'border-l-2 border-yellow-500': entry.level === 'warning',
                        'border-l-2 border-blue-500':   entry.level === 'info',
                        'border-l-2 border-gray-600':   entry.level === 'debug',
                     }">
                    <div class="flex-shrink-0 w-36 text-gray-500" x-text="entry.timestamp"></div>
                    <div class="flex-shrink-0 w-14">
                        <span class="px-1.5 py-0.5 rounded text-xs font-bold uppercase"
                              :class="{
                                'bg-red-900/50 text-red-300':    ['error','critical','emergency','alert'].includes(entry.level),
                                'bg-yellow-900/50 text-yellow-300': entry.level === 'warning',
                                'bg-blue-900/50 text-blue-300':  entry.level === 'info',
                                'bg-gray-700 text-gray-400':     entry.level === 'debug',
                              }"
                              x-text="entry.level"></span>
                    </div>
                    <div class="flex-1 text-gray-300 break-all whitespace-pre-wrap leading-relaxed" x-text="entry.message"></div>
                </div>
            </template>
        </div>
    </div>

    {{-- Footer --}}
    <p class="mt-2 text-right text-xs text-gray-600" x-show="ultimaActualizacion">
        Última actualización: <span x-text="ultimaActualizacion"></span>
    </p>
</div>

<script>
function logsManager() {
    return {
        canalActivo: 'bot',
        cargando: false,
        limpiando: false,
        entries: [],
        tamano: 0,
        ultimaActualizacion: '',

        canales: [
            { id: 'bot',          icono: '🤖', label: 'Bot',           desc: 'Actividad del bot: webhooks, mensajes, IA, media. Archivo: bot.log' },
            { id: 'configuracion',icono: '⚙️',  label: 'Configuración', desc: 'Cambios en la configuración del sistema. Archivo: configuracion.log' },
            { id: 'sistema',      icono: '🖥️',  label: 'Sistema',       desc: 'Errores generales de PHP/Laravel. Archivo: laravel.log' },
        ],

        async cambiarCanal(canal) {
            this.canalActivo = canal;
            this.entries = [];
            await this.cargar(canal);
        },

        async cargar(canal) {
            this.cargando = true;
            try {
                const res = await fetch(`{{ url('/admin/logs') }}/${canal}`, {
                    headers: { 'Accept': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.entries           = data.entries ?? [];
                    this.tamano            = data.size ?? 0;
                    this.ultimaActualizacion = data.timestamp ?? '';
                }
            } finally {
                this.cargando = false;
            }
        },

        async limpiar(canal) {
            if (!confirm(`¿Limpiar el log de ${canal}? Esta acción no se puede deshacer.`)) return;
            this.limpiando = true;
            try {
                await fetch(`{{ url('/admin/logs') }}/${canal}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                               'Accept': 'application/json' }
                });
                this.entries = [];
                this.tamano  = 0;
            } finally {
                this.limpiando = false;
            }
        },
    };
}
</script>

</x-admin-layout>
