@props(['field', 'value'])

<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
        <i class="fa fa-link text-sm" style="color: var(--text-muted);"></i>
    </div>
    <select name="{{ $field->name }}"
            id="{{ $field->name }}"
            class="w-full h-12 pl-12 pr-4 rounded-xl text-sm appearance-none cursor-pointer transition-all focus:outline-none"
            style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
            onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
            onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'">
        <option value="">{{ __buildora('Select...') }}</option>
        @foreach($field->getOptions() as $key => $option)
            <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
        <i class="fa fa-chevron-down text-xs" style="color: var(--text-muted);"></i>
    </div>
</div>

@include('buildora::components.field.help')
@include('buildora::components.field.error', ['field' => $field])
