<x-admin-layout title="Gestión de Negocios">

<div
    x-data="negociosPanel()"
    x-init="init()"
    class="max-w-6xl mx-auto space-y-6"
>

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Negocios</h2>
            <p class="text-sm text-gray-500 mt-0.5">Administra los tenants del sistema</p>
        </div>
        <button @click="abrirModalCrear()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700
                       text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Negocio
        </button>
    </div>

    {{-- ── Alertas de sesión ── --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Mensaje inline (Ajax) ── --}}
    <div x-show="mensaje" x-transition
         :class="error ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'"
         class="flex items-center gap-3 p-4 border rounded-xl text-sm">
        <span x-text="mensaje"></span>
    </div>

    {{-- ── Tabla de negocios ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Negocio</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Slug / BD</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Instancias</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Colaboradores</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Admin</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Creado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <template x-if="negocios.length === 0">
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                            <p class="font-medium">Sin negocios registrados</p>
                            <p class="text-xs mt-1">Crea el primero con el botón de arriba</p>
                        </td>
                    </tr>
                </template>
                <template x-for="n in negocios" :key="n.id">
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-semibold text-gray-900" x-text="n.nombre"></span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-0.5">
                                <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-700" x-text="n.slug"></code>
                                <code class="text-xs text-gray-400" x-text="n.db_name ?? ('tenant_' + n.slug)"></code>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-700">
                                <span x-text="n.instancias_count ?? 0"></span>
                                <span class="text-gray-400">/</span>
                                <span x-text="n.max_instances"></span>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-700">
                                <span x-text="n.colaboradores_count ?? 0"></span>
                                <span class="text-gray-400">/</span>
                                <span x-text="n.max_collaborators"></span>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <template x-if="n.admin">
                                <div class="flex flex-col">
                                    <span class="text-gray-800 font-medium text-xs" x-text="n.admin.name"></span>
                                    <span class="text-gray-400 text-xs" x-text="n.admin.email"></span>
                                </div>
                            </template>
                            <template x-if="!n.admin">
                                <span class="text-gray-300 text-xs italic">Sin admin</span>
                            </template>
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-400" x-text="n.created_at"></td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                <button @click="abrirModalEditar(n)" title="Editar"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="confirmarEliminar(n)" title="Eliminar"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL: Crear / Editar negocio
    ══════════════════════════════════════════════════ --}}
    <div x-show="modalAbierto" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900" x-text="editando ? 'Editar Negocio' : 'Nuevo Negocio'"></h3>
                <button @click="modalAbierto = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="guardar" class="px-6 py-5 space-y-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre del negocio <span class="text-red-500">*</span></label>
                    <input x-model="form.nombre" type="text" required maxlength="100" placeholder="Ej: Clínica Salud Total"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>

                <template x-if="!editando">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Slug (identificador único) <span class="text-red-500">*</span></label>
                            <input x-model="form.slug" type="text" required maxlength="60" placeholder="clinica-salud-total"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <p class="text-[11px] text-gray-400 mt-1">Solo letras minúsculas, números y guiones</p>
                        </div>

                        {{-- Botón rápido: usar mi propia cuenta --}}
                        <button type="button"
                                @click="usarMiCuenta = !usarMiCuenta; if(usarMiCuenta){ form.admin_email = miEmail; form.admin_password = ''; } else { form.admin_email = ''; }"
                                :class="usarMiCuenta ? 'bg-indigo-50 border-indigo-300 text-indigo-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                class="w-full flex items-center gap-2 px-3 py-2 border rounded-xl text-xs font-medium transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span x-text="usarMiCuenta ? '✓ Usando mi propia cuenta como admin' : 'Usar mi propia cuenta como admin'"></span>
                        </button>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Email admin <span class="text-red-500">*</span>
                            </label>
                            <input x-model="form.admin_email" type="email" required placeholder="admin@negocio.com"
                                   :readonly="usarMiCuenta"
                                   :class="usarMiCuenta ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : ''"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                            <p x-show="!usarMiCuenta" class="text-[11px] text-gray-400 mt-1">
                                Si el email ya existe, se reutilizará esa cuenta (sin necesitar contraseña)
                            </p>
                        </div>

                        <div x-show="!usarMiCuenta">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Contraseña
                                <span class="text-gray-400 font-normal">(solo para usuarios nuevos)</span>
                            </label>
                            <input x-model="form.admin_password" type="password" minlength="8" placeholder="••••••••"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Máx. instancias WhatsApp</label>
                        <input x-model.number="form.max_instances" type="number" min="1" max="20"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Máx. colaboradores</label>
                        <input x-model.number="form.max_collaborators" type="number" min="0" max="50"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
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
                        <span x-text="guardando ? 'Guardando…' : (editando ? 'Guardar cambios' : 'Crear negocio')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         MODAL: Confirmar eliminación
    ══════════════════════════════════════════════════ --}}
    <div x-show="modalEliminar" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
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
            <h3 class="text-base font-bold text-gray-900 mb-1">¿Eliminar negocio?</h3>
            <p class="text-sm text-gray-500 mb-1">
                Esto eliminará <strong x-text="negocioAEliminar?.nombre" class="text-gray-700"></strong>
                y todos sus datos de forma permanente.
            </p>
            <p class="text-xs text-red-500 font-medium mb-5">Esta acción no se puede deshacer.</p>
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
function negociosPanel() {
    return {
        negocios:        @json($negocios ?? []),
        miEmail:         @json(auth()->user()->email),
        modalAbierto:    false,
        modalEliminar:   false,
        editando:        false,
        guardando:       false,
        eliminando:      false,
        mensaje:         '',
        error:           false,
        errorModal:      '',
        negocioAEliminar: null,
        usarMiCuenta:    false,

        form: {
            nombre:            '',
            slug:              '',
            admin_email:       '',
            admin_password:    '',
            max_instances:     1,
            max_collaborators: 3,
        },
        editandoId: null,

        init() {},

        abrirModalCrear() {
            this.editando      = false;
            this.usarMiCuenta  = false;
            this.errorModal    = '';
            this.form = { nombre: '', slug: '', admin_email: '', admin_password: '', max_instances: 1, max_collaborators: 3 };
            this.modalAbierto  = true;
        },

        abrirModalEditar(n) {
            this.editando      = true;
            this.editandoId    = n.id;
            this.usarMiCuenta  = false;
            this.errorModal    = '';
            this.form = {
                nombre:            n.nombre,
                slug:              n.slug,
                max_instances:     n.max_instances,
                max_collaborators: n.max_collaborators,
            };
            this.modalAbierto = true;
        },

        confirmarEliminar(n) {
            this.negocioAEliminar = n;
            this.modalEliminar    = true;
        },

        async guardar() {
            this.guardando  = true;
            this.errorModal = '';

            const url    = this.editando
                ? `/admin/negocios/${this.editandoId}`
                : '/admin/negocios';
            const method = this.editando ? 'PUT' : 'POST';

            try {
                const res  = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':           'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const json = await res.json();

                if (!res.ok) {
                    this.errorModal = json.message ?? 'Error al guardar';
                    return;
                }

                if (this.editando) {
                    const idx = this.negocios.findIndex(n => n.id === this.editandoId);
                    if (idx !== -1) Object.assign(this.negocios[idx], json);
                } else {
                    this.negocios.unshift(json);
                }

                this.modalAbierto = false;
                this.mostrarMensaje('Negocio guardado correctamente', false);
            } catch {
                this.errorModal = 'Error de conexión';
            } finally {
                this.guardando = false;
            }
        },

        async eliminar() {
            if (!this.negocioAEliminar) return;
            this.eliminando = true;

            try {
                const res = await fetch(`/admin/negocios/${this.negocioAEliminar.id}`, {
                    method:  'DELETE',
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

                this.negocios     = this.negocios.filter(n => n.id !== this.negocioAEliminar.id);
                this.modalEliminar = false;
                this.mostrarMensaje('Negocio eliminado', false);
            } catch {
                this.mostrarMensaje('Error de conexión', true);
            } finally {
                this.eliminando = false;
            }
        },

        mostrarMensaje(txt, esError) {
            this.mensaje = txt;
            this.error   = esError;
            setTimeout(() => { this.mensaje = ''; }, 4000);
        },
    };
}
</script>
@endpush

</x-admin-layout>
