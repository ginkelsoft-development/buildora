<div class="relative space-y-1">
    <input type="text"
           name="{{ $field->name }}"
           id="{{ $field->name }}"
           value="{{ old($field->name, $value ?? '') }}"
           class="w-full border border-border rounded-lg p-3 text-foreground bg-muted focus:ring-2 focus:ring-ring focus:outline-none transition shadow-sm" />

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
