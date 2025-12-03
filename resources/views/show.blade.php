@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" visibility="detail" :model="$item" />

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                {{ class_basename($item::class) }} #{{ $item->id }}
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('buildora.index', $resource->uriKey()) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                <i class="fa-solid fa-arrow-left"></i>
                {{ __buildora('Back') }}
            </a>

            @can($resource->uriKey() . '.edit')
                <a href="{{ route('buildora.edit', ['resource' => $resource->uriKey(), 'id' => $item->id]) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 btn-primary text-white font-medium rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fa-solid fa-pen-to-square"></i>
                    {{ __buildora('Edit') }}
                </a>
            @endcan
        </div>
    </div>

    {{-- Detail Card --}}
    <div class="rounded-2xl overflow-hidden mb-6"
         style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
        <div class="p-6 lg:p-8">
            <div class="grid grid-cols-12 gap-6">
                @foreach($fields as $field)
                    @if(!$field->isVisible('detail'))
                        @continue
                    @endif

                    @php
                        $value = old($field->name, $item->{$field->name} ?? '');
                        $colSpan = (int) ($field->getColumnSpan()['default'] ?? 12);
                        $colSpan = max(1, min(12, $colSpan));
                    @endphp

                    @if(method_exists($field, 'shouldStartNewRow') && $field->shouldStartNewRow())
                        <div class="col-span-12"></div>
                    @endif

                    <div class="col-span-12 lg:col-span-{{ $colSpan }}" style="grid-column: span {{ $colSpan }} / span {{ $colSpan }};">
                        <div class="rounded-xl p-4 h-full"
                             style="background: var(--bg-input); border: 1px solid var(--border-color);">
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-muted);">
                                {{ $field->label }}
                            </label>

                            <div class="text-base" style="color: var(--text-primary);">
                                {!! $field instanceof \Ginkelsoft\Buildora\Fields\Types\ViewField
                                    ? $field->detailPage()
                                    : e($item->{$field->name})
                                !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @php
        use Ginkelsoft\Buildora\Layouts\Panel;

        $panels = collect($resource->getRelationResources())
            ->filter(fn ($layout) => $layout instanceof Panel);
    @endphp

    @if ($panels->isNotEmpty())
        @foreach ($panels as $panel)
            <x-buildora::relation.panel
                    :relation="$panel"
                    :model="$model"
                    :resource="$resource"
            />
        @endforeach
    @endif

@endsection
