/**
 * Disease index management
 * Handles creation, edition and AI integrations for disease-product relationships
 */

class DiseaseManager {
    constructor() {
        this.diseases = [];
        this.filteredDiseases = [];
        this.currentCountry = '';
        this.searchTerm = '';
        this.manualRecommendations = [];
        this.selectedAiRecommendations = [];
        this.lastSuggestions = { same_country: [], cross_country: [] };
        this.isEditing = false;
        this.currentDiseaseId = null;
        this.products = [];
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
        this.parseProducts();
        this.bindEvents();
        this.loadDiseases();
    }

    parseProducts() {
        const dataElement = document.getElementById('diseaseProductsData');
        if (dataElement) {
            try {
                this.products = JSON.parse(dataElement.textContent || '[]');
            } catch (error) {
                console.error('No se pudo parsear la data de productos', error);
                this.products = [];
            }
        }
    }

    bindEvents() {
        const createBtn = document.getElementById('createDiseaseBtn');
        const createFirstBtn = document.getElementById('createFirstDiseaseBtn');
        const closeModalBtn = document.getElementById('closeDiseaseModal');
        const cancelBtn = document.getElementById('cancelDiseaseBtn');
        const form = document.getElementById('diseaseForm');
        const addManualBtn = document.getElementById('addManualProductBtn');
        const generateSuggestionsBtn = document.getElementById('generateSuggestionsBtn');
        const generateInfoBtn = document.getElementById('generateInformationBtn');
        const informationModeInputs = document.querySelectorAll('input[name="information_mode"]');
        const closeDetailBtn = document.getElementById('closeDiseaseDetailsBtn');
        const closeDetailModalBtn = document.getElementById('closeDiseaseDetailsModal');

        createBtn?.addEventListener('click', () => this.openCreateModal());
        createFirstBtn?.addEventListener('click', () => this.openCreateModal());
        closeModalBtn?.addEventListener('click', () => this.closeModal());
        cancelBtn?.addEventListener('click', () => this.closeModal());
        closeDetailBtn?.addEventListener('click', () => this.closeDetailsModal());
        closeDetailModalBtn?.addEventListener('click', () => this.closeDetailsModal());

        informationModeInputs.forEach((input) => {
            input.addEventListener('change', (event) => this.toggleInformationMode(event.target.value));
        });

        addManualBtn?.addEventListener('click', () => this.addManualRecommendation());
        generateSuggestionsBtn?.addEventListener('click', () => this.generateSuggestions());
        generateInfoBtn?.addEventListener('click', () => this.generateInformation());

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            this.saveDisease();
        });

        const countryFilter = document.getElementById('countryFilter');
        const searchInput = document.getElementById('searchDiseases');

        countryFilter?.addEventListener('change', (event) => {
            this.currentCountry = event.target.value;
            this.filterDiseases();
        });

        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (event) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.searchTerm = event.target.value.toLowerCase();
                    this.filterDiseases();
                }, 250);
            });
        }

        document.addEventListener('click', (event) => {
            const diseaseCard = event.target.closest('.disease-card');
            if (diseaseCard && !event.target.closest('.disease-actions')) {
                const diseaseId = diseaseCard.dataset.diseaseId;
                this.showDiseaseDetails(diseaseId);
                return;
            }

            if (event.target.closest('.edit-disease-btn')) {
                event.stopPropagation();
                const id = event.target.closest('.edit-disease-btn').dataset.diseaseId;
                this.editDisease(id);
            }

            if (event.target.closest('.delete-disease-btn')) {
                event.stopPropagation();
                const id = event.target.closest('.delete-disease-btn').dataset.diseaseId;
                this.deleteDisease(id);
            }

            if (event.target.closest('.remove-manual-recommendation')) {
                const index = Number(event.target.closest('[data-index]')?.dataset.index);
                if (! Number.isNaN(index)) {
                    this.manualRecommendations.splice(index, 1);
                    this.renderManualRecommendations();
                }
            }

            if (event.target.closest('.remove-ai-recommendation')) {
                const index = Number(event.target.closest('[data-index]')?.dataset.index);
                if (! Number.isNaN(index)) {
                    this.selectedAiRecommendations.splice(index, 1);
                    this.renderSelectedAiRecommendations();
                }
            }

            if (event.target.closest('.add-suggestion-btn')) {
                const button = event.target.closest('.add-suggestion-btn');
                const suggestionId = button.dataset.suggestionId;
                const isCrossCountry = button.dataset.crossCountry === 'true';
                const group = isCrossCountry ? this.lastSuggestions.cross_country : this.lastSuggestions.same_country;
                const suggestion = group.find((item) => `${item.product_id}` === suggestionId);
                if (suggestion) {
                    this.addAiRecommendation({ ...suggestion });
                }
            }

            if (event.target.closest('.approve-suggestion-btn')) {
                const button = event.target.closest('.approve-suggestion-btn');
                const suggestionId = button.dataset.suggestionId;
                this.approveSuggestion(suggestionId);
            }
        });
    }

    loadDiseases() {
        const cards = document.querySelectorAll('.disease-card');
        this.diseases = Array.from(cards).map((card) => ({
            id: card.dataset.diseaseId,
            name: card.dataset.diseaseName || '',
            country: card.dataset.country,
            element: card,
        }));

        this.filteredDiseases = [...this.diseases];
        this.updateDiseaseCount();
    }

    filterDiseases() {
        const sections = document.querySelectorAll('.country-section');
        let visible = 0;

        sections.forEach((section) => {
            const country = section.dataset.country;
            let hasVisible = false;

            section.querySelectorAll('.disease-card').forEach((card) => {
                const matchesCountry = !this.currentCountry || country === this.currentCountry;
                const matchesSearch = !this.searchTerm || (card.dataset.diseaseName || '').includes(this.searchTerm);

                if (matchesCountry && matchesSearch) {
                    card.classList.remove('hidden');
                    hasVisible = true;
                    visible += 1;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (hasVisible) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        });

        this.updateDiseaseCount(visible);
    }

    updateDiseaseCount(count) {
        const counter = document.getElementById('diseaseCount');
        if (! counter) return;

        if (typeof count === 'number') {
            counter.textContent = count;
            return;
        }

        const total = document.querySelectorAll('.disease-card').length;
        counter.textContent = total;
    }

    toggleInformationMode(mode) {
        const generateInfoBtn = document.getElementById('generateInformationBtn');
        if (! generateInfoBtn) return;

        if (mode === 'ai') {
            generateInfoBtn.classList.remove('hidden');
        } else {
            generateInfoBtn.classList.add('hidden');
        }
    }

    resetForm() {
        const form = document.getElementById('diseaseForm');
        if (! form) return;

        form.reset();
        this.manualRecommendations = [];
        this.selectedAiRecommendations = [];
        this.lastSuggestions = { same_country: [], cross_country: [] };
        this.isEditing = false;
        this.currentDiseaseId = null;
        this.toggleInformationMode('manual');
        this.renderManualRecommendations();
        this.renderSelectedAiRecommendations();
        this.renderSuggestions();
    }

    openCreateModal() {
        this.resetForm();
        const modal = document.getElementById('diseaseModal');
        const title = document.getElementById('diseaseModalTitle');
        if (title) title.textContent = 'Crear índice';
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    closeModal() {
        const modal = document.getElementById('diseaseModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    closeDetailsModal() {
        const modal = document.getElementById('diseaseDetailsModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    addManualRecommendation() {
        const productSelect = document.getElementById('manualProductSelect');
        const reasonTextarea = document.getElementById('manualProductReason');

        if (! productSelect || ! reasonTextarea) return;

        const productId = productSelect.value;
        const reason = reasonTextarea.value.trim();

        if (! productId) {
            this.showToast('Selecciona un producto para agregar.', 'warning');
            return;
        }

        if (! reason) {
            this.showToast('Describe cómo el producto apoya la condición.', 'warning');
            return;
        }

        const product = this.products.find((item) => `${item.id}` === `${productId}`);
        if (! product) {
            this.showToast('El producto seleccionado no está disponible.', 'error');
            return;
        }

        const alreadyAdded = this.manualRecommendations.some((item) => `${item.productId}` === `${productId}`);
        if (alreadyAdded) {
            this.showToast('Este producto ya fue agregado manualmente.', 'warning');
            return;
        }

        this.manualRecommendations.push({
            productId: product.id,
            productName: product.name,
            country: product.country,
            reasoning: reason,
        });

        productSelect.value = '';
        reasonTextarea.value = '';

        this.renderManualRecommendations();
    }

    renderManualRecommendations() {
        const container = document.getElementById('manualRecommendationsList');
        if (! container) return;

        container.innerHTML = '';

        if (this.manualRecommendations.length === 0) {
            container.classList.add('empty');
            container.innerHTML = '<p class="text-sm text-gray-500">No hay productos manuales agregados.</p>';
            return;
        }

        container.classList.remove('empty');

        this.manualRecommendations.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'recommendation-item';
            wrapper.dataset.index = index;
            wrapper.innerHTML = `
                <div class="recommendation-header">
                    <span>${item.productName} <span class="text-xs text-gray-500">(${item.country})</span></span>
                    <div class="recommendation-actions">
                        <button type="button" class="remove-manual-recommendation">Quitar</button>
                    </div>
                </div>
                <p class="text-sm text-gray-600">${item.reasoning}</p>
            `;
            container.appendChild(wrapper);
        });
    }

    renderSuggestions() {
        this.renderSuggestionGroup('sameCountrySuggestions', this.lastSuggestions.same_country, false);
        this.renderSuggestionGroup('crossCountrySuggestions', this.lastSuggestions.cross_country, true);
    }

    renderSuggestionGroup(elementId, suggestions, isCrossCountry) {
        const container = document.getElementById(elementId);
        if (! container) return;

        container.innerHTML = '';

        if (! suggestions || suggestions.length === 0) {
            container.classList.add('empty');
            container.innerHTML = '<p class="text-sm text-gray-500">Aún no se han generado sugerencias.</p>';
            return;
        }

        container.classList.remove('empty');

        suggestions.forEach((suggestion) => {
            const alreadySelected = this.selectedAiRecommendations.some((item) => `${item.productId}` === `${suggestion.product_id}`);
            const wrapper = document.createElement('div');
            wrapper.className = `recommendation-item ${isCrossCountry ? 'cross-country' : ''}`;
            const analysis = (suggestion.analysis_points || []).map((point) => `<li class="list-disc ml-5 text-xs text-gray-600">${point}</li>`).join('');
            wrapper.innerHTML = `
                <div class="recommendation-header">
                    <span>${suggestion.product_name} <span class="text-xs text-gray-500">(${suggestion.country})</span></span>
                    <div class="recommendation-actions">
                        <button type="button" class="add-suggestion-btn" data-suggestion-id="${suggestion.product_id}" data-cross-country="${isCrossCountry}">
                            ${alreadySelected ? 'Agregado' : (isCrossCountry ? 'Aceptar sugerencia' : 'Agregar sugerencia')}
                        </button>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-1">${suggestion.reason}</p>
                ${analysis ? `<ul class="space-y-1">${analysis}</ul>` : ''}
            `;

            if (alreadySelected) {
                wrapper.querySelector('.add-suggestion-btn').setAttribute('disabled', 'true');
                wrapper.querySelector('.add-suggestion-btn').classList.add('opacity-60', 'cursor-not-allowed');
            }

            container.appendChild(wrapper);
        });
    }

    addAiRecommendation(suggestion) {
        const alreadySelected = this.selectedAiRecommendations.some((item) => `${item.productId}` === `${suggestion.product_id}`);
        if (alreadySelected) {
            this.showToast('Esta sugerencia ya fue agregada.', 'info');
            return;
        }

        this.selectedAiRecommendations.push({
            productId: suggestion.product_id,
            productName: suggestion.product_name,
            country: suggestion.country,
            reasoning: suggestion.reason,
            analysisPoints: suggestion.analysis_points || [],
            isCrossCountry: !! suggestion.is_cross_country,
            isApproved: suggestion.is_cross_country ? false : true,
        });

        this.renderSelectedAiRecommendations();
        this.renderSuggestions();
        this.showToast('Sugerencia agregada correctamente.', 'success');
    }

    renderSelectedAiRecommendations() {
        const container = document.getElementById('selectedAiRecommendations');
        if (! container) return;

        container.innerHTML = '';

        if (this.selectedAiRecommendations.length === 0) {
            container.classList.add('empty');
            container.innerHTML = '<p class="text-sm text-gray-500">Todavía no has aceptado sugerencias.</p>';
            return;
        }

        container.classList.remove('empty');

        this.selectedAiRecommendations.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = `recommendation-item ${item.isCrossCountry ? 'cross-country' : ''}`;
            wrapper.dataset.index = index;
            const analysis = item.analysisPoints?.map((point) => `<li class="list-disc ml-5 text-xs text-gray-600">${point}</li>`).join('') || '';
            wrapper.innerHTML = `
                <div class="recommendation-header">
                    <span>${item.productName} <span class="text-xs text-gray-500">(${item.country})</span></span>
                    <div class="recommendation-actions">
                        <button type="button" class="remove-ai-recommendation">Quitar</button>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-1">${item.reasoning}</p>
                ${analysis ? `<ul class="space-y-1">${analysis}</ul>` : ''}
                ${item.isCrossCountry ? '<p class="text-xs text-rose-500">Marcado como sugerencia de otro país. Se registrará como pendiente de aprobación.</p>' : ''}
            `;
            container.appendChild(wrapper);
        });
    }

    async generateSuggestions() {
        const diseaseName = document.getElementById('diseaseName')?.value.trim();
        const country = document.getElementById('diseaseCountry')?.value;
        const context = document.getElementById('suggestionContext')?.value.trim();
        const onlySameCountry = document.getElementById('onlySameCountry')?.checked;
        const includeOthers = document.getElementById('includeOtherCountries')?.checked;

        if (! diseaseName) {
            this.showToast('Indica el nombre de la condición para generar sugerencias.', 'warning');
            return;
        }

        const button = document.getElementById('generateSuggestionsBtn');
        if (button) {
            button.disabled = true;
            button.classList.add('opacity-70');
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path></svg> Procesando...';
        }

        try {
            const response = await fetch('/diseases/suggestions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    disease_name: diseaseName,
                    description: context,
                    country: country || null,
                    only_same_country: !! onlySameCountry,
                    include_cross_country: includeOthers !== false,
                    limit: 3,
                }),
            });

            if (! response.ok) {
                throw new Error('No se pudo generar sugerencias en este momento.');
            }

            const data = await response.json();
            this.lastSuggestions = data.data || { same_country: [], cross_country: [] };
            this.renderSuggestions();
            this.showToast('Sugerencias generadas.', 'success');
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'No se pudieron obtener sugerencias.', 'error');
        } finally {
            if (button) {
                button.disabled = false;
                button.classList.remove('opacity-70');
                button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Generar sugerencias IA';
            }
        }
    }

    async generateInformation() {
        const diseaseName = document.getElementById('diseaseName')?.value.trim();
        if (! diseaseName) {
            this.showToast('Debes indicar el nombre de la condición.', 'warning');
            return;
        }

        const productIds = [
            ...this.manualRecommendations.map((item) => item.productId),
            ...this.selectedAiRecommendations.map((item) => item.productId),
        ];

        if (productIds.length === 0) {
            this.showToast('Agrega productos antes de solicitar información con IA.', 'warning');
            return;
        }

        const button = document.getElementById('generateInformationBtn');
        if (button) {
            button.disabled = true;
            button.classList.add('opacity-70');
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path></svg> Analizando...';
        }

        try {
            const response = await fetch('/diseases/information', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    disease_name: diseaseName,
                    product_ids: productIds,
                    focus: document.getElementById('suggestionContext')?.value.trim() || null,
                }),
            });

            if (! response.ok) {
                throw new Error('No fue posible generar la información en este momento.');
            }

            const data = await response.json();
            document.getElementById('diseaseInformation').value = data.data || '';
            this.showToast('Información actualizada.', 'success');
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'Ocurrió un problema al generar la información.', 'error');
        } finally {
            if (button) {
                button.disabled = false;
                button.classList.remove('opacity-70');
            }
        }
    }

    async saveDisease() {
        const form = document.getElementById('diseaseForm');
        if (! form) return;

        const formData = new FormData(form);
        const name = formData.get('name')?.toString().trim();
        const country = formData.get('country')?.toString();
        const informationMode = formData.get('information_mode')?.toString();
        const information = formData.get('information')?.toString() || null;

        if (! name || ! country) {
            this.showToast('El nombre y el país son obligatorios.', 'warning');
            return;
        }

        const payload = {
            name,
            country,
            information_mode: informationMode || 'manual',
            information,
            metadata: {
                context: document.getElementById('suggestionContext')?.value.trim() || null,
                only_same_country: document.getElementById('onlySameCountry')?.checked || false,
                include_cross_country: document.getElementById('includeOtherCountries')?.checked !== false,
            },
            manual_recommendations: this.manualRecommendations.map((item) => ({
                product_id: item.productId,
                reasoning: item.reasoning,
            })),
            ai_recommendations: this.selectedAiRecommendations.map((item) => ({
                product_id: item.productId,
                reasoning: item.reasoning,
                is_cross_country: item.isCrossCountry,
                is_approved: item.isApproved,
                analysis_points: item.analysisPoints || [],
            })),
        };

        const url = this.isEditing ? `/diseases/${this.currentDiseaseId}` : '/diseases';
        const method = this.isEditing ? 'PUT' : 'POST';
        const saveButton = document.getElementById('saveDiseaseBtn');

        if (saveButton) {
            saveButton.disabled = true;
            saveButton.classList.add('opacity-70');
            saveButton.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path></svg> Guardando...';
        }

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            if (! response.ok) {
                const errorData = await response.json().catch(() => ({}));
                const message = errorData.message || 'No se pudo guardar el índice.';
                throw new Error(message);
            }

            this.showToast('Índice guardado correctamente.', 'success');
            setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'Ocurrió un error al guardar.', 'error');
        } finally {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.classList.remove('opacity-70');
                saveButton.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Guardar índice';
            }
        }
    }

    async editDisease(id) {
        try {
            const response = await fetch(`/diseases/${id}`);
            if (! response.ok) {
                throw new Error('No fue posible obtener la información del índice.');
            }

            const data = await response.json();
            const disease = data.data;

            if (! disease) {
                throw new Error('No se encontró el índice solicitado.');
            }

            this.resetForm();

            const form = document.getElementById('diseaseForm');
            form.querySelector('#diseaseName').value = disease.name;
            form.querySelector('#diseaseCountry').value = disease.country;
            form.querySelector('#diseaseInformation').value = disease.information || '';

            const modeInput = form.querySelector(`input[name="information_mode"][value="${disease.information_mode}"]`);
            if (modeInput) {
                modeInput.checked = true;
                this.toggleInformationMode(disease.information_mode);
            }

            this.manualRecommendations = (disease.recommendations || [])
                .filter((item) => item.type === 'manual')
                .map((item) => ({
                    productId: item.product.id,
                    productName: item.product.name,
                    country: item.product.country,
                    reasoning: item.reasoning,
                }));

            this.selectedAiRecommendations = (disease.recommendations || [])
                .filter((item) => item.type === 'ai')
                .map((item) => ({
                    productId: item.product.id,
                    productName: item.product.name,
                    country: item.product.country,
                    reasoning: item.reasoning,
                    analysisPoints: item.analysis?.analysis_points || [],
                    isCrossCountry: item.is_cross_country,
                    isApproved: item.is_approved,
                }));

            this.renderManualRecommendations();
            this.renderSelectedAiRecommendations();
            this.renderSuggestions();

            const title = document.getElementById('diseaseModalTitle');
            if (title) title.textContent = 'Editar índice';

            this.isEditing = true;
            this.currentDiseaseId = disease.id;

            const modal = document.getElementById('diseaseModal');
            modal?.classList.remove('hidden');
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'No se pudo abrir el índice para edición.', 'error');
        }
    }

    async deleteDisease(id) {
        if (! confirm('¿Deseas eliminar este índice? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const response = await fetch(`/diseases/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            if (! response.ok) {
                throw new Error('No se pudo eliminar el índice.');
            }

            this.showToast('Índice eliminado.', 'success');
            setTimeout(() => window.location.reload(), 400);
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'Ocurrió un problema al eliminar.', 'error');
        }
    }

    async showDiseaseDetails(id) {
        try {
            const response = await fetch(`/diseases/${id}`);
            if (! response.ok) {
                throw new Error('No fue posible cargar los detalles.');
            }

            const data = await response.json();
            const disease = data.data;

            document.getElementById('detailDiseaseName').textContent = disease.name;
            document.getElementById('detailDiseaseCountry').textContent = `📍 ${disease.country}`;
            document.getElementById('detailDiseaseInformation').textContent = disease.information || 'Sin información registrada.';

            const manualList = document.getElementById('detailManualList');
            const aiList = document.getElementById('detailAiList');

            manualList.innerHTML = '';
            aiList.innerHTML = '';

            const manualItems = disease.recommendations.filter((item) => item.type === 'manual');
            const aiItems = disease.recommendations.filter((item) => item.type === 'ai');

            document.getElementById('detailManualCount').textContent = manualItems.length;
            document.getElementById('detailAiCount').textContent = aiItems.length;

            if (manualItems.length === 0) {
                manualList.classList.add('empty');
                manualList.innerHTML = '<p class="text-sm text-gray-500">Sin productos manuales registrados.</p>';
            } else {
                manualList.classList.remove('empty');
                manualItems.forEach((item) => {
                    const element = document.createElement('div');
                    element.className = 'recommendation-item';
                    element.innerHTML = `
                        <div class="recommendation-header">
                            <span>${item.product.name} <span class="text-xs text-gray-500">(${item.product.country})</span></span>
                        </div>
                        <p class="text-sm text-gray-600">${item.reasoning}</p>
                    `;
                    manualList.appendChild(element);
                });
            }

            if (aiItems.length === 0) {
                aiList.classList.add('empty');
                aiList.innerHTML = '<p class="text-sm text-gray-500">Sin sugerencias de IA registradas.</p>';
            } else {
                aiList.classList.remove('empty');
                aiItems.forEach((item) => {
                    const element = document.createElement('div');
                    element.className = `recommendation-item ${item.is_cross_country ? 'cross-country' : ''}`;
                    const analysis = item.analysis?.analysis_points || [];
                    const analysisHtml = analysis.map((point) => `<li class="list-disc ml-5 text-xs text-gray-600">${point}</li>`).join('');
                    element.innerHTML = `
                        <div class="recommendation-header">
                            <span>${item.product.name} <span class="text-xs text-gray-500">(${item.product.country})</span></span>
                            ${item.is_cross_country && !item.is_approved ? `<div class="recommendation-actions"><button type="button" class="approve-suggestion-btn" data-suggestion-id="${item.id}">Aprobar sugerencia</button></div>` : ''}
                        </div>
                        <p class="text-sm text-gray-600">${item.reasoning}</p>
                        ${analysisHtml ? `<ul class="space-y-1">${analysisHtml}</ul>` : ''}
                        ${item.is_cross_country ? `<p class="text-xs text-rose-500">Sugerencia de otro país ${item.is_approved ? 'ya aprobada.' : 'pendiente de aprobación.'}</p>` : ''}
                    `;
                    aiList.appendChild(element);
                });
            }

            const modal = document.getElementById('diseaseDetailsModal');
            modal?.classList.remove('hidden');
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'No se pudieron cargar los detalles.', 'error');
        }
    }

    async approveSuggestion(id) {
        try {
            const response = await fetch(`/diseases/suggestions/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            if (! response.ok) {
                throw new Error('No fue posible aprobar la sugerencia.');
            }

            this.showToast('Sugerencia aprobada.', 'success');
            this.closeDetailsModal();
            setTimeout(() => window.location.reload(), 400);
        } catch (error) {
            console.error(error);
            this.showToast(error.message || 'Ocurrió un problema al aprobar la sugerencia.', 'error');
        }
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-white z-[9999] transition-opacity duration-300 ${this.getToastColor(type)}`;
        toast.textContent = message;

        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 2800);
    }

    getToastColor(type) {
        switch (type) {
            case 'success':
                return 'bg-emerald-500';
            case 'error':
                return 'bg-rose-500';
            case 'warning':
                return 'bg-amber-500';
            default:
                return 'bg-slate-600';
        }
    }
}

new DiseaseManager();
