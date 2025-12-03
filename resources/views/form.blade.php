@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" :visibility="isset($item) ? 'edit' : 'create'"/>

    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="p-4 rounded-xl mb-6 flex items-start justify-between"
             style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.05) 100%); border: 1px solid rgba(239, 68, 68, 0.3);">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(239, 68, 68, 0.2);">
                    <i class="fa-solid fa-exclamation-circle text-red-500"></i>
                </div>
                <div>
                    <span class="font-medium block" style="color: var(--text-primary);">{{ __buildora('There are issues with your input') }}</span>
                    <ul class="mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-red-400">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="color: var(--text-muted);">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                @if(isset($item))
                    {{ __buildora('Edit') }}
                @else
                    {{ __buildora('Create new item') }}
                @endif
            </h1>
            <p class="text-sm mt-1" style="color: var(--text-muted);">
                {{ __buildora('Fill in the details below') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('buildora.index', $model) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                <i class="fa-solid fa-arrow-left"></i>
                {{ __buildora('Back') }}
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="rounded-2xl overflow-hidden"
         style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">

        <form method="POST"
              enctype="multipart/form-data"
              action="{{ isset($item) ? route('buildora.update', [$model, $item->id]) : route('buildora.store', $model) }}">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            {{-- Form Fields --}}
            <div class="p-6 lg:p-8">
                <div class="grid grid-cols-12 gap-6">
                    @foreach($fields as $field)
                        @if(!$field->isVisible('create') && !isset($item))
                            @continue
                        @endif
                        @if(!$field->isVisible('edit') && isset($item))
                            @continue
                        @endif

                        @php
                            $value = old($field->name, $item->{$field->name} ?? '');
                            $colSpan = (int) ($field->getColumnSpan()['default'] ?? 12);
                            $colSpan = max(1, min(12, $colSpan));
                        @endphp

                        @if($field->shouldStartNewRow())
                            <div class="col-span-12"></div>
                        @endif

                        <div class="col-span-12 lg:col-span-{{ $colSpan }}" style="grid-column: span {{ $colSpan }} / span {{ $colSpan }};">
                            <label for="{{ $field->name }}"
                                   class="block text-sm font-medium mb-2"
                                   style="color: var(--text-secondary);">
                                {{ $field->label }}
                            </label>

                            @if(!$field instanceof \Ginkelsoft\Buildora\Fields\Types\ViewField)
                                @component("buildora::components.input.{$field->type}", [
                                    'field' => $field,
                                    'value' => $value,
                                ])
                                @endcomponent
                            @else
                                <div class="p-4 rounded-xl" style="background: var(--bg-input); border: 1px solid var(--border-color);">
                                    {!! $field->detailPage() !!}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="px-6 lg:px-8 py-4 flex items-center justify-end gap-3"
                 style="background: var(--bg-input); border-top: 1px solid var(--border-color);">
                <a href="{{ route('buildora.index', $model) }}"
                   class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
                   style="background: var(--bg-dropdown); border: 1px solid var(--border-color); color: var(--text-primary);">
                    {{ __buildora('cancel') }}
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 btn-primary text-white font-medium rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fa-solid fa-check"></i>
                    {{ __buildora('Save') }}
                </button>
            </div>
        </form>
    </div>

@endsection
