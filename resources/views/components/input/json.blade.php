<div class="relative">
    <div class="absolute top-3 left-4 pointer-events-none">
        <i class="fa fa-code text-sm" style="color: var(--text-muted);"></i>
    </div>
    <textarea name="{{ $field->name }}"
              id="{{ $field->name }}"
              rows="6"
              class="w-full pl-11 pr-4 py-3 rounded-xl text-sm font-mono transition-all focus:outline-none"
              style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
              onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
              onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
              placeholder="{ }">{{ old($field->name, $value ?? '') }}</textarea>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
