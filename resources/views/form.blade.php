@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" :visibility="isset($item) ? 'edit' : 'create'"/>

    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="mt-4 bg-red-400 text-white p-4 rounded-lg shadow-md mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-buildora-icon icon="fa fa-exclamation-circle" class="text-white text-xl"/>
                <div>
                    <strong class="font-semibold">There were some issues with your submission:</strong>
                    <ul class="mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button @click="show = false" class="text-white ml-4 hover:text-gray-300">
                <x-buildora-icon icon="fa fa-times"/>
            </button>
        </div>
    @endif

    <div class="bg-white p-8 rounded-lg shadow-sm flex flex-col min-h-[400px]">
        <form method="POST"
              enctype="multipart/form-data"
              action="{{ isset($item) ? route('buildora.update', [$model, $item->id]) : route('buildora.store', $model) }}"
              class="space-y-6 flex-1 flex flex-col">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            <div class="flex-1 space-y-6">
                @foreach($fields as $field)
                    @if(!$field->isVisible('create') && !isset($item))
                        @continue
                    @endif

                    @if(!$field->isVisible('edit') && isset($item))
                        @continue
                    @endif

                    @php
                        $value = old($field->name, $item->{$field->name} ?? '');
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <label for="{{ $field->name }}" class="font-semibold text-gray-700 dark:text-gray-300">
                            {{ $field->label }}
                        </label>

                        <div class="col-span-2">
                            @if(!$field instanceof \Ginkelsoft\Buildora\Fields\Types\ViewField)
                                @component("buildora::components.input.{$field->type}", ['field' => $field, 'value' => $value])
                                @endcomponent
                            @else
                                {!! $field->detailPage() !!}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ✅ Sticky footer-knoppen -->
            <div class="flex justify-between mt-auto pt-6 border-t border-gray-200">
                <x-buildora::button.back :model="$model"/>
                <x-buildora::button.save/>
            </div>
        </form>
    </div>

@endsection('content')
