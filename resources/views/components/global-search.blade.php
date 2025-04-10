<div x-data="globalSearch()" class="relative w-full max-w-2xl mx-auto">
    <div class="relative">
        <input type="text"
               x-model="query"
               @input.debounce.300ms="search"
               placeholder="{{ __buildora('Search...') }}"
               class="w-full border border-gray-300 dark:border-gray-600 rounded-full pl-12 pr-5 py-2.5 shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 text-base"
        />

        <div class="absolute inset-y-0 left-4 flex items-center text-gray-400 pointer-events-none">
            <i class="fas fa-search"></i>
        </div>
    </div>

    <div x-show="results.length > 0"
         x-transition
         @click.away="results = []"
         class="absolute right-0 mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50">

        <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-64 overflow-auto">
            <template x-for="result in results" :key="result.url">
                <li>
                    <a :href="result.url"
                       class="flex justify-between items-center px-4 py-3 text-sm text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900 transition duration-150 ease-in-out text-right">
                        <span class="truncate w-full" x-text="result.label"></span>
                        <i class="fas fa-arrow-right text-gray-400 ml-2"></i>
                    </a>
                </li>
            </template>
        </ul>
    </div>
</div>

<script>
    function globalSearch() {
        return {
            query: '',
            results: [],

            async search() {
                if (this.query.length < 2) {
                    this.results = [];
                    return;
                }

                try {
                    const response = await fetch(`/buildora/global-search?q=${encodeURIComponent(this.query)}`);
                    const json = await response.json();
                    this.results = json.results ?? [];
                } catch (e) {
                    this.results = [];
                }
            }
        };
    }
</script>
