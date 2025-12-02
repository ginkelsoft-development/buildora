@props([
    'endpoint' => request()->url() . '/datatable/json',
    'componentKey' => Str::random(8)
])

<div x-data="dataTable({{ json_encode($endpoint) }})" class="mx-auto" :key="{{ json_encode($componentKey) }}">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <!-- 🔎 Zoekbalk -->
        <div class="relative w-full max-w-sm">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-muted-foreground"></i>
            <input type="text"
                   x-model="search"
                   @input="debouncedFetchData()"
                   placeholder="{{ __buildora('Search...') }}"
                   class="w-full pl-12 pr-4 py-2 border border-border rounded-full shadow-sm focus:ring-2 focus:ring-ring focus:outline-none focus:border-ring bg-white dark:bg-white text-foreground transition">
        </div>

        <!-- ⚡ Bulk Actions -->
        <div class="flex space-x-3 items-center" x-show="selectedRows.length > 0">
            <select x-show="bulkActions.length > 0"
                    x-model="selectedBulkAction"
                    class="px-4 py-2 border border-border rounded-lg shadow-sm bg-base text-foreground hover:bg-muted transition">
                <option value="">{{ __buildora('Select Action') }}</option>
                <template x-for="action in bulkActions" :key="action.url">
                    <option :value="action.url" x-text="action.label"></option>
                </template>
            </select>

            <button @click="executeBulkAction"
                    class="px-5 py-2 bg-primary text-primary-foreground rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition">
                {{ __buildora('Apply') }}
            </button>
        </div>
    </div>

    <div class="overflow-y-auto rounded-xl">
        <table class="min-w-full bg-base border border-border rounded-lg">
            <thead class="bg-muted text-foreground text-left">
            <tr>
                <th class="py-3 px-4 bg-muted">
                    <input type="checkbox" @click="toggleAllSelection($event)">
                </th>
                <template x-for="(col, index) in columns" :key="index">
                    <th @click="toggleSort(col)"
                        class="py-3 px-4 bg-muted"
                        :class="{ 'cursor-pointer': col.sortable, 'cursor-default': !col.sortable }">
                        <div class="flex justify-between items-center w-full">
                            <span x-text="col.label"></span>
                            <span class="ml-2">
                                <template x-if="col.sortable">
                                    <span>
                                        <i class="fas fa-sort text-muted-foreground"
                                           x-show="sortBy !== col.name"></i>
                                        <i class="fas fa-sort-up text-muted-foreground"
                                           x-show="sortBy === col.name && sortDirection === 'asc'"></i>
                                        <i class="fas fa-sort-down text-muted-foreground"
                                           x-show="sortBy === col.name && sortDirection === 'desc'"></i>
                                    </span>
                                </template>
                            </span>
                        </div>
                    </th>
                </template>
                <th class="py-3 px-4 bg-muted">
                    {{ __buildora('Actions') }}
                </th>
            </tr>
            </thead>
            <tbody>
            <tr x-show="isLoading">
                <td colspan="100%" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i> {{ __buildora('Loading...') }}
                </td>
            </tr>
            <template x-for="(row, rowIndex) in data" :key="rowIndex">
                <tr class="border-b border-border hover:bg-muted transition odd:bg-base even:bg-muted dark:odd:bg-base dark:even:bg-muted/70" :class="rowIndex % 2 === 0 ? 'bg-base' : 'bg-muted'" >
                    <td class="py-2 px-4 text-foreground">
                        <input type="checkbox" :value="row.id" x-model="selectedRows">
                    </td>
                    <template x-for="(col, colIndex) in columns" :key="colIndex">
                        <td x-html="formatCell(row, col.name)" class="py-2 px-4 text-foreground"></td>
                    </template>
                    <td class="py-2 px-4 text-right text-foreground">
                        <template x-for="(action, actionIndex) in row.actions" :key="actionIndex">
                            <button @click="handleAction(action, row)"
                                    class="px-2 py-1 mx-1 text-white rounded"
                                    :class="{
                            'bg-slate-400 hover:bg-slate-600': action.method === 'GET',
                            'bg-red-500 hover:bg-red-600': action.method === 'DELETE'
                        }">
                                <i :class="action.icon"></i>
                            </button>
                        </template>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>

    <!-- 📄 Paginering -->
    <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
        <div class="text-sm text-gray-600">
            {{ __buildora('Page') }} <span x-text="pagination.current_page"></span> {{ __buildora('From') }} <span x-text="displayLastPage()"></span>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <!-- Per pagina -->
            <div class="flex items-center space-x-2">
                <label for="perPage" class="text-sm text-gray-500">{{ __buildora('Per page') }}:</label>
                <select id="perPage"
                        x-model="pagination.per_page"
                        @change="updatePerPage"
                        class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm bg-white hover:bg-gray-100 transition">
                    <template x-for="option in paginationOptions" :key="option">
                        <option :value="option" x-text="option"></option>
                    </template>
                </select>
            </div>
            <!-- Knoppen -->
            <div class="flex items-center space-x-2">
                <button @click="prevPage"
                        :disabled="isLoading || pagination.current_page === 1"
                        class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    {{ __buildora('Previous') }}
                </button>
                <button @click="nextPage"
                        :disabled="isLoading || !pagination.has_more"
                        class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    {{ __buildora('Next') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dataTable(customEndpoint = null) {
        function debounce(func, wait, immediate) {
            let timeout;
            return function () {
                const context = this, args = arguments;
                const later = function () {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }

        return {
            search: '',
            data: [],
            columns: [],
            sortBy: '',
            sortDirection: 'asc',
            pagination: { current_page: 1, per_page: 25, total: null, last_page: 1, has_more: false },
            paginationOptions: [10, 25, 50, 100, 250], // <- ✅ init waarde toegevoegd
            endpoint: customEndpoint,
            debouncedFetchData: null,
            selectedRows: [],
            selectedBulkAction: '',
            bulkActions: [],
            isLoading: false,
            _initialized: false,

            init() {
                if (this._initialized) return;
                this._initialized = true;

                this.debouncedFetchData = debounce(() => {
                    this.fetchData();
                }, 300);
                this.fetchData();
            },

            fetchData() {
                this.isLoading = true;
                const params = new URLSearchParams({
                    search: this.search,
                    per_page: this.pagination.per_page,
                    sortBy: this.sortBy,
                    sortDirection: this.sortDirection,
                    page: this.pagination.current_page
                });

                fetch(`${this.endpoint}?${params.toString()}`)
                    .then(response => response.json())
                    .then(json => {
                        this.data = json.data ?? [];
                        this.columns = json.columns ?? [];
                        this.pagination = Object.assign({}, this.pagination, json.pagination ?? {});
                        if (typeof this.pagination.has_more === 'undefined') {
                            this.pagination.has_more = this.pagination.current_page < this.pagination.last_page;
                        }
                        this.paginationOptions = json.pagination_options ?? this.paginationOptions;
                        this.bulkActions = json.bulk_actions ?? [];
                    })
                    .catch(error => console.error('Error fetching data:', error))
                    .finally(() => this.isLoading = false);
            },

            formatCell(row, column) {
                if (!row || !column) return '';
                let cell = row[column];
                if (Array.isArray(cell)) return cell.join(', ');
                if (typeof cell === 'object' && cell !== null) return Object.values(cell).join(', ');
                return cell ?? '-';
            },

            toggleSort(column) {
                if (!column.sortable) return;
                if (this.sortBy === column.name) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = column.name;
                    this.sortDirection = 'asc';
                }
                this.fetchData();
            },

            handleAction(action, row) {
                if (action.confirm && !confirm(action.confirm)) return;

                if (action.method === 'GET') {
                    window.location.href = action.url;
                } else if (action.method === 'DELETE') {
                    // ✅ Veiliger: check of meta bestaat en haal token op
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    console.debug(row)
                    const url = `${action.url}/${row.id}`;

                    fetch(action.url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    }).then(response => {
                        if (response.ok) {
                            this.fetchData();
                        }
                    });
                }
            },

            updatePerPage() {
                this.pagination.current_page = 1;
                this.fetchData();
            },

            prevPage() {
                if (this.pagination.current_page > 1) {
                    this.pagination.current_page--;
                    this.fetchData();
                }
            },

            nextPage() {
                if (this.pagination.has_more) {
                    this.pagination.current_page++;
                    this.fetchData();
                }
            },

            displayLastPage() {
                if (this.pagination.total !== null && typeof this.pagination.total !== 'undefined') {
                    return this.pagination.last_page;
                }

                return this.pagination.has_more ? '...' : this.pagination.current_page;
            },

            executeBulkAction() {
                if (!this.selectedBulkAction) {
                    return;
                }
                if (this.selectedRows.length === 0) {
                    return;
                }

                const action = this.bulkActions.find(a => a.url === this.selectedBulkAction);
                if (!action) return;
                if (action.method === 'DELETE' && !confirm('{{ __buildora('Are you sure you want to delete the selected records?') }}')) return;

                window.location.href = action.url + '?' + new URLSearchParams({ ids: this.selectedRows }).toString();
            },

            toggleAllSelection(event) {
                if (event.target.checked) {
                    this.selectedRows = this.data.map(row => row.id);
                } else {
                    this.selectedRows = [];
                }
                this.$nextTick(() => {
                    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                        checkbox.checked = event.target.checked;
                    });
                });
            },
        };
    }
</script>
