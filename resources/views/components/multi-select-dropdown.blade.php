@props(['name', 'options', 'selected' => []])

<div x-data="tagSelector(
    {{ json_encode($options) }},
    {{ json_encode(array_map('strval', array_keys($selected))) }}
)" class="relative">
    <div class="mb-2 flex flex-wrap gap-2">
        <template x-for="tag in selectedTags" :key="tag">
            <span class="bg-primary/20 text-primary text-sm px-3 py-1 rounded-full flex items-center">
                <span x-text="options[tag] || 'Onbekend'"></span>
                <button class="ml-2 cursor-pointer" @click.prevent="removeTag(tag)">&times;</button>
            </span>
        </template>
    </div>

    <input type="text"
           readonly
           class="border border-border shadow-sm p-2 rounded-lg w-full bg-muted text-foreground focus:ring-2 focus:ring-ring focus:outline-none mb-2"
           placeholder="Selecteer een waarde..."
           @click="toggleDropdown()" />

    <!-- Hidden inputs voor Laravel -->
    <template x-for="tag in selectedTags" :key="'input-'+tag">
        <input type="hidden" name="{{ $name }}[]" :value="tag"/>
    </template>


    <script>
        function tagSelector(options, selectedTags) {
            return {
                options: Object.fromEntries(Object.entries(options).map(([k, v]) => [String(k), v])),
                selectedTags: selectedTags.map(String),
                dropdownVisible: false,
                searchTerm: '',

                toggleDropdown() {
                    this.dropdownVisible = !this.dropdownVisible;
                },

                closeDropdown() {
                    this.dropdownVisible = false;
                },

                addTag(key) {
                    key = String(key);
                    if (!this.selectedTags.includes(key)) {
                        this.selectedTags.push(key);
                    }
                    this.closeDropdown();
                },

                removeTag(key) {
                    key = String(key);
                    this.selectedTags = this.selectedTags.filter(tag => tag !== key);
                },

                get filteredOptions() {
                    const search = this.searchTerm.toLowerCase();
                    return Object.entries(this.options).filter(
                        ([key, label]) =>
                            label.toLowerCase().includes(search) && !this.selectedTags.includes(key)
                    );
                },
            };
        }
    </script>

    <div x-show="dropdownVisible"
         @click.away="closeDropdown"
         class="absolute left-0 right-0 bg-base shadow-lg mt-1 rounded-lg border border-border max-h-60 overflow-auto z-10"
    >
        <div class="p-2">
            <input type="text"
                   class="border border-border p-2 rounded-lg w-full bg-muted text-foreground focus:ring-2 focus:ring-ring focus:outline-none"
                   placeholder="Zoek..." x-model="searchTerm"
            />
        </div>

        <div class="overflow-auto max-h-40">
            <template x-for="[key, label] in filteredOptions" :key="key">
                <div class="p-2 hover:bg-muted/70 cursor-pointer text-foreground"
                     @click="addTag(key)">
                    <span x-text="label"></span>
                </div>
            </template>
        </div>
    </div>
</div>
