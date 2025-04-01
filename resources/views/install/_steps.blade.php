@php
    $steps = [
        ['label' => 'User model', 'route' => 'buildora.install', 'active' => request()->routeIs('buildora.install')],
        ['label' => 'Gebruiker aanmaken', 'route' => 'buildora.install.user', 'active' => request()->routeIs('buildora.install.user')],
        ['label' => 'Modellen activeren', 'route' => 'buildora.install.models', 'active' => request()->routeIs('buildora.install.models')],
    ];
@endphp

<div class="flex items-center justify-between mb-8">
    @foreach ($steps as $index => $step)
        <div class="flex items-center gap-2 {{ $step['active'] ? 'font-bold text-indigo-600' : 'text-gray-500' }}">
            <div class="w-6 h-6 rounded-full flex items-center justify-center
                {{ $step['active'] ? 'bg-indigo-600 text-white' : 'bg-gray-300' }}">
                {{ $index + 1 }}
            </div>
            <a href="{{ route($step['route']) }}">{{ $step['label'] }}</a>
        </div>

        @if (!$loop->last)
            <div class="flex-1 border-t mx-2 border-gray-300"></div>
        @endif
    @endforeach
</div>
