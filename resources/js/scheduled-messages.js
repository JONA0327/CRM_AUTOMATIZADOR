/**
 * Gestión de Mensajes Programados
 * Sistema completo para crear, editar y gestionar mensajes con texto y audio
 */

class ScheduledMessagesManager {
    constructor() {
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.recordingTimer = null;
        this.recordingSeconds = 0;
        this.currentMessageId = null;
        this.isEditing = false;

        this.initializeEventListeners();
        this.initializeModalHandlers();
        this.initializeTabHandlers();
        this.initializeAudioRecording();
        this.initializeFilters();
    }

    // =================== INICIALIZACIÓN ===================

    initializeEventListeners() {
        // Botones principales
        document.getElementById('create-message-btn')?.addEventListener('click', () => this.openCreateModal());
        document.getElementById('create-first-message-btn')?.addEventListener('click', () => this.openCreateModal());
        document.getElementById('current-messages-btn')?.addEventListener('click', () => this.loadCurrentMessages());

        // Botones de acción en la lista
        document.querySelectorAll('.view-message-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewMessage(e.target.closest('button').dataset.id));
        });

        document.querySelectorAll('.edit-message-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.editMessage(e.target.closest('button').dataset.id));
        });

        document.querySelectorAll('.toggle-status-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleMessageStatus(e.target.closest('button').dataset.id));
        });

        document.querySelectorAll('.delete-message-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.deleteMessage(e.target.closest('button').dataset.id));
        });

        // Formulario
        document.getElementById('message-form')?.addEventListener('submit', (e) => this.handleFormSubmit(e));

        // Contador de caracteres
        document.getElementById('message_text')?.addEventListener('input', (e) => this.updateCharacterCount(e.target));

        // Cambios en categoría y horarios
        document.getElementById('category')?.addEventListener('change', (e) => this.handleCategoryChange(e.target.value));
        document.getElementById('start_time')?.addEventListener('change', (e) => this.handleTimeChange());
        document.getElementById('end_time')?.addEventListener('change', (e) => this.handleTimeChange());
    }

    initializeModalHandlers() {
        // Modal principal
        document.getElementById('close-modal')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancel-btn')?.addEventListener('click', () => this.closeModal());

        // Modal de detalles
        document.getElementById('close-details-modal')?.addEventListener('click', () => this.closeDetailsModal());
        document.getElementById('close-details-btn')?.addEventListener('click', () => this.closeDetailsModal());
        document.getElementById('edit-from-details-btn')?.addEventListener('click', () => this.editFromDetails());

        // Modal de mensajes actuales
        document.getElementById('close-current-messages-modal')?.addEventListener('click', () => this.closeCurrentMessagesModal());
        document.getElementById('close-current-messages-btn')?.addEventListener('click', () => this.closeCurrentMessagesModal());
        document.getElementById('refresh-current-messages-btn')?.addEventListener('click', () => this.loadCurrentMessages());
        document.getElementById('create-message-from-current')?.addEventListener('click', () => {
            this.closeCurrentMessagesModal();
            this.openCreateModal();
        });

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Cerrar modales haciendo clic fuera
        document.getElementById('message-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'message-modal') this.closeModal();
        });

        document.getElementById('details-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'details-modal') this.closeDetailsModal();
        });

        document.getElementById('current-messages-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'current-messages-modal') this.closeCurrentMessagesModal();
        });
    }

    initializeTabHandlers() {
        // Tabs en modal principal
        document.getElementById('text-tab')?.addEventListener('click', () => this.switchTab('text'));
        document.getElementById('audio-tab')?.addEventListener('click', () => this.switchTab('audio'));

        // Tabs en modal de mensajes actuales
        document.getElementById('period-messages-tab')?.addEventListener('click', () => this.switchCurrentTab('period'));
        document.getElementById('time-range-messages-tab')?.addEventListener('click', () => this.switchCurrentTab('time-range'));
    }

    initializeAudioRecording() {
        document.getElementById('record-btn')?.addEventListener('click', () => this.toggleRecording());
        document.getElementById('stop-btn')?.addEventListener('click', () => this.stopRecording());
        document.getElementById('delete-audio-btn')?.addEventListener('click', () => this.deleteAudio());
    }

    initializeFilters() {
        // Filtros de la página principal
        document.getElementById('category-filter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('time-period-filter')?.addEventListener('change', () => this.applyFilters());
        document.getElementById('status-filter')?.addEventListener('change', () => this.applyFilters());
    }

    // =================== GESTIÓN DE MODALES ===================

    openCreateModal() {
        this.isEditing = false;
        this.currentMessageId = null;
        this.resetForm();

        document.getElementById('modal-title').textContent = 'Crear Nuevo Mensaje';
        document.getElementById('save-text').textContent = 'Guardar Mensaje';
        document.getElementById('message-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Focus en el primer campo
        document.getElementById('title')?.focus();
    }

    async editMessage(messageId) {
        try {
            const response = await fetch(`/scheduled-messages/${messageId}/edit`);
            if (!response.ok) throw new Error('Error al cargar el mensaje');

            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Error desconocido');

            this.isEditing = true;
            this.currentMessageId = messageId;
            this.populateForm(data.message);

            document.getElementById('modal-title').textContent = 'Editar Mensaje';
            document.getElementById('save-text').textContent = 'Actualizar Mensaje';
            document.getElementById('message-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al cargar el mensaje para editar', 'error');
        }
    }

    closeModal() {
        document.getElementById('message-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        this.resetForm();
        this.stopRecording();
    }

    async viewMessage(messageId) {
        try {
            const response = await fetch(`/scheduled-messages/${messageId}`);
            if (!response.ok) throw new Error('Error al cargar el mensaje');

            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Error desconocido');

            this.populateDetailsModal(data.message);
            document.getElementById('details-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al cargar los detalles del mensaje', 'error');
        }
    }

    closeDetailsModal() {
        document.getElementById('details-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    closeCurrentMessagesModal() {
        document.getElementById('current-messages-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    closeAllModals() {
        this.closeModal();
        this.closeDetailsModal();
        this.closeCurrentMessagesModal();
    }

    editFromDetails() {
        const messageId = document.getElementById('edit-from-details-btn').dataset.messageId;
        this.closeDetailsModal();
        this.editMessage(messageId);
    }

    // =================== GESTIÓN DE FORMULARIO ===================

    resetForm() {
        document.getElementById('message-form').reset();
        document.getElementById('message-id').value = '';
        document.getElementById('time_period_display').value = '';
        document.getElementById('text-counter').textContent = '0';

        // Resetear validaciones
        document.getElementById('content-validation').classList.add('hidden');

        // Resetear tabs
        this.switchTab('text');

        // Resetear audio
        this.deleteAudio();

        // Ocultar grupos condicionales
        document.getElementById('associated-question-group').classList.add('hidden');
        document.getElementById('time-configuration-group').classList.add('hidden');

        // Limpiar required en campos condicionales
        document.getElementById('associated_question').removeAttribute('required');
    }

    populateForm(message) {
        document.getElementById('message-id').value = message.id;
        document.getElementById('title').value = message.title || '';
        document.getElementById('category').value = message.category || '';
        document.getElementById('message_text').value = message.message_text || '';
        document.getElementById('associated_question').value = message.associated_question || '';
        document.getElementById('start_time').value = message.start_time ? message.start_time.substring(0, 5) : '';
        document.getElementById('end_time').value = message.end_time ? message.end_time.substring(0, 5) : '';
        document.getElementById('is_active').value = message.is_active ? '1' : '0';

        // Actualizar contador de caracteres
        this.updateCharacterCount(document.getElementById('message_text'));

        // Manejar audio si existe
        if (message.audio_data) {
            document.getElementById('audio_data').value = message.audio_data;
            this.showAudioPreview(message.audio_url);
        }

        // Manejar cambios en categoría y tiempo
        this.handleCategoryChange(message.category);
        this.handleTimeChange();

        // Seleccionar tab apropiado
        if (message.message_text && !message.audio_data) {
            this.switchTab('text');
        } else if (message.audio_data && !message.message_text) {
            this.switchTab('audio');
        } else if (message.message_text && message.audio_data) {
            this.switchTab('text');
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        // Validación de contenido
        if (!data.message_text && !data.audio_data) {
            document.getElementById('content-validation').classList.remove('hidden');
            return;
        } else {
            document.getElementById('content-validation').classList.add('hidden');
        }

        // Preparar datos para envío
        const submitData = {
            title: data.title,
            message_text: data.message_text || null,
            audio_data: data.audio_data || null,
            category: data.category,
            associated_question: data.associated_question || null,
            start_time: data.start_time || null,
            end_time: data.end_time || null,
            is_active: data.is_active === '1'
        };

        try {
            let url = '/scheduled-messages';
            let method = 'POST';

            if (this.isEditing && this.currentMessageId) {
                url = `/scheduled-messages/${this.currentMessageId}`;
                method = 'PUT';
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(submitData)
            });

            if (!response.ok) throw new Error('Error en la petición');

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message, 'success');
                this.closeModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(result.message || 'Error desconocido');
            }

        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al guardar el mensaje', 'error');
        }
    }

    // =================== GESTIÓN DE CATEGORÍAS Y TIEMPO ===================

    handleCategoryChange(category) {
        const questionGroup = document.getElementById('associated-question-group');
        const timeGroup = document.getElementById('time-configuration-group');
        const questionField = document.getElementById('associated_question');

        // Mostrar/ocultar campos según categoría
        if (category === 'contestar_preguntas') {
            questionGroup.classList.remove('hidden');
            questionField.setAttribute('required', 'required');
        } else {
            questionGroup.classList.add('hidden');
            questionField.removeAttribute('required');
            questionField.value = '';
        }

        if (category === 'bienvenida') {
            timeGroup.classList.remove('hidden');
        } else {
            timeGroup.classList.add('hidden');
            document.getElementById('start_time').value = '';
            document.getElementById('end_time').value = '';
            document.getElementById('time_period_display').value = '';
        }
    }

    handleTimeChange() {
        const startTime = document.getElementById('start_time').value;
        if (startTime) {
            const hour = parseInt(startTime.split(':')[0]);
            const period = this.determineTimePeriod(hour);
            const periodNames = {
                'mañana': 'Mañana (06:00 - 11:59)',
                'tarde': 'Tarde (12:00 - 17:59)',
                'noche': 'Noche (18:00 - 05:59)'
            };
            document.getElementById('time_period_display').value = periodNames[period] || '';
        } else {
            document.getElementById('time_period_display').value = '';
        }
    }

    determineTimePeriod(hour) {
        if (hour >= 6 && hour < 12) {
            return 'mañana';
        } else if (hour >= 12 && hour < 18) {
            return 'tarde';
        } else {
            return 'noche';
        }
    }

    // =================== GESTIÓN DE AUDIO ===================

    async toggleRecording() {
        if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
            this.stopRecording();
        } else {
            await this.startRecording();
        }
    }

    async startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.mediaRecorder = new MediaRecorder(stream);
            this.audioChunks = [];
            this.recordingSeconds = 0;

            this.mediaRecorder.ondataavailable = (event) => {
                this.audioChunks.push(event.data);
            };

            this.mediaRecorder.onstop = () => {
                const audioBlob = new Blob(this.audioChunks, { type: 'audio/mpeg' });
                this.processAudioBlob(audioBlob);
                stream.getTracks().forEach(track => track.stop());
            };

            this.mediaRecorder.start();
            this.updateRecordingUI(true);
            this.startRecordingTimer();

        } catch (error) {
            console.error('Error al acceder al micrófono:', error);
            this.showNotification('Error al acceder al micrófono', 'error');
        }
    }

    stopRecording() {
        if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
            this.mediaRecorder.stop();
            this.updateRecordingUI(false);
            this.stopRecordingTimer();
        }
    }

    processAudioBlob(blob) {
        const reader = new FileReader();
        reader.onloadend = () => {
            const base64 = reader.result.split(',')[1];
            document.getElementById('audio_data').value = base64;

            const audioUrl = URL.createObjectURL(blob);
            this.showAudioPreview(audioUrl);
        };
        reader.readAsDataURL(blob);
    }

    showAudioPreview(audioUrl) {
        const audioPreview = document.getElementById('audio-preview');
        const visualizer = document.getElementById('audio-visualizer');

        audioPreview.src = audioUrl;
        visualizer.classList.remove('hidden');
    }

    deleteAudio() {
        document.getElementById('audio_data').value = '';
        document.getElementById('audio-preview').src = '';
        document.getElementById('audio-visualizer').classList.add('hidden');
    }

    updateRecordingUI(isRecording) {
        const recordBtn = document.getElementById('record-btn');
        const stopBtn = document.getElementById('stop-btn');
        const recordingStatus = document.getElementById('recording-status');
        const recordText = document.getElementById('record-text');

        if (isRecording) {
            recordBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');
            recordingStatus.classList.remove('hidden');
            recordText.textContent = 'Grabando...';
        } else {
            recordBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            recordingStatus.classList.add('hidden');
            recordText.textContent = 'Grabar Audio';
        }
    }

    startRecordingTimer() {
        this.recordingTimer = setInterval(() => {
            this.recordingSeconds++;
            const minutes = Math.floor(this.recordingSeconds / 60);
            const seconds = this.recordingSeconds % 60;
            document.getElementById('recording-time').textContent =
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            // Límite de 2 minutos
            if (this.recordingSeconds >= 120) {
                this.stopRecording();
                this.showNotification('Grabación detenida: límite de 2 minutos alcanzado', 'warning');
            }
        }, 1000);
    }

    stopRecordingTimer() {
        if (this.recordingTimer) {
            clearInterval(this.recordingTimer);
            this.recordingTimer = null;
        }
    }

    // =================== GESTIÓN DE TABS ===================

    switchTab(tabName) {
        // Actualizar botones de tab
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });

        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // Activar tab seleccionado
        document.getElementById(`${tabName}-tab`).classList.add('active', 'border-blue-500', 'text-blue-600');
        document.getElementById(`${tabName}-panel`).classList.remove('hidden');
    }

    switchCurrentTab(tabName) {
        // Actualizar botones de tab en modal de mensajes actuales
        document.querySelectorAll('.current-tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });

        document.querySelectorAll('.current-tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // Activar tab seleccionado
        document.getElementById(`${tabName}-messages-tab`).classList.add('active', 'border-blue-500', 'text-blue-600');
        document.getElementById(`${tabName}-messages-panel`).classList.remove('hidden');
    }

    // =================== UTILIDADES ===================

    updateCharacterCount(textarea) {
        const count = textarea.value.length;
        document.getElementById('text-counter').textContent = count;
    }

    async toggleMessageStatus(messageId) {
        try {
            const response = await fetch(`/scheduled-messages/${messageId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) throw new Error('Error en la petición');

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(result.message || 'Error desconocido');
            }

        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al cambiar el estado del mensaje', 'error');
        }
    }

    async deleteMessage(messageId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este mensaje? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const response = await fetch(`/scheduled-messages/${messageId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (!response.ok) throw new Error('Error en la petición');

            const result = await response.json();

            if (result.success) {
                this.showNotification(result.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(result.message || 'Error desconocido');
            }

        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al eliminar el mensaje', 'error');
        }
    }

    applyFilters() {
        const category = document.getElementById('category-filter').value;
        const timePeriod = document.getElementById('time-period-filter').value;
        const status = document.getElementById('status-filter').value;

        const params = new URLSearchParams();
        if (category) params.append('category', category);
        if (timePeriod) params.append('time_period', timePeriod);
        if (status) params.append('status', status);

        window.location.href = `/scheduled-messages?${params.toString()}`;
    }

    async loadCurrentMessages() {
        const modal = document.getElementById('current-messages-modal');
        const loading = document.getElementById('current-messages-loading');

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        loading.classList.remove('hidden');

        try {
            const response = await fetch('/scheduled-messages/current');
            if (!response.ok) throw new Error('Error al cargar mensajes actuales');

            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Error desconocido');

            this.displayCurrentMessages(data);

        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error al cargar los mensajes actuales', 'error');
        } finally {
            loading.classList.add('hidden');
        }
    }

    displayCurrentMessages(data) {
        // Actualizar información de tiempo
        document.getElementById('current-time-display').textContent =
            new Date(data.current_time).toLocaleString('es-MX');
        document.getElementById('current-period-display').textContent =
            data.time_periods[data.time_period] || data.time_period;

        // Mostrar mensajes por período
        this.renderMessagesList(data.messages_by_period, 'period-messages-list');

        // Mostrar mensajes por rango horario
        this.renderMessagesList(data.messages_by_time_range, 'time-range-messages-list');

        // Mostrar estado vacío si no hay mensajes
        const hasMessages = data.messages_by_period.length > 0 || data.messages_by_time_range.length > 0;
        document.getElementById('no-current-messages').classList.toggle('hidden', hasMessages);
    }

    renderMessagesList(messages, containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        if (messages.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <p>No hay mensajes para este criterio</p>
                </div>
            `;
            return;
        }

        messages.forEach(message => {
            const messageCard = this.createMessageCard(message);
            container.appendChild(messageCard);
        });
    }

    createMessageCard(message) {
        const template = document.getElementById('current-message-card-template');
        const card = template.content.cloneNode(true);

        // Datos básicos
        card.querySelector('.message-title').textContent = message.title;
        card.querySelector('.message-created-at').textContent =
            new Date(message.created_at).toLocaleDateString('es-MX');

        // Badge de categoría
        const categoryBadge = card.querySelector('.message-category-badge');
        const categoryClasses = {
            'bienvenida': 'bg-blue-100 text-blue-800',
            'seguimiento': 'bg-yellow-100 text-yellow-800',
            'contestar_preguntas': 'bg-green-100 text-green-800',
            'informacion_productos': 'bg-purple-100 text-purple-800'
        };
        categoryBadge.className = `message-category-badge px-2 py-1 rounded-full text-xs font-medium ${categoryClasses[message.category] || 'bg-gray-100 text-gray-800'}`;
        categoryBadge.textContent = message.category_name;

        // Contenido
        if (message.message_text) {
            const textElement = card.querySelector('.message-text');
            textElement.textContent = message.message_text.substring(0, 150) +
                (message.message_text.length > 150 ? '...' : '');
            textElement.classList.remove('hidden');
        }

        if (message.audio_data) {
            const audioElement = card.querySelector('.message-audio audio');
            audioElement.src = `data:audio/mpeg;base64,${message.audio_data}`;
            card.querySelector('.message-audio').classList.remove('hidden');
            card.querySelector('.message-audio-indicator').classList.remove('hidden');
        }

        // Pregunta asociada
        if (message.associated_question) {
            const questionElement = card.querySelector('.message-question');
            card.querySelector('.question-text').textContent = message.associated_question;
            questionElement.classList.remove('hidden');
        }

        // Información de tiempo
        if (message.start_time && message.end_time) {
            card.querySelector('.time-range').textContent =
                `${message.start_time.substring(0, 5)} - ${message.end_time.substring(0, 5)}`;
        } else if (message.time_period_name) {
            card.querySelector('.time-range').textContent = message.time_period_name;
        }

        // Event listener para ver detalles
        card.querySelector('.view-message-detail-btn').addEventListener('click', () => {
            this.closeCurrentMessagesModal();
            this.viewMessage(message.id);
        });

        return card;
    }

    populateDetailsModal(message) {
        // Información básica
        document.getElementById('details-title').textContent = message.title;
        document.getElementById('details-created-at').textContent =
            new Date(message.created_at).toLocaleString('es-MX');
        document.getElementById('details-updated-at').textContent =
            new Date(message.updated_at).toLocaleString('es-MX');

        // Estado
        const statusBadge = document.getElementById('details-status-badge');
        const statusText = document.getElementById('details-status-text');
        if (message.is_active) {
            statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
            statusBadge.textContent = 'Activo';
            statusText.textContent = 'Activo';
        } else {
            statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
            statusBadge.textContent = 'Inactivo';
            statusText.textContent = 'Inactivo';
        }

        // Categoría
        const categoryBadge = document.getElementById('details-category-badge');
        const categoryClasses = {
            'bienvenida': 'bg-blue-100 text-blue-800',
            'seguimiento': 'bg-yellow-100 text-yellow-800',
            'contestar_preguntas': 'bg-green-100 text-green-800',
            'informacion_productos': 'bg-purple-100 text-purple-800'
        };
        categoryBadge.className = `px-3 py-1 rounded-full text-sm font-medium ${categoryClasses[message.category] || 'bg-gray-100 text-gray-800'}`;
        categoryBadge.textContent = message.category_name;
        document.getElementById('details-category-name').textContent = message.category_name;

        // Período de tiempo
        if (message.time_period_name) {
            document.getElementById('details-time-period').textContent = message.time_period_name;
            document.getElementById('details-time-period-group').classList.remove('hidden');
        } else {
            document.getElementById('details-time-period-group').classList.add('hidden');
        }

        // Rango horario
        if (message.start_time && message.end_time) {
            document.getElementById('details-time-range').textContent =
                `${message.start_time.substring(0, 5)} - ${message.end_time.substring(0, 5)}`;
            document.getElementById('details-time-range-group').classList.remove('hidden');
        } else {
            document.getElementById('details-time-range-group').classList.add('hidden');
        }

        // Pregunta asociada
        if (message.associated_question) {
            document.getElementById('details-question').textContent = message.associated_question;
            document.getElementById('details-question-group').classList.remove('hidden');
        } else {
            document.getElementById('details-question-group').classList.add('hidden');
        }

        // Contenido
        let hasContent = false;

        if (message.message_text) {
            document.getElementById('details-text').textContent = message.message_text;
            document.getElementById('details-text-group').classList.remove('hidden');
            hasContent = true;
        } else {
            document.getElementById('details-text-group').classList.add('hidden');
        }

        if (message.audio_url) {
            document.getElementById('details-audio').src = message.audio_url;
            document.getElementById('details-audio-group').classList.remove('hidden');
            hasContent = true;
        } else {
            document.getElementById('details-audio-group').classList.add('hidden');
        }

        document.getElementById('details-no-content').classList.toggle('hidden', hasContent);

        // Configurar botón de editar
        document.getElementById('edit-from-details-btn').dataset.messageId = message.id;
    }

    showNotification(message, type = 'info') {
        // Crear notificación toast
        const notification = document.createElement('div');
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        }[type] || 'bg-blue-500';

        notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Remover después de 4 segundos
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 4000);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.scheduledMessagesManager = new ScheduledMessagesManager();
});
