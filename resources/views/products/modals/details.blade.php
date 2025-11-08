<!-- Modal para Ver Detalles del Producto -->
<div id="productDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-600 to-green-700 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900" id="detailProductName">Nombre del Producto</h3>
                            <p class="text-sm text-gray-600" id="detailProductCategory">Categoría</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button id="editFromDetailsBtn" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring ring-blue-300 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar
                        </button>
                        <button type="button" id="closeDetailsModal" class="bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Columna Izquierda: Multimedia -->
                    <div class="space-y-6">
                        <!-- Imagen Principal -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Imagen del Producto</h4>
                            <div id="detailImageContainer" class="bg-gray-100 rounded-lg overflow-hidden">
                                <img id="detailProductImage" src="" alt="" class="w-full h-64 object-cover">
                            </div>
                            <div id="detailNoImage" class="hidden bg-gray-100 rounded-lg h-64 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-gray-500">Sin imagen disponible</p>
                                </div>
                            </div>
                        </div>

                        <!-- Video (si existe) -->
                        <div id="detailVideoSection" class="hidden">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Video Informativo</h4>
                            <div class="bg-gray-100 rounded-lg overflow-hidden">
                                <video id="detailProductVideo" controls class="w-full h-64 object-cover">
                                    Tu navegador no soporta el elemento de video.
                                </video>
                            </div>
                            <p id="detailVideoName" class="text-sm text-gray-600 mt-2"></p>
                        </div>

                        <!-- Información de Dosis -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Dosificación Recomendada</h4>
                            <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 gap-4">
                                    <div id="detailDosagePreventivo" class="hidden">
                                        <div class="flex items-center mb-2">
                                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                            <span class="text-sm font-medium text-gray-700">Preventivo</span>
                                        </div>
                                        <p class="text-sm text-gray-600 ml-5" id="detailDosagePreventext"></p>
                                    </div>
                                    <div id="detailDosageCorrectivo" class="hidden">
                                        <div class="flex items-center mb-2">
                                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                            <span class="text-sm font-medium text-gray-700">Correctivo</span>
                                        </div>
                                        <p class="text-sm text-gray-600 ml-5" id="detailDosageCorrectext"></p>
                                    </div>
                                    <div id="detailDosageCronico" class="hidden">
                                        <div class="flex items-center mb-2">
                                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                            <span class="text-sm font-medium text-gray-700">Crónico</span>
                                        </div>
                                        <p class="text-sm text-gray-600 ml-5" id="detailDosageCrontext"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Información -->
                    <div class="space-y-6">
                        <!-- Información Básica -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Información General</h4>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Categoría:</span>
                                    <span class="text-sm text-gray-900" id="detailInfoCategory"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">País:</span>
                                    <span class="text-sm text-gray-900 flex items-center" id="detailInfoCountry">
                                        <span class="mr-1">📍</span>
                                        <span id="detailCountryName"></span>
                                    </span>
                                </div>
                                <div id="detailDiseaseSection" class="hidden">
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium text-gray-500">Enfermedad Asociada:</span>
                                        <span class="text-sm text-gray-900" id="detailInfoDisease"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Puntos Clave -->
                        <div id="detailKeyPointsSection" class="hidden">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Puntos Clave</h4>
                            <div id="detailKeyPointsList" class="space-y-2">
                                <!-- Los puntos clave se cargarán dinámicamente -->
                            </div>
                        </div>

                        <!-- Descripción Detallada -->
                        <div id="detailInformationSection" class="hidden">
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Descripción del Producto</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div id="detailProductInformation" class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
                                    <!-- La información se cargará dinámicamente -->
                                </div>
                            </div>
                        </div>

                        <!-- Acciones Rápidas -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Acciones</h4>
                            <div class="flex flex-wrap gap-3">
                                <button id="shareProductBtn" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring ring-green-300 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                    </svg>
                                    Compartir
                                </button>
                                <button id="printProductBtn" class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring ring-gray-300 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Imprimir Ficha
                                </button>
                                <button id="deleteFromDetailsBtn" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring ring-red-300 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variables para el modal de detalles
let currentProductId = null;

