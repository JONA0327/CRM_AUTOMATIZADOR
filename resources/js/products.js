/**
 * Products functionality for CRM_AUTOMATIZADOR
 * Handles product management, filtering, and modal interactions
 */

class ProductManager {
    constructor() {
        this.products = [];
        this.filteredProducts = [];
        this.currentCategory = '';
        this.searchTerm = '';
        this.isLoading = false;
        
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.bindEvents();
        this.setupFilters();
        this.loadProducts();
    }

    bindEvents() {
        // Botones de crear producto
        const createBtn = document.getElementById('createProductBtn');
        const createFirstBtn = document.getElementById('createFirstProductBtn');
        
        if (createBtn) {
            createBtn.addEventListener('click', () => this.openCreateModal());
        }
        
        if (createFirstBtn) {
            createFirstBtn.addEventListener('click', () => this.openCreateModal());
        }

        // Event delegation para cards de productos
        document.addEventListener('click', (e) => {
            // Click en card
            const productCard = e.target.closest('.product-card');
            if (productCard && !e.target.closest('.product-actions')) {
                const productId = productCard.dataset.productId;
                this.showProductDetails(productId);
                return;
            }

            // Botones de acción
            if (e.target.closest('.edit-product-btn')) {
                e.stopPropagation();
                const productId = e.target.closest('.edit-product-btn').dataset.productId;
                this.editProduct(productId);
            }

            if (e.target.closest('.delete-product-btn')) {
                e.stopPropagation();
                const productId = e.target.closest('.delete-product-btn').dataset.productId;
                this.deleteProduct(productId);
            }
        });
    }

