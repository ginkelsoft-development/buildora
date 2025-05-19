<div class="relative">
    <div class="flex items-center gap-3">
        <input type="checkbox" name="{{ $field->name }}" id="{{ $field->name }}"
               class="h-5 w-5 text-primary border-border rounded-lg focus:ring-primary transition"
               value="1" {{ old($field->name, $value ?? false) ? 'checked' : '' }}>
        <span class="text-muted-foreground">Yes / No</span>
    </div>

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])
