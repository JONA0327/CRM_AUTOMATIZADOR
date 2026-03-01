<x-admin-layout title="Conversaciones del Bot">

<div class="flex gap-0 h-[calc(100vh-10rem)] bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- ── Panel izquierdo: lista de contactos ───────────────────────────── --}}
    <div class="w-80 flex-shrink-0 border-r border-gray-100 flex flex-col"
         x-data="botConversaciones()"
         x-init="init()">

        {{-- Cabecera --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <span class="font-semibold text-gray-800 text-sm">Contactos</span>
            <span class="flex items-center gap-1 text-xs"
                  :class="conectado ? 'text-green-600' : 'text-gray-400'">
                <span class="w-2 h-2 rounded-full"
                      :class="conectado ? 'bg-green-500 animate-pulse' : 'bg-gray-300'"></span>
                <span x-text="conectado ? 'EN VIVO' : 'Conectando…'"></span>
            </span>
        </div>

        {{-- Buscador --}}
        <div class="px-3 py-2 border-b border-gray-100">
            <input type="text" x-model="busqueda" placeholder="Buscar contacto…"
                   class="w-full text-xs rounded-lg border border-gray-200 px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-400">
        </div>

        {{-- Estado del bot --}}
        <div class="px-4 py-2 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $botActivo ? 'bg-green-500' : 'bg-red-400' }}"></span>
                <span class="text-xs {{ $botActivo ? 'text-green-700' : 'text-red-600' }}">
                    Bot {{ $botActivo ? 'activo' : 'inactivo' }}
                </span>
            </div>
            <a href="{{ route('bot.index') }}" class="text-xs text-blue-500 hover:underline">Configurar</a>
        </div>

        {{-- Lista de contactos --}}
        <div class="flex-1 overflow-y-auto">
            <template x-if="contactos.length === 0">
                <p class="text-center text-xs text-gray-400 py-10 px-4">
                    Sin conversaciones aún.<br>Los mensajes aparecerán aquí en tiempo real.
                </p>
            </template>

            <template x-for="c in contactosFiltrados" :key="c.phone">
                <button @click="seleccionar(c)"
                        class="w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-blue-50 transition-colors"
                        :class="contactoActivo?.phone === c.phone ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'border-l-4 border-l-transparent'">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate"
                               x-text="c.contact_name || c.phone"></p>
                            <p class="text-xs text-gray-400 truncate" x-text="c.phone"></p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate" x-text="'📱 ' + c.instancia"></p>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-2">
                            <span class="text-xs text-gray-400" x-text="formatHora(c.ultimo)"></span>
                            <template x-if="c.nuevo">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            </template>
                        </div>
                    </div>
                </button>
            </template>
        </div>
    </div>

    {{-- ── Panel derecho: chat ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col" x-data>

        {{-- Sin contacto seleccionado --}}
        <template x-if="!$store.bot || !$store.bot.contactoActivo">
            <div class="flex-1 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-sm">Selecciona un contacto para ver la conversación</p>
                </div>
            </div>
        </template>

        {{-- Chat activo --}}
        <template x-if="$store.bot && $store.bot.contactoActivo">
            <div class="flex flex-col h-full">

                {{-- Cabecera del chat --}}
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm"
                         x-text="($store.bot.contactoActivo.contact_name || $store.bot.contactoActivo.phone).charAt(0).toUpperCase()">
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm"
                           x-text="$store.bot.contactoActivo.contact_name || $store.bot.contactoActivo.phone"></p>
                        <p class="text-xs text-gray-400"
                           x-text="$store.bot.contactoActivo.phone + ' · ' + $store.bot.contactoActivo.instancia"></p>
                    </div>
                </div>

                {{-- Mensajes --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-gray-50"
                     id="chat-mensajes">

                    <template x-if="$store.bot.cargando">
                        <div class="flex justify-center py-8">
                            <svg class="animate-spin w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </div>
                    </template>

                    <template x-for="m in $store.bot.mensajes" :key="m.id">
                        <div class="space-y-2">
                            {{-- Mensaje del usuario (burbuja derecha) --}}
                            <div class="flex justify-end">
                                <div class="max-w-[72%]">
                                    <div class="bg-blue-500 text-white text-sm px-4 py-2 rounded-2xl rounded-tr-sm shadow-sm whitespace-pre-wrap"
                                         x-text="m.user_message"></div>
                                    <p class="text-right text-xs text-gray-400 mt-1"
                                       x-text="formatHora(m.created_at)"></p>
                                </div>
                            </div>
                            {{-- Respuesta del bot (burbuja izquierda) --}}
                            <div class="flex justify-start">
                                <div class="max-w-[72%]">
                                    <div class="bg-white text-gray-800 text-sm px-4 py-2 rounded-2xl rounded-tl-sm shadow-sm border border-gray-100 whitespace-pre-wrap"
                                         x-text="m.bot_response"></div>
                                    <p class="text-left text-xs text-gray-400 mt-1"
                                       x-text="'🤖 Bot · ' + formatHora(m.created_at)"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function botConversaciones() {
    return {
        contactos: [],
        contactoActivo: null,
        busqueda: '',
        conectado: false,

        get contactosFiltrados() {
            if (!this.busqueda) return this.contactos;
            const q = this.busqueda.toLowerCase();
            return this.contactos.filter(c =>
                (c.phone || '').includes(q) ||
                (c.contact_name || '').toLowerCase().includes(q)
            );
        },

        async init() {
            Alpine.store('bot', {
                contactoActivo: null,
                mensajes: [],
                cargando: false,
            });

            await this.cargarContactos();
            this.escucharEventos();
        },

        async cargarContactos() {
            try {
                const res = await axios.get('{{ route("bot.contactos") }}');
                this.contactos = res.data.map(c => ({ ...c, nuevo: false }));
            } catch (e) {
                console.error('Error cargando contactos', e);
            }
        },

        async seleccionar(contacto) {
            this.contactoActivo = contacto;
            Alpine.store('bot').contactoActivo = contacto;
            Alpine.store('bot').cargando = true;
            Alpine.store('bot').mensajes = [];

            const idx = this.contactos.findIndex(c => c.phone === contacto.phone);
            if (idx !== -1) this.contactos[idx].nuevo = false;

            try {
                const phone = encodeURIComponent(contacto.phone);
                const res = await axios.get(`{{ url('/bot/mensajes') }}/${phone}`);
                Alpine.store('bot').mensajes = res.data;
            } catch (e) {
                console.error('Error cargando mensajes', e);
            } finally {
                Alpine.store('bot').cargando = false;
                this.$nextTick(() => this.scrollAbajo());
            }
        },

        escucharEventos() {
            if (typeof window.Echo === 'undefined') {
                console.warn('Laravel Echo no disponible. Verifica que Reverb esté corriendo.');
                return;
            }

            const tenantId = '{{ tenancy()->tenant?->getTenantKey() ?? "" }}';
            const canal = tenantId ? `bot-tenant.${tenantId}` : 'bot-conversaciones';

            window.Echo.channel(canal)
                .listen('.nuevo-mensaje', (data) => {
                    this.conectado = true;
                    this.manejarNuevoMensaje(data);
                });

            window.Echo.connector.pusher.connection.bind('connected', () => {
                this.conectado = true;
            });
            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                this.conectado = false;
            });
        },

        manejarNuevoMensaje(data) {
            // Actualizar lista de contactos
            const idx = this.contactos.findIndex(c => c.phone === data.phone);
            const esActivo = this.contactoActivo?.phone === data.phone;

            if (idx !== -1) {
                this.contactos[idx].ultimo = data.hora;
                this.contactos[idx].nuevo = !esActivo;
                const c = this.contactos.splice(idx, 1)[0];
                this.contactos.unshift(c);
            } else {
                this.contactos.unshift({
                    phone: data.phone,
                    contact_name: data.contact_name,
                    instancia: data.instancia,
                    ultimo: data.hora,
                    nuevo: !esActivo,
                });
            }

            // Agregar mensaje al chat si el contacto está activo
            if (esActivo) {
                Alpine.store('bot').mensajes.push({
                    id: data.id,
                    user_message: data.user_message,
                    bot_response: data.bot_response,
                    created_at: new Date().toISOString(),
                });
                this.$nextTick(() => this.scrollAbajo());
            }
        },

        scrollAbajo() {
            const el = document.getElementById('chat-mensajes');
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatHora(valor) {
            if (!valor) return '';
            try {
                return new Date(valor).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
            } catch {
                return valor;
            }
        },
    };
}
</script>

</x-admin-layout>
