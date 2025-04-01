<div class="relative">
    <input type="checkbox" name="{{ $field->name }}" id="{{ $field->name }}"
           class="h-5 w-5 text-blue-500 border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-400 transition"
           value="1" {{ old($field->name, $value ?? false) ? 'checked' : '' }}>
    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])
