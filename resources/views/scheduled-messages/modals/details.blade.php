<!-- Modal para Ver Detalles del Mensaje -->
<div id="details-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header del Modal -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">Detalles del Mensaje</h3>
                <button id="close-details-modal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Contenido del Modal -->
        <div class="p-6">
            <!-- Información Principal -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <h2 id="details-title" class="text-2xl font-bold text-gray-900"></h2>
                    <span id="details-status-badge" class="px-3 py-1 rounded-full text-sm font-medium"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <div class="flex items-center gap-2">
                            <span id="details-category-badge" class="px-3 py-1 rounded-full text-sm font-medium"></span>
                            <span id="details-category-name" class="text-gray-900"></span>
                        </div>
                    </div>

                    <!-- Período de Tiempo -->
                    <div id="details-time-period-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Período de Tiempo</label>
                        <div class="flex items-center gap-2 text-gray-900">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="details-time-period"></span>
                        </div>
                    </div>

                    <!-- Horario -->
                    <div id="details-time-range-group">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horario (México)</label>
                        <div class="flex items-center gap-2 text-gray-900">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span id="details-time-range"></span>
                        </div>
                    </div>

                    <!-- Fecha de Creación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Creación</label>
                        <div class="flex items-center gap-2 text-gray-900">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span id="details-created-at"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pregunta Asociada -->
            <div id="details-question-group" class="mb-6 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pregunta Asociada</label>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p id="details-question" class="text-blue-900 font-medium"></p>
                    </div>
                </div>
            </div>

            <!-- Contenido del Mensaje -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Contenido del Mensaje</label>

                <!-- Mensaje de Texto -->
                <div id="details-text-group" class="mb-4 hidden">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Mensaje de Texto</span>
                        </div>
                        <div id="details-text" class="text-gray-900 whitespace-pre-wrap leading-relaxed"></div>
                    </div>
                </div>

                <!-- Mensaje de Audio -->
                <div id="details-audio-group" class="mb-4 hidden">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Mensaje de Audio</span>
                        </div>
                        <audio id="details-audio" controls class="w-full">
                            Tu navegador no soporta la reproducción de audio.
                        </audio>
                    </div>
                </div>

                <!-- Mensaje si no hay contenido -->
                <div id="details-no-content" class="text-center py-8 text-gray-500 hidden">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p>Sin contenido de mensaje</p>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Información Adicional</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Estado:</span>
                        <span id="details-status-text" class="ml-2 font-medium"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Última actualización:</span>
                        <span id="details-updated-at" class="ml-2 font-medium"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer del Modal -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg">
            <div class="flex justify-end gap-3">
                <button
                    id="edit-from-details-btn"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </button>
                <button
                    id="close-details-btn"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all duration-200"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
