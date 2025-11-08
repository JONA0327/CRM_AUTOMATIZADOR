<x-guest-layout>
    <div class="auth-header">
        <h2 class="auth-header__title">{{ __('Crear cuenta') }}</h2>
        <p class="auth-header__subtitle">{{ __('Regístrate para acceder al sistema') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="form-stack">
        @csrf

        <div class="form-stack--columns two">
            <div class="form-field">
                <x-input-label for="name" :value="__('Nombre completo')" />
                <div class="form-field--icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <x-text-input
                        id="name"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="{{ __('Tu nombre completo') }}"
                    />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

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
                        autocomplete="username"
                        placeholder="tu@ejemplo.com"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
        </div>

        <div class="form-stack--columns two">
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
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="form-field">
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
                <div class="form-field--icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <x-text-input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <label for="terms" class="form-callout">
            <input id="terms" type="checkbox" name="terms" class="form-checkbox" required>
            <span class="form-helper">
                {!! __('Acepto los :terms y la :privacy del CRM_AUTOMATIZADOR.', [
                    'terms' => '<a href="#" class="form-link">' . __('términos y condiciones') . '</a>',
                    'privacy' => '<a href="#" class="form-link">' . __('política de privacidad') . '</a>'
                ]) !!}
            </span>
        </label>

        <x-primary-button class="btn--full">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            {{ __('Crear cuenta') }}
        </x-primary-button>

        <div class="auth-footer">
            {{ __('¿Ya tienes una cuenta?') }}
            <a href="{{ route('login') }}" class="form-link">{{ __('Inicia sesión aquí') }}</a>
        </div>
    </form>
</x-guest-layout>
