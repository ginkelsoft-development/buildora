@extends('buildora::layouts.buildora')

@section('content')
    <div class="space-y-8">
        {{-- Welcome Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                    {{ __buildora('Welcome back') }}, {{ auth()->user()->name }}!
                </h1>
                <p class="mt-1 text-sm" style="color: var(--text-muted);">
                    {{ __buildora('Here\'s what\'s happening in your application.') }}
                </p>
            </div>
            <div class="text-sm" style="color: var(--text-muted);">
                <i class="fa-regular fa-calendar mr-2"></i>
                {{ now()->format('l, j F Y') }}
            </div>
        </div>

        {{-- Stats Cards --}}
        @if(count($stats) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($stats as $stat)
                    <a href="{{ $stat['route'] }}"
                       class="group rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                       style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-muted);">
                                    {{ $stat['label'] }}
                                </p>
                                <p class="mt-2 text-3xl font-bold" style="color: var(--text-primary);">
                                    {{ number_format($stat['value']) }}
                                </p>
                            </div>
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                                 style="background: {{ $stat['color'] }}20;">
                                <i class="{{ $stat['icon'] }} text-lg" style="color: {{ $stat['color'] }};"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm font-medium transition-colors"
                             style="color: {{ $stat['color'] }};">
                            {{ __buildora('View all') }}
                            <i class="fa-solid fa-arrow-right ml-2 text-xs transition-transform duration-300 group-hover:translate-x-1"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Quick Actions --}}
        @if(count($stats) > 0)
            <div class="rounded-2xl p-6" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
                <h2 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                    <i class="fa-solid fa-bolt mr-2" style="color: #667eea;"></i>
                    {{ __buildora('Quick Actions') }}
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($stats as $stat)
                        <a href="{{ route('buildora.create', ['resource' => $stat['slug']]) }}"
                           class="group flex flex-col items-center gap-2 p-4 rounded-xl transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
                           style="border: 1px solid var(--border-color);">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                 style="background: {{ $stat['color'] }}15;">
                                <i class="fa-solid fa-plus text-sm" style="color: {{ $stat['color'] }};"></i>
                            </div>
                            <span class="text-xs font-medium text-center" style="color: var(--text-secondary);">
                                {{ __buildora('Add') }} {{ Str::singular($stat['label']) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Custom Widgets --}}
        @if($widgets->count() > 0)
            <div class="grid grid-cols-12 gap-6">
                @foreach($widgets as $widget)
                    <div class="@foreach($widget->getColSpan() as $break => $span) {{ $break === 'default' ? "col-span-$span" : "$break:col-span-$span" }} @endforeach">
                        {!! $widget->render() !!}
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Recent Activity Placeholder --}}
        <div class="rounded-2xl p-6" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            <h2 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                <i class="fa-solid fa-clock-rotate-left mr-2" style="color: #667eea;"></i>
                {{ __buildora('System Information') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl" style="background: var(--bg-input);">
                    <div class="text-xs font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">
                        {{ __buildora('PHP Version') }}
                    </div>
                    <div class="text-lg font-semibold" style="color: var(--text-primary);">
                        {{ PHP_VERSION }}
                    </div>
                </div>
                <div class="p-4 rounded-xl" style="background: var(--bg-input);">
                    <div class="text-xs font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">
                        {{ __buildora('Laravel Version') }}
                    </div>
                    <div class="text-lg font-semibold" style="color: var(--text-primary);">
                        {{ app()->version() }}
                    </div>
                </div>
                <div class="p-4 rounded-xl" style="background: var(--bg-input);">
                    <div class="text-xs font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">
                        {{ __buildora('Buildora Version') }}
                    </div>
                    <div class="text-lg font-semibold" style="color: var(--text-primary);">
                        {{ config('buildora.version', 'dev') }}
                    </div>
                </div>
                <div class="p-4 rounded-xl" style="background: var(--bg-input);">
                    <div class="text-xs font-medium uppercase tracking-wider mb-1" style="color: var(--text-muted);">
                        {{ __buildora('Environment') }}
                    </div>
                    <div class="text-lg font-semibold" style="color: var(--text-primary);">
                        {{ ucfirst(app()->environment()) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
