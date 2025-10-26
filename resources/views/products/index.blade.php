<x-app-layout>
    <x-slot name="header">
        <div class="products-header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-green-600 to-green-700 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-900">Gestión de Productos</h2>
                    <p class="text-xs text-gray-600">Catálogo completo de productos 4Life</p>
                </div>
            </div>
            <!-- Botón de crear removido aquí para evitar duplicados en la interfaz -->
        </div>
        </div>
    </x-slot>

    <div class="w-full products-container">
        <!-- Filtros -->
        <div class="mb-4 products-filters">
            <div class="filter-controls">
                <div class="filter-inputs flex-1">
                    <select id="categoryFilter" class="filter-select">
                            <option value="">Todas las categorías</option>
                            @foreach($catalog as $category => $products)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                    </select>
                    <input type="text" id="searchProducts" placeholder="Buscar productos..." class="filter-input flex-1 min-w-64">
                </div>
                <div class="product-count">
                    Total: <span id="productCount" class="font-medium text-gray-900">{{ $productsByCategory->flatten()->count() }}</span> productos
                </div>
            </div>
        </div>

        <!-- Productos por categoría -->
        @if($productsByCategory->count() > 0)
            @foreach($productsByCategory as $category => $products)
                <div class="mb-8 category-section" data-category="{{ $category }}">
                    <div class="category-header">
                        <h3 class="category-title">{{ $category }}</h3>
                        <span class="category-badge">{{ $products->count() }} productos</span>
                    </div>

                    <div class="products-grid">
                        @foreach($products as $product)
                            <div class="product-card bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 cursor-pointer border border-gray-200 overflow-hidden" 
                                 data-product-id="{{ $product->id }}" 
                                 data-product-name="{{ strtolower($product->name) }}"
                                 data-category="{{ $category }}">
                                
                                <!-- Imagen del producto -->
                                <div class="product-image">
                                    @if($product->image)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="product-image-placeholder">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Información del producto -->
                                <div class="product-info">
                                    <h4 class="product-name">{{ $product->name }}</h4>
                                    <p class="product-category">{{ $product->category }}</p>
                                    
                                    @if($product->key_points && count($product->key_points) > 0)
                                        <div class="product-key-points">
                                            <div class="key-points-list">
                                                @foreach(array_slice($product->key_points, 0, 2) as $point)
                                                    <span class="key-point">{{ $point }}</span>
                                                @endforeach
                                                @if(count($product->key_points) > 2)
                                                    <span class="key-point key-point-more">+{{ count($product->key_points) - 2 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="product-footer">
                                        <span class="product-country">📍 {{ $product->country }}</span>
                                        <div class="product-actions">
                                            <button class="action-btn edit-btn edit-product-btn" data-product-id="{{ $product->id }}">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button class="action-btn delete-btn delete-product-btn" data-product-id="{{ $product->id }}">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <!-- Estado vacío -->
            <div class="empty-state">
                <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3>No hay productos disponibles</h3>
                <p>Comienza creando tu primer producto para el catálogo 4Life</p>
                <button id="createFirstProductBtn" class="empty-state-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Crear Primer Producto
                </button>
            </div>
        @endif
    </div>

    <!-- Modal para crear/editar producto -->
    @include('products.modals.create-edit')
    
    <!-- Modal para ver detalles del producto -->
    @include('products.modals.details')

    @vite('resources/css/products.css')
    @vite('resources/js/products.js')
</x-app-layout>