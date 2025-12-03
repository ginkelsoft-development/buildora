@php
    $fieldId = 'field-' . Str::slug($field->name);
    $value = old($field->name, $value ?? false);
    $options = $field->getOptions();
@endphp

<div class="space-y-2">
    @foreach ($options as $optionValue => $optionLabel)
        <label for="{{ $fieldId . '-' . $optionValue }}"
               class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all group"
               style="background: var(--bg-input); border: 1px solid var(--border-color);"
               onmouseenter="this.style.borderColor='#667eea'"
               onmouseleave="this.style.borderColor='var(--border-color)'">
            <div class="relative flex items-center justify-center">
                <input type="radio"
                       name="{{ $field->name }}"
                       id="{{ $fieldId . '-' . $optionValue }}"
                       value="{{ $optionValue }}"
                       class="w-5 h-5 rounded-full border-2 appearance-none cursor-pointer transition-all"
                       style="border-color: var(--border-color); background: var(--bg-card);"
                       {{ array_key_exists((string)(int)$value, $options) && (string) $optionValue === (string)(int) $value ? 'checked' : '' }}
                       onchange="this.style.borderColor='#667eea'; this.style.background='#667eea'">
                <div class="absolute w-2 h-2 rounded-full bg-white opacity-0 pointer-events-none transition-opacity"
                     style="{{ array_key_exists((string)(int)$value, $options) && (string) $optionValue === (string)(int) $value ? 'opacity: 1' : '' }}"></div>
            </div>
            <span class="text-sm" style="color: var(--text-primary);">{{ $optionLabel }}</span>
        </label>
    @endforeach
</div>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
