@php
    use Illuminate\Support\Str;

    $fieldId = 'field-' . Str::slug($field->name);
    $field->format('Y-m-d');
    $value = old($field->name, $field->formattedValue() ?? '');
@endphp

<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
        <i class="fa fa-calendar text-sm" style="color: var(--text-muted);"></i>
    </div>
    <input type="date"
           name="{{ $field->name }}"
           id="{{ $fieldId }}"
           value="{{ $value }}"
           class="w-full h-12 pl-12 pr-4 rounded-xl text-sm transition-all focus:outline-none"
           style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
           onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'" />

    @include('buildora::components.field.help')
    @include('buildora::components.field.error', ['field' => $field])
</div>
