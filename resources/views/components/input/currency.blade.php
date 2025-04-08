<div class="relative">
    <div class="flex items-center gap-2">
        <span class="text-gray-500 dark:text-gray-300">€</span>
        <input
            type="number"
            step="0.01"
            min="0"
            name="{{ $field->name }}"
            id="{{ $field->name }}"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition shadow-sm"
            value="{{ old($field->name, $value ?? '') }}"
        >
    </div>

    @include('buildora::components.field.help')
</div>
@include('buildora::components.field.error', ['field' => $field])
