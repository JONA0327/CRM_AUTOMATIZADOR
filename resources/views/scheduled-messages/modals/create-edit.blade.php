<!-- Modal para Crear/Editar Mensaje -->
<div id="message-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header del Modal -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 id="modal-title" class="text-xl font-semibold text-gray-900">Crear Nuevo Mensaje</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Contenido del Modal -->
        <form id="message-form" class="p-6">
            <input type="hidden" id="message-id" name="message_id">

            <!-- Información Básica -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Título -->
                <div class="lg:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Título del Mensaje <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ej: Mensaje de bienvenida matutino"
                        required
                    >
                </div>

                <!-- Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="category"
                        name="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Seleccionar categoría</option>
                        <option value="bienvenida">Mensaje de Bienvenida</option>
                        <option value="seguimiento">Seguimiento</option>
                        <option value="contestar_preguntas">Contestar Preguntas</option>
                        <option value="informacion_productos">Información Adicional de Productos</option>
                    </select>
                </div>

                <!-- Estado -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select
                        id="is_active"
                        name="is_active"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Pregunta Asociada (solo para contestar_preguntas) -->
            <div id="associated-question-group" class="mb-6 hidden">
                <label for="associated_question" class="block text-sm font-medium text-gray-700 mb-2">
                    Pregunta Asociada <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="associated_question"
                    name="associated_question"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="¿Cuál es el precio del producto?"
                >
            </div>

            <!-- Configuración de Horario (solo para bienvenida) -->
            <div id="time-configuration-group" class="mb-6 hidden">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Configuración de Horario (Zona México)</h4>
                    <p class="text-xs text-blue-700">El sistema determinará automáticamente el período (mañana/tarde/noche) según la hora de inicio</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Hora Inicio</label>
                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">Hora Fin</label>
                        <input
                            type="time"
                            id="end_time"
                            name="end_time"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <div>
                        <label for="time_period_display" class="block text-sm font-medium text-gray-700 mb-2">Período Detectado</label>
                        <input
                            type="text"
                            id="time_period_display"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                            placeholder="Se detectará automáticamente"
                            readonly
                        >
                    </div>
                </div>
            </div>

            <!-- Contenido del Mensaje -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Contenido del Mensaje</h4>

                <!-- Tabs para Texto y Audio -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            type="button"
                            id="text-tab"
                            class="tab-button active whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                                Mensaje de Texto
                            </div>
                        </button>

                        <button
                            type="button"
                            id="audio-tab"
                            class="tab-button whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                </svg>
                                Mensaje de Audio
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Panel de Texto -->
                <div id="text-panel" class="tab-panel">
                    <div class="mb-4">
                        <label for="message_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Texto del Mensaje
                        </label>
                        <textarea
                            id="message_text"
                            name="message_text"
                            rows="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                            placeholder="Escribe aquí el contenido de tu mensaje..."
                        ></textarea>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-gray-500">Caracteres: <span id="text-counter">0</span></span>
                            <button
                                type="button"
                                id="preview-text-btn"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                Vista Previa
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel de Audio -->
                <div id="audio-panel" class="tab-panel hidden">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <!-- Controles de Grabación -->
                        <div class="text-center mb-6">
                            <div class="flex justify-center items-center gap-4 mb-4">
                                <button
                                    type="button"
                                    id="record-btn"
                                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-full font-medium transition-all duration-200 flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                    </svg>
                                    <span id="record-text">Grabar Audio</span>
                                </button>

                                <button
                                    type="button"
                                    id="stop-btn"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-full font-medium transition-all duration-200 flex items-center gap-2 hidden"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"></path>
                                    </svg>
                                    Detener
                                </button>
                            </div>

                            <div id="recording-status" class="text-sm text-gray-600 mb-4 hidden">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                                    Grabando... <span id="recording-time">00:00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Visualizador de Audio -->
                        <div id="audio-visualizer" class="hidden mb-4">
                            <div class="bg-white border border-gray-300 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Audio Grabado</span>
                                    <button
                                        type="button"
                                        id="delete-audio-btn"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                                <audio id="audio-preview" controls class="w-full">
                                    Tu navegador no soporta la reproducción de audio.
                                </audio>
                            </div>
                        </div>

                        <!-- Fallback: Subir archivo de audio cuando la captura no esté disponible -->
                        <div id="audio-fallback" class="hidden text-center mb-4">
                            <label for="audio-file-input" class="block text-sm font-medium text-gray-700 mb-2">Subir archivo de audio (MP3, WAV)</label>
                            <input type="file" id="audio-file-input" accept="audio/*" class="mx-auto" />
                        </div>

                        <div id="recorder-unavailable-message" class="hidden mt-4 text-sm text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
                            La grabación directa desde el navegador no está disponible porque el dispositivo o navegador no soporta la captura de audio. Utiliza el botón "Subir archivo de audio" como alternativa.
                        </div>

                        <!-- Input oculto para el audio en base64 -->
                        <input type="hidden" id="audio_data" name="audio_data">

                        <!-- Información -->
                        <div class="text-center text-sm text-gray-600">
                            <p class="mb-2">Haz clic en "Grabar Audio" para comenzar a grabar tu mensaje</p>
                            <p class="text-xs">Formatos soportados: MP3, WAV. Duración máxima: 2 minutos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validación de Contenido -->
            <div id="content-validation" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-yellow-800 font-medium">Atención</p>
                        <p class="text-sm text-yellow-700">Debe proporcionar al menos un mensaje de texto o un audio.</p>
                    </div>
                </div>
            </div>

            <!-- Botones del Modal -->
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <button
                    type="button"
                    id="cancel-btn"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all duration-200"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    id="save-btn"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span id="save-text">Guardar Mensaje</span>
                </button>
            </div>
        </form>
    </div>
</div>
