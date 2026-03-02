<x-admin-layout title="Colaboradores">

<div
    x-data="colaboradoresPanel()"
    x-init="init()"
    class="max-w-4xl mx-auto space-y-6"
>

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Colaboradores</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Usuarios con acceso a tu negocio
                <span class="ml-2 inline-flex items-center gap-1 font-medium"
                      :class="total >= maxColabs ? 'text-red-500' : 'text-gray-700'">
                    (<span x-text="total"></span>/<span x-text="maxColabs"></span>)
                </span>
            </p>
        </div>
        <button @click="abrirModal()"
                :disabled="total >= maxColabs"
                :title="total >= maxColabs ? 'Has alcanzado el límite de colaboradores' : 'Invitar colaborador'"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700
                       text-white text-sm font-medium rounded-lg transition-colors shadow-sm
                       disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Invitar colaborador
        </button>
    </div>

    {{-- ── Alerta límite ── --}}
    <div x-show="total >= maxColabs"
         class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Has alcanzado el límite de <strong x-text="maxColabs"></strong> colaborador(es). Contacta al administrador para aumentar el límite.</span>
    </div>

    {{-- ── Mensaje inline ── --}}
    <div x-show="mensaje" x-transition
         :class="esError ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'"
         class="flex items-center gap-3 p-4 border rounded-xl text-sm">
        <span x-text="mensaje"></span>
    </div>

    {{-- ── Tabla ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Nombre</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Usuario</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Registrado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <template x-if="colaboradores.length === 0">
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="font-medium">Sin colaboradores aún</p>
                            <p class="text-xs mt-1">Invita a tu primer colaborador con el botón de arriba</p>
                        </td>
                    </tr>
                </template>
                <template x-for="c in colaboradores" :key="c.id">
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-blue-600 font-semibold text-xs" x-text="c.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <span class="font-medium text-gray-900" x-text="c.name"></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <code class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600" x-text="'@' + c.username"></code>
                        </td>
                        <td class="px-5 py-4 text-gray-500" x-text="c.email"></td>
                        <td class="px-4 py-4 text-xs text-gray-400" x-text="c.created_at"></td>
                        <td class="px-4 py-4">
                            <button @click="confirmarEliminar(c)"
                                    title="Eliminar colaborador"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL: Invitar colaborador
    ══════════════════════════════════════════════════ --}}
    <div x-show="modalAbierto" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Invitar colaborador</h3>
                <button @click="modalAbierto = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="guardar" class="px-6 py-5 space-y-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                    <input x-model="form.name"
                           @input="autoUsername()"
                           type="text" required maxlength="100" placeholder="Ej: María García"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre de usuario <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                        <input x-model="form.username" type="text" required maxlength="60"
                               placeholder="maria-garcia"
                               class="w-full border border-gray-200 rounded-xl pl-7 pr-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Se genera automáticamente, puedes editarlo</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input x-model="form.email" type="email" required placeholder="colaborador@ejemplo.com"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Contraseña <span class="text-red-500">*</span></label>
                    <input x-model="form.password" type="password" required minlength="8" placeholder="Mínimo 8 caracteres"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <div x-show="errorModal" x-text="errorModal"
                     class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5"></div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="modalAbierto = false"
                            class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="guardando"
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-60 flex items-center justify-center gap-2">
                        <svg x-show="guardando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="guardando ? 'Guardando…' : 'Invitar colaborador'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL: Confirmar eliminación
    ══════════════════════════════════════════════════ --}}
    <div x-show="modalEliminar" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">¿Eliminar colaborador?</h3>
            <p class="text-sm text-gray-500 mb-5">
                <strong x-text="colaAEliminar?.name" class="text-gray-700"></strong>
                perderá acceso al sistema.
            </p>
            <div class="flex gap-3">
                <button @click="modalEliminar = false"
                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button @click="eliminar()" :disabled="eliminando"
                        class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold transition-colors disabled:opacity-60 flex items-center justify-center gap-2">
                    <svg x-show="eliminando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span x-text="eliminando ? 'Eliminando…' : 'Sí, eliminar'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function colaboradoresPanel() {
    return {
        colaboradores:  @json($colaboradores ?? []),
        maxColabs:      {{ $max_collaborators ?? 3 }},
        total:          {{ $total ?? 0 }},

        modalAbierto:   false,
        modalEliminar:  false,
        guardando:      false,
        eliminando:     false,
        mensaje:        '',
        esError:        false,
        errorModal:     '',
        colaAEliminar:  null,

        form: { name: '', username: '', email: '', password: '' },

        init() {},

        autoUsername() {
            // Genera username automáticamente a partir del nombre
            this.form.username = this.form.name
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .substring(0, 60);
        },

        abrirModal() {
            this.errorModal = '';
            this.form = { name: '', username: '', email: '', password: '' };
            this.modalAbierto = true;
        },

        confirmarEliminar(c) {
            this.colaAEliminar = c;
            this.modalEliminar = true;
        },

        async guardar() {
            this.guardando  = true;
            this.errorModal = '';

            try {
                const res  = await fetch('/colaboradores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const json = await res.json();

                if (!res.ok) {
                    // Mostrar primer error de validación o mensaje genérico
                    if (json.errors) {
                        const primerError = Object.values(json.errors)[0];
                        this.errorModal = Array.isArray(primerError) ? primerError[0] : primerError;
                    } else {
                        this.errorModal = json.message ?? 'Error al guardar';
                    }
                    return;
                }

                this.colaboradores.push({
                    id:         json.id,
                    name:       json.name,
                    username:   json.username ?? this.form.username,
                    email:      json.email,
                    created_at: 'Hoy',
                });
                this.total++;
                this.modalAbierto = false;
                this.mostrarMensaje('Colaborador invitado correctamente', false);
            } catch {
                this.errorModal = 'Error de conexión';
            } finally {
                this.guardando = false;
            }
        },

        async eliminar() {
            if (!this.colaAEliminar) return;
            this.eliminando = true;

            try {
                const res = await fetch(`/colaboradores/${this.colaAEliminar.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                });

                if (!res.ok) {
                    const json = await res.json();
                    this.mostrarMensaje(json.message ?? 'Error al eliminar', true);
                    return;
                }

                this.colaboradores = this.colaboradores.filter(c => c.id !== this.colaAEliminar.id);
                this.total--;
                this.modalEliminar = false;
                this.mostrarMensaje('Colaborador eliminado', false);
            } catch {
                this.mostrarMensaje('Error de conexión', true);
            } finally {
                this.eliminando = false;
            }
        },

        mostrarMensaje(txt, esError) {
            this.mensaje = txt;
            this.esError = esError;
            setTimeout(() => { this.mensaje = ''; }, 4000);
        },
    };
}
</script>
@endpush

</x-admin-layout>
