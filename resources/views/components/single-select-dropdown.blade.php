@props(['name', 'options', 'selected' => null])

@php
    $selectedKey = is_array($selected) ? array_key_first($selected) : $selected;
@endphp

<div x-data='singleSelector(@json($options), @json($selectedKey))' class="relative">
    <input type="hidden" name="{{ $name }}" :value="selectedKey">

    <input type="text"
           readonly
           :value="options[selectedKey] || ''"
           class="border border-border shadow-sm p-2 rounded-lg w-full bg-muted text-foreground focus:ring-2 focus:ring-ring focus:outline-none"
           placeholder="{{ __buildora('Select a value...') }}"
           @click="toggleDropdown()" />

    <div x-show="dropdownVisible"
         @click.away="closeDropdown"
         class="absolute left-0 right-0 bg-base shadow-lg mt-1 rounded-lg border border-border max-h-60 overflow-auto z-10"
    >
        <div class="p-2">
            <input type="text"
                   class="border border-border p-2 rounded-lg w-full bg-muted text-foreground focus:ring-2 focus:ring-ring focus:outline-none"
                   placeholder="{{ __buildora('Search...') }}" x-model="searchTerm"
            />
        </div>

        <div class="overflow-auto max-h-40">
            <template x-for="[key, label] in filteredOptions" :key="key">
                <div class="p-2 hover:bg-muted/70 cursor-pointer text-foreground"
                     @click="selectTag(key)">
                    <span x-text="label"></span>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    window.singleSelector = function (options, selectedKey) {
        return {
            options: Object.fromEntries(Object.entries(options).map(([k, v]) => [String(k), v])),
            selectedKey: selectedKey?.toString() || '',
            dropdownVisible: false,
            searchTerm: '',

            toggleDropdown() {
                this.dropdownVisible = !this.dropdownVisible;
            },

            closeDropdown() {
                this.dropdownVisible = false;
            },

            selectTag(key) {
                this.selectedKey = key.toString();
                this.closeDropdown();
            },

            get filteredOptions() {
                const search = this.searchTerm.toLowerCase();
                return Object.entries(this.options).filter(
                    ([key, label]) => label.toLowerCase().includes(search)
                );
            },
        };
    };
</script>
