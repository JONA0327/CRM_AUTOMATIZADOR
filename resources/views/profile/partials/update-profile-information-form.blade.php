<section class="form-card" aria-labelledby="profile-information-heading">
    <header class="form-card__header">
        <h2 id="profile-information-heading" class="form-card__title">
            {{ __('Profile Information') }}
        </h2>
        <p class="form-card__subtitle">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="form-stack">
        @csrf
        @method('patch')

        <div class="form-stack--columns two">
            <div class="form-field">
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    :value="old('name', $user->name)"
                    required
                    autofocus
                    autocomplete="name"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div class="form-field">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email', $user->email)"
                    required
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="form-helper">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="form-link" type="submit">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="form-note form-note--success mt-2">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
