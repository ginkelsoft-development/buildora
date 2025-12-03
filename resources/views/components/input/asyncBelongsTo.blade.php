@php
    $searchUrl = $field->getSearchUrl();
    $selectedId = $value;
    $selectedLabel = $field->getSelectedLabel($selectedId);
    $name = $field->name;
    $placeholder = $field->label ?? __buildora('Select...');
@endphp

<div x-data="asyncSelectDropdown()" x-init="init('{{ $searchUrl }}', '{{ $selectedId }}', '{{ $selectedLabel }}')" class="relative">
    <input type="hidden" name="{{ $name }}" x-model="selectedId" />

    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <i class="fa fa-search text-sm" style="color: var(--text-muted);"></i>
        </div>
        <input type="text"
               x-model="searchTerm"
               x-on:input.debounce.300ms="search"
               x-on:focus="open = true"
               x-on:click.away="open = false"
               class="w-full h-12 pl-12 pr-4 rounded-xl text-sm transition-all focus:outline-none"
               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
               :placeholder="'{{ $placeholder }}'"
               onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
               onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'" />
    </div>

    <ul x-show="open && results.length > 0"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute z-20 mt-2 w-full rounded-xl shadow-lg overflow-hidden max-h-60 overflow-auto"
        style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
        <template x-for="result in results" :key="result.id">
            <li class="px-4 py-2.5 cursor-pointer text-sm transition-colors"
                style="color: var(--text-primary);"
                x-text="result.text"
                @click="select(result)"
                @mouseenter="$el.style.background='var(--bg-hover)'"
                @mouseleave="$el.style.background='transparent'"></li>
        </template>
    </ul>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])

<script>
    window.asyncSelectDropdown = function () {
        return {
            searchUrl: '',
            searchTerm: '',
            results: [],
            selectedId: '',
            selectedLabel: '',
            open: false,

            init(url, selectedId, selectedLabel) {
                this.searchUrl = url;
                this.selectedId = selectedId;
                this.searchTerm = selectedLabel;
            },

            async search() {
                if (this.searchTerm.length < 2) {
                    this.results = [];
                    return;
                }

                const separator = this.searchUrl.includes('?') ? '&' : '?';
                const response = await fetch(`${this.searchUrl}${separator}q=${encodeURIComponent(this.searchTerm)}`);

                if (!response.ok) {
                    console.error('Error fetching async data:', response.status);
                    this.results = [];
                    return;
                }

                const data = await response.json();
                this.results = data;
            },

            select(result) {
                this.selectedId = result.id;
                this.searchTerm = result.text;
                this.open = false;
            }
        };
    };
</script>
