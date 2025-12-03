@props(['field', 'value'])

<div class="rounded-xl overflow-hidden" style="background: var(--bg-input); border: 1px solid var(--border-color);">
    <select name="{{ $field->name }}[]"
            id="{{ $field->name }}"
            class="w-full p-3 text-sm focus:outline-none"
            style="background: transparent; color: var(--text-primary);"
            multiple
            size="5">
        @foreach($field->getOptions() as $key => $option)
            <option value="{{ $key }}"
                    class="py-2 px-3"
                    {{ in_array($key, (array)$value) ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
</div>

<p class="mt-2 text-xs" style="color: var(--text-muted);">
    <i class="fa fa-info-circle mr-1"></i>
    {{ __buildora('Hold Ctrl/Cmd to select multiple items') }}
</p>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
