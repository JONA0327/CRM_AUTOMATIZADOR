@extends('layouts.app')

@section('title', 'Mensajes Programados')

@section('content')
<div class="main-content-area bg-gray-50 min-h-screen">
    <div class="container mx-auto px-6 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mensajes Programados</h1>
                    <p class="text-gray-600 mt-1">Gestiona mensajes automáticos por categoría y horario</p>
                </div>
                <button
                    id="create-message-btn"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Crear Mensaje
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Filtro por Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $key => $name)
                            <option value="{{ $key }}" {{ $currentCategory == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Período -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <select id="time-period-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los períodos</option>
                        @foreach($time_periods as $key => $name)
                            <option value="{{ $key }}" {{ $currentTimePeriod == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ $currentStatus == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>

                <!-- Botón para mensajes actuales -->
                <div class="flex items-end">
                    <button
                        id="current-messages-btn"
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200"
                    >
                        Ver Actuales
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Mensajes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Lista de Mensajes</h2>
            </div>

            <div id="messages-container">
                @if($messages->count() > 0)
                    @foreach($messages as $message)
                        <div class="border-b border-gray-200 last:border-b-0 p-6 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $message->title }}</h3>

                                        <!-- Badge de Categoría -->
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            @if($message->category == 'bienvenida') bg-blue-100 text-blue-800
                                            @elseif($message->category == 'seguimiento') bg-yellow-100 text-yellow-800
                                            @elseif($message->category == 'contestar_preguntas') bg-green-100 text-green-800
                                            @elseif($message->category == 'informacion_productos') bg-purple-100 text-purple-800
                                            @endif">
                                            {{ $message->category_name }}
                                        </span>

                                        <!-- Badge de Estado -->
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $message->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $message->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>

                                    <!-- Contenido del mensaje -->
                                    @if($message->message_text)
                                        <p class="text-gray-600 mb-2">{{ Str::limit($message->message_text, 100) }}</p>
                                    @endif

                                    <!-- Pregunta asociada -->
                                    @if($message->associated_question)
                                        <p class="text-sm text-blue-600 mb-2">
                                            <strong>Pregunta:</strong> {{ $message->associated_question }}
                                        </p>
                                    @endif

                                    <!-- Información de horario -->
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                        @if($message->time_period)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $message->time_period_name }}
                                            </span>
                                        @endif

                                        @if($message->start_time && $message->end_time)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ date('H:i', strtotime($message->start_time)) }} - {{ date('H:i', strtotime($message->end_time)) }}
                                            </span>
                                        @endif

                                        @if($message->audio_data)
                                            <span class="flex items-center gap-1 text-green-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 12a3 3 0 106 0v5a3 3 0 11-6 0V7a3 3 0 013-3z"></path>
                                                </svg>
                                                Incluye Audio
                                            </span>
                                        @endif

                                        <span>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>

                                <!-- Botones de Acción -->
                                <div class="flex items-center gap-2">
                                    <button
                                        class="view-message-btn text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors duration-200"
                                        data-id="{{ $message->id }}"
                                        title="Ver detalles"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>

                                    <button
                                        class="edit-message-btn text-yellow-600 hover:text-yellow-800 p-2 rounded-lg hover:bg-yellow-50 transition-colors duration-200"
                                        data-id="{{ $message->id }}"
                                        title="Editar"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <button
                                        class="toggle-status-btn {{ $message->is_active ? 'text-red-600 hover:text-red-800 hover:bg-red-50' : 'text-green-600 hover:text-green-800 hover:bg-green-50' }} p-2 rounded-lg transition-colors duration-200"
                                        data-id="{{ $message->id }}"
                                        title="{{ $message->is_active ? 'Desactivar' : 'Activar' }}"
                                    >
                                        @if($message->is_active)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </button>

                                    <button
                                        class="delete-message-btn text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200"
                                        data-id="{{ $message->id }}"
                                        title="Eliminar"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay mensajes programados</h3>
                        <p class="text-gray-600 mb-4">Comienza creando tu primer mensaje programado</p>
                        <button
                            id="create-first-message-btn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200"
                        >
                            Crear Primer Mensaje
                        </button>
                    </div>
                @endif
            </div>

            <!-- Paginación -->
            @if($messages->hasPages())
                <div class="p-6 border-t border-gray-200">
                    {{ $messages->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Incluir modales -->
@include('scheduled-messages.modals.create-edit')
@include('scheduled-messages.modals.details')
@include('scheduled-messages.modals.current-messages')

@endsection

@push('styles')
@vite(['resources/css/scheduled-messages.css'])
@endpush

@push('scripts')
@vite(['resources/js/scheduled-messages.js'])
@endpush
