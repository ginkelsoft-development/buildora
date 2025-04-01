@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" visibility="index" />


    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms class="bg-green-400 text-white p-4 rounded-lg shadow-md mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-buildora-icon icon="fa fa-check-circle" class="text-white text-xl" />
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-white ml-4 hover:text-gray-300">
                <x-buildora-icon icon="fa fa-times" />
            </button>
        </div>
    @endif

    <div class="flex justify-end mb-4">
        <a href="{{ route('buildora.create', ['resource' => $model]) }}"
           class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200 ease-in-out">
            <x-buildora-icon icon="fa fa-plus" class="mr-2" />
            Create {{ ucfirst($model) }}
        </a>
    </div>

    <x-buildora::datatable :columns="$columns"/>
@endsection