// Event listeners para el modal de detalles
document.addEventListener('DOMContentLoaded', function() {
    const detailsModal = document.getElementById('productDetailsModal');
    const closeDetailsBtn = document.getElementById('closeDetailsModal');
    const editFromDetailsBtn = document.getElementById('editFromDetailsBtn');
    const deleteFromDetailsBtn = document.getElementById('deleteFromDetailsBtn');
    const shareBtn = document.getElementById('shareProductBtn');
    const printBtn = document.getElementById('printProductBtn');

    // Cerrar modal
    closeDetailsBtn?.addEventListener('click', closeDetailsModal);

    // Editar desde detalles
    editFromDetailsBtn?.addEventListener('click', function() {
        const productIdToEdit = currentProductId;
        closeDetailsModal();
        setTimeout(() => {
            editProduct(productIdToEdit);
        }, 300);
    });

    // Eliminar desde detalles
    deleteFromDetailsBtn?.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            deleteProduct(currentProductId);
            closeDetailsModal();
        }
    });

    // Compartir producto
    shareBtn?.addEventListener('click', shareProduct);

    // Imprimir ficha
    printBtn?.addEventListener('click', printProductSheet);

    // Cerrar modal al hacer clic fuera
    detailsModal?.addEventListener('click', function(e) {
        if (e.target === detailsModal) {
            closeDetailsModal();
        }
    });
});

function showProductDetails(productId) {
    currentProductId = productId;

    fetch(`/products/${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                populateDetailsModal(product);
                document.getElementById('productDetailsModal').classList.remove('hidden');
            } else {
                alert('Error al cargar los detalles del producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del producto');
        });
}

function populateDetailsModal(product) {
    console.log('Product data:', product); // Debug

    // Información básica
    document.getElementById('detailProductName').textContent = product.name;
    document.getElementById('detailProductCategory').textContent = product.category;
    document.getElementById('detailInfoCategory').textContent = product.category;
    document.getElementById('detailCountryName').textContent = product.country;

    // Imagen
    console.log('Image URL:', product.image_url); // Debug
    if (product.image_url && product.image_url !== null) {
        document.getElementById('detailProductImage').src = product.image_url;
        document.getElementById('detailProductImage').alt = product.name;
        document.getElementById('detailImageContainer').classList.remove('hidden');
        document.getElementById('detailNoImage').classList.add('hidden');
    } else {
        document.getElementById('detailImageContainer').classList.add('hidden');
        document.getElementById('detailNoImage').classList.remove('hidden');
    }

    // Video (solo mostrar si existe)
    console.log('Video URL:', product.video_url); // Debug
    if (product.video_url && product.video_url !== null) {
        document.getElementById('detailProductVideo').src = product.video_url;
        document.getElementById('detailVideoName').textContent = product.video_name || 'Video informativo';
        document.getElementById('detailVideoSection').classList.remove('hidden');
    } else {
        document.getElementById('detailVideoSection').classList.add('hidden');
    }

    // Enfermedad asociada
    if (product.disease) {
        document.getElementById('detailInfoDisease').textContent = product.disease;
        document.getElementById('detailDiseaseSection').classList.remove('hidden');
    } else {
        document.getElementById('detailDiseaseSection').classList.add('hidden');
    }

    // Puntos clave
    if (product.key_points && product.key_points.length > 0) {
        const keyPointsList = document.getElementById('detailKeyPointsList');
        keyPointsList.innerHTML = '';

        product.key_points.forEach((point, index) => {
            const pointElement = document.createElement('div');
            pointElement.className = 'flex items-start';
            pointElement.innerHTML = `
                <div class="flex-shrink-0 w-5 h-5 bg-blue-500 text-white text-xs rounded-full flex items-center justify-center mt-0.5 mr-3">
                    ${index + 1}
                </div>
                <span class="text-sm text-gray-700">${point}</span>
            `;
            keyPointsList.appendChild(pointElement);
        });

        document.getElementById('detailKeyPointsSection').classList.remove('hidden');
    } else {
        document.getElementById('detailKeyPointsSection').classList.add('hidden');
    }

    // Información del producto
    if (product.information) {
        document.getElementById('detailProductInformation').textContent = product.information;
        document.getElementById('detailInformationSection').classList.remove('hidden');
    } else {
        document.getElementById('detailInformationSection').classList.add('hidden');
    }

    // Dosis
    if (product.dosage) {
        // Preventivo
        if (product.dosage.preventivo) {
            document.getElementById('detailDosagePreventext').textContent = product.dosage.preventivo;
            document.getElementById('detailDosagePreventivo').classList.remove('hidden');
        } else {
            document.getElementById('detailDosagePreventivo').classList.add('hidden');
        }

        // Correctivo
        if (product.dosage.correctivo) {
            document.getElementById('detailDosageCorrectext').textContent = product.dosage.correctivo;
            document.getElementById('detailDosageCorrectivo').classList.remove('hidden');
        } else {
            document.getElementById('detailDosageCorrectivo').classList.add('hidden');
        }

        // Crónico
        if (product.dosage.cronico) {
            document.getElementById('detailDosageCrontext').textContent = product.dosage.cronico;
            document.getElementById('detailDosageCronico').classList.remove('hidden');
        } else {
            document.getElementById('detailDosageCronico').classList.add('hidden');
        }
    }
}

function closeDetailsModal() {
    document.getElementById('productDetailsModal').classList.add('hidden');
    currentProductId = null;
}

function shareProduct() {
    if (navigator.share && currentProductId) {
        fetch(`/products/${currentProductId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = data.product;
                    navigator.share({
                        title: product.name,
                        text: `Conoce más sobre ${product.name} de ${product.category}`,
                        url: window.location.href
                    });
                }
            });
    } else {
        // Fallback: copiar al portapapeles
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Enlace copiado al portapapeles');
        });
    }
}

