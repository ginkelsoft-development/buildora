<div>
    <label for="{{ $field->name }}" class="text-lg font-semibold text-foreground">
        {{ $field->label }}
    </label>

    <select name="{{ $field->name }}[]" id="{{ $field->name }}"
            class="w-full border border-border rounded-lg p-3 bg-muted text-foreground focus:ring-2 focus:ring-ring focus:outline-none transition"
            multiple>
        @foreach($field->getOptions() as $key => $option)
            <option value="{{ $key }}" {{ in_array($key, (array)$value) ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
</div>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
