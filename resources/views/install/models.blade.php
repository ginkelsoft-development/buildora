@extends('buildora::layouts.buildora-guest')

@section('content')

    <div class="max-w-3xl mx-auto mt-12 bg-white rounded-2xl shadow-lg p-8 border border-gray-200">

        @include('buildora::install._steps')

        <h1 class="text-3xl font-bold text-gray-800 mb-6">Stap 3: Projectmodellen controleren</h1>

        <p class="text-gray-600 mb-8">
            Hieronder zie je alle modellen in <code>app/Models</code>. Je kunt modellen waar nodig direct Buildora-functionaliteit aan toevoegen.
        </p>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md mb-6">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <ul class="space-y-4">
            @foreach ($models as $model)
                <li class="p-4 border rounded-lg flex justify-between items-center
                    {{ $model['hasTrait'] ? 'bg-green-50 border-green-400' : 'bg-yellow-50 border-yellow-400' }}">
                    <div>
                        <h2 class="font-semibold text-gray-800">
                            <code><i class="fas fa-cube mr-1"></i> {{ $model['class'] }}</code>
                            <small class="text-gray-400">{{ str_replace(base_path() . '/', '', $model['file']) }}</small>
                        </h2>
                        @if($model['hasTrait'])
                            <p class="text-green-700 mt-1 text-sm">✅ Heeft HasBuildora trait</p>
                        @else
                            <p class="text-yellow-700 mt-1 text-sm">⚠️ Heeft nog geen Buildora-functionaliteit</p>
                        @endif
                    </div>

                    @unless($model['hasTrait'])
                        <form method="POST" action="{{ route('buildora.install.models.add') }}">
                            @csrf
                            <input type="hidden" name="class" value="{{ $model['class'] }}">
                            <input type="hidden" name="path" value="{{ $model['file'] }}">
                            <button class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                <i class="fas fa-wand-magic-sparkles"></i> Activeer Buildora
                            </button>
                        </form>
                    @endunless
                </li>
            @endforeach
        </ul>

        <div class="mt-10 text-right">
            <a href="{{ route('buildora.dashboard') }}"
               class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                <i class="fas fa-check-circle"></i> Installatie voltooien & naar dashboard
            </a>
        </div>
    </div>
@endsection
