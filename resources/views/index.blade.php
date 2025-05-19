@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" visibility="index" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="bg-primary text-primary-foreground p-4 rounded-lg shadow-md mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-buildora-icon icon="fa fa-check-circle" class="text-primary-foreground text-xl" />
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-primary-foreground ml-4 hover:opacity-75">
                <x-buildora-icon icon="fa fa-times" />
            </button>
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-6 text-foreground">
        {{ $resource->title() }}
    </h1>

    @can(Str::kebab($model) . '.create')
        <div class="flex justify-end mb-4">
            <a href="{{ route('buildora.create', ['resource' => $model]) }}"
               class="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg shadow-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition duration-200 ease-in-out">
                <x-buildora-icon icon="fa fa-plus" class="mr-2" />
                {{ __buildora('create') }} {{ $resource->title() }}
            </a>
        </div>
    @endcan

    <x-buildora::datatable :columns="$columns"/>
@endsection
