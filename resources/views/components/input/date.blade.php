@php
    use Illuminate\Support\Str;

    $fieldId = 'field-' . Str::slug($field->name);
    $value = old($field->name, $field->value() ?? '');
@endphp

<div class="form-group">

    <input type="date"
           name="{{ $field->name }}"
           id="{{ $fieldId }}"
           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition"
           value="{{ $value }}">

    @include('buildora::components.field.help')
    @include('buildora::components.field.error', ['field' => $field])
</div>
