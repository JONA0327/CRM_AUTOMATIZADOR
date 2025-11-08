<x-guest-layout>
    <div class="auth-header">
        <span class="app-modal__icon auth-header__icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </span>
        <h2 class="auth-header__title">{{ __('Recuperar contraseña') }}</h2>
        <p class="auth-header__subtitle">
            {{ __('Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="form-stack">
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
                    placeholder="tu@ejemplo.com"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <x-primary-button class="btn--full">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            {{ __('Enviar enlace de recuperación') }}
        </x-primary-button>

        <div class="auth-footer">
            {{ __('¿Recordaste tu contraseña?') }}
            <a href="{{ route('login') }}" class="form-link">{{ __('Volver al inicio de sesión') }}</a>
        </div>
    </form>
</x-guest-layout>
