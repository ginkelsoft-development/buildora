@extends('buildora::layouts.buildora-guest')

@section('head')
<style>
    .login-brand-bg {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
    }
    .login-accent-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
</style>
@endsection

@section('content')
    <div class="min-h-screen flex">
        {{-- Left side - Branding --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden login-brand-bg">
            {{-- Abstract background pattern --}}
            <div class="absolute inset-0 opacity-60">
                <div class="absolute top-0 -left-4 w-72 h-72 login-blob-1 rounded-full filter blur-3xl animate-pulse"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 login-blob-2 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 login-blob-3 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 4s;"></div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-20">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ route('buildora.asset', ['file' => 'buildora.png']) }}" class="h-16" alt="Buildora" style="filter: brightness(0) invert(1);">
                    </div>
                    <h1 class="text-4xl xl:text-5xl font-bold text-white leading-tight mb-4">
                        {{ __buildora('Build something') }}<br>
                        <span class="login-link">{{ __buildora('amazing today') }}</span>
                    </h1>
                    <p class="text-white/60 text-lg max-w-md">
                        {{ __buildora('Powerful admin panels, resources, and datatables — fully based on your Eloquent models.') }}
                    </p>
                </div>

                {{-- Feature highlights --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-white/80">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="fas fa-bolt text-yellow-300 text-sm"></i>
                        </div>
                        <span>{{ __buildora('Lightning fast development') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/80">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="fas fa-shield-halved text-green-300 text-sm"></i>
                        </div>
                        <span>{{ __buildora('Built-in permissions') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/80">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <i class="fas fa-palette text-pink-300 text-sm"></i>
                        </div>
                        <span>{{ __buildora('Fully customizable') }}</span>
                    </div>
                </div>
            </div>

            {{-- Version footer --}}
            <div class="absolute bottom-6 left-12 xl:left-20 text-white/30 text-sm">
                Buildora v{{ config('buildora.version', 'dev') }}
            </div>
        </div>

        {{-- Right side - Login form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8" style="background: #0f0f1a;">
            <div class="w-full max-w-md">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
                    <img src="{{ route('buildora.asset', ['file' => 'buildora.png']) }}" alt="Buildora" class="h-10" style="filter: brightness(0) invert(1);">
                </div>

                {{-- Welcome text --}}
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">{{ __buildora('Welcome back') }}</h2>
                    <p class="text-white/50">{{ __buildora('Enter your credentials to access your account') }}</p>
                </div>

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                            <div class="text-red-400 text-sm">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Login form --}}
                <form method="POST" action="{{ route('buildora.login.post') }}" class="space-y-6">
                    @csrf

                    {{-- Email field --}}
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-white/70">{{ __buildora('E-mailaddress') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-white/30"></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required
                                   class="w-full pl-11 pr-4 py-3 login-input rounded-xl text-white placeholder-white/30 focus:outline-none transition-all duration-200">
                        </div>
                    </div>

                    {{-- Password field --}}
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-white/70">{{ __buildora('Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-white/30"></i>
                            </div>
                            <input type="password" id="password" name="password" placeholder="••••••••" required
                                   class="w-full pl-11 pr-4 py-3 login-input rounded-xl text-white placeholder-white/30 focus:outline-none transition-all duration-200">
                        </div>
                    </div>

                    {{-- Remember me & Forgot password --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-0">
                            <span class="text-sm text-white/50">{{ __buildora('Remember me') }}</span>
                        </label>
                        <a href="{{ route('buildora.password.request') }}" class="text-sm login-link transition-colors">
                            {{ __buildora('Forgot password?') }}
                        </a>
                    </div>

                    {{-- Submit button --}}
                    <button type="submit"
                            class="w-full py-3 px-4 login-btn text-white font-semibold rounded-xl shadow-lg transition-all duration-200">
                        {{ __buildora('Sign in') }}
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                {{-- Mobile version --}}
                <div class="lg:hidden mt-8 text-center text-white/20 text-xs">
                    Buildora v{{ config('buildora.version', 'dev') }}
                </div>
            </div>
        </div>
    </div>
@endsection
