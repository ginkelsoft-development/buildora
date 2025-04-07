@extends('buildora::layouts.buildora-guest')

@section('content')
    <div class="max-w-3xl mx-auto mt-12 bg-white rounded-2xl shadow-lg p-8 border border-gray-200">

        @include('buildora::install._steps')

        <h1 class="text-3xl font-bold text-gray-800 mb-6">🚀 Welkom bij Buildora Installatie</h1>
        <p class="text-gray-600 mb-8">
            We begeleiden je in enkele stappen om Buildora correct te configureren in je project.
        </p>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md mb-4">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 text-blue-800 px-4 py-3 rounded-md mb-4">
                <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6">
            {{-- Stap 1: User model check --}}
            <div class="flex items-start gap-4 p-4 border rounded-lg shadow-sm
                @if($hasUserModel) border-green-400 bg-green-50 @else border-red-400 bg-red-50 @endif">
                <div class="mt-1 text-2xl">
                    @if($hasUserModel)
                        <i class="fas fa-check-circle text-green-600"></i>
                    @else
                        <i class="fas fa-times-circle text-red-600"></i>
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Stap 1: User model controleren</h2>
                    @if($hasUserModel)
                        <p class="text-green-700 mt-1">✅ User model gevonden in <code>app/Models/User.php</code>.</p>
                    @else
                        <p class="text-red-600 mt-1">❌ Geen User model gevonden.</p>
                        <p class="text-gray-500 text-sm mt-1">
                            Je kunt een gebruikersmodel genereren via Jetstream, Breeze of met:
                            <code class="bg-gray-100 px-1 py-0.5 rounded text-sm">php artisan make:auth</code>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Stap 2: Trait check --}}
            @if($hasUserModel)
                <div class="flex items-start gap-4 p-4 border rounded-lg shadow-sm
                    @if($hasTrait) border-green-400 bg-green-50 @else border-yellow-400 bg-yellow-50 @endif">
                    <div class="mt-1 text-2xl">
                        @if($hasTrait)
                            <i class="fas fa-check-circle text-green-600"></i>
                        @else
                            <i class="fas fa-exclamation-circle text-yellow-600"></i>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-800">Stap 2: HasBuildora trait toevoegen</h2>
                        @if($hasTrait)
                            <p class="text-green-700 mt-1">✅ Trait <code>HasBuildora</code> is aanwezig in het User model.</p>
                        @else
                            <p class="text-yellow-700 mt-1">⚠️ Trait <code>HasBuildora</code> ontbreekt in je User model.</p>
                            <form method="POST" action="{{ route('buildora.install.run') }}" class="mt-4">
                                @csrf
                                <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    <i class="fas fa-magic"></i> Voeg trait automatisch toe
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Stap 3: Gebruiker aanmaken --}}
            @if($hasUserModel && $hasTrait)
                <div class="flex items-start gap-4 p-4 border rounded-lg shadow-sm border-blue-400 bg-blue-50">
                    <div class="mt-1 text-2xl">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-800">Stap 3: Eerste gebruiker aanmaken</h2>
                        <p class="text-blue-700 mt-1">Maak nu je eerste gebruiker aan om Buildora te gebruiken.</p>
                        <div class="mt-4">
                            <a href="{{ route('buildora.install.user') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-arrow-right"></i> Ga naar stap 3
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-10 text-sm text-center text-gray-400">
            Buildora installatie wizard v1.0
        </div>
    </div>
@endsection
