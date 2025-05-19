@extends('buildora::layouts.buildora-guest')

@section('content')
    <form method="POST" action="{{ route('buildora.login.post') }}" class="max-w-md mx-auto mt-20 bg-white dark:bg-gray-900 shadow-lg rounded-2xl p-8 space-y-6">
        @csrf

        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __buildora('Welcome') }} {{ config('app.name') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __buildora('Log in to continue') }}</p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __buildora('E-mailaddress') }}</label>
            <input type="email" id="email" name="email" placeholder="jij@example.com" required
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition shadow-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __buildora('Password') }}</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition shadow-sm">
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('buildora.password.request') }}" class="text-sm text-blue-600 hover:underline dark:text-blue-400">{{ __('Forgot your password?') }}</a>
        </div>

        <div>
            <button type="submit"
                    class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm transition-all">
                {{ __buildora('Login') }}
            </button>
        </div>
    </form>
@endsection
