<!-- Modal para Crear/Editar Índice -->
<div id="diseaseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto disease-modal-content">
        <form id="diseaseForm" class="flex flex-col min-h-full">
            @csrf
            <input type="hidden" id="diseaseId" name="disease_id">
            <input type="hidden" id="diseaseFormMethod" name="_method" value="POST">

            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900" id="diseaseModalTitle">Crear índice</h3>
                        <p class="text-sm text-gray-600">Define la condición, agrega productos y genera soporte inteligente.</p>
                    </div>
                </div>
                <button type="button" id="closeDiseaseModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Cerrar modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 space-y-8 disease-modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="diseaseName" class="block text-sm font-medium text-gray-700">Nombre de la condición <span class="text-red-500">*</span></label>
                            <input type="text" id="diseaseName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Resistencia inmunológica baja">
                        </div>

                        <div class="space-y-2">
                            <label for="diseaseCountry" class="block text-sm font-medium text-gray-700">País principal <span class="text-red-500">*</span></label>
                            <select id="diseaseCountry" name="country" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecciona un país</option>
                                @foreach($availableCountries as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <span class="block text-sm font-medium text-gray-700">Modo de información <span class="text-red-500">*</span></span>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio" name="information_mode" value="manual" class="text-blue-600 focus:ring-blue-500" checked>
                                    Manual
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio" name="information_mode" value="ai" class="text-blue-600 focus:ring-blue-500">
                                    Sugerencia IA
                                </label>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label for="diseaseInformation" class="block text-sm font-medium text-gray-700">Información de apoyo</label>
                            <textarea id="diseaseInformation" name="information" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Explica cómo los productos apoyan esta condición sin prometer curación."></textarea>
                            <p class="text-xs text-gray-500">Si eliges la opción de IA, puedes generar un texto detallado basado en los productos seleccionados.</p>
                            <button type="button" id="generateInformationBtn" class="generate-info-btn hidden px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a4 4 0 004-4M7 15V9a5 5 0 0110 0v6"></path>
                                </svg>
                                Generar información con IA
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div id="manualModeSection" class="card-section">
                            <div class="card-section-header">
                                <h4>Productos añadidos manualmente</h4>
                                <p>Selecciona productos y describe por qué apoyan esta condición.</p>
                            </div>
                            <div class="card-section-body space-y-4">
                                <div id="manualProductForm" class="flex flex-col gap-3">
                                    <select id="manualProductSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Selecciona un producto</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-country="{{ $product->country }}" data-name="{{ $product->name }}">
                                                {{ $product->name }} ({{ $product->country }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <textarea id="manualProductReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Describe cómo este producto apoya la condición."></textarea>
                                    <button type="button" id="addManualProductBtn" class="add-manual-btn">Agregar producto manual</button>
                                </div>
                                <div id="manualRecommendationsList" class="recommendations-list empty">
                                    <p class="text-sm text-gray-500">No hay productos manuales agregados.</p>
                                </div>
                            </div>
                        </div>

                        <div id="aiModeSection" class="card-section hidden">
                            <div class="card-section-header">
                                <h4>Sugerencias con IA</h4>
                                <p>Analiza el catálogo para proponer productos relevantes.</p>
                            </div>
                            <div class="card-section-body space-y-4">
                                <div id="aiSuggestionControls" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" id="onlySameCountry" class="text-blue-600 focus:ring-blue-500">
                                        Solo productos del país seleccionado
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" id="includeOtherCountries" class="text-blue-600 focus:ring-blue-500" checked>
                                        Incluir sugerencias de otros países
                                    </label>
                                </div>
                                <textarea id="suggestionContext" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Añade síntomas, contexto u objetivos específicos para el análisis."></textarea>
                                <button type="button" id="generateSuggestionsBtn" class="generate-suggestions-btn">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Generar sugerencias IA
                                </button>
                                <div id="aiSuggestionsContainer" class="space-y-4">
                                    <div>
                                        <h5 class="suggestion-group-title">Coincidencias del mismo país</h5>
                                        <div id="sameCountrySuggestions" class="recommendations-list empty">
                                            <p class="text-sm text-gray-500">Aún no se han generado sugerencias.</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="suggestion-group-title">Sugerencias de otros países (requieren aprobación)</h5>
                                        <div id="crossCountrySuggestions" class="recommendations-list empty">
                                            <p class="text-sm text-gray-500">Aún no se han generado sugerencias.</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="suggestion-group-title">Sugerencias aceptadas</h5>
                                        <div id="selectedAiRecommendations" class="recommendations-list empty">
                                            <p class="text-sm text-gray-500">Todavía no has aceptado sugerencias.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg flex justify-end gap-3">
                <button type="button" id="cancelDiseaseBtn" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Cancelar</button>
                <button type="submit" id="saveDiseaseBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Guardar índice
                </button>
            </div>
        </form>

        <script type="application/json" id="diseaseProductsData">{!! $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'country' => $product->country,
            'key_points' => $product->key_points,
            'information' => $product->information,
        ];
    })->toJson() !!}</script>
    </div>
</div>
