<!-- Modal para Ver Mensajes Actuales -->
<div id="current-messages-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header del Modal -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Mensajes Actuales</h3>
                    <p class="text-sm text-gray-600 mt-1">Basados en la hora actual de México</p>
                </div>
                <button id="close-current-messages-modal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Contenido del Modal -->
        <div class="p-6">
            <!-- Información de Tiempo Actual -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-semibold text-blue-900 mb-1">Hora Actual de México</h4>
                        <div class="flex items-center gap-4 text-blue-800">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span id="current-time-display" class="font-medium"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                                <span id="current-period-display" class="font-medium px-2 py-1 bg-blue-100 rounded-full text-sm"></span>
                            </div>
                        </div>
                    </div>
                    <button
                        id="refresh-current-messages-btn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Actualizar
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div id="current-messages-loading" class="text-center py-12 hidden">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-gray-600">Cargando mensajes actuales...</p>
            </div>

            <!-- Tabs para diferentes tipos de filtros -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        type="button"
                        id="period-messages-tab"
                        class="current-tab-button active whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Por Período
                        </div>
                    </button>

                    <button
                        type="button"
                        id="time-range-messages-tab"
                        class="current-tab-button whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Por Horario Específico
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Panel de Mensajes por Período -->
            <div id="period-messages-panel" class="current-tab-panel">
                <div class="mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Mensajes del Período Actual</h4>
                    <p class="text-sm text-gray-600">Mensajes programados para el período de tiempo actual</p>
                </div>

                <div id="period-messages-list" class="space-y-3">
                    <!-- Los mensajes se cargarán aquí dinámicamente -->
                </div>
            </div>

            <!-- Panel de Mensajes por Horario -->
            <div id="time-range-messages-panel" class="current-tab-panel hidden">
                <div class="mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Mensajes por Horario Específico</h4>
                    <p class="text-sm text-gray-600">Mensajes con horarios específicos que incluyen la hora actual</p>
                </div>

                <div id="time-range-messages-list" class="space-y-3">
                    <!-- Los mensajes se cargarán aquí dinámicamente -->
                </div>
            </div>

            <!-- Estado vacío -->
            <div id="no-current-messages" class="text-center py-12 hidden">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h4 class="text-lg font-medium text-gray-900 mb-2">No hay mensajes activos</h4>
                <p class="text-gray-600 mb-4">No se encontraron mensajes programados para el momento actual</p>
                <button
                    id="create-message-from-current"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200"
                >
                    Crear Mensaje
                </button>
            </div>
        </div>

        <!-- Footer del Modal -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg">
            <div class="flex justify-end">
                <button
                    id="close-current-messages-btn"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all duration-200"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template para tarjeta de mensaje -->
<template id="current-message-card-template">
    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
                <h5 class="message-title font-semibold text-gray-900"></h5>
                <span class="message-category-badge px-2 py-1 rounded-full text-xs font-medium"></span>
            </div>
            <div class="flex items-center gap-2">
                <button class="view-message-detail-btn text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="message-content mb-3">
            <div class="message-text text-sm text-gray-700 mb-2 hidden"></div>
            <div class="message-audio hidden">
                <audio controls class="w-full h-8 text-xs">
                    Tu navegador no soporta la reproducción de audio.
                </audio>
            </div>
        </div>

        <div class="message-question bg-blue-50 border border-blue-200 rounded p-2 text-sm text-blue-800 mb-3 hidden">
            <strong>Pregunta:</strong> <span class="question-text"></span>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-4">
                <span class="message-time-info flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="time-range"></span>
                </span>
                <span class="message-audio-indicator hidden flex items-center gap-1 text-green-600">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 12a3 3 0 106 0v5a3 3 0 11-6 0V7a3 3 0 013-3z"></path>
                    </svg>
                    Audio
                </span>
            </div>
            <span class="message-created-at"></span>
        </div>
    </div>
</template>
