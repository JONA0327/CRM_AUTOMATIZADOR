<section class="form-card" aria-labelledby="update-password-heading">
    <header class="form-card__header">
        <h2 id="update-password-heading" class="form-card__title">
            {{ __('Update Password') }}
        </h2>
        <p class="form-card__subtitle">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="form-stack">
        @csrf
        @method('put')

        <div class="form-field">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div class="form-stack--columns two">
            <div class="form-field">
                <x-input-label for="update_password_password" :value="__('New Password')" />
                <x-text-input
                    id="update_password_password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
            </div>

            <div class="form-field">
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="form-actions">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="form-note form-note--success"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
