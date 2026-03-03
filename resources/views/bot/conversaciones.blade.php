<x-admin-layout title="Conversaciones del Bot">

{{-- Un solo componente Alpine cubre todo el layout --}}
<div class="flex gap-0 h-[calc(100vh-10rem)] bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
     x-data="botConversaciones()"
     x-init="init()">

    {{-- ── Panel izquierdo: lista de contactos ───────────────────────────── --}}
    <div class="w-80 flex-shrink-0 border-r border-gray-100 flex flex-col">

        {{-- Cabecera --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <span class="font-semibold text-gray-800 text-sm">Contactos</span>
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1 text-xs"
                      :class="conectado ? 'text-green-600' : 'text-amber-500'">
                    <span class="w-2 h-2 rounded-full"
                          :class="conectado ? 'bg-green-500 animate-pulse' : 'bg-amber-400 animate-pulse'"></span>
                    <span x-text="conectado ? 'EN VIVO' : 'Polling'"></span>
                </span>
                {{-- Borrar todo --}}
                <button @click="confirmarBorrarTodo()"
                        title="Borrar todas las conversaciones"
                        class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
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
            <div class="flex items-center gap-2">
                <a href="{{ route('bot.index') }}" class="text-xs text-blue-500 hover:underline">Configurar</a>
                @if(auth()->user()->hasRole('super_admin'))
                <button type="button"
                        @click="confirmarBorrarLogs()"
                        title="Limpiar archivo de logs"
                        class="text-xs text-red-400 hover:text-red-600 hover:underline">
                    Limpiar logs
                </button>
                @endif
            </div>
        </div>

        {{-- Lista de contactos --}}
        <div class="flex-1 overflow-y-auto">
            <template x-if="contactos.length === 0">
                <p class="text-center text-xs text-gray-400 py-10 px-4">
                    Sin conversaciones aún.<br>Los mensajes aparecerán aquí en tiempo real.
                </p>
            </template>

            <template x-for="c in contactosFiltrados" :key="c.phone">
                <div class="group relative border-b border-gray-50">
                    <button @click="seleccionar(c)"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors pr-10"
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
                    {{-- Botón eliminar contacto --}}
                    <button @click.stop="confirmarBorrarContacto(c)"
                            title="Eliminar conversación"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
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

        {{-- Sin contacto seleccionado --}}
        <template x-if="!contactoActivo">
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
        <template x-if="contactoActivo">
            <div class="flex flex-col h-full">

                {{-- Cabecera del chat --}}
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm"
                             x-text="(contactoActivo.contact_name || contactoActivo.phone).charAt(0).toUpperCase()">
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm"
                               x-text="contactoActivo.contact_name || contactoActivo.phone"></p>
                            <p class="text-xs text-gray-400"
                               x-text="contactoActivo.phone + ' · ' + contactoActivo.instancia"></p>
                        </div>
                    </div>
                    {{-- Borrar conversación activa --}}
                    <button @click="confirmarBorrarContacto(contactoActivo)"
                            title="Eliminar esta conversación"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Borrar conversación
                    </button>
                </div>

                {{-- Mensajes --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4 bg-gray-50"
                     id="chat-mensajes">

                    <template x-if="cargando">
                        <div class="flex justify-center py-8">
                            <svg class="animate-spin w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </div>
                    </template>

                    <template x-for="m in mensajes" :key="m.id">
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

{{-- Modal de confirmación --}}
<template x-teleport="body">
    <div x-data="confirmDialog()" @show-confirm.window="mostrar($event.detail)" x-show="abierto"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cancelar()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm" x-text="titulo"></p>
                    <p class="text-xs text-gray-500 mt-1" x-text="mensaje"></p>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button @click="cancelar()"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button @click="confirmar()"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</template>

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

        // ── Carga / refresca lista de contactos preservando indicador "nuevo" ──
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

        // ── Seleccionar contacto y cargar mensajes ──
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

        // ── Borrar conversación individual ──
        confirmarBorrarContacto(contacto) {
            window.dispatchEvent(new CustomEvent('show-confirm', {
                detail: {
                    titulo:  'Borrar conversación',
                    mensaje: `¿Eliminar todas las conversaciones de ${contacto.contact_name || contacto.phone}? Esta acción no se puede deshacer.`,
                    accion:  async () => { await this.borrarContacto(contacto); },
                },
            }));
        },

        // ── Borrar todas las conversaciones ──
        confirmarBorrarTodo() {
            window.dispatchEvent(new CustomEvent('show-confirm', {
                detail: {
                    titulo:  'Borrar todas las conversaciones',
                    mensaje: 'Se eliminarán TODAS las conversaciones del sistema. Esta acción no se puede deshacer.',
                    accion:  async () => { await this.borrarTodo(); },
                },
            }));
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
                alert('No se pudo eliminar la conversación.');
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
                alert('No se pudo eliminar las conversaciones.');
            }
        },

        // ── Limpiar logs (solo super_admin, método siempre existe) ──
        @if(auth()->user()->hasRole('super_admin'))
        confirmarBorrarLogs() {
            if (!confirm('¿Limpiar el archivo de logs del sistema? Esta acción no se puede deshacer.')) return;
            axios.delete('{{ route("admin.logs.clear") }}', {
                headers: { 'X-CSRF-TOKEN': this._csrf },
            }).then(() => alert('Logs limpiados correctamente.'))
              .catch(() => alert('No se pudo limpiar el archivo de logs.'));
        },
        @else
        confirmarBorrarLogs() {},
        @endif

        // ── WebSocket + polling fallback ──
        escucharEventos() {
            // Siempre arranca polling; se pausa si WS conecta
            this.iniciarPolling();

            if (typeof window.Echo === 'undefined') {
                console.warn('Laravel Echo no disponible. Usando polling cada 5 s.');
                return;
            }

            try {
                const tenantId = '{{ tenancy()->tenant?->getTenantKey() ?? "" }}';
                const canal    = tenantId ? `bot-tenant.${tenantId}` : 'bot-conversaciones';

                window.Echo.channel(canal)
                    .listen('.nuevo-mensaje', (data) => {
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
                // Refrescar mensajes del contacto activo si hay mensajes nuevos
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
            const idx     = this.contactos.findIndex(c => c.phone === data.phone);
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
                    id:            data.id,
                    user_message:  data.user_message,
                    bot_response:  data.bot_response,
                    created_at:    new Date().toISOString(),
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

function confirmDialog() {
    return {
        abierto:  false,
        titulo:   '',
        mensaje:  '',
        _accion:  null,

        mostrar(detail) {
            this.titulo  = detail.titulo;
            this.mensaje = detail.mensaje;
            this._accion = detail.accion;
            this.abierto = true;
        },

        async confirmar() {
            this.abierto = false;
            if (this._accion) await this._accion();
        },

        cancelar() {
            this.abierto = false;
            this._accion = null;
        },
    };
}
</script>

</x-admin-layout>
