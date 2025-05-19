<div class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200 px-4 py-2 rounded-lg">
    {{ $value ?: '-' }}
</div>
<input type="hidden" name="{{ $field->name }}" value="{{ $field->value }}">
@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
