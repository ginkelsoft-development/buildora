@php
    use Illuminate\Support\Str;

    $fieldId = 'field-' . Str::slug($field->name);
    $fieldName = $field->name;
    $selectedKey = is_array($value) ? array_key_first($value) : $value;
    $options = $field->getOptions();
@endphp

<div class="relative">
    <div class="form-group">
        <div x-data='singleSelector(@json($options), @json($selectedKey))' class="relative">
            <input type="hidden" name="{{ $fieldName }}" :value="selectedKey">

            <input type="text"
                   readonly
                   :value="options[selectedKey] || ''"
                   id="{{ $fieldId }}"
                   class="border shadow-sm border-gray-300 dark:border-gray-700 p-2 rounded-lg w-full bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="Selecteer een waarde..."
                   @click="toggleDropdown()"/>

            <div x-show="dropdownVisible"
                 @click.away="closeDropdown"
                 class="absolute left-0 right-0 bg-white dark:bg-gray-900 shadow-lg mt-1 rounded-lg border border-gray-300 dark:border-gray-700 max-h-60 overflow-auto z-10"
            >
                <div class="p-2">
                    <input type="text"
                           class="border border-gray-300 dark:border-gray-700 p-2 rounded-lg w-full bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                           placeholder="Zoek..." x-model="searchTerm"
                    />
                </div>

                <div class="overflow-auto max-h-40">

                    <template x-for="[key, label] in filteredOptions" :key="key">
                        <div class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer"
                             @click="selectTag(key)">
                            <span x-text="label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        @include('buildora::components.field.help')
    </div>
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
