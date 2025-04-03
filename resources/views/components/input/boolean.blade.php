@php
    $fieldId = 'field-' . Str::slug($field->name);
    $value = old($field->name, $field->value() ?? false);
    $options = $field->getOptions();
@endphp

<div class="space-y-2">
    @foreach ($options as $optionValue => $optionLabel)
        <div class="flex items-center space-x-2">
            <input type="radio"
                   name="{{ $field->name }}"
                   id="{{ $fieldId . '-' . $optionValue }}"
                   value="{{ $optionValue }}"
                   class="text-blue-500 focus:ring-2 focus:ring-blue-400 transition"
                    {{ array_key_exists($value, $options) && (string) $optionValue === (string) $value ? 'checked' : '' }}>

            <label for="{{ $fieldId . '-' . $optionValue }}" class="text-sm text-gray-700 dark:text-gray-200">
                {{ $optionLabel }}
            </label>
        </div>
    @endforeach
</div>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
