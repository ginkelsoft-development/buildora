@php
    use Illuminate\Support\Str;

    $fieldId = 'field-' . Str::slug($field->name);
    $fieldName = $field->name;
    $selectedKey = is_array($value) ? array_key_first($value) : $value;
    $options = $field->getOptions();
@endphp

<div class="relative">
    <div x-data='singleSelector(@json($options), @json($selectedKey))' class="relative">
        <input type="hidden" name="{{ $fieldName }}" :value="selectedKey">

        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <i class="fa fa-chevron-down text-xs transition-transform" style="color: var(--text-muted);"
               :class="dropdownVisible ? 'rotate-180' : ''"></i>
        </div>

        <input type="text"
               readonly
               :value="options[selectedKey] || ''"
               id="{{ $fieldId }}"
               class="w-full h-12 pl-12 pr-4 rounded-xl text-sm cursor-pointer transition-all focus:outline-none"
               style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
               placeholder="{{ __buildora('Select a value...') }}"
               @click="toggleDropdown()"
               @focus="$el.style.borderColor='#667eea'; $el.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
               @blur="$el.style.borderColor='var(--border-color)'; $el.style.boxShadow='none'" />

        <div x-show="dropdownVisible"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="closeDropdown"
             class="absolute left-0 right-0 mt-2 rounded-xl shadow-xl overflow-hidden z-50"
             style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            <div class="p-3" style="border-bottom: 1px solid var(--border-color);">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="fa fa-search text-sm" style="color: var(--text-muted);"></i>
                    </div>
                    <input type="text"
                           class="w-full h-10 pl-12 pr-4 rounded-lg text-sm focus:outline-none"
                           style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
                           placeholder="{{ __buildora('Search...') }}"
                           x-model="searchTerm"
                           @focus="$el.style.borderColor='#667eea'; $el.style.boxShadow='0 0 0 3px rgba(102,126,234,0.1)'"
                           @blur="$el.style.borderColor='var(--border-color)'; $el.style.boxShadow='none'" />
                </div>
            </div>

            <div class="overflow-auto max-h-64 py-2">
                <template x-for="[key, label] in filteredOptions" :key="key">
                    <div class="px-4 py-3 cursor-pointer text-sm transition-colors"
                         style="color: var(--text-primary);"
                         :style="selectedKey === key ? 'background: rgba(102,126,234,0.1)' : ''"
                         @click="selectTag(key)"
                         @mouseenter="$el.style.background = 'var(--bg-hover)'"
                         @mouseleave="$el.style.background = selectedKey === key ? 'rgba(102,126,234,0.1)' : 'transparent'">
                        <span x-text="label"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @include('buildora::components.field.help')
    @include('buildora::components.field.error', ['field' => $field])
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
