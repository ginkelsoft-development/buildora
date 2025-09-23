@extends('buildora::layouts.buildora')

@section('content')

    {{-- ✅ Resource-specifieke widgets --}}
    <x-buildora::widgets :resource="$resource" :visibility="isset($item) ? 'edit' : 'create'"/>

    {{-- ✅ Foutmeldingen --}}
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="mt-6 bg-red-50 text-red-700 p-4 rounded-lg shadow-md mb-6 flex items-start justify-between">
            <div class="flex items-start gap-3">
                <x-buildora-icon icon="fa fa-exclamation-circle" class="text-red-600 text-xl mt-0.5"/>
                <div>
                    <strong class="font-semibold block mb-1">{{ __buildora('Er zijn problemen met je invoer') }}:</strong>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button @click="show = false" class="text-red-600 hover:opacity-75 ml-4">
                <x-buildora-icon icon="fa fa-times"/>
            </button>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <form method="POST"
              enctype="multipart/form-data"
              action="{{ isset($item) ? route('buildora.update', [$model, $item->id]) : route('buildora.store', $model) }}"
              class="flex flex-col space-y-8 max-w-7xl mx-auto">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            {{-- ✅ Velden-grid --}}
            <div class="grid grid-cols-12 gap-6">
                @foreach($fields as $field)
                    {{-- Sla onzichtbare velden over --}}
                    @if(!$field->isVisible('create') && !isset($item))
                        @continue
                    @endif
                    @if(!$field->isVisible('edit') && isset($item))
                        @continue
                    @endif

                    @php
                        $value = old($field->name, $item->{$field->name} ?? '');
                        $spans = collect($field->getColumnSpan())
                            ->filter(fn($v, $bp) => in_array($bp, ['default','sm','md','lg','xl','2xl']))
                            ->map(fn($v) => max(1, min(12, (int) $v)));

                        $colClasses = trim($spans->map(fn($cols,$bp) =>
                            $bp === 'default' ? "col-span-{$cols}" : "{$bp}:col-span-{$cols}"
                        )->implode(' '));

                        if ($colClasses === '') $colClasses = 'col-span-12';
                    @endphp

                    {{-- Nieuwe rij forceren --}}
                    @if($field->shouldStartNewRow())
                        <div class="col-span-12"></div>
                    @endif

                    <div class="{{ $colClasses }}">
                        <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            {{ $field->label }}
                        </label>

                        @if(!$field instanceof \Ginkelsoft\Buildora\Fields\Types\ViewField)
                            @component("buildora::components.input.{$field->type}", [
                                'field' => $field,
                                'value' => $value,
                                'class' => 'w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm
                                            focus:ring-2 focus:ring-primary focus:outline-none
                                            bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100',
                            ])
                            @endcomponent
                        @else
                            {!! $field->detailPage() !!}
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- ✅ Actieknoppen --}}
            <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <x-buildora::button.back :model="$model"
                                         class="px-5 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition"/>
                <x-buildora::button.save
                        class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 shadow transition"/>
            </div>
        </form>
    </div>

@endsection
