<!-- Modal de detalles de la enfermedad -->
<div id="diseaseDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-800 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900" id="detailDiseaseName">Condición</h3>
                            </div>
                            <button type="button" id="closeDiseaseDetailsModal" class="bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div class="detail-section">
                                <h4>Información de apoyo</h4>
                                <p id="detailDiseaseInformation" class="text-gray-700 leading-relaxed"></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="detail-section">
                                    <div class="detail-section-header">
                                        <h4>Productos manuales</h4>
                                        <span id="detailManualCount" class="detail-count-badge">0</span>
                                    </div>
                                    <div id="detailManualList" class="detail-list empty">
                                        <p class="text-sm text-gray-500">Sin productos manuales registrados.</p>
                                    </div>
                                </div>
                                <div class="detail-section">
                                    <div class="detail-section-header">
                                        <h4>Sugerencias IA</h4>
                                        <span id="detailAiCount" class="detail-count-badge">0</span>
                                    </div>
                                    <div id="detailAiList" class="detail-list empty">
                                        <p class="text-sm text-gray-500">Sin sugerencias de IA registradas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="closeDiseaseDetailsBtn" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
