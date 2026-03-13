<x-admin-layout title="Conversaciones del Bot">

<div class="flex gap-0 h-[calc(100vh-10rem)] bg-gray-900 rounded-xl border border-white/5 overflow-hidden"
     x-data="botConversaciones()"
     x-init="init()">

    {{-- ── Panel izquierdo: lista de contactos ───────────────────────────── --}}
    <div class="w-80 flex-shrink-0 border-r border-white/5 flex flex-col">

        <div class="px-4 py-3 bg-gray-900 border-b border-white/5 flex items-center justify-between">
            <span class="font-semibold text-gray-200 text-sm">Contactos</span>
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1 text-xs"
                      :class="conectado ? 'text-green-400' : 'text-amber-400'">
                    <span class="w-2 h-2 rounded-full"
                          :class="conectado ? 'bg-green-400 animate-pulse' : 'bg-amber-400 animate-pulse'"></span>
                    <span x-text="conectado ? 'EN VIVO' : 'Polling'"></span>
                </span>
                <button @click="abrirConfirm('Borrar todas las conversaciones', 'Se eliminarán TODAS las conversaciones del sistema. Esta acción no se puede deshacer.', () => borrarTodo())"
                        title="Borrar todas las conversaciones"
                        class="p-1 text-gray-600 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-3 py-2 border-b border-white/5">
            <input type="text" x-model="busqueda" placeholder="Buscar contacto…"
                   class="w-full text-xs rounded-lg bg-gray-800 border border-white/10 text-gray-200 placeholder-gray-500 px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        <div class="px-4 py-2 border-b border-white/5 bg-gray-900 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $botActivo ? 'bg-green-400' : 'bg-red-500' }}"></span>
                <span class="text-xs {{ $botActivo ? 'text-green-400' : 'text-red-400' }}">
                    Bot {{ $botActivo ? 'activo' : 'inactivo' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bot.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Configurar</a>
                @if(auth()->user()->hasRole('super_admin'))
                <button type="button" @click="limpiarLogs()"
                        class="text-xs text-red-400 hover:text-red-300">
                    Limpiar logs
                </button>
                @endif
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <template x-if="contactos.length === 0">
                <p class="text-center text-xs text-gray-600 py-10 px-4">
                    Sin conversaciones aún.<br>Los mensajes aparecerán aquí en tiempo real.
                </p>
            </template>

            <template x-for="c in contactosFiltrados" :key="c.phone">
                <div class="group relative border-b border-white/5">
                    <button @click="seleccionar(c)"
                            class="w-full text-left px-4 py-3 hover:bg-indigo-500/5 transition-colors pr-10"
                            :class="contactoActivo?.phone === c.phone ? 'bg-indigo-500/10 border-l-4 border-l-indigo-500' : 'border-l-4 border-l-transparent'">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-200 truncate" x-text="c.contact_name || c.phone"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="c.phone"></p>
                                <p class="text-xs text-gray-600 mt-0.5 truncate" x-text="'📱 ' + c.instancia"></p>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0 ml-2">
                                <span class="text-xs text-gray-600" x-text="formatHora(c.ultimo)"></span>
                                <template x-if="c.nuevo">
                                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                </template>
                            </div>
                        </div>
                    </button>
                    <button @click.stop="abrirConfirm('Borrar conversación', '¿Eliminar todas las conversaciones de ' + (c.contact_name || c.phone) + '?', () => borrarContacto(c))"
                            title="Eliminar conversación"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-700 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Panel derecho: chat ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col">

        <template x-if="!contactoActivo">
            <div class="flex-1 flex items-center justify-center text-gray-600">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-sm">Selecciona un contacto para ver la conversación</p>
                </div>
            </div>
        </template>

        <template x-if="contactoActivo">
            <div class="flex flex-col h-full">
                <div class="px-5 py-3 border-b border-white/5 bg-gray-900 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-300 font-bold text-sm"
                             x-text="(contactoActivo.contact_name || contactoActivo.phone).charAt(0).toUpperCase()"></div>
                        <div>
                            <p class="font-semibold text-gray-100 text-sm"
                               x-text="contactoActivo.contact_name || contactoActivo.phone"></p>
                            <p class="text-xs text-gray-500"
                               x-text="contactoActivo.phone + ' · ' + contactoActivo.instancia"></p>
                        </div>
                    </div>
                    <button @click="abrirConfirm('Borrar conversación', '¿Eliminar la conversación de ' + (contactoActivo.contact_name || contactoActivo.phone) + '?', () => borrarContacto(contactoActivo))"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Borrar conversación
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-gray-950" id="chat-mensajes">
                    <template x-if="cargando">
                        <div class="flex justify-center py-8">
                            <svg class="animate-spin w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </div>
                    </template>

                    <template x-for="m in mensajes" :key="m.id">
                        <div class="space-y-2">
                            <div class="flex justify-end">
                                <div class="max-w-[72%]">
                                    <div class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-2xl rounded-tr-sm shadow-sm whitespace-pre-wrap"
                                         x-text="m.user_message"></div>
                                    <p class="text-right text-xs text-gray-600 mt-1" x-text="formatHora(m.created_at)"></p>
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="max-w-[72%]">
                                    <div class="bg-gray-800 text-gray-100 text-sm px-4 py-2 rounded-2xl rounded-tl-sm border border-white/10 whitespace-pre-wrap"
                                         x-text="m.bot_response"></div>
                                    <p class="text-left text-xs text-gray-600 mt-1"
                                       x-text="'🤖 Bot · ' + formatHora(m.created_at)"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- ── Modal de confirmación ───────────────────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="dlg.abierto"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             style="display:none">
            <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-10 h-10 rounded-full bg-red-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-100 text-sm" x-text="dlg.titulo"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="dlg.mensaje"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="dlg.abierto = false"
                            class="px-4 py-2 text-sm text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button @click="ejecutarConfirm()"
                            class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function botConversaciones() {
    return {
        contactos:      [],
        contactoActivo: null,
        mensajes:       [],
        cargando:       false,
        busqueda:       '',
        conectado:      false,
        _pollInterval:  null,
        _csrf:          document.querySelector('meta[name="csrf-token"]')?.content ?? '',

        dlg: {
            abierto: false,
            titulo:  '',
            mensaje: '',
            accion:  null,
        },

        get contactosFiltrados() {
            if (!this.busqueda) return this.contactos;
            const q = this.busqueda.toLowerCase();
            return this.contactos.filter(c =>
                (c.phone || '').includes(q) ||
                (c.contact_name || '').toLowerCase().includes(q)
            );
        },

        async init() {
            await this.cargarContactos();
            this.escucharEventos();
        },

        abrirConfirm(titulo, mensaje, accion) {
            this.dlg.titulo  = titulo;
            this.dlg.mensaje = mensaje;
            this.dlg.accion  = accion;
            this.dlg.abierto = true;
        },

        async ejecutarConfirm() {
            this.dlg.abierto = false;
            if (this.dlg.accion) {
                await this.dlg.accion();
            }
        },

        async cargarContactos() {
            try {
                const res = await axios.get('{{ route("bot.contactos") }}');
                const nuevoMap = {};
                this.contactos.forEach(c => { if (c.nuevo) nuevoMap[c.phone] = true; });
                this.contactos = res.data.map(c => ({ ...c, nuevo: !!nuevoMap[c.phone] }));
            } catch (e) {
                console.error('Error cargando contactos', e);
            }
        },

        async seleccionar(contacto) {
            this.contactoActivo = contacto;
            this.cargando       = true;
            this.mensajes       = [];

            const idx = this.contactos.findIndex(c => c.phone === contacto.phone);
            if (idx !== -1) this.contactos[idx].nuevo = false;

            try {
                const phone = encodeURIComponent(contacto.phone);
                const res   = await axios.get(`{{ url('/bot/mensajes') }}/${phone}`);
                this.mensajes = res.data;
            } catch (e) {
                console.error('Error cargando mensajes', e);
            } finally {
                this.cargando = false;
                this.$nextTick(() => this.scrollAbajo());
            }
        },

        async borrarContacto(contacto) {
            try {
                const phone = encodeURIComponent(contacto.phone);
                await axios.delete(`{{ url('/bot/conversaciones') }}/${phone}`, {
                    headers: { 'X-CSRF-TOKEN': this._csrf },
                });
                this.contactos = this.contactos.filter(c => c.phone !== contacto.phone);
                if (this.contactoActivo?.phone === contacto.phone) {
                    this.contactoActivo = null;
                    this.mensajes       = [];
                }
            } catch (e) {
                console.error('Error borrando conversación', e);
                alert('No se pudo eliminar la conversación. Ver consola.');
            }
        },

        async borrarTodo() {
            try {
                await axios.delete('{{ route("bot.conversaciones.eliminar-todo") }}', {
                    headers: { 'X-CSRF-TOKEN': this._csrf },
                });
                this.contactos      = [];
                this.contactoActivo = null;
                this.mensajes       = [];
            } catch (e) {
                console.error('Error borrando todas las conversaciones', e);
                alert('No se pudo eliminar. Ver consola.');
            }
        },

        @if(auth()->user()->hasRole('super_admin'))
        async limpiarLogs() {
            if (!confirm('¿Limpiar el archivo de logs del sistema?')) return;
            try {
                await axios.delete('{{ route("admin.logs.clear", ["canal" => "sistema"]) }}', {
                    headers: { 'X-CSRF-TOKEN': this._csrf },
                });
                alert('Logs limpiados correctamente.');
            } catch (e) {
                alert('No se pudo limpiar el archivo de logs.');
            }
        },
        @else
        limpiarLogs() {},
        @endif

        escucharEventos() {
            this.iniciarPolling();

            if (typeof window.Echo === 'undefined') {
                console.warn('Echo no disponible. Usando polling cada 5 s.');
                return;
            }

            try {
                const tenantId = '{{ tenancy()->tenant?->getTenantKey() ?? "" }}';
                const canal    = tenantId ? `bot-tenant.${tenantId}` : 'bot-conversaciones';

                window.Echo.channel(canal).listen('.nuevo-mensaje', (data) => {
                    this.manejarNuevoMensaje(data);
                });

                window.Echo.connector.pusher.connection.bind('connected', () => {
                    this.conectado = true;
                    this.detenerPolling();
                });
                window.Echo.connector.pusher.connection.bind('disconnected', () => {
                    this.conectado = false;
                    this.iniciarPolling();
                });
                window.Echo.connector.pusher.connection.bind('failed', () => {
                    this.conectado = false;
                    this.iniciarPolling();
                });
            } catch (e) {
                console.warn('Echo error, continuando con polling:', e);
            }
        },

        iniciarPolling() {
            if (this._pollInterval) return;
            this._pollInterval = setInterval(async () => {
                await this.cargarContactos();
                if (this.contactoActivo) {
                    try {
                        const phone = encodeURIComponent(this.contactoActivo.phone);
                        const res   = await axios.get(`{{ url('/bot/mensajes') }}/${phone}`);
                        if (res.data.length > this.mensajes.length) {
                            this.mensajes = res.data;
                            this.$nextTick(() => this.scrollAbajo());
                        }
                    } catch (_) {}
                }
            }, 5000);
        },

        detenerPolling() {
            if (this._pollInterval) {
                clearInterval(this._pollInterval);
                this._pollInterval = null;
            }
        },

        manejarNuevoMensaje(data) {
            const idx      = this.contactos.findIndex(c => c.phone === data.phone);
            const esActivo = this.contactoActivo?.phone === data.phone;

            if (idx !== -1) {
                this.contactos[idx].ultimo = data.hora;
                this.contactos[idx].nuevo  = !esActivo;
                const c = this.contactos.splice(idx, 1)[0];
                this.contactos.unshift(c);
            } else {
                this.contactos.unshift({
                    phone:        data.phone,
                    contact_name: data.contact_name,
                    instancia:    data.instancia,
                    ultimo:       data.hora,
                    nuevo:        !esActivo,
                });
            }

            if (esActivo) {
                this.mensajes.push({
                    id:           data.id,
                    user_message: data.user_message,
                    bot_response: data.bot_response,
                    created_at:   new Date().toISOString(),
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
