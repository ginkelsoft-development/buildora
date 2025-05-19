@php
    $fieldId = 'field-' . Str::slug($field->name);
    $value = old($field->name, $value ?? false);
    $options = $field->getOptions();
@endphp

<div class="space-y-2">
    @foreach ($options as $optionValue => $optionLabel)
        <div class="flex items-center space-x-2">
            <input type="radio"
                   name="{{ $field->name }}"
                   id="{{ $fieldId . '-' . $optionValue }}"
                   value="{{ $optionValue }}"
                   class="text-primary focus:ring-2 focus:ring-primary border-border transition"
                    {{ array_key_exists((string)(int)$value, $options) && (string) $optionValue === (string)(int) $value ? 'checked' : '' }}>
            <label for="{{ $fieldId . '-' . $optionValue }}" class="text-sm text-foreground">
                {{ $optionLabel }}
            </label>
        </div>
    @endforeach
</div>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
