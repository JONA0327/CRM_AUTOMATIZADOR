@extends('layouts.app')

@section('content')
<div class="module-shell" data-module="profile">
    <header class="module-header">
        <div class="module-header__headline">
            <span class="stat-icon">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
            <div>
                <h1 class="module-title">{{ __('Profile settings') }}</h1>
                <p class="module-subtitle">{{ __('Manage your identity, security preferences and account visibility.') }}</p>
            </div>
        </div>
        <div class="module-actions">
            <p class="module-tagline">{{ __('Account center') }}</p>
        </div>
    </header>

    <section class="module-section">
        <div class="form-stack--columns two">
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.update-password-form')
        </div>

        @include('profile.partials.delete-user-form')
    </section>
</div>
@endsection
