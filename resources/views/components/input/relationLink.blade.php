{{-- RelationLinkField is display-only and should not appear in forms --}}
{{-- This component is a fallback in case visibility settings are overridden --}}
@if(isset($field) && isset($model))
    <div class="p-3 rounded-lg" style="background: var(--bg-muted); border: 1px solid var(--border-color);">
        <span style="color: var(--text-primary);">
            {!! $field->getDisplayValue($model) !!}
        </span>
    </div>
@endif
