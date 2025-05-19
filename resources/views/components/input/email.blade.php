<div class="relative">
    <input
            type="email"
            name="{{ $field->name }}"
            id="{{ $field->name }}"
            class="w-full border border-border rounded-lg p-3 pr-12 bg-input text-foreground focus:ring-2 focus:ring-ring transition shadow-sm"
            value="{{ old($field->name, $value ?? '') }}"
    >

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
