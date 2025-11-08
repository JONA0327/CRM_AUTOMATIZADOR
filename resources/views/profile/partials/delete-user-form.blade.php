<section class="form-card" aria-labelledby="delete-account-heading">
    <header class="form-card__header">
        <h2 id="delete-account-heading" class="form-card__title">
            {{ __('Delete Account') }}
        </h2>
        <p class="form-card__subtitle">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="app-modal__header">
            <div class="app-modal__title">
                <span class="app-modal__icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 17.293A1 1 0 014 16.586V8a2 2 0 012-2h12a2 2 0 012 2v8.586a1 1 0 01-.293.707l-6 6a1 1 0 01-1.414 0l-6-6z" />
                    </svg>
                </span>
                <div>
                    <h2 class="app-modal__headline">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>
                    <p class="app-modal__description">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>
                </div>
            </div>
            <button type="button" class="app-modal__close" x-on:click="$dispatch('close')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="post" action="{{ route('profile.destroy') }}" class="form-stack">
            @csrf
            @method('delete')

            <div class="app-modal__body">
                <div class="form-field">
                    <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="{{ __('Password') }}"
                        autofocus
                    />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
                </div>
            </div>

            <div class="app-modal__footer">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
