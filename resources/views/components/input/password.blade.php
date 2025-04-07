<input type="password" name="{{ $field->name }}" id="{{ $field->name }}" value=""
       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:bg-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition shadow-sm">

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
