@extends('buildora::layouts.buildora')

@section('content')

    <x-buildora::widgets :resource="$resource" visibility="detail" :model="$item" />

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-foreground">
            {{ class_basename($item::class) }} #{{ $item->id }}
        </h1>

        @can($resource->uriKey() . '.edit')
            <a href="{{ route('buildora.edit', ['resource' => $resource->uriKey(), 'id' => $item->id]) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium bg-primary text-primary-foreground hover:opacity-90 rounded-lg shadow transition">
                <x-buildora-icon icon="fas fa-edit" class="mr-2"></x-buildora-icon> Bewerk
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-12 gap-6">
        @foreach($fields as $field)
            @if(!$field->isVisible('detail'))
                @continue
            @endif

            @php
                $value = old($field->name, $item->{$field->name} ?? '');
                $colSpans = $field->getColumnSpan();
                $colClasses = collect($colSpans)->map(
                    fn($cols, $breakpoint) => $breakpoint === 'default'
                        ? "col-span-{$cols}"
                        : "{$breakpoint}:col-span-{$cols}"
                )->implode(' ');
            @endphp

            @if(method_exists($field, 'shouldStartNewRow') && $field->shouldStartNewRow())
                <div class="col-span-12"></div>
            @endif

            <div class="{{ $colClasses }}">
                <div class="bg-muted text-foreground border border-border rounded-xl p-4 h-full">
                    <label class="block text-sm font-semibold text-muted-foreground mb-1">
                        {{ $field->label }}
                    </label>

                    <div class="text-foreground text-base">
                        {!! $field instanceof \Ginkelsoft\Buildora\Fields\Types\ViewField
                            ? $field->detailPage()
                            : e($item->{$field->name})
                        !!}
                    </div>
                </div>
            </div>
        @endforeach
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
