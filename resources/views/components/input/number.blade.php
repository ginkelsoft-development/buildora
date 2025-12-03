<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
        <i class="fa fa-hashtag text-sm" style="color: var(--text-muted);"></i>
    </div>
    <input type="number"
           name="{{ $field->name }}"
           id="{{ $field->name }}"
           value="{{ old($field->name, $value ?? '') }}"
           placeholder="{{ $field->placeholder ?? '' }}"
           class="w-full h-12 pl-12 pr-4 rounded-xl text-sm transition-all focus:outline-none"
           style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
           onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
           {{ $field->readonly ? 'readonly' : '' }} />

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
