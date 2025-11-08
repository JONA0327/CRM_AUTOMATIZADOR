/**
 * Disease index management
 * Handles creation, edition and AI integrations for disease-product relationships
 */

class DiseaseManager {
    constructor() {
        this.diseases = [];
        this.filteredDiseases = [];
        this.searchTerm = '';
        this.manualRecommendations = [];
        this.selectedAiRecommendations = [];
        this.lastSuggestions = [];
        this.isEditing = false;
        this.currentDiseaseId = null;
        this.products = [];
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        this.init();
    }

    autoSelectAiRecommendations({ silent = false } = {}) {
        const diseaseName = document.getElementById('diseaseName')?.value.trim() || '';

        if (! Array.isArray(this.lastSuggestions) || this.lastSuggestions.length === 0) {
            this.selectedAiRecommendations = [];
            this.renderSelectedAiRecommendations();
            this.renderSuggestions();

            if (! silent) {
                this.showToast('No se encontraron productos relevantes para la condición especificada.', 'warning');
            }

            return;
        }

        const seenProducts = new Set();
        const recommendations = [];

        this.lastSuggestions.forEach((suggestion) => {
            const product = this.getProductById(suggestion.product_id);
            if (! product) {
                return;
            }

            if (seenProducts.has(`${product.id}`)) {
                return;
            }

            seenProducts.add(`${product.id}`);

            const keyPoints = this.normaliseKeyPoints(product.key_points);
            const analysisPoints = (Array.isArray(suggestion.analysis_points) && suggestion.analysis_points.length > 0)
                ? suggestion.analysis_points
                : keyPoints.slice(0, 3);

            const reasoning = suggestion.reason && suggestion.reason.trim().length > 0
                ? suggestion.reason.trim()
                : this.buildReasonFromProduct(product, diseaseName);

            recommendations.push({
                productId: product.id,
                productName: product.name,
                reasoning,
                analysisPoints,
                confidence: typeof suggestion.confidence === 'number'
                    ? Math.min(100, Math.max(0, Math.round(suggestion.confidence)))
                    : null,
            });
        });

        this.selectedAiRecommendations = recommendations;
        this.renderSelectedAiRecommendations();
        this.renderSuggestions();

        if (! silent) {
            this.showToast('Sugerencias generadas automáticamente.', 'success');
        }
    }

    getProductById(id) {
        return this.products.find((product) => `${product.id}` === `${id}`);
    }

    normaliseKeyPoints(raw) {
        if (! raw) {
            return [];
        }

        if (Array.isArray(raw)) {
            return raw
                .map((point) => (typeof point === 'string' ? point.trim() : ''))
                .filter((point) => point.length > 0);
        }

        if (typeof raw === 'string') {
            try {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    return parsed
                        .map((point) => (typeof point === 'string' ? point.trim() : ''))
                        .filter((point) => point.length > 0);
                }
            } catch (error) {
                // El valor no estaba serializado como JSON.
            }

            return raw
                .split(/[\n;]+/)
                .map((point) => point.trim())
                .filter((point) => point.length > 0);
        }

