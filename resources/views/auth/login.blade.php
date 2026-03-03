<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center p-4 font-sans antialiased">

    <div class="w-full max-w-sm">

        {{-- Logo / marca --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 shadow-xl mb-4">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">{{ config('app.name') }}</h1>
            <p class="text-slate-400 text-sm mt-1">Plataforma de automatización WhatsApp</p>
        </div>

        {{-- Card de login --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            {{-- Estado de sesión (ej: "Tu contraseña fue restablecida") --}}
            @if (session('status'))
                <div class="mb-5 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <h2 class="text-lg font-semibold text-gray-900 mb-6">Inicia sesión en tu cuenta</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Correo electrónico
                    </label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email') }}"
                           required autofocus autocomplete="username"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('email') border-red-400 bg-red-50 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password"
                           required autocomplete="current-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition @error('password') border-red-400 bg-red-50 @enderror">
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recuérdame --}}
                <div class="flex items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="remember_me" class="text-sm text-gray-600">Mantener sesión iniciada</label>
                </div>

                {{-- Botón --}}
                <button type="submit"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Iniciar sesión
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            El acceso es únicamente para cuentas autorizadas.<br>
            Contacta al administrador si necesitas acceso.
        </p>

    </div>

</body>
</html>
