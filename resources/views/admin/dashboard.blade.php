@extends('layouts.app')

@section('content')
<div class="module-shell" data-module="admin-dashboard">
    <header class="module-header">
        <div class="module-header__headline">
            <span class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.039 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </span>
            <div>
                <h2 class="module-title">Panel de Administración</h2>
                <p class="module-subtitle">CRM_AUTOMATIZADOR · Gestión del Sistema</p>
            </div>
        </div>
        <div class="module-actions">
            <p class="module-tagline">Administrador</p>
            <p class="text-lg font-semibold text-blue-900">{{ Auth::user()->name }}</p>
        </div>
    </header>

    <section class="module-section">
        <div class="card-surface flex items-center gap-3 border border-red-100 bg-red-50/60 text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <span class="font-semibold">Estás accediendo como Administrador del Sistema</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <article class="card-surface">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="module-btn module-btn--ghost text-xs">Ver todos</a>
                </div>
                <h3 class="text-3xl font-bold text-blue-900">{{ \App\Models\User::count() }}</h3>
                <p class="text-sm text-soft">Usuarios registrados</p>
            </article>

            <article class="card-surface">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.039 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <a href="{{ route('admin.roles.index') }}" class="module-btn module-btn--ghost text-xs">Gestionar</a>
                </div>
                <h3 class="text-3xl font-bold text-blue-900">{{ \App\Models\Role::count() }}</h3>
                <p class="text-sm text-soft">Roles del sistema</p>
            </article>

            <article class="card-surface">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="module-chip" style="background: rgba(239,68,68,0.12); color: rgb(220,38,38);">Crítico</span>
                </div>
                <h3 class="text-3xl font-bold text-blue-900">{{ \App\Models\User::whereHas('role', function($q) { $q->where('name', 'admin'); })->count() }}</h3>
                <p class="text-sm text-soft">Administradores activos</p>
            </article>

            <article class="card-surface">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <a href="{{ route('admin.system.info') }}" class="module-btn module-btn--ghost text-xs">Ver info</a>
                </div>
                <h3 class="text-3xl font-bold text-blue-900">Laravel {{ app()->version() }}</h3>
                <p class="text-sm text-soft">Versión del sistema</p>
            </article>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="hero-card">
                    <div class="hero-card-content">
                        <div class="space-y-5">
                            <p class="text-xs uppercase tracking-[0.45em] text-blue-100">Control total</p>
                            <div class="space-y-3">
                                <h3 class="text-3xl font-bold leading-tight">¡Bienvenido al panel de administración!</h3>
                                <p class="text-sm text-blue-100 max-w-xl">
                                    Gestiona usuarios, roles, configuración y supervisa el estado del CRM desde una interfaz centralizada.
                                </p>
                            </div>
                            <div class="hero-card-actions">
                                <a href="{{ route('admin.users.index') }}" class="hero-card-button primary">Gestionar usuarios</a>
                                <a href="{{ route('admin.system.info') }}" class="hero-card-button secondary">Información del sistema</a>
                            </div>
                        </div>
                        <div class="hero-illustration">
                            <div class="hero-illustration-circle">
                                <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.039 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="card-surface">
                    <h4 class="font-semibold text-blue-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Acciones de administrador
                    </h4>
                    <div class="space-y-3">
                        <a href="{{ route('admin.users.index') }}" class="flex items-center p-3 rounded-xl bg-blue-50/60 hover:bg-blue-100 transition">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center mr-3 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-blue-900">Gestionar usuarios</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="flex items-center p-3 rounded-xl bg-green-50/60 hover:bg-green-100 transition">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center mr-3 text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.039 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-green-800">Gestionar roles</span>
                        </a>
                        <a href="{{ route('admin.system.info') }}" class="flex items-center p-3 rounded-xl bg-purple-50/60 hover:bg-purple-100 transition">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center mr-3 text-purple-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-purple-800">Información del sistema</span>
                        </a>
                    </div>
                </div>

                <div class="card-surface">
                    <h4 class="font-semibold text-blue-900 mb-4">Estado del sistema</h4>
                    <div class="space-y-3 text-sm text-soft">
                        <div class="flex items-center justify-between">
                            <span>Estado del servidor</span>
                            <span class="text-green-600 font-semibold">● En línea</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Base de datos</span>
                            <span class="text-green-600 font-semibold">● Conectada</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>PHP</span>
                            <span class="text-blue-600 font-semibold">{{ phpversion() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
