@extends('buildora::layouts.buildora')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                {{ __buildora('Profile Settings') }}
            </h1>
            <p class="mt-1 text-sm" style="color: var(--text-muted);">
                {{ __buildora('Manage your account settings and change your password.') }}
            </p>
        </div>

        {{-- Success Messages --}}
        @if(session('success'))
            <div class="rounded-lg p-4 mb-6" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-check-circle text-emerald-500"></i>
                    <span class="text-sm text-emerald-600 dark:text-emerald-400">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Profile Information --}}
        <div class="rounded-xl shadow-sm" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            <div class="p-6" style="border-bottom: 1px solid var(--border-color);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fa-solid fa-user mr-2" style="color: #667eea;"></i>
                    {{ __buildora('Profile Information') }}
                </h2>
                <p class="mt-1 text-sm" style="color: var(--text-muted);">
                    {{ __buildora('Update your account\'s profile information and email address.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('buildora.profile.update') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">
                        {{ __buildora('Name') }}
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-2.5 rounded-lg input-styled focus:outline-none transition-all"
                           required>
                    @error('name')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">
                        {{ __buildora('Email') }}
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-2.5 rounded-lg input-styled focus:outline-none transition-all"
                           required>
                    @error('email')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-lg text-white font-medium transition-all duration-200 btn-primary">
                        <i class="fa-solid fa-save mr-2"></i>
                        {{ __buildora('Save') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Update Password --}}
        <div class="rounded-xl shadow-sm" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            <div class="p-6" style="border-bottom: 1px solid var(--border-color);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fa-solid fa-lock mr-2" style="color: #667eea;"></i>
                    {{ __buildora('Update Password') }}
                </h2>
                <p class="mt-1 text-sm" style="color: var(--text-muted);">
                    {{ __buildora('Ensure your account is using a long, random password to stay secure.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('buildora.profile.password') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">
                        {{ __buildora('Current Password') }}
                    </label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           class="w-full px-4 py-2.5 rounded-lg input-styled focus:outline-none transition-all"
                           required>
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">
                        {{ __buildora('New Password') }}
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="w-full px-4 py-2.5 rounded-lg input-styled focus:outline-none transition-all"
                           required>
                    @error('password')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">
                        {{ __buildora('Confirm Password') }}
                    </label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="w-full px-4 py-2.5 rounded-lg input-styled focus:outline-none transition-all"
                           required>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-lg text-white font-medium transition-all duration-200 btn-primary">
                        <i class="fa-solid fa-key mr-2"></i>
                        {{ __buildora('Update Password') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Two-Factor Authentication --}}
        <div class="rounded-xl shadow-sm" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            <div class="p-6" style="border-bottom: 1px solid var(--border-color);">
                <h2 class="text-lg font-semibold" style="color: var(--text-primary);">
                    <i class="fa-solid fa-shield-halved mr-2" style="color: #667eea;"></i>
                    {{ __buildora('Two-Factor Authentication') }}
                </h2>
                <p class="mt-1 text-sm" style="color: var(--text-muted);">
                    {{ __buildora('Add an extra layer of security to your account using two-factor authentication.') }}
                </p>
            </div>

            <div class="p-6">
                @if($user->two_factor_confirmed_at)
                    {{-- 2FA is enabled --}}
                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(16, 185, 129, 0.1);">
                            <i class="fa-solid fa-check text-emerald-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-primary);">
                                {{ __buildora('Two-factor authentication is enabled.') }}
                            </p>
                            <p class="text-sm mt-1" style="color: var(--text-muted);">
                                {{ __buildora('Your account is protected with an authenticator app.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        {{-- Regenerate Recovery Codes --}}
                        <form method="POST" action="{{ route('buildora.two-factor.regenerate') }}" x-data="{ showPassword: false }">
                            @csrf
                            <button type="button"
                                    @click="showPassword = true"
                                    x-show="!showPassword"
                                    class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all"
                                    style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                                <i class="fa-solid fa-arrows-rotate mr-2"></i>
                                {{ __buildora('Regenerate Recovery Codes') }}
                            </button>
                            <div x-show="showPassword" x-cloak class="flex items-center gap-2">
                                <input type="password"
                                       name="password"
                                       placeholder="{{ __buildora('Password') }}"
                                       class="px-4 py-2.5 rounded-lg text-sm input-styled focus:outline-none"
                                       required>
                                <button type="submit"
                                        class="px-4 py-2.5 rounded-lg text-sm font-medium text-white btn-primary">
                                    {{ __buildora('Confirm') }}
                                </button>
                                <button type="button"
                                        @click="showPassword = false"
                                        class="px-3 py-2.5 rounded-lg text-sm"
                                        style="color: var(--text-muted);">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Disable 2FA --}}
                        <form method="POST" action="{{ route('buildora.two-factor.disable') }}" x-data="{ showPassword: false }">
                            @csrf
                            <button type="button"
                                    @click="showPassword = true"
                                    x-show="!showPassword"
                                    class="px-4 py-2.5 rounded-lg text-sm font-medium text-red-600 transition-all"
                                    style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                <i class="fa-solid fa-power-off mr-2"></i>
                                {{ __buildora('Disable') }}
                            </button>
                            <div x-show="showPassword" x-cloak class="flex items-center gap-2">
                                <input type="password"
                                       name="password"
                                       placeholder="{{ __buildora('Password') }}"
                                       class="px-4 py-2.5 rounded-lg text-sm input-styled focus:outline-none"
                                       required>
                                <button type="submit"
                                        class="px-4 py-2.5 rounded-lg text-sm font-medium text-white"
                                        style="background: #ef4444;">
                                    {{ __buildora('Disable 2FA') }}
                                </button>
                                <button type="button"
                                        @click="showPassword = false"
                                        class="px-3 py-2.5 rounded-lg text-sm"
                                        style="color: var(--text-muted);">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    {{-- 2FA is disabled --}}
                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: rgba(245, 158, 11, 0.1);">
                            <i class="fa-solid fa-exclamation-triangle text-amber-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-primary);">
                                {{ __buildora('Two-factor authentication is not enabled.') }}
                            </p>
                            <p class="text-sm mt-1" style="color: var(--text-muted);">
                                {{ __buildora('Enable two-factor authentication for enhanced security.') }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('buildora.two-factor.setup') }}"
                       class="inline-flex items-center px-5 py-2.5 rounded-lg text-white font-medium transition-all duration-200 btn-primary">
                        <i class="fa-solid fa-shield-halved mr-2"></i>
                        {{ __buildora('Enable Two-Factor Authentication') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