        return [];
    }

    buildReasonFromProduct(product, diseaseName) {
        const keyPoints = this.normaliseKeyPoints(product?.key_points) || [];
        const topHighlights = keyPoints.slice(0, 3);

        if (topHighlights.length > 0) {
            const formatted = topHighlights.join('; ');
            const context = diseaseName
                ? `para apoyar la condición ${diseaseName}`
                : 'como apoyo complementario';

            return `Se recomienda ${product.name} ${context} gracias a sus puntos clave: ${formatted}.`;
        }

        const info = (product?.information || '').trim();
        if (info.length > 0) {
            return info.length > 220 ? `${info.slice(0, 217).trim()}…` : info;
        }

        return 'Sugerido automáticamente por similitudes con la condición especificada.';
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
        this.updateModeAvailability();
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
        const modal = document.getElementById('diseaseModal');
        const generateSuggestionsBtn = document.getElementById('generateSuggestionsBtn');
        const generateInfoBtn = document.getElementById('generateInformationBtn');
        const informationModeInputs = document.querySelectorAll('input[name="information_mode"]');
        const diseaseNameInput = document.getElementById('diseaseName');
        const closeDetailBtn = document.getElementById('closeDiseaseDetailsBtn');
        const closeDetailModalBtn = document.getElementById('closeDiseaseDetailsModal');

        createBtn?.addEventListener('click', () => this.openCreateModal());
        createFirstBtn?.addEventListener('click', () => this.openCreateModal());
        closeModalBtn?.addEventListener('click', () => this.closeModal());
        cancelBtn?.addEventListener('click', () => this.closeModal());
        closeDetailBtn?.addEventListener('click', () => this.closeDetailsModal());
        closeDetailModalBtn?.addEventListener('click', () => this.closeDetailsModal());

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                this.closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && ! modal?.classList.contains('hidden')) {
                this.closeModal();
            }
        });

        informationModeInputs.forEach((input) => {
            input.addEventListener('change', (event) => this.handleInformationModeChange(event.target.value));
        });

        diseaseNameInput?.addEventListener('input', () => this.updateModeAvailability());

        generateSuggestionsBtn?.addEventListener('click', () => this.generateSuggestions());
        generateInfoBtn?.addEventListener('click', () => this.generateInformation());

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            this.saveDisease();
        });

        const searchInput = document.getElementById('searchDiseases');

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
            const productCard = event.target.closest('.product-selector-card');
            if (productCard) {
                const productId = Number(productCard.dataset.productId);
                if (! Number.isNaN(productId)) {
                    this.toggleManualProduct(productId);
                }
                return;
            }

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
                    this.updateGallerySelection();
                }
            }

            if (event.target.closest('.remove-ai-recommendation')) {
                const index = Number(event.target.closest('[data-index]')?.dataset.index);
                if (! Number.isNaN(index)) {
                    this.selectedAiRecommendations.splice(index, 1);
                    this.renderSelectedAiRecommendations();
                    this.renderSuggestions();
                }
            }

            if (event.target.closest('.add-suggestion-btn')) {
                const button = event.target.closest('.add-suggestion-btn');
                const suggestionIndex = Number(button.dataset.suggestionIndex);
                if (! Number.isNaN(suggestionIndex) && this.lastSuggestions[suggestionIndex]) {
                    this.addAiRecommendation({ ...this.lastSuggestions[suggestionIndex] });
                }
            }
        });

        document.addEventListener('input', (event) => {
            if (event.target.classList.contains('manual-reason-input')) {
                const index = Number(event.target.closest('[data-index]')?.dataset.index);
                if (! Number.isNaN(index) && this.manualRecommendations[index]) {
                    this.manualRecommendations[index].reasoning = event.target.value;
                }
            }
        });
    }

    loadDiseases() {
        const cards = document.querySelectorAll('.disease-card');
        this.diseases = Array.from(cards).map((card) => ({
            id: card.dataset.diseaseId,
            name: card.dataset.diseaseName || '',
            element: card,
        }));

        this.filteredDiseases = [...this.diseases];
        this.updateDiseaseCount();
    }

    filterDiseases() {
        const cards = document.querySelectorAll('.disease-card');
        let visible = 0;

        cards.forEach((card) => {
            const matchesSearch = ! this.searchTerm || (card.dataset.diseaseName || '').includes(this.searchTerm);

            if (matchesSearch) {
                card.classList.remove('hidden');
                visible += 1;
            } else {
                card.classList.add('hidden');
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

    handleInformationModeChange(mode) {
        if (! mode) {
            return;
        }

        this.toggleInformationMode(mode);

        if (
            mode === 'ai'
            && this.manualRecommendations.length === 0
            && this.selectedAiRecommendations.length === 0
        ) {
            this.lastSuggestions = [];
            this.renderSuggestions();
            this.generateSuggestions({ autoSelect: true, silent: true });
        }
    }

    toggleInformationMode(mode) {
        const generateInfoBtn = document.getElementById('generateInformationBtn');
        const manualSection = document.getElementById('manualModeSection');
        const aiSection = document.getElementById('aiModeSection');

        if (mode === 'ai') {
            generateInfoBtn?.classList.remove('hidden');
            manualSection?.classList.add('hidden');
            aiSection?.classList.remove('hidden');

            if (this.manualRecommendations.length > 0) {
                this.manualRecommendations = [];
                this.renderManualRecommendations();
                this.updateGallerySelection();
            }
        } else {
            generateInfoBtn?.classList.add('hidden');
            manualSection?.classList.remove('hidden');
            aiSection?.classList.add('hidden');

            if (
                this.selectedAiRecommendations.length > 0
                || (Array.isArray(this.lastSuggestions) && this.lastSuggestions.length > 0)
            ) {
                this.selectedAiRecommendations = [];
                this.lastSuggestions = [];
                this.renderSelectedAiRecommendations();
                this.renderSuggestions();
            }
        }
    }

    updateModeAvailability() {
        const diseaseNameInput = document.getElementById('diseaseName');
        const informationModeInputs = document.querySelectorAll('input[name="information_mode"]');
        const hasName = !! diseaseNameInput?.value.trim();

        informationModeInputs.forEach((input) => {
            input.disabled = ! hasName;
            const label = input.closest('label');
            if (label) {
                label.classList.toggle('opacity-60', ! hasName);
            }
        });

        if (! hasName) {
            const manualInput = document.querySelector('input[name="information_mode"][value="manual"]');
            if (manualInput) {
                manualInput.checked = true;
                this.toggleInformationMode('manual');
            }
        }
    }

    resetForm() {
        const form = document.getElementById('diseaseForm');
        if (! form) return;

        form.reset();
        this.manualRecommendations = [];
        this.selectedAiRecommendations = [];
        this.lastSuggestions = [];
        this.isEditing = false;
        this.currentDiseaseId = null;
        this.toggleInformationMode('manual');
        const countryInput = form.querySelector('#diseaseCountry');
        if (countryInput) {
            countryInput.value = 'Global';
        }
        this.renderManualRecommendations();
        this.renderSelectedAiRecommendations();
        this.renderSuggestions();
        this.updateGallerySelection();
        this.updateModeAvailability();
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

    toggleManualProduct(productId) {
        const product = this.getProductById(productId);

        if (! product) {
            this.showToast('El producto seleccionado no está disponible.', 'error');
            return;
        }

        const existingIndex = this.manualRecommendations.findIndex((item) => `${item.productId}` === `${productId}`);

        if (existingIndex !== -1) {
            this.manualRecommendations.splice(existingIndex, 1);
            this.renderManualRecommendations();
            this.updateGallerySelection();
            return;
        }

        const diseaseName = document.getElementById('diseaseName')?.value.trim() || '';
        const defaultReason = this.buildReasonFromProduct(product, diseaseName);

        this.manualRecommendations.push({
            productId: product.id,
            productName: product.name,
            imageUrl: product.image_url || null,
            reasoning: defaultReason,
        });

        this.renderManualRecommendations();
        this.updateGallerySelection();
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
            const product = this.getProductById(item.productId) || {};
            const imageUrl = item.imageUrl || product.image_url;

            const wrapper = document.createElement('div');
            wrapper.className = 'selected-product-card';
            wrapper.dataset.index = index;

            const figure = document.createElement('div');
            figure.className = 'detail-product-image';
            if (imageUrl) {
                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = item.productName;
                figure.appendChild(img);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'product-image-placeholder';
                placeholder.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                figure.appendChild(placeholder);
            }

            const info = document.createElement('div');
            info.className = 'selected-product-info';

            const title = document.createElement('h6');
            title.textContent = item.productName;

            const label = document.createElement('label');
            label.className = 'block text-xs text-gray-500';
            label.textContent = 'Información de apoyo';

            const reasoningField = document.createElement('textarea');
            reasoningField.className = 'manual-reason-input';
            reasoningField.rows = 3;
            reasoningField.value = item.reasoning || '';
            reasoningField.placeholder = 'Describe por qué este producto es útil para la condición.';

            info.appendChild(title);
            info.appendChild(label);
            info.appendChild(reasoningField);

            const actions = document.createElement('div');
            actions.className = 'selected-product-actions';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-manual-recommendation';
            removeBtn.textContent = 'Quitar';

            actions.appendChild(removeBtn);

            wrapper.appendChild(figure);
            wrapper.appendChild(info);
            wrapper.appendChild(actions);
            container.appendChild(wrapper);
        });
    }

    updateGallerySelection() {
        const gallery = document.getElementById('manualProductGallery');
        if (! gallery) return;

        const selectedIds = new Set(this.manualRecommendations.map((item) => `${item.productId}`));

        gallery.querySelectorAll('.product-selector-card').forEach((card) => {
            if (selectedIds.has(card.dataset.productId)) {
                card.classList.add('is-active');
            } else {
                card.classList.remove('is-active');
            }
        });
    }

    renderSuggestions() {
        const container = document.getElementById('aiSuggestions');
        if (! container) return;

        container.innerHTML = '';

        const suggestions = Array.isArray(this.lastSuggestions) ? this.lastSuggestions : [];

        if (suggestions.length === 0) {
            container.classList.add('empty');
            container.innerHTML = '<p class="text-sm text-gray-500">Aún no se han generado sugerencias.</p>';
            return;
        }

        container.classList.remove('empty');

        suggestions.forEach((suggestion, index) => {
            const product = this.getProductById(suggestion.product_id);
            if (! product) {
                return;
            }

            const alreadySelected = this.selectedAiRecommendations.some((item) => `${item.productId}` === `${suggestion.product_id}`);

            const card = document.createElement('div');
            card.className = 'ai-suggestion-card';

            const figure = document.createElement('div');
            figure.className = 'ai-suggestion-figure';
            if (product.image_url) {
                const img = document.createElement('img');
                img.src = product.image_url;
                img.alt = product.name;
                figure.appendChild(img);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'product-image-placeholder';
                placeholder.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                figure.appendChild(placeholder);
            }

            const info = document.createElement('div');
            info.className = 'ai-suggestion-info';

            const title = document.createElement('h6');
            title.textContent = suggestion.product_name;

            const reason = document.createElement('p');
            reason.className = 'text-sm text-gray-600';
            reason.textContent = suggestion.reason || this.buildReasonFromProduct(product, document.getElementById('diseaseName')?.value.trim() || '');

            const meta = document.createElement('div');
            meta.className = 'ai-suggestion-meta';
            if (typeof suggestion.confidence === 'number') {
                const chip = document.createElement('span');
                chip.className = 'confidence-chip';
                chip.textContent = `${Math.round(suggestion.confidence)}% coincidencia`;
                meta.appendChild(chip);
            }

            info.appendChild(title);
            info.appendChild(reason);
            if (meta.children.length > 0) {
                info.appendChild(meta);
            }

            const analysisPoints = Array.isArray(suggestion.analysis_points) ? suggestion.analysis_points.filter(Boolean) : [];
            if (analysisPoints.length > 0) {
                const list = document.createElement('ul');
                list.className = 'analysis-points';
                analysisPoints.slice(0, 3).forEach((point) => {
                    const li = document.createElement('li');
                    li.textContent = point;
                    list.appendChild(li);
                });
                info.appendChild(list);
            }

            const actions = document.createElement('div');
            actions.className = 'ai-suggestion-actions';

            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'add-suggestion-btn';
            addBtn.dataset.suggestionIndex = index;
            addBtn.textContent = alreadySelected ? 'Agregado' : 'Seleccionar';

            if (alreadySelected) {
                addBtn.disabled = true;
                addBtn.classList.add('opacity-60', 'cursor-not-allowed');
            }

            actions.appendChild(addBtn);

            card.appendChild(figure);
            card.appendChild(info);
            card.appendChild(actions);

            container.appendChild(card);
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
            reasoning: suggestion.reason,
            analysisPoints: suggestion.analysis_points || [],
            confidence: typeof suggestion.confidence === 'number'
                ? Math.min(100, Math.max(0, Math.round(suggestion.confidence)))
                : null,
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
            const aiModeSelected = document.querySelector('input[name="information_mode"][value="ai"]')?.checked;
            const message = aiModeSelected
                ? 'La IA aún no encuentra coincidencias para la condición ingresada.'
                : 'Todavía no has aceptado sugerencias.';
            container.innerHTML = `<p class="text-sm text-gray-500">${message}</p>`;
            return;
        }

        container.classList.remove('empty');

        this.selectedAiRecommendations.forEach((item, index) => {
            const product = this.getProductById(item.productId) || {};
            const card = document.createElement('div');
            card.className = 'ai-suggestion-card selected-ai-card';
            card.dataset.index = index;

            const figure = document.createElement('div');
            figure.className = 'ai-suggestion-figure';
            if (product.image_url) {
                const img = document.createElement('img');
                img.src = product.image_url;
                img.alt = item.productName;
                figure.appendChild(img);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'product-image-placeholder';
                placeholder.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                figure.appendChild(placeholder);
            }

            const info = document.createElement('div');
            info.className = 'ai-suggestion-info';

            const title = document.createElement('h6');
            title.textContent = item.productName;

            const reason = document.createElement('p');
            reason.className = 'text-sm text-gray-600';
            reason.textContent = item.reasoning || '';

            const meta = document.createElement('div');
            meta.className = 'ai-suggestion-meta';
            if (typeof item.confidence === 'number') {
                const chip = document.createElement('span');
                chip.className = 'confidence-chip';
                chip.textContent = `${Math.round(item.confidence)}% coincidencia`;
                meta.appendChild(chip);
            }

            info.appendChild(title);
            info.appendChild(reason);
            if (meta.children.length > 0) {
                info.appendChild(meta);
            }

            const analysisPoints = Array.isArray(item.analysisPoints) ? item.analysisPoints.filter(Boolean) : [];
            if (analysisPoints.length > 0) {
                const list = document.createElement('ul');
                list.className = 'analysis-points';
                analysisPoints.slice(0, 3).forEach((point) => {
                    const li = document.createElement('li');
                    li.textContent = point;
                    list.appendChild(li);
                });
                info.appendChild(list);
            }

            const actions = document.createElement('div');
            actions.className = 'ai-suggestion-actions';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-ai-recommendation';
            removeBtn.textContent = 'Quitar';

            actions.appendChild(removeBtn);

            card.appendChild(figure);
            card.appendChild(info);
            card.appendChild(actions);

            container.appendChild(card);
        });
    }

    async generateSuggestions(options = {}) {
        const { autoSelect = false, silent = false } = options;
        const diseaseName = document.getElementById('diseaseName')?.value.trim();
        const context = document.getElementById('suggestionContext')?.value.trim();

        if (! diseaseName) {
            if (! silent) {
                this.showToast('Indica el nombre de la condición para generar sugerencias.', 'warning');
            }
            return;
        }

        const button = document.getElementById('generateSuggestionsBtn');
        const shouldShowLoading = ! silent;
        if (button && shouldShowLoading) {
            button.disabled = true;
            button.classList.add('opacity-70');
            button.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path></svg> Analizando...';
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
                    limit: 3,
                }),
            });

            if (! response.ok) {
                throw new Error('No se pudo generar sugerencias en este momento.');
            }

            const data = await response.json();
            this.lastSuggestions = Array.isArray(data.data) ? data.data : [];
            this.renderSuggestions();

            if (autoSelect) {
                this.autoSelectAiRecommendations({ silent });
            }

            if (! silent) {
                this.showToast('Sugerencias generadas.', 'success');
            }
        } catch (error) {
            console.error(error);
            if (! silent) {
                this.showToast(error.message || 'No se pudieron obtener sugerencias.', 'error');
            }
        } finally {
            if (button && shouldShowLoading) {
                button.disabled = false;
                button.classList.remove('opacity-70');
                button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Analizar con IA';
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
                button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a4 4 0 004-4M7 15V9a5 5 0 0110 0v6"></path></svg> Generar información con IA';
            }
        }
    }

    async saveDisease() {
        const form = document.getElementById('diseaseForm');
        if (! form) return;

        const formData = new FormData(form);
        const name = formData.get('name')?.toString().trim();
        const informationMode = formData.get('information_mode')?.toString();
        const information = formData.get('information')?.toString() || null;
        const country = formData.get('country')?.toString()?.trim() || 'Global';

        if (! name) {
            this.showToast('El nombre de la condición es obligatorio.', 'warning');
            return;
        }

        const payload = {
            name,
            country,
            information_mode: informationMode || 'manual',
            information,
            metadata: {
                context: document.getElementById('suggestionContext')?.value.trim() || null,
            },
            manual_recommendations: this.manualRecommendations.map((item) => ({
                product_id: item.productId,
                reasoning: item.reasoning?.trim() || '',
            })),
            ai_recommendations: this.selectedAiRecommendations.map((item) => ({
                product_id: item.productId,
                reasoning: item.reasoning?.trim() || '',
                analysis_points: item.analysisPoints || [],
                confidence: typeof item.confidence === 'number' ? item.confidence : null,
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
            form.querySelector('#diseaseInformation').value = disease.information || '';
            const countryInput = form.querySelector('#diseaseCountry');
            if (countryInput) {
                countryInput.value = disease.country || 'Global';
            }
            this.updateModeAvailability();

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
                    reasoning: item.reasoning,
                    imageUrl: item.product.image_url || null,
                }));

            this.selectedAiRecommendations = (disease.recommendations || [])
                .filter((item) => item.type === 'ai')
                .map((item) => ({
                    productId: item.product.id,
                    productName: item.product.name,
                    reasoning: item.reasoning,
                    analysisPoints: item.analysis?.analysis_points || [],
                    confidence: item.analysis?.confidence ?? null,
                }));

            this.renderManualRecommendations();
            this.renderSelectedAiRecommendations();
            this.renderSuggestions();
            this.updateGallerySelection();

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
                    element.className = 'detail-product-card';

                    const figure = document.createElement('div');
                    figure.className = 'detail-product-image';
                    if (item.product.image_url) {
                        const img = document.createElement('img');
                        img.src = item.product.image_url;
                        img.alt = item.product.name;
                        figure.appendChild(img);
                    } else {
                        const placeholder = document.createElement('div');
                        placeholder.className = 'product-image-placeholder';
                        placeholder.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                        figure.appendChild(placeholder);
                    }

                    const info = document.createElement('div');
                    info.className = 'detail-product-info';

                    const title = document.createElement('h6');
                    title.textContent = item.product.name;

                    const reason = document.createElement('p');
                    reason.textContent = item.reasoning;

                    info.appendChild(title);
                    info.appendChild(reason);

                    element.appendChild(figure);
                    element.appendChild(info);
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
                    element.className = 'detail-product-card';

                    const figure = document.createElement('div');
                    figure.className = 'detail-product-image';
                    if (item.product.image_url) {
                        const img = document.createElement('img');
                        img.src = item.product.image_url;
                        img.alt = item.product.name;
                        figure.appendChild(img);
                    } else {
                        const placeholder = document.createElement('div');
                        placeholder.className = 'product-image-placeholder';
                        placeholder.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                        figure.appendChild(placeholder);
                    }

                    const info = document.createElement('div');
                    info.className = 'detail-product-info';

                    const title = document.createElement('h6');
                    title.textContent = item.product.name;

                    const reason = document.createElement('p');
                    reason.textContent = item.reasoning;

                    const analysis = item.analysis?.analysis_points || [];
                    if (analysis.length > 0) {
                        const list = document.createElement('ul');
                        list.className = 'analysis-points';
                        analysis.slice(0, 3).forEach((point) => {
                            const li = document.createElement('li');
                            li.textContent = point;
                            list.appendChild(li);
                        });
                        info.appendChild(list);
                    }

                    if (typeof item.analysis?.confidence === 'number') {
                        const meta = document.createElement('div');
                        meta.className = 'ai-suggestion-meta';
                        const chip = document.createElement('span');
                        chip.className = 'confidence-chip';
                        chip.textContent = `${Math.round(item.analysis.confidence)}% coincidencia`;
                        meta.appendChild(chip);
                        info.appendChild(meta);
                    }

                    element.appendChild(figure);
                    element.appendChild(info);
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
