@extends('buildora::layouts.buildora')

@section('content')
    <div class="max-w-lg mx-auto">
        {{-- Back Button --}}
        <a href="{{ route('buildora.profile.edit') }}"
           class="inline-flex items-center text-sm mb-6 transition-colors"
           style="color: var(--text-muted);">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            {{ __buildora('Back') }}
        </a>

        <div class="rounded-xl shadow-sm" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            {{-- Header --}}
            <div class="p-6 text-center" style="border-bottom: 1px solid var(--border-color);">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: rgba(102, 126, 234, 0.1);">
                    <i class="fa-solid fa-shield-halved text-2xl" style="color: #667eea;"></i>
                </div>
                <h1 class="text-xl font-bold" style="color: var(--text-primary);">
                    {{ __buildora('Set up Two-Factor Authentication') }}
                </h1>
                <p class="mt-2 text-sm" style="color: var(--text-muted);">
                    {{ __buildora('Scan this QR code with your authenticator app.') }}
                </p>
            </div>

            {{-- QR Code --}}
            <div class="p-6" style="border-bottom: 1px solid var(--border-color);">
                <div class="flex justify-center mb-6">
                    <div class="p-4 rounded-xl" style="background: white;">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>

                {{-- Secret Key --}}
                <div class="text-center">
                    <p class="text-xs uppercase tracking-wider mb-2" style="color: var(--text-muted);">
                        {{ __buildora('Or enter this code manually:') }}
                    </p>
                    <code class="px-4 py-2 rounded-lg text-sm font-mono select-all" style="background: var(--bg-input); color: var(--text-primary);">
                        {{ $secret }}
                    </code>
                </div>
            </div>

            {{-- Verify Code Form --}}
            <form method="POST" action="{{ route('buildora.two-factor.enable') }}" class="p-6">
                @csrf

                <div class="mb-6">
                    <label for="code" class="block text-sm font-medium mb-2" style="color: var(--text-secondary);">
                        {{ __buildora('Verification Code') }}
                    </label>
                    <input type="text"
                           id="code"
                           name="code"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           maxlength="6"
                           autocomplete="one-time-code"
                           placeholder="000000"
                           class="w-full px-4 py-3 rounded-lg text-center text-2xl font-mono tracking-[0.5em] input-styled focus:outline-none"
                           required
                           autofocus>
                    @error('code')
                        <p class="mt-2 text-sm text-red-500 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-lg text-white font-medium transition-all duration-200 btn-primary">
                    {{ __buildora('Enable Two-Factor Authentication') }}
                </button>
            </form>
        </div>
    </div>
@endsection
