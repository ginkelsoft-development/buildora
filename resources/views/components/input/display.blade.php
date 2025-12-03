@php
    $content = $field->getContent($model ?? null);
@endphp

<div class="p-4 rounded-xl" style="background: var(--bg-muted); border: 1px solid var(--border-color);">
    <div class="text-sm" style="color: var(--text-primary);">
        {!! $content !!}
    </div>
</div>
