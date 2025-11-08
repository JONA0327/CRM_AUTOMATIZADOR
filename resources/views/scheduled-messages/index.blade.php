@extends('layouts.app')

@push('styles')
    @vite('resources/css/products.css')
    @vite('resources/css/scheduled-messages.css')
@endpush

@section('content')
<div class="module-shell" data-module="scheduled-messages">
    <!-- Header -->
    <header class="module-header">
        <div class="module-header__headline">
            <span class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-6 4h10M5 5a2 2 0 012-2h10a2 2 0 012 2v14l-4-2-4 2-4-2-4 2V5z"></path>
                </svg>
            </span>
            <div>
                <h2 class="module-title">Mensajes Programados</h2>
                <p class="module-subtitle">Automatiza recordatorios y seguimientos 4Life</p>
            </div>
        </div>
        <div class="module-actions">
            <p class="module-tagline">Panel actualizado</p>
            <button
                id="create-message-btn"
                class="module-btn module-btn--primary"
                type="button"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"></path>
                </svg>
                Crear mensaje
            </button>
        </div>
    </header>

    <section class="module-section scheduled-messages-container">
        <div class="module-panel module-panel--filters">
            <div class="module-panel__controls scheduled-filter-controls">
                <div class="module-panel__fields scheduled-filter-inputs">
                    <div class="module-field scheduled-filter-field">
                        <label for="category-filter">Categoría</label>
                        <select id="category-filter" class="module-input filter-select">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $key => $name)
                                <option value="{{ $key }}" {{ $currentCategory == $key ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="module-field scheduled-filter-field">
                        <label for="time-period-filter">Período</label>
                        <select id="time-period-filter" class="module-input filter-select">
                            <option value="">Todos los períodos</option>
                            @foreach($time_periods as $key => $name)
                                <option value="{{ $key }}" {{ $currentTimePeriod == $key ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="module-field scheduled-filter-field">
                        <label for="status-filter">Estado</label>
                        <select id="status-filter" class="module-input filter-select">
                            <option value="">Todos los estados</option>
                            <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="inactive" {{ $currentStatus == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                </div>

                <div class="scheduled-filter-meta">
                    <div class="module-count">
                        Total: <span class="font-semibold text-blue-900">{{ $messages->total() }}</span> mensajes
                    </div>
                    <button
                        id="current-messages-btn"
                        class="module-btn module-btn--ghost scheduled-secondary-btn"
                        type="button"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ver actuales
                    </button>
                </div>
            </div>
        </div>

        <div id="messages-container">
            @if($messages->count() > 0)
                <div class="module-grid scheduled-messages-grid">
                    @foreach($messages as $message)
                        <div class="module-card scheduled-message-card" data-message-id="{{ $message->id }}">
                            <div class="module-card__body scheduled-card-body">
                                <div class="scheduled-card-header">
                                    <div class="scheduled-card-title">
                                        <h3>{{ $message->title }}</h3>
                                        @if($message->associated_question)
                                            <p class="scheduled-associated-question">
                                                <span>Pregunta:</span> {{ $message->associated_question }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="scheduled-card-badges">
                                        <span class="scheduled-badge scheduled-category-badge scheduled-category-badge--{{ $message->category }}">
                                            {{ $message->category_name }}
                                        </span>
                                        <span class="scheduled-badge scheduled-status {{ $message->is_active ? 'scheduled-status--active' : 'scheduled-status--inactive' }}">
                                            {{ $message->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>

                                @if($message->message_text)
                                    <p class="scheduled-message-text">{{ Str::limit($message->message_text, 140) }}</p>
                                @endif

                                <div class="scheduled-meta">
                                    @if($message->time_period)
                                        <span class="scheduled-meta-item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message->time_period_name }}
                                        </span>
                                    @endif

                                    @if($message->start_time && $message->end_time)
                                        <span class="scheduled-meta-item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ date('H:i', strtotime($message->start_time)) }} - {{ date('H:i', strtotime($message->end_time)) }}
                                        </span>
                                    @endif

                                    @if($message->audio_data)
                                        <span class="scheduled-meta-item scheduled-meta-item--audio">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 12a3 3 0 106 0v5a3 3 0 11-6 0V7a3 3 0 013-3z"></path>
                                            </svg>
                                            Incluye audio
                                        </span>
                                    @endif

                                    <span class="scheduled-meta-item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 4v1m0-1H9m3 0h3"></path>
                                        </svg>
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <div class="module-card__footer scheduled-card-footer">
                                    <span class="scheduled-footer-note">Actualizado {{ $message->updated_at->diffForHumans() }}</span>
                                    <div class="product-actions scheduled-actions">
                                        <button
                                            class="action-btn scheduled-action scheduled-action--view view-message-btn"
                                            data-id="{{ $message->id }}"
                                            type="button"
                                            title="Ver detalles"
                                        >
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>

                                        <button
                                            class="action-btn scheduled-action scheduled-action--edit edit-message-btn"
                                            data-id="{{ $message->id }}"
                                            type="button"
                                            title="Editar"
                                        >
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <button
                                            class="action-btn scheduled-action scheduled-action--toggle toggle-status-btn"
                                            data-id="{{ $message->id }}"
                                            type="button"
                                            title="{{ $message->is_active ? 'Desactivar' : 'Activar' }}"
                                        >
                                            @if($message->is_active)
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @else
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            @endif
                                        </button>

                                        <button
                                            class="action-btn scheduled-action scheduled-action--delete delete-message-btn"
                                            data-id="{{ $message->id }}"
                                            type="button"
                                            title="Eliminar"
                                        >
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($messages->hasPages())
                    <div class="scheduled-pagination">
                        {{ $messages->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="module-empty empty-state">
                    <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h3>Sin mensajes programados</h3>
                    <p>Comienza creando tu primer mensaje para nutrir a tus clientes automáticamente.</p>
                    <!-- empty state: no large create button here; use header 'Crear mensaje' -->
                </div>
            @endif
        </div>
    </section>
</div>

@include('scheduled-messages.modals.create-edit')
@include('scheduled-messages.modals.details')
@include('scheduled-messages.modals.current-messages')
@endsection

@push('scripts')
    @vite('resources/js/scheduled-messages.js')
@endpush
