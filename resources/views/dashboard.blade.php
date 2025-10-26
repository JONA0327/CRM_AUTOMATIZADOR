<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
                <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-900">Panel de Control</h2>
                    <p class="text-xs text-gray-600">CRM_AUTOMATIZADOR</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Bienvenido de nuevo</p>
                <p class="font-semibold text-gray-900">{{ Auth::user()->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="w-full">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-2 mb-3 w-full">
                <!-- Clientes -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <span class="text-green-500 text-sm font-medium">+12%</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-600 text-sm">Total Clientes</p>
                    <div class="mt-2 text-xs text-gray-500">
                        <p>Activos: 0 • Nuevos hoy: 0</p>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Ventas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-green-500 text-sm font-medium">+8%</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">$0</h3>
                    <p class="text-gray-600 text-sm">Ventas del Mes</p>
                    <div class="mt-2 text-xs text-gray-500">
                        <p>Meta: $10,000 • Restante: $10,000</p>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                            <div class="bg-green-600 h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Tareas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <span class="text-red-500 text-sm font-medium">3 pendientes</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-600 text-sm">Tareas Completadas</p>
                    <div class="mt-2 text-xs text-gray-500">
                        <p>Hoy: 0 • Esta semana: 0</p>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                            <div class="bg-orange-600 h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Oportunidades -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 hover:shadow-md transition duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <span class="text-green-500 text-sm font-medium">+15%</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-600 text-sm">Oportunidades</p>
                    <div class="mt-2 text-xs text-gray-500">
                        <p>En proceso: 0 • Cerradas: 0</p>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                            <div class="bg-purple-600 h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-2 w-full">
                <!-- Welcome Message -->
                <div class="lg:col-span-3">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold mb-2">¡Bienvenido al CRM_AUTOMATIZADOR!</h3>
                                <p class="text-blue-100 mb-6">
                                    Has iniciado sesión exitosamente. Desde aquí puedes gestionar tus clientes, 
                                    realizar seguimiento de ventas y automatizar tus procesos de negocio.
                                </p>
                                <div class="flex space-x-4">
                                    <button class="bg-white text-blue-600 px-6 py-2 rounded-lg font-medium hover:bg-blue-50 transition duration-200">
                                        Agregar Cliente
                                    </button>
                                    <button class="border border-white text-white px-6 py-2 rounded-lg font-medium hover:bg-white hover:text-blue-600 transition duration-200">
                                        Ver Reportes
                                    </button>
                                </div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="w-32 h-32 bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                        <h4 class="font-semibold text-gray-900 mb-4">Acciones Rápidas</h4>
                        <div class="space-y-2">
                            <button class="w-full flex items-center p-3 text-left hover:bg-gray-50 rounded-lg transition duration-200">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Nuevo Cliente</span>
                            </button>
                            <button class="w-full flex items-center p-3 text-left hover:bg-gray-50 rounded-lg transition duration-200">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Nueva Venta</span>
                            </button>
                            <button class="w-full flex items-center p-3 text-left hover:bg-gray-50 rounded-lg transition duration-200">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Nueva Tarea</span>
                            </button>
                            <button class="w-full flex items-center p-3 text-left hover:bg-gray-50 rounded-lg transition duration-200">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Ver Reportes</span>
                            </button>
                            <button class="w-full flex items-center p-3 text-left hover:bg-gray-50 rounded-lg transition duration-200">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Configuración</span>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                        <h4 class="font-semibold text-gray-900 mb-4">Estadísticas del Sistema</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Tiempo activo</span>
                                <span class="text-sm font-medium text-green-600">Online</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Base de datos</span>
                                <span class="text-sm font-medium text-green-600">Conectada</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Último backup</span>
                                <span class="text-sm font-medium text-gray-900">Hoy</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Versión Laravel</span>
                                <span class="text-sm font-medium text-blue-600">12.35.1</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Memoria usada</span>
                                <span class="text-sm font-medium text-orange-600">45%</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Espacio disco</span>
                                <span class="text-sm font-medium text-green-600">78% libre</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .main-content-desktop {
            width: calc(100vw - 240px) !important;
            max-width: calc(100vw - 240px) !important;
        }
        
        main {
            width: 100% !important;
            padding: 1rem !important;
        }
        
        .py-2 {
            height: 100% !important;
        }
        
        /* Stats cards más altas */
        .bg-white.rounded-lg.shadow-sm.border.border-gray-100.p-4 {
            min-height: 120px !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        /* Welcome card más alta */
        .bg-gradient-to-r {
            min-height: 300px !important;
        }
        
        /* Sidebar cards más altas */
        .space-y-4 .bg-white {
            min-height: 250px !important;
        }
        
        .w-full {
            width: 100% !important;
        }
        
        .grid {
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Distribuir verticalmente */
        .grid.grid-cols-1.lg\\:grid-cols-4 {
            align-content: stretch;
            height: calc(100% - 140px);
        }
        
        .px-1, .px-2, .px-4, .px-6, .px-8 {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
    </style>
</x-app-layout>
