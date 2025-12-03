<div class="relative" x-data="{ checked: {{ old($field->name, $value ?? false) ? 'true' : 'false' }} }">
    <label for="{{ $field->name }}"
           class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all"
           style="background: var(--bg-input); border: 1px solid var(--border-color);"
           :style="checked ? 'border-color: #667eea; background: rgba(102,126,234,0.05)' : ''"
           @mouseenter="if(!checked) $el.style.borderColor='#667eea'"
           @mouseleave="if(!checked) $el.style.borderColor='var(--border-color)'">
        <div class="relative">
            <input type="checkbox"
                   name="{{ $field->name }}"
                   id="{{ $field->name }}"
                   class="w-5 h-5 rounded-md border-2 appearance-none cursor-pointer transition-all"
                   style="border-color: var(--border-color); background: var(--bg-card);"
                   :style="checked ? 'border-color: #667eea; background: #667eea' : ''"
                   value="1"
                   x-model="checked">
            <svg class="absolute top-0.5 left-0.5 w-4 h-4 text-white pointer-events-none transition-opacity"
                 :class="checked ? 'opacity-100' : 'opacity-0'"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <span class="text-sm" style="color: var(--text-primary);">
            {{ $field->checkboxLabel ?? __buildora('Yes') }} / {{ __buildora('No') }}
        </span>
    </label>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
