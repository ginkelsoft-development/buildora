@extends('buildora::layouts.buildora-guest')

@section('head')
<style>
    .login-brand-bg {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
    }
    .login-blob-1 { background: rgba(102, 126, 234, 0.4); }
    .login-blob-2 { background: rgba(118, 75, 162, 0.4); }
    .login-blob-3 { background: rgba(240, 147, 251, 0.3); }
    .login-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .login-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }
    .login-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .login-btn:hover {
        background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        transform: translateY(-1px);
        box-shadow: 0 10px 40px -10px rgba(102, 126, 234, 0.5);
    }
    .login-link { color: #667eea; }
    .login-link:hover { color: #8b9cf4; }
    .tab-active {
        background: rgba(102, 126, 234, 0.2);
        color: #667eea;
    }
    .code-input {
        letter-spacing: 0.75em;
        text-indent: 0.75em;
    }
</style>
@endsection

@section('content')
    <div class="min-h-screen flex">
        {{-- Left side - Branding --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden login-brand-bg">
            <div class="absolute inset-0 opacity-60">
                <div class="absolute top-0 -left-4 w-72 h-72 login-blob-1 rounded-full filter blur-3xl animate-pulse"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 login-blob-2 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 login-blob-3 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 4s;"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-20">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ route('buildora.asset', ['file' => 'buildora.png']) }}" class="h-16" alt="Buildora" style="filter: brightness(0) invert(1);">
                    </div>
                    <h1 class="text-4xl xl:text-5xl font-bold text-white leading-tight mb-4">
                        {{ __buildora('Secure') }}<br>
                        <span class="login-link">{{ __buildora('verification') }}</span>
                    </h1>
                    <p class="text-white/60 text-lg max-w-md">
                        {{ __buildora('Your account is protected with two-factor authentication.') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-white/80">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="fas fa-shield-halved text-green-300 text-sm"></i>
                        </div>
                        <span>{{ __buildora('Extra layer of security') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/80">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="fas fa-mobile-screen text-blue-300 text-sm"></i>
                        </div>
                        <span>{{ __buildora('Use your authenticator app') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/80">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="fas fa-key text-yellow-300 text-sm"></i>
                        </div>
                        <span>{{ __buildora('Or use a recovery code') }}</span>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-6 left-12 xl:left-20 text-white/30 text-sm">
                Buildora v{{ config('buildora.version', 'dev') }}
            </div>
        </div>

        {{-- Right side - 2FA Form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8" style="background: #0f0f1a;">
            <div class="w-full max-w-md" x-data="{ tab: 'code' }">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
                    <img src="{{ route('buildora.asset', ['file' => 'buildora.png']) }}" alt="Buildora" class="h-10" style="filter: brightness(0) invert(1);">
                </div>

                {{-- Header --}}
                <div class="text-center mb-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: rgba(102, 126, 234, 0.15);">
                        <i class="fa-solid fa-shield-halved text-2xl" style="color: #667eea;"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">{{ __buildora('Two-Factor Authentication') }}</h2>
                    <p class="text-white/50">{{ __buildora('Enter the code from your authenticator app.') }}</p>
                </div>

                {{-- Error Messages --}}
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                            <span class="text-red-400 text-sm">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                {{-- Tab Navigation --}}
                <div class="flex rounded-xl p-1 mb-6" style="background: rgba(255, 255, 255, 0.05);">
                    <button type="button"
                            @click="tab = 'code'"
                            :class="tab === 'code' ? 'tab-active' : 'text-white/50'"
                            class="flex-1 py-2.5 text-sm font-medium rounded-lg transition-all">
                        <i class="fas fa-mobile-screen mr-2"></i>
                        {{ __buildora('Authentication Code') }}
                    </button>
                    <button type="button"
                            @click="tab = 'recovery'"
                            :class="tab === 'recovery' ? 'tab-active' : 'text-white/50'"
                            class="flex-1 py-2.5 text-sm font-medium rounded-lg transition-all">
                        <i class="fas fa-key mr-2"></i>
                        {{ __buildora('Recovery Code') }}
                    </button>
                </div>

                {{-- Authentication Code Form --}}
                <form method="POST" action="{{ route('buildora.two-factor.verify') }}" x-show="tab === 'code'" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="code" class="block text-sm font-medium text-white/70">{{ __buildora('6-Digit Code') }}</label>
                        <input type="text"
                               id="code"
                               name="code"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               maxlength="6"
                               autocomplete="one-time-code"
                               placeholder="000000"
                               class="w-full px-4 py-4 login-input rounded-xl text-white text-center text-2xl font-mono code-input focus:outline-none transition-all duration-200"
                               required
                               autofocus>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 login-btn text-white font-semibold rounded-xl shadow-lg transition-all duration-200">
                        {{ __buildora('Verify') }}
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                {{-- Recovery Code Form --}}
                <form method="POST" action="{{ route('buildora.two-factor.verify') }}" x-show="tab === 'recovery'" x-cloak class="space-y-6">
                    @csrf
                    <input type="hidden" name="recovery" value="1">

                    <div class="space-y-2">
                        <label for="recovery_code" class="block text-sm font-medium text-white/70">{{ __buildora('Recovery Code') }}</label>
                        <input type="text"
                               id="recovery_code"
                               name="recovery_code"
                               autocomplete="off"
                               placeholder="XXXXXXXXXX"
                               class="w-full px-4 py-4 login-input rounded-xl text-white text-center font-mono uppercase tracking-widest focus:outline-none transition-all duration-200"
                               required>
                        <p class="text-xs text-white/40 text-center mt-2">
                            {{ __buildora('Enter one of your recovery codes.') }}
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 login-btn text-white font-semibold rounded-xl shadow-lg transition-all duration-200">
                        {{ __buildora('Verify') }}
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                {{-- Back to Login --}}
                <div class="mt-8 text-center">
                    <a href="{{ route('buildora.login') }}" class="text-sm text-white/40 hover:text-white/60 transition-colors">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        {{ __buildora('Back to login') }}
                    </a>
                </div>

                {{-- Mobile version --}}
                <div class="lg:hidden mt-8 text-center text-white/20 text-xs">
                    Buildora v{{ config('buildora.version', 'dev') }}
                </div>
            </div>
        </div>
    </div>
@endsection
