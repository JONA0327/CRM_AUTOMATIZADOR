<!-- Modal para Crear/Editar Producto -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto product-modal-content">
        <form id="productForm" enctype="multipart/form-data" class="flex flex-col min-h-full">
            @csrf
            <input type="hidden" id="productId" name="product_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="text-xl font-semibold text-gray-900">Crear nuevo producto</h3>
                        <p class="text-sm text-gray-600">Completa la información del catálogo para mantenerlo actualizado.</p>
                    </div>
                </div>
                <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Cerrar modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cuerpo -->
            <div class="px-6 py-6 space-y-8 product-modal-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Categoría <span class="text-red-500">*</span></label>
                            <select id="category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Seleccione una categoría</option>
                            </select>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Producto <span class="text-red-500">*</span></label>
                            <select id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm disabled:opacity-60" disabled>
                                <option value="">Seleccione primero una categoría</option>
                            </select>
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">País <span class="text-red-500">*</span></label>
                            <select id="country" name="country" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Seleccione un país</option>
                            </select>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700">Puntos clave</label>
                                <button type="button" id="addKeyPointBtn" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">+ {{ __('Agregar punto clave') }}</button>
                            </div>
                            <div id="keyPointsContainer" class="space-y-3">
                                <div class="key-point-row">
                                    <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <button type="button" class="add-key-point p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center" aria-label="Agregar punto clave">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="information" class="block text-sm font-medium text-gray-700 mb-2">Información del producto</label>
                            <textarea id="information" name="information" rows="4" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none" placeholder="Describa el producto, sus beneficios, composición, etc."></textarea>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-3">
                            <label for="image" class="block text-sm font-medium text-gray-700">Imagen del producto</label>
                            <div class="border border-dashed border-gray-300 rounded-xl p-6 text-center">
                                <input type="file" id="image" name="image" accept="image/*" class="hidden">
                                <div id="imagePreview" class="hidden space-y-4">
                                    <img id="imagePreviewImg" src="" alt="Vista previa" class="media-preview rounded-lg">
                                    <button type="button" id="removeImage" class="text-sm font-medium text-red-600 hover:text-red-700">{{ __('Eliminar imagen') }}</button>
                                </div>
                                <div id="imagePlaceholder" class="space-y-3">
                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Seleccione una imagen (máx. 150MB)</p>
                                    <button type="button" id="selectImageBtn" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-medium transition-colors">{{ __('Seleccionar archivo') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label for="video" class="block text-sm font-medium text-gray-700">Video del producto (opcional)</label>
                            <div class="border border-dashed border-gray-300 rounded-xl p-6 text-center">
                                <input type="file" id="video" name="video" accept="video/*" class="hidden">
                                <div id="videoPreview" class="hidden space-y-4">
                                    <video id="videoPreviewPlayer" controls class="media-preview media-preview--video rounded-lg"></video>
                                    <p id="videoFileName" class="text-sm text-gray-500"></p>
                                    <button type="button" id="removeVideo" class="text-sm font-medium text-red-600 hover:text-red-700">{{ __('Eliminar video') }}</button>
                                </div>
                                <div id="videoPlaceholder" class="space-y-3">
                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Seleccione un video (máx. 150MB)</p>
                                    <button type="button" id="selectVideoBtn" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg font-medium transition-colors">{{ __('Seleccionar video') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="disease" class="block text-sm font-medium text-gray-700">Enfermedad asociada (opcional)</label>
                            <input type="text" id="disease" name="disease" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Ej: Diabetes, Hipertensión, etc.">
                            <p class="text-xs text-gray-500">Este campo estará disponible cuando se implemente el índice de enfermedades.</p>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-700">Dosis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <label for="dosage_preventivo" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Preventivo</label>
                                    <input type="text" id="dosage_preventivo" name="dosage_preventivo" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Ej: 1 cápsula al día">
                                </div>
                                <div class="space-y-2">
                                    <label for="dosage_correctivo" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Correctivo</label>
                                    <input type="text" id="dosage_correctivo" name="dosage_correctivo" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Ej: 2 cápsulas cada 8 horas">
                                </div>
                                <div class="space-y-2">
                                    <label for="dosage_cronico" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Crónico</label>
                                    <input type="text" id="dosage_cronico" name="dosage_cronico" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Ej: 3 cápsulas cada 6 horas">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg flex justify-end gap-3">
                <button type="button" id="cancelBtn" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">{{ __('Cancelar') }}</button>
                <button type="submit" id="saveProductBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('Guardar producto') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Variables globales para el modal
let productCatalog = {};
let americanCountries = [];
let isEditing = false;

