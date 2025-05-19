@php
    use Illuminate\Support\Str;

    $fieldId = 'field-' . Str::slug($field->name);
    $field->format('Y-m-d');
    $value = old($field->name, $field->formattedValue() ?? '');
@endphp

<div class="form-group">
    <input type="date"
           name="{{ $field->name }}"
           id="{{ $fieldId }}"
           class="w-full border border-border rounded-lg p-3 bg-input text-foreground focus:ring-2 focus:ring-primary focus:outline-none transition shadow-sm"
           value="{{ $value }}">

    @include('buildora::components.field.help')
    @include('buildora::components.field.error', ['field' => $field])
</div>
