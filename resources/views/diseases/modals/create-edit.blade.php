<!-- Modal para Crear/Editar Índice -->
<div id="diseaseModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <form id="diseaseForm">
                @csrf
                <input type="hidden" id="diseaseId" name="disease_id">
                <input type="hidden" id="diseaseFormMethod" name="_method" value="POST">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900" id="diseaseModalTitle">Crear índice</h3>
                                        <p class="text-sm text-gray-600">Define la condición, agrega productos y genera soporte inteligente.</p>
                                    </div>
                                </div>
                                <button type="button" id="closeDiseaseModal" class="bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-6">
                                        <div>
                                            <label for="diseaseName" class="block text-sm font-medium text-gray-700 mb-2">Nombre de la condición *</label>
                                            <input type="text" id="diseaseName" name="name" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ej: Resistencia inmunológica baja">
                                        </div>

                                        <div>
                                            <label for="diseaseCountry" class="block text-sm font-medium text-gray-700 mb-2">País principal *</label>
                                            <select id="diseaseCountry" name="country" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Selecciona un país</option>
                                                @foreach($availableCountries as $country)
                                                    <option value="{{ $country }}">{{ $country }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <span class="block text-sm font-medium text-gray-700 mb-2">Modo de información *</span>
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

                                        <div>
                                            <label for="diseaseInformation" class="block text-sm font-medium text-gray-700 mb-2">Información de apoyo</label>
                                            <textarea id="diseaseInformation" name="information" rows="6" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Explica cómo los productos apoyan esta condición sin prometer curación."></textarea>
                                            <p class="mt-2 text-xs text-gray-500">Si eliges la opción de IA, puedes generar un texto detallado basado en los productos seleccionados.</p>
                                            <button type="button" id="generateInformationBtn" class="generate-info-btn mt-3 hidden">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a4 4 0 004-4M7 15V9a5 5 0 0110 0v6"></path>
                                                </svg>
                                                Generar información con IA
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <div class="card-section">
                                            <div class="card-section-header">
                                                <h4>Productos añadidos manualmente</h4>
                                                <p>Selecciona productos y describe por qué apoyan esta condición.</p>
                                            </div>
                                            <div class="card-section-body space-y-4">
                                                <div class="flex flex-col gap-3">
                                                    <select id="manualProductSelect" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                                        <option value="">Selecciona un producto</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}" data-country="{{ $product->country }}" data-name="{{ $product->name }}">
                                                                {{ $product->name }} ({{ $product->country }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <textarea id="manualProductReason" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Describe cómo este producto apoya la condición."></textarea>
                                                    <button type="button" id="addManualProductBtn" class="add-manual-btn">Agregar producto manual</button>
                                                </div>
                                                <div id="manualRecommendationsList" class="recommendations-list empty">
                                                    <p class="text-sm text-gray-500">No hay productos manuales agregados.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-section">
                                            <div class="card-section-header">
                                                <h4>Sugerencias con IA</h4>
                                                <p>Analiza el catálogo para proponer productos relevantes.</p>
                                            </div>
                                            <div class="card-section-body space-y-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                                        <input type="checkbox" id="onlySameCountry" class="text-blue-600 focus:ring-blue-500">
                                                        Solo productos del país seleccionado
                                                    </label>
                                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                                        <input type="checkbox" id="includeOtherCountries" class="text-blue-600 focus:ring-blue-500" checked>
                                                        Incluir sugerencias de otros países
                                                    </label>
                                                </div>
                                                <textarea id="suggestionContext" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Añade síntomas, contexto u objetivos específicos para el análisis."></textarea>
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
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="saveDiseaseBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar índice
                    </button>
                    <button type="button" id="cancelDiseaseBtn" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

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
