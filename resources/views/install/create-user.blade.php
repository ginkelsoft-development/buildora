@extends('buildora::layouts.buildora-guest')

@section('content')

    <div class="max-w-3xl mx-auto mt-12 bg-white rounded-2xl shadow-lg p-8 border border-gray-200">

        @include('buildora::install._steps')

        <h1 class="text-3xl font-bold text-gray-800 mb-6">🚀 Buildora Installatie</h1>
        <p class="text-gray-600 mb-8">
            Je bent bijna klaar! Maak hieronder je eerste gebruiker aan om toegang te krijgen tot Buildora.
        </p>

        @if(session('error'))
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded-md mb-4">
                <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <div class="flex items-start gap-4 p-4 border border-blue-400 bg-blue-50 rounded-lg shadow-sm">
            <div class="mt-1 text-2xl">
                <i class="fas fa-user-plus text-blue-600"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-semibold text-gray-800">Stap 3: Eerste gebruiker aanmaken</h2>
                <p class="text-blue-700 mt-1">Vul de onderstaande gegevens in om een admin-gebruiker aan te maken.</p>

                <form method="POST" action="{{ route('buildora.install.user.store') }}" class="mt-6 space-y-6">
                    @csrf

                    {{-- Naam --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1" for="name">
                            <i class="fas fa-user mr-1 text-gray-500"></i> Naam
                        </label>
                        <input type="text" name="name" id="name"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1" for="email">
                            <i class="fas fa-envelope mr-1 text-gray-500"></i> E-mailadres
                        </label>
                        <input type="email" name="email" id="email"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('email') }}" required>
                        @error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Wachtwoord --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1" for="password">
                            <i class="fas fa-lock mr-1 text-gray-500"></i> Wachtwoord
                        </label>
                        <input type="password" name="password" id="password"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               required>
                        @error('password') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Bevestig wachtwoord --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1" for="password_confirmation">
                            <i class="fas fa-check-circle mr-1 text-gray-500"></i> Bevestig wachtwoord
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               required>
                    </div>

                    {{-- Knoppen --}}
                    <div class="flex justify-between items-center">
                        <a href="{{ route('buildora.install') }}" class="text-sm text-gray-500 hover:text-indigo-600">
                            <i class="fas fa-arrow-left mr-1"></i> Terug
                        </a>

                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-indigo-700 transition">
                            <i class="fas fa-plus-circle"></i> Gebruiker aanmaken
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Vooruitblik op stap 4 --}}
        <div class="mt-10 text-right">
            <a href="{{ route('buildora.install.models') }}" class="text-sm text-indigo-600 hover:text-indigo-800 transition">
                Ga verder naar stap 4 <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
@endsection
