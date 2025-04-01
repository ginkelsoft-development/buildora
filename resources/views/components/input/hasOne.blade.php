<div>
    <label for="{{ $field->name }}" class="text-lg font-semibold text-gray-700 dark:text-gray-300">
        {{ $field->label }}
    </label>

    <select name="{{ $field->name }}" id="{{ $field->name }}"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 transition">
        @foreach($field->getOptions() as $key => $option)
            <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
</div>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
