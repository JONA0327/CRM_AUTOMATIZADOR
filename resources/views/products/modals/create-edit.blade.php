<!-- Modal para Crear/Editar Producto -->
<div id="productModal" class="app-modal hidden">
    <div class="app-modal__overlay" aria-hidden="true"></div>

    <div class="app-modal__dialog app-modal__dialog--2xl">
        <form id="productForm" enctype="multipart/form-data" class="form-stack">
            @csrf
            <input type="hidden" id="productId" name="product_id">
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <div class="app-modal__header">
                <div class="app-modal__title">
                    <span class="app-modal__icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </span>
                    <div>
                        <h3 id="modalTitle" class="app-modal__headline">Crear nuevo producto</h3>
                        <p class="app-modal__description">Completa la información del catálogo para mantenerlo actualizado.</p>
                    </div>
                </div>
                <button type="button" id="closeModal" class="app-modal__close" aria-label="Cerrar modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="app-modal__body">
                <div class="app-modal__grid two-columns">
                    <div class="form-stack">
                        <div class="form-field">
                            <label for="category" class="form-label">Categoría *</label>
                            <select id="category" name="category" required class="form-select">
                                <option value="">Seleccione una categoría</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="name" class="form-label">Producto *</label>
                            <select id="name" name="name" required class="form-select" disabled>
                                <option value="">Seleccione primero una categoría</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label for="country" class="form-label">País *</label>
                            <select id="country" name="country" required class="form-select">
                                <option value="">Seleccione un país</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label class="form-label">Puntos clave</label>
                            <div id="keyPointsContainer" class="form-stack">
                                <div class="key-point-row">
                                    <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control">
                                    <button type="button" class="add-key-point action-btn action-btn--positive" aria-label="Agregar punto clave">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" id="addKeyPointBtn" class="form-link">+ {{ __('Agregar punto clave') }}</button>
                        </div>

                        <div class="form-field">
                            <label for="information" class="form-label">Información del producto</label>
                            <textarea id="information" name="information" rows="4" class="form-control form-textarea" placeholder="Describa el producto, sus beneficios, composición, etc."></textarea>
                        </div>
                    </div>

                    <div class="form-stack">
                        <div class="form-field">
                            <label for="image" class="form-label">Imagen del producto</label>
                            <div class="app-modal__surface">
                                <input type="file" id="image" name="image" accept="image/*" class="hidden">
                                <div id="imagePreview" class="hidden">
                                    <img id="imagePreviewImg" src="" alt="Vista previa" class="media-preview">
                                    <button type="button" id="removeImage" class="form-link form-link--danger">{{ __('Eliminar imagen') }}</button>
                                </div>
                                <div id="imagePlaceholder" class="form-stack">
                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="form-helper text-center">Seleccione una imagen (máx. 150MB)</p>
                                    <button type="button" id="selectImageBtn" class="module-btn module-btn--secondary">{{ __('Seleccionar archivo') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="video" class="form-label">Video del producto (opcional)</label>
                            <div class="app-modal__surface">
                                <input type="file" id="video" name="video" accept="video/*" class="hidden">
                                <div id="videoPreview" class="hidden">
                                    <video id="videoPreviewPlayer" controls class="media-preview media-preview--video"></video>
                                    <p id="videoFileName" class="form-helper"></p>
                                    <button type="button" id="removeVideo" class="form-link form-link--danger">{{ __('Eliminar video') }}</button>
                                </div>
                                <div id="videoPlaceholder" class="form-stack">
                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <p class="form-helper text-center">Seleccione un video (máx. 150MB)</p>
                                    <button type="button" id="selectVideoBtn" class="module-btn module-btn--secondary">{{ __('Seleccionar video') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="disease" class="form-label">Enfermedad asociada (opcional)</label>
                            <input type="text" id="disease" name="disease" class="form-control" placeholder="Ej: Diabetes, Hipertensión, etc.">
                            <p class="form-helper">Este campo estará disponible cuando se implemente el índice de enfermedades.</p>
                        </div>

                        <div class="form-field">
                            <label class="form-label">Dosis</label>
                            <div class="form-stack">
                                <div class="form-field">
                                    <label for="dosage_preventivo" class="form-label">Preventivo</label>
                                    <input type="text" id="dosage_preventivo" name="dosage_preventivo" class="form-control" placeholder="Ej: 1 cápsula al día">
                                </div>
                                <div class="form-field">
                                    <label for="dosage_correctivo" class="form-label">Correctivo</label>
                                    <input type="text" id="dosage_correctivo" name="dosage_correctivo" class="form-control" placeholder="Ej: 2 cápsulas cada 8 horas">
                                </div>
                                <div class="form-field">
                                    <label for="dosage_cronico" class="form-label">Crónico</label>
                                    <input type="text" id="dosage_cronico" name="dosage_cronico" class="form-control" placeholder="Ej: 3 cápsulas cada 6 horas">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-modal__footer">
                <button type="button" id="cancelBtn" class="module-btn module-btn--secondary">{{ __('Cancelar') }}</button>
                <button type="submit" id="saveProductBtn" class="module-btn module-btn--primary">
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
    const overlay = modal?.querySelector('.app-modal__overlay');
    const categorySelect = document.getElementById('category');
    const nameSelect = document.getElementById('name');

    // Cerrar modal
    closeBtn?.addEventListener('click', closeProductModal);
    cancelBtn?.addEventListener('click', closeProductModal);
    overlay?.addEventListener('click', closeProductModal);

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
        <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control">
        <button type="button" class="remove-key-point action-btn action-btn--danger" aria-label="Eliminar punto clave">
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
            <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control">
            <button type="button" class="add-key-point action-btn action-btn--positive" aria-label="Agregar punto clave">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </button>
        </div>
    `;

    document.getElementById('productModal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}

function editProduct(productId) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = 'Editar producto';

    fetch(`/products/${productId}/edit`)
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
                            <input type="text" name="key_points[]" value="${point}" class="form-control">
                            <button type="button" class="remove-key-point action-btn action-btn--danger" aria-label="Eliminar punto clave">
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
                            <input type="text" name="key_points[]" placeholder="Ingrese un punto clave" class="form-control">
                            <button type="button" class="add-key-point action-btn action-btn--positive" aria-label="Agregar punto clave">
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

                document.getElementById('productModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del producto');
        });
}
</script>
