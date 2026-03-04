<x-admin-layout title="Módulos (no-code)">

    <div x-data="modulosAdmin()" x-init="cargar()">
        <div>

            {{-- Notificación flash --}}
            <div x-show="flash.msg" x-cloak
                 :class="flash.ok ? 'bg-green-500/10 border-green-400 text-green-400' : 'bg-red-500/10 border-red-400 text-red-400'"
                 class="mb-4 border px-4 py-3 rounded flex justify-between items-center">
                <span x-text="flash.msg"></span>
                <button @click="flash.msg=''" class="ml-4 font-bold">✕</button>
            </div>

            <div class="flex gap-6">

                {{-- ═══ COLUMNA IZQUIERDA: Lista de módulos ═══ --}}
                <div class="w-80 flex-shrink-0">
                    <div class="bg-gray-900 dark:bg-gray-800 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold text-gray-200 dark:text-gray-300">Módulos</h3>
                            <button @click="abrirModalModulo(null)"
                                    class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-500">
                                + Nuevo
                            </button>
                        </div>

                        <div class="space-y-1">
                            <template x-for="mod in modulos" :key="mod.id">
                                <div @click="seleccionar(mod)"
                                     :class="moduloActivo?.id === mod.id
                                         ? 'bg-indigo-500/10 dark:bg-indigo-900 border-indigo-500/30'
                                         : 'border-transparent hover:bg-white/5 dark:hover:bg-gray-700'"
                                     class="flex items-center justify-between p-2 rounded-lg border cursor-pointer transition">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl" x-text="mod.icono || '📋'"></span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-100 dark:text-gray-200" x-text="mod.nombre"></div>
                                            <div class="text-xs text-gray-500" x-text="mod.campos_count + ' campo(s)'"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span :class="mod.activo ? 'bg-green-500/10 text-green-400' : 'bg-gray-800 text-gray-500'"
                                              class="text-xs px-1.5 py-0.5 rounded-full"
                                              x-text="mod.activo ? 'activo' : 'oculto'"></span>
                                        <button @click.stop="abrirModalModulo(mod)"
                                                class="text-gray-600 hover:text-indigo-400 p-1 rounded">✏️</button>
                                        <button @click.stop="eliminarModulo(mod)"
                                                class="text-gray-600 hover:text-red-400 p-1 rounded">🗑️</button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="modulos.length === 0" class="text-center text-gray-500 text-sm py-4">
                                No hay módulos. Crea el primero.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ COLUMNA DERECHA: Campos del módulo seleccionado ═══ --}}
                <div class="flex-1">
                    <template x-if="moduloActivo">
                        <div class="bg-gray-900 dark:bg-gray-800 rounded-xl p-4">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl" x-text="moduloActivo.icono || '📋'"></span>
                                    <div>
                                        <h3 class="font-semibold text-gray-100 dark:text-gray-200" x-text="moduloActivo.nombre"></h3>
                                        <code class="text-xs text-gray-500" x-text="'/' + moduloActivo.slug"></code>
                                    </div>
                                </div>
                                <button @click="abrirModalCampo(null)"
                                        class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700">
                                    + Agregar campo
                                </button>
                            </div>

                            {{-- Tabla de campos --}}
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 border-b dark:border-gray-700">
                                        <th class="pb-2 w-6"></th>
                                        <th class="pb-2 font-medium">Nombre</th>
                                        <th class="pb-2 font-medium">Slug</th>
                                        <th class="pb-2 font-medium">Tipo</th>
                                        <th class="pb-2 font-medium">Obligatorio</th>
                                        <th class="pb-2 font-medium">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(campo, idx) in campos" :key="campo.id">
                                        <tr draggable="true"
                                            @dragstart="onDragStart($event, idx)"
                                            @dragover.prevent="onDragOver($event, idx)"
                                            @dragleave="dragOverIdx = null"
                                            @drop.prevent="onDrop($event, idx)"
                                            @dragend="dragSrcIdx = null; dragOverIdx = null"
                                            :class="{
                                                'opacity-40': dragSrcIdx === idx,
                                                'border-t-2 border-indigo-500': dragOverIdx === idx && dragSrcIdx !== idx
                                            }"
                                            class="border-b dark:border-gray-700 hover:bg-white/5 dark:hover:bg-gray-750">
                                            <td class="py-2 pr-1 text-gray-600 hover:text-gray-500 cursor-grab active:cursor-grabbing select-none text-base leading-none" title="Arrastrar para reordenar">⠿</td>
                                            <td class="py-2 font-medium text-gray-100 dark:text-gray-200" x-text="campo.nombre"></td>
                                            <td class="py-2"><code class="text-xs bg-gray-800 dark:bg-gray-700 px-1.5 py-0.5 rounded" x-text="campo.slug"></code></td>
                                            <td class="py-2">
                                                <span class="inline-block text-xs bg-indigo-500/15 text-indigo-300 dark:bg-indigo-900 dark:text-indigo-300 px-2 py-0.5 rounded-full" x-text="campo.tipo"></span>
                                            </td>
                                            <td class="py-2">
                                                <span :class="campo.obligatorio ? 'text-red-400' : 'text-gray-600'"
                                                      x-text="campo.obligatorio ? '✓ Sí' : 'No'"></span>
                                            </td>
                                            <td class="py-2 flex gap-2">
                                                <button @click="abrirModalCampo(campo)"
                                                        class="text-gray-600 hover:text-indigo-400">✏️</button>
                                                <button @click="eliminarCampo(campo)"
                                                        class="text-gray-600 hover:text-red-400">🗑️</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="campos.length === 0">
                                        <td colspan="6" class="py-6 text-center text-gray-500">
                                            Este módulo no tiene campos. Agrega el primero.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="mt-4 pt-3 border-t dark:border-gray-700 text-xs text-gray-500">
                                💡 Los campos definen las columnas del catálogo. Puedes usar tipos: text, number, date, select, relation, email, phone, textarea.
                            </div>
                        </div>
                    </template>

                    <div x-show="!moduloActivo" class="text-center py-20 text-gray-500">
                        <div class="text-5xl mb-3">📋</div>
                        <p>Selecciona un módulo de la lista o crea uno nuevo.</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══ MODAL: Módulo ═══ --}}
        <div x-show="modalModulo" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
             @click.self="modalModulo=false">
            <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-100 dark:text-gray-200 mb-4"
                    x-text="formModulo.id ? 'Editar módulo' : 'Nuevo módulo'"></h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Nombre *</label>
                        <input type="text" x-model="formModulo.nombre" placeholder="Ej: Agenda de Contactos"
                               class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Ícono (emoji)</label>
                            <input type="text" x-model="formModulo.icono" placeholder="📋" maxlength="4"
                                   class="w-full border border-white/10 rounded-lg px-3 py-2 text-2xl bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none text-center">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Color</label>
                            <input type="color" x-model="formModulo.color"
                                   class="w-full h-10 border border-white/10 rounded-lg cursor-pointer">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="formModulo.activo" id="mod-activo"
                               class="rounded border-white/15 text-indigo-600">
                        <label for="mod-activo" class="text-sm text-gray-200 dark:text-gray-300">Visible en el menú</label>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <button @click="modalModulo=false"
                            class="px-4 py-2 text-sm text-gray-400 border border-white/10 rounded-lg hover:bg-white/5">
                        Cancelar
                    </button>
                    <button @click="guardarModulo()"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-500">
                        <span x-text="formModulo.id ? 'Guardar cambios' : 'Crear módulo'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ MODAL: Campo ═══ --}}
        <div x-show="modalCampo" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
             @click.self="modalCampo=false">
            <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-100 dark:text-gray-200 mb-4"
                    x-text="formCampo.id ? 'Editar campo' : 'Nuevo campo'"></h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Nombre del campo *</label>
                        <input type="text" x-model="formCampo.nombre" placeholder="Ej: Teléfono de contacto"
                               class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Tipo *</label>
                        <select x-model="formCampo.tipo"
                                class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="date">Fecha</option>
                            <option value="email">Email</option>
                            <option value="phone">Teléfono</option>
                            <option value="textarea">Texto largo</option>
                            <option value="url">URL / Enlace</option>
                            <option value="file">Archivo / Imagen / Video</option>
                            <option value="id">Identificador (Folio / UUID / Secuencial)</option>
                            <option value="tags">Etiquetas (tags)</option>
                            <option value="select">Selección única (lista)</option>
                            <option value="multiselect">Selección múltiple (lista)</option>
                            <option value="category_select">Selección por categoría (con ítems)</option>
                            <option value="relation">Relación (otro módulo)</option>
                        </select>
                    </div>

                    {{-- Opciones para tipo "select" y "multiselect" --}}
                    <template x-if="formCampo.tipo === 'select' || formCampo.tipo === 'multiselect'">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">
                                Opciones (una por línea)
                            </label>
                            <textarea x-model="formCampo.opciones_texto" rows="3"
                                      placeholder="Opción A&#10;Opción B&#10;Opción C"
                                      class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                        </div>
                    </template>

                    {{-- Opciones para tipo "category_select" --}}
                    <template x-if="formCampo.tipo === 'category_select'">
                        <div>
                            <label class="block text-sm font-medium text-gray-200 mb-1">
                                Categorías e ítems
                            </label>
                            <textarea x-model="formCampo.opciones_texto" rows="5"
                                      placeholder="Electrónica: Celular, Laptop, Tablet&#10;Ropa: Camisa, Pantalón, Zapatos&#10;Alimentos: Fruta, Verdura"
                                      class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Formato: <code class="text-gray-400">Categoría: ítem1, ítem2, ítem3</code> — una categoría por línea.</p>
                        </div>
                    </template>

                    {{-- Módulo relacionado para tipo "relation" --}}
                    <template x-if="formCampo.tipo === 'relation'">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">
                                    Módulo relacionado
                                </label>
                                <select x-model="formCampo.modulo_relacion"
                                        class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">-- Selecciona módulo --</option>
                                    <template x-for="mod in modulos" :key="mod.slug">
                                        <option :value="mod.slug" x-text="mod.icono + ' ' + mod.nombre"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="formCampo.meta_multiple" id="campo-multiple"
                                       class="rounded border-white/15 text-indigo-600">
                                <label for="campo-multiple" class="text-sm text-gray-200 dark:text-gray-300">
                                    Permitir selección múltiple
                                </label>
                            </div>
                        </div>
                    </template>

                    {{-- Configuración para tipo "file" --}}
                    <template x-if="formCampo.tipo === 'file'">
                        <div class="space-y-3 p-3 bg-gray-950 dark:bg-gray-700/50 rounded-lg border border-white/10 dark:border-gray-600">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Configuración del archivo</p>
                            <div>
                                <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Tipos permitidos</label>
                                <select x-model="formCampo.meta_accept"
                                        class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="all">Imágenes, videos y documentos</option>
                                    <option value="image">Solo imágenes (jpg, png, gif, webp…)</option>
                                    <option value="video">Solo videos (mp4, mov, webm…)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Tamaño máximo (MB)</label>
                                <input type="number" x-model="formCampo.meta_max_mb" min="1" max="200" step="1"
                                       class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="10">
                            </div>
                        </div>
                    </template>

                    {{-- Configuración para tipo "id" --}}
                    <template x-if="formCampo.tipo === 'id'">
                        <div class="space-y-3 p-3 bg-gray-950 dark:bg-gray-700/50 rounded-lg border border-white/10 dark:border-gray-600">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Configuración del identificador</p>
                            <div>
                                <label class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-1">Formato</label>
                                <select x-model="formCampo.meta_tipo_id"
                                        class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="folio">Folio personalizado (ej: ASD-0001)</option>
                                    <option value="autoincrement">Número secuencial (1, 2, 3…)</option>
                                    <option value="uuid">UUID (ej: a1b2c3d4-…)</option>
                                </select>
                            </div>

                            {{-- Opciones solo para folio --}}
                            <template x-if="formCampo.meta_tipo_id === 'folio'">
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-400 mb-1">Prefijo</label>
                                            <input type="text" x-model="formCampo.meta_folio_prefijo"
                                                   maxlength="20" placeholder="ASD"
                                                   class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-400 mb-1">Separador</label>
                                            <input type="text" x-model="formCampo.meta_folio_separador"
                                                   maxlength="5" placeholder="-"
                                                   class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1">Número de cifras</label>
                                        <input type="number" x-model="formCampo.meta_folio_cifras"
                                               min="1" max="10" step="1" placeholder="4"
                                               class="w-full border border-white/10 rounded-lg px-3 py-2 text-sm bg-gray-800 text-gray-100 placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    </div>
                                    <div class="pt-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Vista previa:
                                            <code class="bg-gray-700 dark:bg-gray-600 px-1.5 py-0.5 rounded text-xs font-mono"
                                                  x-text="(formCampo.meta_folio_prefijo || 'PRE') + (formCampo.meta_folio_separador ?? '-') + '0'.repeat(Math.max(0, (parseInt(formCampo.meta_folio_cifras) || 4) - 1)) + '1'">
                                            </code>
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <p class="text-xs text-gray-600">
                                El valor se genera automáticamente al crear un registro. No puede editarse.
                            </p>
                        </div>
                    </template>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="formCampo.obligatorio" id="campo-obligatorio"
                               class="rounded border-white/15 text-indigo-600">
                        <label for="campo-obligatorio" class="text-sm text-gray-200 dark:text-gray-300">Campo obligatorio</label>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <button @click="modalCampo=false"
                            class="px-4 py-2 text-sm text-gray-400 border border-white/10 rounded-lg hover:bg-white/5">
                        Cancelar
                    </button>
                    <button @click="guardarCampo()"
                            class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <span x-text="formCampo.id ? 'Guardar cambios' : 'Agregar campo'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function modulosAdmin() {
        return {
            modulos: [],
            moduloActivo: null,
            campos: [],
            modalModulo: false,
            modalCampo: false,
            flash: { msg: '', ok: true },
            dragSrcIdx: null,
            dragOverIdx: null,
            formModulo: { id: null, nombre: '', icono: '📋', color: '#6366f1', activo: true },
            formCampo:  { id: null, nombre: '', tipo: 'text', obligatorio: false, opciones_texto: '', modulo_relacion: '', meta_accept: 'all', meta_max_mb: 10, meta_multiple: false, meta_tipo_id: 'folio', meta_folio_prefijo: '', meta_folio_separador: '-', meta_folio_cifras: 4 },

            async cargar() {
                const res = await fetch('/admin/modulos', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                this.modulos = data;
                if (this.moduloActivo) {
                    const updated = data.find(m => m.id === this.moduloActivo.id);
                    if (updated) this.seleccionar(updated);
                }
            },

            async seleccionar(mod) {
                this.moduloActivo = mod;
                const res = await fetch(`/admin/modulos`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                const found = data.find(m => m.id === mod.id);
                this.campos = found ? found.campos : [];
            },

            abrirModalModulo(mod) {
                if (mod) {
                    this.formModulo = { id: mod.id, nombre: mod.nombre, icono: mod.icono || '📋', color: mod.color || '#6366f1', activo: !!mod.activo };
                } else {
                    this.formModulo = { id: null, nombre: '', icono: '📋', color: '#6366f1', activo: true };
                }
                this.modalModulo = true;
            },

            async guardarModulo() {
                const url    = this.formModulo.id ? `/admin/modulos/${this.formModulo.id}` : '/admin/modulos';
                const method = this.formModulo.id ? 'PUT' : 'POST';
                try {
                    const res = await fetch(url, {
                        method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(this.formModulo),
                    });
                    if (!res.ok) throw await res.json();
                    this.modalModulo = false;
                    this.mostrarFlash('Módulo guardado correctamente.', true);
                    await this.cargar();
                } catch (e) {
                    this.mostrarFlash(e.message || 'Error al guardar.', false);
                }
            },

            async eliminarModulo(mod) {
                if (!confirm(`¿Eliminar el módulo "${mod.nombre}" y todos sus registros? Esta acción no se puede deshacer.`)) return;
                const res = await fetch(`/admin/modulos/${mod.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                if (res.ok) {
                    this.moduloActivo = null;
                    this.campos = [];
                    this.mostrarFlash('Módulo eliminado.', true);
                    await this.cargar();
                }
            },

            abrirModalCampo(campo) {
                if (campo) {
                    this.formCampo = {
                        id: campo.id,
                        nombre: campo.nombre,
                        tipo: campo.tipo,
                        obligatorio: !!campo.obligatorio,
                        opciones_texto: Array.isArray(campo.opciones)
                            ? (campo.opciones || []).join('\n')
                            : (campo.opciones && typeof campo.opciones === 'object')
                                ? Object.entries(campo.opciones).map(([cat, items]) => `${cat}: ${items.join(', ')}`).join('\n')
                                : '',
                        modulo_relacion: campo.modulo_relacion || '',
                        meta_accept:           campo.meta?.accept           || 'all',
                        meta_max_mb:           campo.meta?.max_mb           || 10,
                        meta_multiple:         campo.meta?.multiple         ?? false,
                        meta_tipo_id:          campo.meta?.tipo_id          || 'folio',
                        meta_folio_prefijo:    campo.meta?.folio_prefijo    ?? '',
                        meta_folio_separador:  campo.meta?.folio_separador  ?? '-',
                        meta_folio_cifras:     campo.meta?.folio_cifras     ?? 4,
                    };
                } else {
                    this.formCampo = { id: null, nombre: '', tipo: 'text', obligatorio: false, opciones_texto: '', modulo_relacion: '', meta_accept: 'all', meta_max_mb: 10, meta_multiple: false, meta_tipo_id: 'folio', meta_folio_prefijo: '', meta_folio_separador: '-', meta_folio_cifras: 4 };
                }
                this.modalCampo = true;
            },

            async guardarCampo() {
                const payload = {
                    nombre: this.formCampo.nombre,
                    tipo: this.formCampo.tipo,
                    obligatorio: this.formCampo.obligatorio,
                    opciones: ['select', 'multiselect'].includes(this.formCampo.tipo)
                        ? this.formCampo.opciones_texto.split('\n').map(s => s.trim()).filter(Boolean)
                        : this.formCampo.tipo === 'category_select'
                            ? (() => {
                                const obj = {};
                                this.formCampo.opciones_texto.split('\n').forEach(line => {
                                    const colon = line.indexOf(':');
                                    if (colon === -1) return;
                                    const cat   = line.slice(0, colon).trim();
                                    const items = line.slice(colon + 1).split(',').map(s => s.trim()).filter(Boolean);
                                    if (cat && items.length) obj[cat] = items;
                                });
                                return obj;
                              })()
                            : null,
                    modulo_relacion: this.formCampo.tipo === 'relation' ? this.formCampo.modulo_relacion : null,
                    meta: this.formCampo.tipo === 'file'
                        ? { accept: this.formCampo.meta_accept || 'all', max_mb: parseInt(this.formCampo.meta_max_mb) || 10 }
                        : this.formCampo.tipo === 'relation'
                            ? { multiple: !!this.formCampo.meta_multiple }
                            : this.formCampo.tipo === 'id'
                                ? {
                                    tipo_id: this.formCampo.meta_tipo_id || 'folio',
                                    ...(this.formCampo.meta_tipo_id === 'folio' ? {
                                        folio_prefijo:   this.formCampo.meta_folio_prefijo   ?? '',
                                        folio_separador: this.formCampo.meta_folio_separador ?? '-',
                                        folio_cifras:    parseInt(this.formCampo.meta_folio_cifras) || 4,
                                    } : {}),
                                  }
                                : null,
                };
                const url    = this.formCampo.id
                    ? `/admin/modulos/${this.moduloActivo.id}/campos/${this.formCampo.id}`
                    : `/admin/modulos/${this.moduloActivo.id}/campos`;
                const method = this.formCampo.id ? 'PUT' : 'POST';
                try {
                    const res = await fetch(url, {
                        method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) throw await res.json();
                    this.modalCampo = false;
                    this.mostrarFlash('Campo guardado correctamente.', true);
                    await this.seleccionar(this.moduloActivo);
                    await this.cargar();
                } catch (e) {
                    this.mostrarFlash(e.message || 'Error al guardar el campo.', false);
                }
            },

            async eliminarCampo(campo) {
                if (!confirm(`¿Eliminar el campo "${campo.nombre}"? Los registros existentes perderán este dato.`)) return;
                const res = await fetch(`/admin/modulos/${this.moduloActivo.id}/campos/${campo.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                });
                if (res.ok) {
                    this.mostrarFlash('Campo eliminado.', true);
                    await this.seleccionar(this.moduloActivo);
                    await this.cargar();
                }
            },

            // ── Drag & drop para reordenar campos ────────────────────────────────
            onDragStart(event, idx) {
                this.dragSrcIdx = idx;
                event.dataTransfer.effectAllowed = 'move';
            },

            onDragOver(event, idx) {
                this.dragOverIdx = idx;
            },

            onDrop(event, idx) {
                if (this.dragSrcIdx === null || this.dragSrcIdx === idx) {
                    this.dragSrcIdx = null;
                    this.dragOverIdx = null;
                    return;
                }
                // Reorder in memory
                const moved = this.campos.splice(this.dragSrcIdx, 1)[0];
                this.campos.splice(idx, 0, moved);
                this.dragSrcIdx = null;
                this.dragOverIdx = null;
                // Persist new order
                this.reordenarCampos();
            },

            async reordenarCampos() {
                const ids = this.campos.map(c => c.id);
                try {
                    const res = await fetch(`/admin/modulos/${this.moduloActivo.id}/campos/reorder`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids }),
                    });
                    if (!res.ok) throw await res.json();
                    this.mostrarFlash('Orden de campos guardado.', true);
                } catch (e) {
                    this.mostrarFlash('Error al guardar el orden.', false);
                    // Reload to restore server order
                    await this.seleccionar(this.moduloActivo);
                }
            },

            mostrarFlash(msg, ok) {
                this.flash = { msg, ok };
                setTimeout(() => this.flash.msg = '', 4000);
            },
        };
    }
    </script>
    @endpush
</x-admin-layout>
