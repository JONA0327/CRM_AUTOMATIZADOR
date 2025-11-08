<x-guest-layout>
    <div class="auth-header">
        <h2 class="auth-header__title">{{ __('Iniciar sesión') }}</h2>
        <p class="auth-header__subtitle">{{ __('Ingresa a tu cuenta para continuar') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="form-stack">
        @csrf

        <div class="form-field">
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <div class="form-field--icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="tu@ejemplo.com"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="form-field">
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="form-field--icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <x-text-input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="auth-meta">
            <label for="remember_me" class="form-inline">
                <input id="remember_me" type="checkbox" name="remember" class="form-checkbox">
                <span class="form-helper">{{ __('Recordar sesión') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="form-link" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="btn--full">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5-4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            {{ __('Iniciar sesión') }}
        </x-primary-button>

        @if (Route::has('register'))
            <div class="auth-footer">
                {{ __('¿No tienes una cuenta?') }}
                <a href="{{ route('register') }}" class="form-link">{{ __('Regístrate aquí') }}</a>
            </div>
        @endif
    </form>
</x-guest-layout>
