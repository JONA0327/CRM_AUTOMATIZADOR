<!-- Modal para Crear/Editar Producto -->
<div id="productModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <form id="productForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="productId" name="product_id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <!-- Header del Modal -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Crear Nuevo Producto</h3>
                                        <p class="text-sm text-gray-600">Complete la información del producto</p>
                                    </div>
                                </div>
                                <button type="button" id="closeModal" class="bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Formulario en Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Columna Izquierda -->
                                <div class="space-y-6">
                                    <!-- Selección de Categoría -->
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                                        <select id="category" name="category" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Seleccione una categoría</option>
                                        </select>
                                    </div>

                                    <!-- Selección de Producto -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Producto *</label>
                                        <select id="name" name="name" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                                            <option value="">Seleccione primero una categoría</option>
                                        </select>
                                    </div>

                                    <!-- País -->
                                    <div>
                                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">País *</label>
                                        <select id="country" name="country" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Seleccione un país</option>
                                        </select>
                                    </div>

                                    <!-- Puntos Clave -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Puntos Clave</label>
                                        <div id="keyPointsContainer">
                                            <div class="flex gap-2 mb-2">
                                                <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="flex-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                <button type="button" class="add-key-point px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" id="addKeyPointBtn" class="text-sm text-blue-600 hover:text-blue-800">+ Agregar punto clave</button>
                                    </div>

                                    <!-- Información del Producto -->
                                    <div>
                                        <label for="information" class="block text-sm font-medium text-gray-700 mb-2">Información del Producto</label>
                                        <textarea id="information" name="information" rows="4" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Describa el producto, sus beneficios, composición, etc."></textarea>
                                    </div>
                                </div>

                                <!-- Columna Derecha -->
                                <div class="space-y-6">
                                    <!-- Imagen -->
                                    <div>
                                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Imagen del Producto</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors">
                                            <input type="file" id="image" name="image" accept="image/*" class="hidden">
                                            <div id="imagePreview" class="hidden mb-4">
                                                <img id="imagePreviewImg" src="" alt="Preview" class="max-w-full h-40 mx-auto rounded-lg">
                                                <button type="button" id="removeImage" class="mt-2 text-red-600 text-sm hover:text-red-800">Eliminar imagen</button>
                                            </div>
                                            <div id="imagePlaceholder">
                                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-600">Seleccione una imagen (Max: 150MB)</p>
                                                <button type="button" id="selectImageBtn" class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                                    Seleccionar Archivo
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Video (Opcional) -->
                                    <div>
                                        <label for="video" class="block text-sm font-medium text-gray-700 mb-2">Video del Producto (Opcional)</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors">
                                            <input type="file" id="video" name="video" accept="video/*" class="hidden">
                                            <div id="videoPreview" class="hidden mb-4">
                                                <video id="videoPreviewPlayer" controls class="max-w-full h-40 mx-auto rounded-lg"></video>
                                                <p id="videoFileName" class="text-sm text-gray-600 mt-2"></p>
                                                <button type="button" id="removeVideo" class="mt-2 text-red-600 text-sm hover:text-red-800">Eliminar video</button>
                                            </div>
                                            <div id="videoPlaceholder">
                                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-600">Seleccione un video (Max: 150MB)</p>
                                                <button type="button" id="selectVideoBtn" class="mt-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                                                    Seleccionar Video
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Enfermedad Asociada -->
                                    <div>
                                        <label for="disease" class="block text-sm font-medium text-gray-700 mb-2">Enfermedad Asociada (Opcional)</label>
                                        <input type="text" id="disease" name="disease" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: Diabetes, Hipertensión, etc.">
                                        <p class="text-xs text-gray-500 mt-1">Este campo estará disponible cuando se implemente el índice de enfermedades</p>
                                    </div>

                                    <!-- Dosis -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Dosis</label>
                                        <div class="space-y-3">
                                            <div>
                                                <label for="dosage_preventivo" class="block text-xs font-medium text-gray-600 mb-1">Preventivo</label>
                                                <input type="text" id="dosage_preventivo" name="dosage_preventivo" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: 1 cápsula al día">
                                            </div>
                                            <div>
                                                <label for="dosage_correctivo" class="block text-xs font-medium text-gray-600 mb-1">Correctivo</label>
                                                <input type="text" id="dosage_correctivo" name="dosage_correctivo" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: 2 cápsulas cada 8 horas">
                                            </div>
                                            <div>
                                                <label for="dosage_cronico" class="block text-xs font-medium text-gray-600 mb-1">Crónico</label>
                                                <input type="text" id="dosage_cronico" name="dosage_cronico" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: 3 cápsulas cada 6 horas">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="saveProductBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Producto
                    </button>
                    <button type="button" id="cancelBtn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
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
    // Usar un flag global para evitar múltiples ejecuciones
    if (window.keyPointHandlersSetup) {
        return;
    }
    window.keyPointHandlersSetup = true;

    // Solo usar event delegation en el documento para todos los botones de key points
    document.addEventListener('click', function(e) {
        // Botón principal "Agregar punto clave"
        if (e.target.id === 'addKeyPointBtn') {
            e.preventDefault();
            e.stopPropagation();
            addNewKeyPoint();
            return;
        }

        // Botón + verde dentro del container
        if (e.target.closest('.add-key-point') && !e.target.closest('#addKeyPointBtn')) {
            e.preventDefault();
            e.stopPropagation();
            addNewKeyPoint();
            return;
        }

        // Botón de eliminar
        if (e.target.closest('.remove-key-point')) {
            e.preventDefault();
            e.stopPropagation();
            const keyPointDiv = e.target.closest('.flex.gap-2.mb-2');
            if (keyPointDiv) {
                keyPointDiv.remove();
            }
            return;
        }
    });
}function addNewKeyPoint() {
    const container = document.getElementById('keyPointsContainer');
    if (!container) return;

    const newKeyPoint = document.createElement('div');
    newKeyPoint.className = 'flex gap-2 mb-2';
    newKeyPoint.innerHTML = `
        <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="flex-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
        <button type="button" class="remove-key-point px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </button>
    `;

    container.appendChild(newKeyPoint);

    // Enfocar el nuevo input
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

    // Mostrar indicador de carga
    const saveBtn = document.getElementById('saveProductBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Guardando...';
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
            location.reload(); // Recargar para mostrar el nuevo producto
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

// Funciones globales para abrir/cerrar modal
function openCreateModal() {
    isEditing = false;
    document.getElementById('modalTitle').textContent = 'Crear Nuevo Producto';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('formMethod').value = 'POST';

    // Reset file previews
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('imagePlaceholder').classList.remove('hidden');
    document.getElementById('videoPreview').classList.add('hidden');
    document.getElementById('videoPlaceholder').classList.remove('hidden');

    // Reset key points to initial state
    const container = document.getElementById('keyPointsContainer');
    container.innerHTML = `
        <div class="flex gap-2 mb-2">
            <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="flex-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
            <button type="button" class="add-key-point px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </button>
        </div>
    `;

    document.getElementById('productModal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}

// Esta función será llamada desde el index
function editProduct(productId) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = 'Editar Producto';

    fetch(`/products/${productId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;

                // Llenar formulario
                document.getElementById('productId').value = product.id;
                document.getElementById('category').value = product.category;
                loadProductsByCategory(product.category);

                setTimeout(() => {
                    document.getElementById('name').value = product.name;
                }, 100);

                document.getElementById('country').value = product.country;
                document.getElementById('information').value = product.information || '';
                document.getElementById('disease').value = product.disease || '';

                // Dosis
                if (product.dosage) {
                    document.getElementById('dosage_preventivo').value = product.dosage.preventivo || '';
                    document.getElementById('dosage_correctivo').value = product.dosage.correctivo || '';
                    document.getElementById('dosage_cronico').value = product.dosage.cronico || '';
                }

                // Puntos clave
                const container = document.getElementById('keyPointsContainer');
                container.innerHTML = '';
                if (product.key_points && product.key_points.length > 0) {
                    product.key_points.forEach(point => {
                        const newKeyPoint = document.createElement('div');
                        newKeyPoint.className = 'flex gap-2 mb-2';
                        newKeyPoint.innerHTML = `
                            <input type="text" name="key_points[]" value="${point}" class="flex-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" class="remove-key-point px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        `;
                        container.appendChild(newKeyPoint);

                        newKeyPoint.querySelector('.remove-key-point').addEventListener('click', function() {
                            newKeyPoint.remove();
                        });
                    });
                } else {
                    // Si no hay puntos clave, agregar el campo inicial
                    container.innerHTML = `
                        <div class="flex gap-2 mb-2">
                            <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="flex-1 border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" class="add-key-point px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                }

                // Imagen
                if (product.image_url) {
                    document.getElementById('imagePreviewImg').src = product.image_url;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    document.getElementById('imagePlaceholder').classList.add('hidden');
                }

                // Video
                if (product.video_url) {
                    document.getElementById('videoPreviewPlayer').src = product.video_url;
                    document.getElementById('videoFileName').textContent = product.video_name || 'Video actual';
                    document.getElementById('videoPreview').classList.remove('hidden');
                    document.getElementById('videoPlaceholder').classList.add('hidden');
                }

                document.getElementById('productModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del producto');
        });
}
</script>
