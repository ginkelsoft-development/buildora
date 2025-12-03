<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
        <span class="text-sm font-medium" style="color: var(--text-muted);">€</span>
    </div>
    <input type="number"
           step="0.01"
           min="0"
           name="{{ $field->name }}"
           id="{{ $field->name }}"
           value="{{ old($field->name, $value ?? '') }}"
           placeholder="{{ $field->placeholder ?? '0.00' }}"
           class="w-full h-12 pl-10 pr-4 rounded-xl text-sm transition-all focus:outline-none"
           style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
           onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
           {{ $field->readonly ? 'readonly' : '' }} />

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
