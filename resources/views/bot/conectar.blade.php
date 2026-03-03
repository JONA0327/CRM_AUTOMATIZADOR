<x-admin-layout title="Conectar Número">

<div class="max-w-lg mx-auto" x-data="qrScanner()">

    {{-- ── Paso 1: Formulario ──────────────────────────────────────────────── --}}
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
                    <p class="text-sm text-gray-500 mt-0.5">Vincula un número mediante QR o código de teléfono</p>
                </div>
            </div>

            <div x-show="errorMsg" x-transition
                 role="alert" aria-live="assertive"
                 class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span x-text="errorMsg"></span>
            </div>

            <div class="space-y-5">

                {{-- Nombre de la instancia --}}
                <div>
                    <label for="instancia-nombre" class="block text-sm font-medium text-gray-700 mb-1.5">Nombre de la instancia</label>
                    <input id="instancia-nombre" x-model="nombre" type="text"
                           placeholder="ej. ventas-principal"
                           maxlength="50"
                           autocomplete="off"
                           aria-describedby="instancia-nombre-hint"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <p id="instancia-nombre-hint" class="mt-1.5 text-xs text-gray-400">Solo letras, números, guiones y guiones bajos. Sin espacios.</p>
                </div>

                {{-- Selector de método --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Método de vinculación</label>
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Opción QR --}}
                        <button type="button" @click="metodo = 'qr'"
                                :class="metodo === 'qr'
                                    ? 'border-blue-500 bg-blue-50 text-blue-700 ring-1 ring-blue-500'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50'"
                                class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition-all cursor-pointer">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span class="text-sm font-semibold">Código QR</span>
                            <span class="text-xs text-center opacity-70">Escanea con la cámara</span>
                        </button>

                        {{-- Opción Teléfono --}}
                        <button type="button" @click="metodo = 'phone'"
                                :class="metodo === 'phone'
                                    ? 'border-green-500 bg-green-50 text-green-700 ring-1 ring-green-500'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50'"
                                class="flex flex-col items-center gap-2 p-4 border-2 rounded-xl transition-all cursor-pointer">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-semibold">Número de teléfono</span>
                            <span class="text-xs text-center opacity-70">Código de 8 dígitos</span>
                        </button>
                    </div>
                </div>

                {{-- Número de teléfono (solo modo phone) --}}
                <div x-show="metodo === 'phone'" x-transition>
                    <label for="instancia-telefono" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Número de teléfono WhatsApp
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-green-500 focus-within:border-green-500 transition">
                        <span class="px-3 py-3 bg-gray-50 text-gray-500 text-sm border-r border-gray-300 select-none" aria-hidden="true">+</span>
                        <input id="instancia-telefono" x-model="telefono" type="tel"
                               placeholder="521234567890  (con código de país)"
                               aria-describedby="instancia-telefono-hint"
                               class="flex-1 px-3 py-3 text-sm outline-none bg-white">
                    </div>
                    <p id="instancia-telefono-hint" class="mt-1.5 text-xs text-gray-400">Incluye el código de país sin el +. Ej: 521234567890</p>
                </div>

                {{-- Botón continuar --}}
                <button @click="continuar"
                        :disabled="cargando || !nombre.trim() || (metodo === 'phone' && !telefono.trim())"
                        class="w-full flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors">
                    <template x-if="cargando">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </template>
                    <span x-text="cargando
                        ? (metodo === 'phone' ? 'Generando código… (puede tardar ~5s)' : 'Creando instancia...')
                        : (metodo === 'qr' ? 'Generar código QR' : 'Obtener código de emparejamiento')">
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Paso 2a: Escaneo del QR ─────────────────────────────────────────── --}}
    <div x-show="paso === 'qr'" x-transition>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">

            <h2 class="text-xl font-bold text-gray-900 mb-1">Escanea el código QR</h2>
            <p class="text-sm text-gray-500 mb-6">
                Instancia: <span class="font-semibold text-gray-700" x-text="instancia"></span>
            </p>

            <div class="flex justify-center mb-6">
                <div class="relative">
                    <img :src="qrSrc" alt="Código QR"
                         class="w-64 h-64 rounded-xl border-4 border-gray-100 shadow">
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

            <div class="bg-blue-50 border border-blue-100 rounded-lg px-5 py-4 text-left mb-6">
                <p class="text-sm font-semibold text-blue-800 mb-2">¿Cómo escanear?</p>
                <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
                    <li>Abre WhatsApp en tu teléfono</li>
                    <li>Ve a <strong>Menú → Dispositivos vinculados</strong></li>
                    <li>Toca <strong>Vincular un dispositivo</strong></li>
                    <li>Apunta la cámara al código QR de arriba</li>
                </ol>
            </div>

            <div class="flex items-center justify-center gap-2 text-sm text-gray-500 mb-6">
                <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Verificando estado cada 3 segundos…
            </div>

            <div class="flex items-center gap-3">
                <button @click="refrescarQr" :disabled="refrescando"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 font-medium text-sm rounded-lg transition-colors">
                    <svg class="w-4 h-4" :class="refrescando ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refrescar QR
                </button>
                <button @click="cancelar"
                        class="flex-1 py-2.5 text-gray-500 hover:text-gray-700 font-medium text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    {{-- ── Paso 2b: Ingresar teléfono para pairing code ────────────────────── --}}
    <div x-show="paso === 'pairing-phone'" x-transition>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Vincular por número</h2>
                    <p class="text-xs text-gray-500">Instancia: <span class="font-semibold" x-text="instancia"></span></p>
                </div>
            </div>

            <div x-show="errorMsg" x-transition
                 role="alert" aria-live="assertive"
                 class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span x-text="errorMsg"></span>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Número de WhatsApp</label>
                    <input x-model="telefono" type="tel"
                           placeholder="Ej: 5215512345678"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
                    <p class="mt-1.5 text-xs text-gray-400">
                        Incluye el código de país sin el signo +. Ej: <strong>52</strong>1234567890 (México).
                    </p>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-lg px-4 py-3">
                    <p class="text-xs text-amber-800">
                        <strong>Importante:</strong> El número debe coincidir exactamente con el número de WhatsApp que quieres vincular.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button @click="solicitarCodigo" :disabled="cargando || !telefono.trim()"
                            class="flex-1 flex items-center justify-center gap-2 py-3 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors">
                        <template x-if="cargando">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </template>
                        <span x-text="cargando ? 'Iniciando conexión… (puede tardar ~5s)' : 'Obtener código'"></span>
                    </button>
                    <button @click="cancelar"
                            class="px-4 py-3 text-gray-500 hover:text-gray-700 font-medium text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Paso 3b: Mostrar código de emparejamiento ────────────────────────── --}}
    <div x-show="paso === 'pairing-code'" x-transition>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">

            {{-- Header --}}
            <div class="flex items-center gap-4 mb-6 pb-5 border-b border-gray-100">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Código de vinculación</h2>
                    <p class="text-sm text-gray-500">
                        Instancia: <span class="font-semibold text-gray-700" x-text="instancia"></span>
                    </p>
                </div>
            </div>

            {{-- Bloque del código + botón copiar --}}
            <div class="relative bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-5 mb-5">

                {{-- Botón copiar --}}
                <button @click="copiarCodigo"
                        class="absolute top-3 right-3 flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                        :class="copiado
                            ? 'bg-green-100 text-green-700 border border-green-200'
                            : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:text-gray-900'">
                    <template x-if="!copiado">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </template>
                    <template x-if="copiado">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span x-text="copiado ? 'Copiado' : 'Copiar'"></span>
                </button>

                {{-- Código: fuente grande si es corto (≤20 chars), compacta si es largo --}}
                <div class="pr-20 text-left">
                    <p class="font-mono font-bold text-gray-900 break-all select-all leading-snug"
                       :class="pairingCode.length <= 12 ? 'text-4xl tracking-[0.25em]' : 'text-sm'"
                       x-text="pairingCode"></p>
                </div>
                <p class="text-xs text-gray-400 mt-3"
                   x-text="copiado ? '¡Código copiado al portapapeles!' : 'Selecciona el texto o usa el botón para copiar'"></p>
            </div>

            {{-- Instrucciones --}}
            <div class="bg-green-50 border border-green-100 rounded-xl px-5 py-4 mb-5 text-left">
                <p class="text-sm font-semibold text-green-800 mb-2">¿Cómo ingresar el código?</p>
                <ol class="text-sm text-green-700 space-y-1 list-decimal list-inside">
                    <li>Abre WhatsApp en tu teléfono</li>
                    <li>Ve a <strong>Menú → Dispositivos vinculados</strong></li>
                    <li>Toca <strong>Vincular un dispositivo</strong></li>
                    <li>Selecciona <strong>Vincular con número de teléfono</strong></li>
                    <li>Ingresa el código copiado y confirma</li>
                </ol>
            </div>

            {{-- Estado polling --}}
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-5 justify-center">
                <svg class="w-3.5 h-3.5 animate-spin text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Esperando vinculación… verificando cada 3 segundos
            </div>

            {{-- Acciones --}}
            <div class="flex gap-3">
                <button @click="solicitarCodigo" :disabled="cargando"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 font-medium text-sm rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Nuevo código
                </button>
                <button @click="cancelar"
                        class="flex-1 py-2.5 text-gray-500 hover:text-gray-700 font-medium text-sm rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    {{-- ── Paso final: Conexión exitosa ─────────────────────────────────────── --}}
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
        metodo:      'qr',         // 'qr' | 'phone'
        nombre:      '',
        instancia:   '',
        qrSrc:       '',
        pairingCode: '',
        telefono:    '',
        cargando:    false,
        refrescando: false,
        errorMsg:    '',
        pollingId:   null,
        copiado:     false,
        _csrf:       '{{ csrf_token() }}',

        // ── Paso 1: Crear instancia y redirigir según método ────────────────
        async continuar() {
            if (!this.nombre.trim()) return;
            this.cargando  = true;
            this.errorMsg  = '';

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
                        metodo:   this.metodo,
                        telefono: this.telefono.trim(),
                    }),
                });

                const data = await res.json();

                if (!data.success) {
                    this.errorMsg = data.message ?? 'Error al crear la instancia.';
                    return;
                }

                this.instancia = data.instancia;

                if (this.metodo === 'qr') {
                    this.qrSrc = data.qr
                        ? `data:image/png;base64,${data.qr.replace(/^data:image\/\w+;base64,/, '')}`
                        : '';
                    this.paso = 'qr';
                    this.iniciarPolling();
                } else if (data.pairingCode) {
                    // El servidor ya nos devolvió el código — mostrarlo directamente
                    this.pairingCode = data.pairingCode;
                    this.paso        = 'pairing-code';
                    this.iniciarPolling();
                } else {
                    // Fallback: pedir código manualmente (método antiguo)
                    this.paso = 'pairing-phone';
                }

            } catch (e) {
                this.errorMsg = 'Error de red. Verifica tu conexión.';
            } finally {
                this.cargando = false;
            }
        },

        // ── Solicitar código de emparejamiento ──────────────────────────────
        async solicitarCodigo() {
            if (!this.telefono.trim()) return;
            this.cargando  = true;
            this.errorMsg  = '';

            try {
                const url = `{{ url('/bot/pairing-code') }}/${encodeURIComponent(this.instancia)}`;
                const res = await fetch(url, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this._csrf,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ phone: this.telefono.trim() }),
                });

                const data = await res.json();

                if (!data.success) {
                    this.errorMsg = data.message ?? 'No se pudo generar el código.';
                    return;
                }

                this.pairingCode = data.code ?? '';
                this.paso        = 'pairing-code';
                this.iniciarPolling();

            } catch (e) {
                this.errorMsg = 'Error de red. Verifica tu conexión.';
            } finally {
                this.cargando = false;
            }
        },

        // ── Refrescar QR ────────────────────────────────────────────────────
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

        // ── Polling de estado ────────────────────────────────────────────────
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

        async copiarCodigo() {
            try {
                await navigator.clipboard.writeText(this.pairingCode);
                this.copiado = true;
                setTimeout(() => { this.copiado = false; }, 2500);
            } catch (e) {
                // Fallback: seleccionar el texto manualmente
                const el = document.querySelector('[x-text="pairingCode"]');
                if (el) {
                    const range = document.createRange();
                    range.selectNodeContents(el);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                }
            }
        },

        cancelar() {
            this.detenerPolling();
            this.paso        = 'formulario';
            this.qrSrc       = '';
            this.pairingCode = '';
            this.instancia   = '';
            this.telefono    = '';
            this.errorMsg    = '';
        },
    };
}
</script>

</x-admin-layout>
