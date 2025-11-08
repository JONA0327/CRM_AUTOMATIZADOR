@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@push('styles')
    @vite('resources/css/diseases.css')
@endpush

@section('content')
<div class="module-shell" data-module="diseases">
    <!-- Header -->
    <header class="module-header">
        <div class="module-header__headline">
            <span class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </span>
            <div>
                <h2 class="module-title">Índice de Enfermedades</h2>
                <p class="module-subtitle">Asocia condiciones con productos 4Life de forma precisa</p>
            </div>
        </div>
        <div class="module-actions">
            <button id="createDiseaseBtn" class="module-btn module-btn--primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"></path>
                </svg>
                Nuevo índice
            </button>
        </div>
    </header>

    <section class="module-section diseases-container">
        <!-- Filtros -->
        <div class="module-panel module-panel--filters diseases-filters">
            <div class="module-panel__controls">
                <div class="module-panel__fields">
                    <div class="module-field">
                        <label for="countryFilter">País</label>
                        <select id="countryFilter" class="module-input filter-select">
                            <option value="">Todos los países</option>
                            @foreach($availableCountries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="module-field">
                        <label for="searchDiseases">Buscar</label>
                        <input type="text" id="searchDiseases" placeholder="Buscar condición..." class="module-input filter-input">
                    </div>
                </div>
                <div class="module-count disease-count">
                    Total: <span id="diseaseCount" class="font-semibold text-blue-900">{{ $diseasesByCountry->flatten()->count() }}</span> condiciones
                </div>
            </div>
        </div>

        <!-- Listado -->
        @if($diseasesByCountry->count() > 0)
            @foreach($diseasesByCountry as $country => $diseases)
                <div class="mb-8 country-section" data-country="{{ $country }}">
                    <div class="country-header">
                        <h3 class="country-title">{{ $country }}</h3>
                        <span class="country-badge">{{ $diseases->count() }} condiciones</span>
                    </div>

                    <div class="module-grid diseases-grid">
                        @foreach($diseases as $disease)
                            <div class="module-card disease-card cursor-pointer"
                                 data-disease-id="{{ $disease->id }}"
                                 data-disease-name="{{ strtolower($disease->name) }}"
                                 data-country="{{ $disease->country }}">
                                <div class="module-card__header disease-card-header">
                                    <div class="badge-mode {{ $disease->information_mode === 'ai' ? 'badge-mode-ai' : 'badge-mode-manual' }}">
                                        {{ $disease->information_mode === 'ai' ? 'IA' : 'Manual' }}
                                    </div>
                                    <div class="disease-actions">
                                        <button class="action-btn edit-disease-btn" data-disease-id="{{ $disease->id }}">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button class="action-btn delete-disease-btn" data-disease-id="{{ $disease->id }}">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="module-card__body disease-card-body">
                                    <h4 class="disease-name">{{ $disease->name }}</h4>
                                    <p class="disease-info-preview">
                                        {{ Str::limit($disease->information ?? 'Información pendiente', 120) }}
                                    </p>
                                </div>
                                <div class="module-card__footer disease-card-footer">
                                    <div class="recommendation-count">
                                        <span class="count-chip">
                                            Manuales: {{ $disease->manual_recommendations->count() }}
                                        </span>
                                        <span class="count-chip">
                                            IA: {{ $disease->ai_recommendations->count() }}
                                        </span>
                                    </div>
                                    <span class="disease-country">📍 {{ $disease->country }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="module-empty empty-state">
                <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <h3>Aún no has creado índices</h3>
                <p>Comienza registrando una condición y conecta productos de forma inteligente.</p>
            </div>
        @endif
    </section>
</div>

<!-- Modales -->
@include('diseases.modals.create-edit', ['products' => $products, 'availableCountries' => $availableCountries])
@include('diseases.modals.details')
@endsection

@push('scripts')
    @vite('resources/js/diseases.js')
@endpush
