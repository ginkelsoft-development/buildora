@extends('buildora::layouts.buildora')

@section('content')

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">
        {{ class_basename($item::class) }} #{{ $item->id }}
    </h1>

    <div class="grid grid-cols-12 gap-6">
        @foreach($fields as $field)
            @if(!$field->isVisible('detail'))
                @continue
            @endif

            @php
                $colSpansRaw = $field->getColumnSpan();
                $colSpans = is_array($colSpansRaw) ? $colSpansRaw : ['default' => $colSpansRaw];
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
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 h-full">
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">
                        {{ $field->label }}
                    </label>

                    <div class="text-gray-900 dark:text-gray-100 text-base">
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

    <div class="mt-8">
        <x-buildora::button.back :model="$model"/>
    </div>

@endsection
