<div class="relative">
    <div class="flex items-center gap-3">
        <input type="checkbox" name="{{ $field->name }}" id="{{ $field->name }}"
               class="h-5 w-5 text-blue-500 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-400 transition"
               value="1" {{ old($field->name, $value ?? false) ? 'checked' : '' }}>
        <span class="text-gray-600 dark:text-gray-300">Yes / No</span>
    </div>

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])
