<x-guest-layout>
    <div class="auth-header">
        <h2 class="auth-header__title">{{ __('Confirmar contraseña') }}</h2>
        <p class="auth-header__subtitle">{{ __('Por seguridad, confirma tu contraseña antes de continuar.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="form-stack">
        @csrf

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

        <div class="form-actions form-actions--end">
            <x-primary-button>
                {{ __('Confirmar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
