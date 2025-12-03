<div class="relative">
    <div class="h-12 px-4 flex items-center rounded-xl text-sm"
         style="background: var(--bg-muted); border: 1px solid var(--border-color); color: var(--text-secondary);">
        {{ $value ?: '-' }}
    </div>
    <input type="hidden" name="{{ $field->name }}" value="{{ $field->value }}">

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