// Event listeners del modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const categorySelect = document.getElementById('category');
    const nameSelect = document.getElementById('name');

    // Cerrar modal
    closeBtn?.addEventListener('click', closeProductModal);
    cancelBtn?.addEventListener('click', closeProductModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeProductModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal?.classList.contains('hidden')) {
            closeProductModal();
        }
    });

    // Cambio de categoría
    categorySelect?.addEventListener('change', function() {
        loadProductsByCategory(this.value);
    });

    // Manejo de archivos
    setupFileHandlers();

    // Puntos clave dinámicos
    setupKeyPointsHandlers();

    // Submit del formulario
    form?.addEventListener('submit', handleFormSubmit);

    // Cargar datos iniciales
    loadInitialData();
});

function loadInitialData() {
    fetch('/products/create')
        .then(response => response.json())
        .then(data => {
            productCatalog = data.catalog;
            americanCountries = data.countries;

            // Cargar categorías
            const categorySelect = document.getElementById('category');
            categorySelect.innerHTML = '<option value="">Seleccione una categoría</option>';
            Object.keys(productCatalog).forEach(category => {
                categorySelect.innerHTML += `<option value="${category}">${category}</option>`;
            });

            // Cargar países
            const countrySelect = document.getElementById('country');
            countrySelect.innerHTML = '<option value="">Seleccione un país</option>';
            americanCountries.forEach(country => {
                countrySelect.innerHTML += `<option value="${country}">${country}</option>`;
            });
        })
        .catch(error => console.error('Error loading data:', error));
}

function loadProductsByCategory(category) {
    const nameSelect = document.getElementById('name');

    if (!category) {
        nameSelect.innerHTML = '<option value="">Seleccione primero una categoría</option>';
        nameSelect.disabled = true;
        return;
    }

    nameSelect.disabled = false;
    nameSelect.innerHTML = '<option value="">Seleccione un producto</option>';

    if (productCatalog[category]) {
        productCatalog[category].forEach(product => {
            nameSelect.innerHTML += `<option value="${product}">${product}</option>`;
        });
    }
}

function setupFileHandlers() {
    // Imagen
    const imageInput = document.getElementById('image');
    const selectImageBtn = document.getElementById('selectImageBtn');
    const imagePreview = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const removeImageBtn = document.getElementById('removeImage');

    selectImageBtn?.addEventListener('click', () => imageInput.click());

    imageInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 157286400) { // 150MB en bytes
                alert('La imagen es demasiado grande. Máximo 150MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreviewImg').src = e.target.result;
                imagePreview.classList.remove('hidden');
                imagePlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    removeImageBtn?.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.classList.add('hidden');
        imagePlaceholder.classList.remove('hidden');
    });

    // Video
    const videoInput = document.getElementById('video');
    const selectVideoBtn = document.getElementById('selectVideoBtn');
    const videoPreview = document.getElementById('videoPreview');
    const videoPlaceholder = document.getElementById('videoPlaceholder');
    const removeVideoBtn = document.getElementById('removeVideo');

    selectVideoBtn?.addEventListener('click', () => videoInput.click());

    videoInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 157286400) { // 150MB en bytes
                alert('El video es demasiado grande. Máximo 150MB.');
                return;
            }

            const url = URL.createObjectURL(file);
            document.getElementById('videoPreviewPlayer').src = url;
            document.getElementById('videoFileName').textContent = file.name;
            videoPreview.classList.remove('hidden');
            videoPlaceholder.classList.add('hidden');
        }
    });

    removeVideoBtn?.addEventListener('click', function() {
        videoInput.value = '';
        videoPreview.classList.add('hidden');
        videoPlaceholder.classList.remove('hidden');
    });
}

function setupKeyPointsHandlers() {
    if (window.keyPointHandlersSetup) {
        return;
    }
    window.keyPointHandlersSetup = true;

    document.addEventListener('click', function(e) {
        if (e.target.id === 'addKeyPointBtn') {
            e.preventDefault();
            e.stopPropagation();
            addNewKeyPoint();
            return;
        }

        if (e.target.closest('.add-key-point') && !e.target.closest('#addKeyPointBtn')) {
            e.preventDefault();
            e.stopPropagation();
            addNewKeyPoint();
            return;
        }

        if (e.target.closest('.remove-key-point')) {
            e.preventDefault();
            e.stopPropagation();
            const keyPointDiv = e.target.closest('.key-point-row');
            if (keyPointDiv) {
                keyPointDiv.remove();
            }
            return;
        }
    });
}

function addNewKeyPoint() {
    const container = document.getElementById('keyPointsContainer');
    if (!container) return;

    const newKeyPoint = document.createElement('div');
    newKeyPoint.className = 'key-point-row';
    newKeyPoint.innerHTML = `
        <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        <button type="button" class="remove-key-point p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors flex items-center justify-center" aria-label="Eliminar punto clave">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    `;

    container.appendChild(newKeyPoint);

    const newInput = newKeyPoint.querySelector('input');
    if (newInput) {
        newInput.focus();
    }
}

function handleFormSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const productId = document.getElementById('productId').value;

    let url = '/products';
    let method = 'POST';

    if (isEditing && productId) {
        url = `/products/${productId}`;
        formData.append('_method', 'PUT');
    }

    const saveBtn = document.getElementById('saveProductBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>' + '{{ __('Guardando...') }}';
    saveBtn.disabled = true;

    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeProductModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
            console.error('Errors:', data.errors);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el producto');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Robust scroll lock: add/remove class + inline styles and capture wheel/touchmove to prevent page scroll
window.__modalPreventScroll = function (e) {
    const modal = document.getElementById('productModal');
    if (!modal || modal.classList.contains('hidden')) return;
    // allow scrolling when the event target is inside the modal body
    if (e.target && e.target.closest && e.target.closest('.product-modal-content')) return;
    // otherwise prevent the document from scrolling
    e.preventDefault();
};

function toggleBodyScroll(isLocked) {
    const html = document.documentElement;
    const body = document.body;

    if (isLocked) {
        body.classList.add('modal-open');
        html.classList.add('modal-open');
        // inline styles are more robust across builds/tooling
        body.style.overflow = 'hidden';
        html.style.overflow = 'hidden';
        // capture wheel/touchmove at document level to avoid scroll leaking to the page
        document.addEventListener('wheel', window.__modalPreventScroll, { passive: false });
        document.addEventListener('touchmove', window.__modalPreventScroll, { passive: false });
    } else {
        body.classList.remove('modal-open');
        html.classList.remove('modal-open');
        body.style.overflow = '';
        html.style.overflow = '';
        document.removeEventListener('wheel', window.__modalPreventScroll);
        document.removeEventListener('touchmove', window.__modalPreventScroll);
    }
}

function showProductModal() {
    document.getElementById('productModal').classList.remove('hidden');
    toggleBodyScroll(true);
}

function hideProductModal() {
    document.getElementById('productModal').classList.add('hidden');
    toggleBodyScroll(false);
}

function openCreateModal() {
    isEditing = false;
    document.getElementById('modalTitle').textContent = 'Crear nuevo producto';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('formMethod').value = 'POST';

    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('imagePlaceholder').classList.remove('hidden');
    document.getElementById('videoPreview').classList.add('hidden');
    document.getElementById('videoPlaceholder').classList.remove('hidden');

    const container = document.getElementById('keyPointsContainer');
    container.innerHTML = `
        <div class="key-point-row">
            <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            <button type="button" class="add-key-point p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center" aria-label="Agregar punto clave">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </button>
        </div>
    `;

    showProductModal();
}

function closeProductModal() {
    hideProductModal();
}

function editProduct(productId) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = 'Editar producto';

    fetch(`/products/${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;

                document.getElementById('productId').value = product.id;
                document.getElementById('category').value = product.category;
                loadProductsByCategory(product.category);

                setTimeout(() => {
                    document.getElementById('name').value = product.name;
                }, 100);

                document.getElementById('country').value = product.country;
                document.getElementById('information').value = product.information || '';
                document.getElementById('disease').value = product.disease || '';

                if (product.dosage) {
                    document.getElementById('dosage_preventivo').value = product.dosage.preventivo || '';
                    document.getElementById('dosage_correctivo').value = product.dosage.correctivo || '';
                    document.getElementById('dosage_cronico').value = product.dosage.cronico || '';
                }

                const container = document.getElementById('keyPointsContainer');
                container.innerHTML = '';
                if (product.key_points && product.key_points.length > 0) {
                    product.key_points.forEach(point => {
                        const newKeyPoint = document.createElement('div');
                        newKeyPoint.className = 'key-point-row';
                        newKeyPoint.innerHTML = `
                            <input type="text" name="key_points[]" value="${point}" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <button type="button" class="remove-key-point p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors flex items-center justify-center" aria-label="Eliminar punto clave">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        `;
                        container.appendChild(newKeyPoint);
                    });
                } else {
                    container.innerHTML = `
                        <div class="key-point-row">
                            <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <button type="button" class="add-key-point p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center" aria-label="Agregar punto clave">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </button>
                        </div>
                    `;
                }

                if (product.image_url) {
                    document.getElementById('imagePreviewImg').src = product.image_url;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    document.getElementById('imagePlaceholder').classList.add('hidden');
                }

                if (product.video_url) {
                    document.getElementById('videoPreviewPlayer').src = product.video_url;
                    document.getElementById('videoFileName').textContent = product.video_name || 'Video actual';
                    document.getElementById('videoPreview').classList.remove('hidden');
                    document.getElementById('videoPlaceholder').classList.add('hidden');
                }

                showProductModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del producto');
        });
}
</script>