function printProductSheet() {
    if (!currentProductId) return;

    // Crear una ventana de impresión con el contenido del producto
    const printWindow = window.open('', '_blank');

    fetch(`/products/${currentProductId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;

                let keyPointsHtml = '';
                if (product.key_points && product.key_points.length > 0) {
                    keyPointsHtml = `
                        <div style="margin-bottom: 20px;">
                            <h3 style="font-weight: bold; margin-bottom: 10px;">Puntos Clave:</h3>
                            <ul style="list-style-type: disc; padding-left: 20px;">
                                ${product.key_points.map(point => `<li style="margin-bottom: 5px;">${point}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }

                let dosageHtml = '';
                if (product.dosage) {
                    const dosageItems = [];
                    if (product.dosage.preventivo) dosageItems.push(`<strong>Preventivo:</strong> ${product.dosage.preventivo}`);
                    if (product.dosage.correctivo) dosageItems.push(`<strong>Correctivo:</strong> ${product.dosage.correctivo}`);
                    if (product.dosage.cronico) dosageItems.push(`<strong>Crónico:</strong> ${product.dosage.cronico}`);

                    if (dosageItems.length > 0) {
                        dosageHtml = `
                            <div style="margin-bottom: 20px;">
                                <h3 style="font-weight: bold; margin-bottom: 10px;">Dosificación:</h3>
                                ${dosageItems.map(item => `<p style="margin-bottom: 5px;">${item}</p>`).join('')}
                            </div>
                        `;
                    }
                }

                const printContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Ficha de Producto: ${product.name}</title>
                        <style>
                            body { font-family: Arial, sans-serif; padding: 20px; }
                            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                            .section { margin-bottom: 20px; }
                            .image { text-align: center; margin: 20px 0; }
                            .image img { max-width: 300px; height: auto; }
                            @media print { body { margin: 0; } }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>${product.name}</h1>
                            <h2>${product.category}</h2>
                            <p><strong>País:</strong> ${product.country}</p>
                            ${product.disease ? `<p><strong>Enfermedad Asociada:</strong> ${product.disease}</p>` : ''}
                        </div>

                        ${product.image_url ? `<div class="image"><img src="${product.image_url}" alt="${product.name}"></div>` : ''}

                        ${keyPointsHtml}

                        ${product.information ? `
                            <div class="section">
                                <h3 style="font-weight: bold; margin-bottom: 10px;">Descripción:</h3>
                                <p style="line-height: 1.6;">${product.information.replace(/\n/g, '<br>')}</p>
                            </div>
                        ` : ''}

                        ${dosageHtml}

                        <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
                            <p>Ficha generada desde CRM_AUTOMATIZADOR - ${new Date().toLocaleDateString()}</p>
                        </div>
                    </body>
                    </html>
                `;

                printWindow.document.write(printContent);
                printWindow.document.close();

                // Esperar a que se cargue y luego imprimir
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            printWindow.close();
            alert('Error al generar la ficha para imprimir');
        });
}
</script>
