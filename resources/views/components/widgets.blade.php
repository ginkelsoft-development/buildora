<div class="grid grid-cols-6 gap-6 mb-8">
    @foreach($resource->defineWidgets() as $widget)
        @php
            $colSpan = $widget->getColSpan();

            $colClasses = collect($colSpan)->map(
                fn($cols, $breakpoint) => $breakpoint === 'default'
                    ? "col-span-{$cols}"
                    : "{$breakpoint}:col-span-{$cols}"
            )->implode(' ');
        @endphp

        @if(in_array($visibility, $widget->pageVisibility()))
            <div class="{{ $colClasses }} h-full">
                {!! $widget
                    ->setResource($resource)
                    ->setModel($model ?? null)
                    ->render() !!}
            </div>
        @endif
    @endforeach
</div>
