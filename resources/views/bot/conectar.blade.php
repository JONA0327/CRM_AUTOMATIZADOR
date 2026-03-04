<x-admin-layout title="Conectar Número">

<div class="max-w-lg mx-auto" x-data="qrScanner()">

    {{-- ── Paso 1: Formulario ──────────────────────────────────────────────── --}}
    <div x-show="paso === 'formulario'" x-transition>
        <div class="bg-gray-900 rounded-xl border border-white/5 p-8">

            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-indigo-500/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-100">Conectar Número de WhatsApp</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Escanea el código QR con tu teléfono</p>
                </div>
            </div>

            <div x-show="errorMsg" x-transition
                 role="alert" aria-live="assertive"
                 class="mb-5 flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg px-4 py-3 text-sm">
                <svg class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span x-text="errorMsg"></span>
            </div>

            <div class="space-y-5">

                {{-- Nombre de la instancia --}}
                <div>
                    <label for="instancia-nombre" class="block text-sm font-medium text-gray-300 mb-1.5">Nombre de la instancia</label>
                    <input id="instancia-nombre" x-model="nombre" type="text"
                           placeholder="ej. ventas-principal"
                           maxlength="50"
                           autocomplete="off"
                           aria-describedby="instancia-nombre-hint"
                           class="w-full px-4 py-3 bg-gray-800 border border-white/10 rounded-lg text-sm text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <p id="instancia-nombre-hint" class="mt-1.5 text-xs text-gray-600">Solo letras, números, guiones y guiones bajos. Sin espacios.</p>
                </div>

                {{-- Número de teléfono --}}
                <div>
                    <label for="instancia-telefono" class="block text-sm font-medium text-gray-300 mb-1.5">
                        Número de teléfono WhatsApp
                    </label>
                    <div class="flex items-center bg-gray-800 border border-white/10 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition">
                        <span class="px-3 py-3 bg-gray-700 text-gray-400 text-sm border-r border-white/10 select-none" aria-hidden="true">+</span>
                        <input id="instancia-telefono" x-model="telefono" type="tel"
                               placeholder="521234567890  (con código de país)"
                               class="flex-1 px-3 py-3 text-sm outline-none bg-gray-800 text-gray-100 placeholder-gray-600">
                    </div>
                    <p class="mt-1.5 text-xs text-gray-600">Incluye el código de país sin el +. Ej: 521234567890</p>
                </div>

                {{-- Botón continuar --}}
                <button @click="continuar"
                        :disabled="cargando || !nombre.trim()"
                        class="w-full flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors">
                    <template x-if="cargando">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </template>
                    <span x-text="cargando ? 'Creando instancia...' : 'Generar código QR'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Paso 2: Escaneo del QR ───────────────────────────────────────────── --}}
    <div x-show="paso === 'qr'" x-transition>
        <div class="bg-gray-900 rounded-xl border border-white/5 p-8 text-center">

            <h2 class="text-xl font-bold text-gray-100 mb-1">Escanea el código QR</h2>
            <p class="text-sm text-gray-500 mb-6">
                Instancia: <span class="font-semibold text-gray-300" x-text="instancia"></span>
            </p>

            <div class="flex justify-center mb-6">
                <div class="relative">
                    <img :src="qrSrc" alt="Código QR"
                         class="w-64 h-64 rounded-xl border-4 border-white/10">
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900/80 rounded-xl"
                         x-show="!qrSrc">
                        <svg class="w-8 h-8 animate-spin text-indigo-500 mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span class="text-sm text-gray-500">Cargando QR…</span>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-lg px-5 py-4 text-left mb-6">
                <p class="text-sm font-semibold text-indigo-300 mb-2">¿Cómo escanear?</p>
                <ol class="text-sm text-indigo-400 space-y-1 list-decimal list-inside">
                    <li>Abre WhatsApp en tu teléfono</li>
                    <li>Ve a <strong>Menú → Dispositivos vinculados</strong></li>
                    <li>Toca <strong>Vincular un dispositivo</strong></li>
                    <li>Apunta la cámara al código QR de arriba</li>
                </ol>
            </div>

            <div class="flex items-center justify-center gap-2 text-sm text-gray-500 mb-6">
                <svg class="w-4 h-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Verificando estado cada 3 segundos…
            </div>

            <div class="flex items-center gap-3">
                <button @click="refrescarQr" :disabled="refrescando"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-white/10 text-gray-400 hover:bg-white/5 disabled:opacity-50 font-medium text-sm rounded-lg transition-colors">
                    <svg class="w-4 h-4" :class="refrescando ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refrescar QR
                </button>
                <button @click="cancelar"
                        class="flex-1 py-2.5 text-gray-500 hover:text-gray-300 font-medium text-sm rounded-lg border border-white/10 hover:bg-white/5 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    {{-- ── Paso final: Conexión exitosa ─────────────────────────────────────── --}}
    <div x-show="paso === 'exito'" x-transition>
        <div class="bg-gray-900 rounded-xl border border-white/5 p-12 text-center">
            <div class="w-16 h-16 bg-green-500/15 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-100 mb-2">¡Número conectado!</h2>
            <p class="text-gray-500 text-sm mb-8">
                La instancia <span class="font-semibold text-gray-300" x-text="instancia"></span>
                se ha vinculado correctamente a WhatsApp.
            </p>
            <a href="{{ route('bot.index') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                Ver números conectados
            </a>
        </div>
    </div>

