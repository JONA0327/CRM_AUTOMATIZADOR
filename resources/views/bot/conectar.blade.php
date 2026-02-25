<x-admin-layout title="Conectar Número">

<div class="max-w-lg mx-auto" x-data="qrScanner()">

    {{-- Paso 1: Formulario de nombre --}}
    <div x-show="paso === 'formulario'" x-transition>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Conectar Número de WhatsApp</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Vincula un número escaneando el código QR</p>
                </div>
            </div>

            {{-- Error general --}}
            <div x-show="errorMsg" x-transition
                 class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span x-text="errorMsg"></span>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nombre de la instancia
                    </label>
                    <input
                        x-model="nombre"
                        type="text"
                        placeholder="ej. ventas-principal"
                        maxlength="50"
                        @keydown.enter="generarQr"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    <p class="mt-1.5 text-xs text-gray-400">
                        Solo letras, números, guiones ( - ) y guiones bajos ( _ ). Sin espacios.
                    </p>
                </div>

                <button
                    @click="generarQr"
                    :disabled="cargando || !nombre.trim()"
                    class="w-full flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors"
                >
                    <template x-if="cargando">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </template>
                    <template x-if="!cargando">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </template>
                    <span x-text="cargando ? 'Generando QR...' : 'Generar código QR'"></span>
                </button>
            </div>

        </div>
    </div>

    {{-- Paso 2: Escaneo del QR --}}
    <div x-show="paso === 'qr'" x-transition>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">

            <h2 class="text-xl font-bold text-gray-900 mb-1">Escanea el código QR</h2>
            <p class="text-sm text-gray-500 mb-6">
                Instancia: <span class="font-semibold text-gray-700" x-text="instancia"></span>
            </p>

            {{-- QR Image --}}
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <img
                        :src="qrSrc"
                        alt="Código QR"
                        class="w-64 h-64 rounded-xl border-4 border-gray-100 shadow"
                    >
                    {{-- Overlay escaneando --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 rounded-xl"
                         x-show="!qrSrc">
                        <svg class="w-8 h-8 animate-spin text-blue-500 mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span class="text-sm text-gray-500">Cargando QR…</span>
                    </div>
                </div>
            </div>

            {{-- Instrucciones --}}
            <div class="bg-blue-50 border border-blue-100 rounded-lg px-5 py-4 text-left mb-6">
                <p class="text-sm font-semibold text-blue-800 mb-2">¿Cómo escanear?</p>
                <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                    <li>Abre WhatsApp en tu teléfono</li>
                    <li>Ve a <strong>Menú → Dispositivos vinculados</strong></li>
                    <li>Toca <strong>Vincular un dispositivo</strong></li>
                    <li>Apunta la cámara al código QR de arriba</li>
                </ol>
            </div>

            {{-- Estado del polling --}}
            <div class="flex items-center justify-center gap-2 text-sm text-gray-500 mb-6">
                <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Verificando estado cada 3 segundos…
            </div>

            {{-- Acciones --}}
            <div class="flex items-center gap-3">
                <button
                    @click="refrescarQr"
                    :disabled="refrescando"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 font-medium text-sm rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" :class="refrescando ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refrescar QR
                </button>
                <button
                    @click="cancelar"
                    class="flex-1 py-2.5 text-gray-500 hover:text-gray-700 font-medium text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                >
                    Cancelar
                </button>
            </div>

        </div>
    </div>

    {{-- Paso 3: Conexión exitosa --}}
    <div x-show="paso === 'exito'" x-transition>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Número conectado!</h2>
            <p class="text-gray-500 text-sm mb-8">
                La instancia <span class="font-semibold text-gray-700" x-text="instancia"></span>
                se ha vinculado correctamente a WhatsApp.
            </p>
            <a href="{{ route('bot.index') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
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
        instancia:   '',
        qrSrc:       '',
        cargando:    false,
        refrescando: false,
        errorMsg:    '',
        pollingId:   null,

        async generarQr() {
            if (!this.nombre.trim()) return;
            this.cargando  = true;
            this.errorMsg  = '';

            try {
                const res = await fetch('{{ route('bot.crear') }}', {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ nombre: this.nombre.trim() }),
                });

                const data = await res.json();

                if (!data.success) {
                    this.errorMsg = data.message ?? 'Error al generar el QR.';
                    return;
                }

                this.instancia = data.instancia;
                this.qrSrc     = data.qr ? `data:image/png;base64,${data.qr.replace(/^data:image\/\w+;base64,/, '')}` : '';
                this.paso      = 'qr';
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
            finally {
                this.refrescando = false;
            }
        },

        iniciarPolling() {
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
            this.errorMsg  = '';
        },
    }
}
</script>

</x-admin-layout>
