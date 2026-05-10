@extends('layouts.app')

@section('content')
    <div class="za-profile-page bg-gradient-to-b from-white to-gray-50 min-h-screen">
        <x-container class="py-10">
            {{-- Header --}}
            <div class="za-profile-hero mb-8">
                <div class="za-profile-badge">
                    <span class="dot"></span>
                    <span>{{ __('Profile') }}</span>
                </div>

                <h1 class="za-profile-title">{{ __('Profile') }}</h1>

                <p class="za-profile-subtitle">
                    Gérez les informations de votre compte, modifiez votre mot de passe et sécurisez votre profil.
                </p>
            </div>

            <div class="space-y-6">
                <div class="za-profile-card">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="za-profile-card">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="za-profile-card za-profile-card-danger">
                    <div class="max-w-2xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </x-container>
    </div>
@endsection