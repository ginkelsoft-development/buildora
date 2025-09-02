@php
    $searchUrl = $field->getSearchUrl();
    $selectedId = $value;
    $selectedLabel = $field->getSelectedLabel($selectedId);
    $name = $field->name;
    $placeholder = $field->label ?? 'Selecteer...';
@endphp

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
                    console.error('Fout bij ophalen async data:', response.status);
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

<div x-data="asyncSelectDropdown()" x-init="init('{{ $searchUrl }}', '{{ $selectedId }}', '{{ $selectedLabel }}')" class="relative">
    <input type="hidden" name="{{ $name }}" x-model="selectedId" />

    <input
            type="text"
            x-model="searchTerm"
            x-on:input.debounce.300ms="search"
            x-on:focus="open = true"
            x-on:click.away="open = false"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2"
            :placeholder="'{{ $placeholder }}'"
    />

    <ul
            x-show="open && results.length > 0"
            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
    >
        <template x-for="result in results" :key="result.id">
            <li
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    x-text="result.text"
                    @click="select(result)"
            ></li>
        </template>
    </ul>

    @include('buildora::components.field.help')
</div>

@include('buildora::components.field.error', ['field' => $field])
