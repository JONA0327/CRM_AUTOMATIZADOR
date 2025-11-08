<x-guest-layout>
    <div class="auth-header">
        <h2 class="auth-header__title">{{ __('Verifica tu correo electrónico') }}</h2>
        <p class="auth-header__subtitle">
            {{ __('Gracias por registrarte. Revisa tu bandeja de entrada y confirma tu correo para activar tu cuenta.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="form-status mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ __('Un nuevo enlace de verificación ha sido enviado a tu correo electrónico.') }}
        </div>
    @endif

    <div class="form-actions form-actions--between">
        <form method="POST" action="{{ route('verification.send') }}" class="form-actions">
            @csrf
            <x-primary-button>
                {{ __('Reenviar enlace') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="form-link">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
