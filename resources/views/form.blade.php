@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" :visibility="isset($item) ? 'edit' : 'create'"/>

    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="mt-4 bg-destructive text-destructive-foreground p-4 rounded-lg shadow-md mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-buildora-icon icon="fa fa-exclamation-circle" class="text-destructive-foreground text-xl"/>
                <div>
                    <strong class="font-semibold">{{ __buildora('There were some issues with your submission') }}:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button @click="show = false" class="text-destructive-foreground ml-4 hover:opacity-75">
                <x-buildora-icon icon="fa fa-times"/>
            </button>
        </div>
    @endif

    <div class="bg-base text-foreground p-8 rounded-lg shadow-sm flex flex-col min-h-[400px] border border-border">
        <form method="POST"
              enctype="multipart/form-data"
              action="{{ isset($item) ? route('buildora.update', [$model, $item->id]) : route('buildora.store', $model) }}"
              class="space-y-6 flex-1 flex flex-col">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            <div class="grid grid-cols-12 gap-6 w-full max-w-screen-lg mx-auto">
                @foreach($fields as $field)
                    @if(!$field->isVisible('create') && !isset($item))
                        @continue
                    @endif

                    @if(!$field->isVisible('edit') && isset($item))
                        @continue
                    @endif

                    @php
                        $value = old($field->name, $item->{$field->name} ?? '');
                        $spans = is_array($field->getColumnSpan()) ? $field->getColumnSpan() : ['default' => $field->getColumnSpan()];

$colClasses = collect($spans)->map(function ($cols, $breakpoint) {

    return $breakpoint === 'default'
        ? "col-span-{$cols}"
        : "{$breakpoint}:col-span-{$cols}";
})->implode(' ');
                    @endphp

                    @if(method_exists($field, 'shouldStartNewRow') && $field->shouldStartNewRow())
                        <div class="col-span-12"></div>
                    @endif

                    <div class="{{ $colClasses }}">
                        <label for="{{ $field->name }}" class="block font-semibold text-foreground mb-1">
                            {{ $field->label }}
                        </label>

                        @if(!$field instanceof \Ginkelsoft\Buildora\Fields\Types\ViewField)
                            @component("buildora::components.input.{$field->type}", ['field' => $field, 'value' => $value])
                            @endcomponent
                        @else
                            {!! $field->detailPage() !!}
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between mt-auto pt-6 border-t border-border">
                <x-buildora::button.back :model="$model"/>
                <x-buildora::button.save/>
            </div>
        </form>
    </div>

@endsection('content')
