@php
    $uniqueId = 'pwd_' . uniqid();
@endphp

<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
        <i class="fa fa-lock text-sm" style="color: var(--text-muted);"></i>
    </div>
    <input type="password"
           name="{{ $field->name }}"
           id="{{ $field->name }}"
           data-toggle="{{ $uniqueId }}"
           value=""
           placeholder="{{ $field->placeholder ?? '••••••••' }}"
           class="w-full h-12 pl-12 pr-12 rounded-xl text-sm transition-all focus:outline-none"
           style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
           onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'" />
    <button type="button"
            data-toggle-btn="{{ $uniqueId }}"
            onclick="togglePasswordVisibility('{{ $uniqueId }}')"
            class="absolute inset-y-0 right-0 flex items-center pr-4 transition-colors cursor-pointer hover:opacity-70"
            style="color: var(--text-muted);">
        <i class="fa fa-eye" data-icon="{{ $uniqueId }}"></i>
    </button>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])

<script>
    function togglePasswordVisibility(id) {
        const input = document.querySelector('[data-toggle="' + id + '"]');
        const icon = document.querySelector('[data-icon="' + id + '"]');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