    setupFilters() {
        const categoryFilter = document.getElementById('categoryFilter');
        const searchInput = document.getElementById('searchProducts');

        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.currentCategory = e.target.value;
                this.filterProducts();
            });
        }

        if (searchInput) {
            // Debounce search
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchTerm = e.target.value.toLowerCase();
                    this.filterProducts();
                }, 300);
            });
        }
    }

    loadProducts() {
        // Esta función carga los productos desde el DOM
        const productCards = document.querySelectorAll('.product-card');
        this.products = Array.from(productCards).map(card => ({
            id: card.dataset.productId,
            name: card.dataset.productName,
            category: card.dataset.category,
            element: card
        }));
        
        this.filteredProducts = [...this.products];
        this.updateProductCount();
    }

    filterProducts() {
        const categorySection = document.querySelectorAll('.category-section');
        let visibleCount = 0;

        categorySection.forEach(section => {
            const category = section.dataset.category;
            let categoryHasVisible = false;

            const cardsInCategory = section.querySelectorAll('.product-card');
            cardsInCategory.forEach(card => {
                const productName = card.dataset.productName || '';
                const productCategory = card.dataset.category;

                const matchesCategory = !this.currentCategory || productCategory === this.currentCategory;
                const matchesSearch = !this.searchTerm || productName.includes(this.searchTerm);

                if (matchesCategory && matchesSearch) {
                    card.style.display = 'block';
                    categoryHasVisible = true;
                    visibleCount++;
                    
                    // Animación de entrada
                    card.style.animation = 'none';
                    card.offsetHeight; // Trigger reflow
                    card.style.animation = 'fadeInUp 0.4s ease-out';
                } else {
                    card.style.display = 'none';
                }
            });

            // Mostrar/ocultar sección completa
            section.style.display = categoryHasVisible ? 'block' : 'none';
        });

        this.updateProductCount(visibleCount);
        this.showNoResults(visibleCount === 0);
    }

    updateProductCount(count = null) {
        const productCountElement = document.getElementById('productCount');
        if (productCountElement) {
            const totalCount = count !== null ? count : this.products.length;
            productCountElement.textContent = totalCount;
        }
    }

    showNoResults(show) {
        const existingNoResults = document.getElementById('noResultsMessage');

        if (show && !existingNoResults) {
            const message = document.createElement('div');
            message.id = 'noResultsMessage';
            message.className = 'empty-state';
            message.style.marginTop = '2rem';

            const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('class', 'empty-state-icon');
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', 'currentColor');
            icon.setAttribute('viewBox', '0 0 24 24');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('d', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z');
            icon.appendChild(path);

            const title = document.createElement('h3');
            title.textContent = 'No se encontraron productos';

            const desc = document.createElement('p');
            desc.textContent = 'Intenta ajustar los filtros de búsqueda';

            const button = document.createElement('button');
            button.className = 'empty-state-btn';
            const bIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            bIcon.setAttribute('fill', 'none');
            bIcon.setAttribute('stroke', 'currentColor');
            bIcon.setAttribute('viewBox', '0 0 24 24');
            const bPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            bPath.setAttribute('stroke-linecap', 'round');
            bPath.setAttribute('stroke-linejoin', 'round');
            bPath.setAttribute('stroke-width', '2');
            bPath.setAttribute('d', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15');
            bIcon.appendChild(bPath);
            button.appendChild(bIcon);
            button.appendChild(document.createTextNode(' Limpiar Filtros'));
            button.addEventListener('click', () => this.clearFilters());

            message.appendChild(icon);
            message.appendChild(title);
            message.appendChild(desc);
            message.appendChild(button);

            const container = document.querySelector('.products-container');
            if (container) {
                container.appendChild(message);
            }
        } else if (!show && existingNoResults) {
            existingNoResults.remove();
        }
    }

    clearFilters() {
        const categoryFilter = document.getElementById('categoryFilter');
        const searchInput = document.getElementById('searchProducts');
        
        if (categoryFilter) categoryFilter.value = '';
        if (searchInput) searchInput.value = '';
        
        this.currentCategory = '';
        this.searchTerm = '';
        this.filterProducts();
    }

    openCreateModal() {
        if (typeof window.openCreateModal === 'function') {
            window.openCreateModal();
        } else {
            console.warn('openCreateModal function not found');
        }
    }

    showProductDetails(productId) {
        if (typeof window.showProductDetails === 'function') {
            window.showProductDetails(productId);
        } else {
            console.warn('showProductDetails function not found');
        }
    }

    editProduct(productId) {
        if (typeof window.editProduct === 'function') {
            window.editProduct(productId);
        } else {
            console.warn('editProduct function not found');
        }
    }

    deleteProduct(productId) {
        if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            this.showLoading(true);
            
            fetch(`/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message || 'Producto eliminado exitosamente', 'success');
                    
                    // Animación de salida
                    const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                    if (productCard) {
                        productCard.style.animation = 'fadeOut 0.3s ease-out';
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    } else {
                        location.reload();
                    }
                } else {
                    this.showNotification('Error al eliminar el producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error al eliminar el producto', 'error');
            })
            .finally(() => {
                this.showLoading(false);
            });
        }
    }

    showLoading(show) {
        const existingLoader = document.getElementById('globalLoader');
        
        if (show && !existingLoader) {
            const loader = document.createElement('div');
            loader.id = 'globalLoader';
            loader.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
            loader.innerHTML = `
                <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                    <svg class="animate-spin w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-700">Procesando...</span>
                </div>
            `;
            document.body.appendChild(loader);
        } else if (!show && existingLoader) {
            existingLoader.remove();
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full max-w-sm`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-black',
            info: 'bg-blue-500 text-white'
        };
        
        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
        };
        
        notification.className += ` ${colors[type] || colors.info}`;
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${icons[type] || icons.info}
                </svg>
                <span class="text-sm font-medium">${message}</span>
                <button class="ml-3 flex-shrink-0 p-1 hover:bg-black hover:bg-opacity-20 rounded" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animación de entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 10);
        
        // Auto remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
}

// Utilidades adicionales
class ProductUtils {
    static formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    static validateImageFile(file) {
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxSize = 157286400; // 150MB

        if (!allowedTypes.includes(file.type)) {
            return { valid: false, message: 'Tipo de archivo no permitido. Use JPG, PNG o GIF.' };
        }

        if (file.size > maxSize) {
            return { valid: false, message: `Archivo muy grande. Máximo ${this.formatFileSize(maxSize)}.` };
        }

        return { valid: true };
    }

    static validateVideoFile(file) {
        const allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'];
        const maxSize = 157286400; // 150MB

        if (!allowedTypes.includes(file.type)) {
            return { valid: false, message: 'Tipo de video no permitido. Use MP4, AVI, MOV o WMV.' };
        }

        if (file.size > maxSize) {
            return { valid: false, message: `Video muy grande. Máximo ${this.formatFileSize(maxSize)}.` };
        }

        return { valid: true };
    }

    static createImagePreview(file, callback) {
        const reader = new FileReader();
        reader.onload = function(e) {
            callback(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    static createVideoPreview(file, callback) {
        const url = URL.createObjectURL(file);
        callback(url, file.name);
    }

    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Inicializar cuando el DOM esté listo
let productManager;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProducts);
} else {
    initializeProducts();
}

function initializeProducts() {
    productManager = new ProductManager();
}

// Exportar para uso global
window.ProductManager = ProductManager;
window.ProductUtils = ProductUtils;
window.productManager = productManager;

// Animaciones movidas a products.css