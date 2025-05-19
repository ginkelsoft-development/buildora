<div class="relative">
    <div class="flex items-center gap-2">
        <span class="text-muted-foreground">€</span>
        <input
                type="number"
                step="0.01"
                min="0"
                name="{{ $field->name }}"
                id="{{ $field->name }}"
                class="w-full border border-border rounded-lg p-3 bg-input text-foreground focus:ring-2 focus:ring-primary focus:outline-none transition shadow-sm"
                value="{{ old($field->name, $value ?? '') }}"
        >
    </div>

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])
