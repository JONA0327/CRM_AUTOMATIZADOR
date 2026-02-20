<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Asistente Virtual</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <!-- Sección Izquierda - Información del Bot -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 p-12 flex-col justify-between relative overflow-hidden">
                <!-- Patrón de fondo decorativo -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 25px 25px, white 2%, transparent 0%), radial-gradient(circle at 75px 75px, white 2%, transparent 0%); background-size: 100px 100px;"></div>
                </div>
                
                <div class="relative z-10">
                    <!-- Logo y nombre -->
                    <div class="flex items-center space-x-3 mb-12">
                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-white">{{ config('app.name', 'Laravel') }}</h1>
                    </div>

                    <!-- Título principal -->
                    <div class="mb-8">
                        <h2 class="text-4xl font-bold text-white mb-4 leading-tight">
                            Asistente Virtual<br/>
                            Inteligente
                        </h2>
                        <p class="text-blue-100 text-lg">
                            Tu bot automatizado para atención al cliente y gestión de productos
                        </p>
                    </div>

                    <!-- Características -->
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mt-1">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">Información de Productos</h3>
                                <p class="text-blue-200 text-sm">Acceso instantáneo a catálogo y detalles completos</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mt-1">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">Atención Personalizada</h3>
                                <p class="text-blue-200 text-sm">Respuestas rápidas y atención 24/7</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mt-1">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">Gestión Eficiente</h3>
                                <p class="text-blue-200 text-sm">Panel administrativo completo y fácil de usar</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer decorativo con ilustración de bot -->
                <div class="relative z-10 flex justify-center">
                    <div class="w-48 h-48 relative">
                        <svg viewBox="0 0 200 200" class="w-full h-full drop-shadow-2xl">
                            <defs>
                                <linearGradient id="botGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:rgb(255,255,255);stop-opacity:0.95" />
                                    <stop offset="100%" style="stop-color:rgb(219,234,254);stop-opacity:0.95" />
                                </linearGradient>
                            </defs>
                            <!-- Cuerpo del bot -->
                            <rect x="50" y="60" width="100" height="120" rx="15" fill="url(#botGradient)" />
                            <!-- Cabeza -->
                            <rect x="60" y="20" width="80" height="60" rx="10" fill="url(#botGradient)" />
                            <!-- Antena -->
                            <line x1="100" y1="20" x2="100" y2="5" stroke="white" stroke-width="3" stroke-linecap="round"/>
                            <circle cx="100" cy="5" r="4" fill="#60A5FA"/>
                            <!-- Ojos -->
                            <circle cx="80" cy="45" r="8" fill="#2563EB"/>
                            <circle cx="120" cy="45" r="8" fill="#2563EB"/>
                            <!-- Boca sonriente -->
                            <path d="M 75 55 Q 100 65 125 55" stroke="#2563EB" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <!-- Brazos -->
                            <rect x="35" y="90" width="15" height="50" rx="7" fill="url(#botGradient)" />
                            <rect x="150" y="90" width="15" height="50" rx="7" fill="url(#botGradient)" />
                            <!-- Detalles del cuerpo -->
                            <circle cx="100" cy="110" r="6" fill="#2563EB"/>
                            <rect x="70" y="140" width="60" height="4" rx="2" fill="#2563EB"/>
                            <rect x="70" y="150" width="60" height="4" rx="2" fill="#2563EB"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Sección Derecha - Formulario de Login -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
                <div class="w-full max-w-md">
                    <!-- Logo móvil -->
                    <div class="lg:hidden flex items-center justify-center space-x-3 mb-8">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ config('app.name', 'Laravel') }}</h1>
                    </div>

                    <!-- Título del formulario -->
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Bienvenido</h2>
                        <p class="text-gray-600">Inicia sesión para acceder al panel de administración</p>
                    </div>

                    @if (Route::has('login'))
                        @auth
                            <!-- Si ya está autenticado, redirigir al dashboard -->
                            <div class="text-center">
                                <p class="text-gray-600 mb-4">Ya has iniciado sesión</p>
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                    Ir al Dashboard
                                </a>
                            </div>
                        @else
                            <!-- Formulario de Login -->
                            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                                @csrf

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Correo Electrónico
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                            </svg>
                                        </div>
                                        <input 
                                            id="email" 
                                            type="email" 
                                            name="email" 
                                            value="{{ old('email') }}" 
                                            required 
                                            autofocus 
                                            autocomplete="username"
                                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('email') border-red-500 @enderror" 
                                            placeholder="nombre@ejemplo.com"
                                        >
                                    </div>
                                    @error('email')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Contraseña
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </div>
                                        <input 
                                            id="password" 
                                            type="password" 
                                            name="password" 
                                            required 
                                            autocomplete="current-password"
                                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out @error('password') border-red-500 @enderror" 
                                            placeholder="••••••••"
                                        >
                                    </div>
                                    @error('password')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Remember Me y Forgot Password -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <input 
                                            id="remember_me" 
                                            name="remember" 
                                            type="checkbox" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        >
                                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                                            Recordarme
                                        </label>
                                    </div>

                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    @endif
                                </div>

                                <!-- Botón de Login -->
                                <button 
                                    type="submit" 
                                    class="w-full flex justify-center items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Iniciar Sesión
                                </button>
                            </form>

                            <!-- Nota informativa -->
                            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Nota:</strong> El registro de nuevos usuarios está restringido. Solo los administradores pueden crear nuevas cuentas.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
                    

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
