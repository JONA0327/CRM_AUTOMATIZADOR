@extends('layouts.app')

@section('content')
<div class="module-shell" data-module="dashboard">
    <!-- Header -->
    <header class="module-header">
        <div class="module-header__headline">
            <span class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </span>
            <div>
                <h1 class="module-title">Panel de Control</h1>
                <p class="module-subtitle">CRM_AUTOMATIZADOR</p>
            </div>
        </div>
        <div class="module-actions">
            <p class="module-tagline">Bienvenido</p>
            <p class="text-lg font-semibold text-blue-900">{{ Auth::user()->name }}</p>
        </div>
    </header>

    <section class="module-section">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <article class="card-surface stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">
                    <span class="stat-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path>
                        </svg>
                    </span>
                    Clientes
                </div>
                <span class="stat-trend">+0%</span>
            </div>
            <h2 class="stat-metric">0</h2>
            <p class="stat-subtitle">Clientes registrados</p>
            <p class="text-soft text-sm">Activos: 0 · Nuevos hoy: 0</p>
            <div class="progress-track">
                <div class="progress-indicator" style="width: 0%"></div>
            </div>
        </article>

        <article class="card-surface stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">
                    <span class="stat-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1"></path>
                        </svg>
                    </span>
                    Ventas
                </div>
                <span class="stat-trend">$0</span>
            </div>
            <h2 class="stat-metric">$0</h2>
            <p class="stat-subtitle">Ingresos del mes</p>
            <p class="text-soft text-sm">Meta mensual: $10,000 · Restante: $10,000</p>
            <div class="progress-track">
                <div class="progress-indicator" style="width: 0%"></div>
            </div>
        </article>

        <article class="card-surface stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">
                    <span class="stat-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path>
                        </svg>
                    </span>
                    Tareas
                </div>
                <span class="stat-trend">Agenda</span>
            </div>
            <h2 class="stat-metric">0</h2>
            <p class="stat-subtitle">Tareas completadas</p>
            <p class="text-soft text-sm">Hoy: 0 · Esta semana: 0</p>
            <div class="progress-track">
                <div class="progress-indicator" style="width: 0%"></div>
            </div>
        </article>

        <article class="card-surface stat-card">
            <div class="stat-card-header">
                <div class="stat-card-title">
                    <span class="stat-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </span>
                    Oportunidades
                </div>
                <span class="stat-trend">Seguimiento</span>
            </div>
            <h2 class="stat-metric">0</h2>
            <p class="stat-subtitle">Oportunidades activas</p>
            <p class="text-soft text-sm">En proceso: 0 · Cerradas: 0</p>
            <div class="progress-track">
                <div class="progress-indicator" style="width: 0%"></div>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <article class="xl:col-span-3 hero-card">
            <div class="hero-card-content">
                <div class="space-y-5">
                    <p class="text-xs uppercase tracking-[0.45em] text-blue-100">Automatiza tu flujo</p>
                    <div class="space-y-3">
                        <h3 class="text-3xl font-bold leading-tight">Bienvenido al CRM Automatizador</h3>
                        <p class="text-sm text-blue-100 max-w-xl">
                            Gestiona clientes, catálogos y comunicaciones desde un espacio unificado. Mantén la información organizada y acelera tus procesos diarios con herramientas diseñadas para tu equipo.
                        </p>
                    </div>
                    <div class="hero-card-actions">
                        <button class="hero-card-button primary">Registrar cliente</button>
                        <button class="hero-card-button secondary">Ver reportes</button>
                    </div>
                </div>
                <div class="hero-illustration">
                    <div class="hero-illustration-circle">
                        <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </article>

        <article class="card-surface quick-actions-card">
            <h4>Acciones rápidas</h4>
            <div class="quick-actions-list">
                <button class="quick-action" type="button">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"></path>
                        </svg>
                    </span>
                    Nuevo cliente
                </button>
                <button class="quick-action" type="button">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"></path>
                        </svg>
                    </span>
                    Nuevo producto
                </button>
                <button class="quick-action" type="button">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    Programar mensaje
                </button>
                <button class="quick-action" type="button">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                    Ver checklist diario
                </button>
            </div>
        </article>
    </section>
</div>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <article class="card-surface">
            <h4 class="section-title">Estado del sistema</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-soft">Tiempo activo</span>
                    <span class="font-semibold text-blue-700">Online</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-soft">Base de datos</span>
                    <span class="font-semibold text-blue-700">Conectada</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-soft">Último respaldo</span>
                    <span class="font-semibold text-blue-900">Hoy</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-soft">Versión Laravel</span>
                    <span class="font-semibold text-blue-700">12.35.1</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-soft">Uso de memoria</span>
                    <span class="font-semibold text-blue-700">45%</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-soft">Espacio disponible</span>
                    <span class="font-semibold text-blue-700">78% libre</span>
                </div>
            </div>
        </article>

        <article class="card-surface">
            <h4 class="section-title">Próximos hitos</h4>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path>
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-blue-900">Campaña de seguimiento</p>
                        <p class="text-soft text-sm">Configura los mensajes automáticos para los prospectos nuevos.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-blue-900">Actualizar catálogos</p>
                        <p class="text-soft text-sm">Añade nuevos productos y asigna categorías para mantener todo ordenado.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="quick-action-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-blue-900">Entrenamiento del equipo</p>
                        <p class="text-soft text-sm">Comparte buenas prácticas para aprovechar el CRM al máximo.</p>
                    </div>
                </div>
            </div>
        </article>
    </section>
@endsection

