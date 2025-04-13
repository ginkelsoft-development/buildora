@php
    $content = $field->getContent($model ?? null);
@endphp

<div class="form-group">
    @if ($field->getLabel())
        <label class="block font-medium text-sm text-foreground">
            {{ $field->getLabel() }}
        </label>
    @endif

    <div class="p-2 rounded bg-muted text-foreground">
        {!! $content !!}
    </div>
</div>