</div>

<script>
function qrScanner() {
    return {
        paso:        'formulario',
        nombre:      '',
        telefono:    '',
        instancia:   '',
        qrSrc:       '',
        cargando:    false,
        refrescando: false,
        errorMsg:    '',
        pollingId:   null,
        _csrf:       '{{ csrf_token() }}',

        async continuar() {
            if (!this.nombre.trim()) return;
            this.cargando = true;
            this.errorMsg = '';

            try {
                const res = await fetch('{{ route('bot.crear') }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this._csrf,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({
                        nombre:   this.nombre.trim(),
                        metodo:   'qr',
                        telefono: this.telefono.trim(),
                    }),
                });

                const data = await res.json();

                if (!data.success) {
                    this.errorMsg = data.message ?? 'Error al crear la instancia.';
                    return;
                }

                this.instancia = data.instancia;
                this.qrSrc = data.qr
                    ? `data:image/png;base64,${data.qr.replace(/^data:image\/\w+;base64,/, '')}`
                    : '';
                this.paso = 'qr';
                this.iniciarPolling();

            } catch (e) {
                this.errorMsg = 'Error de red. Verifica tu conexión.';
            } finally {
                this.cargando = false;
            }
        },

        async refrescarQr() {
            if (this.refrescando) return;
            this.refrescando = true;
            try {
                const res  = await fetch(`{{ url('/bot/qr') }}/${this.instancia}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success && data.qr) {
                    this.qrSrc = `data:image/png;base64,${data.qr.replace(/^data:image\/\w+;base64,/, '')}`;
                }
            } catch (e) { /* silencioso */ }
            finally { this.refrescando = false; }
        },

        iniciarPolling() {
            this.detenerPolling();
            this.pollingId = setInterval(async () => {
                try {
                    const res  = await fetch(`{{ url('/bot/estado') }}/${this.instancia}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (data.conectado) {
                        this.detenerPolling();
                        this.paso = 'exito';
                    }
                } catch (e) { /* continúa polling */ }
            }, 3000);
        },

        detenerPolling() {
            if (this.pollingId) {
                clearInterval(this.pollingId);
                this.pollingId = null;
            }
        },

        cancelar() {
            this.detenerPolling();
            this.paso      = 'formulario';
            this.qrSrc     = '';
            this.instancia = '';
            this.telefono  = '';
            this.errorMsg  = '';
        },
    };
}
</script>

</x-admin-layout>
