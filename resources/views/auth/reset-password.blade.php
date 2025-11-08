<x-guest-layout>
    <div class="auth-header">
        <h2 class="auth-header__title">{{ __('Restablecer contraseña') }}</h2>
        <p class="auth-header__subtitle">{{ __('Crea una nueva contraseña segura para continuar.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="form-stack">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                    :value="old('email', $request->email)"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="tu@ejemplo.com"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="form-stack--columns two">
            <div class="form-field">
                <x-input-label for="password" :value="__('Nueva contraseña')" />
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

        <div class="form-actions form-actions--end">
            <x-primary-button>
                {{ __('Restablecer contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
