@props([
    'endpoint' => request()->url() . '/datatable/json',
    'componentKey' => Str::random(8),
    'inlineEditing' => false,
    'inlineDelete' => false
])

<div x-data="dataTable({{ json_encode($endpoint) }}, {{ json_encode($inlineEditing) }}, {{ json_encode($inlineDelete) }}, {{ json_encode($componentKey) }})"
     @refresh-datatable-{{ $componentKey }}.window="fetchData()"
     class="mx-auto"
     :key="{{ json_encode($componentKey) }}">

    {{-- Toast Notification --}}
    <template x-teleport="body">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-[100] max-w-sm">
            <div class="rounded-xl shadow-lg px-5 py-4 flex items-center gap-3"
                 :class="toast.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                <div class="flex-shrink-0">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </template>
                </div>
                <span class="text-sm font-medium" x-text="toast.message"></span>
                <button @click="toast.show = false" class="flex-shrink-0 ml-2 hover:opacity-75">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </template>

    {{-- Delete Confirmation Modal --}}
    <template x-teleport="body">
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0, 0, 0, 0.5);"
             @keydown.escape.window="showDeleteModal = false">

            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="showDeleteModal = false"
                 class="w-full max-w-sm rounded-2xl shadow-xl overflow-hidden"
                 style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">

                {{-- Header --}}
                <div class="p-6">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: rgba(239, 68, 68, 0.1);">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>

                    <h3 class="text-lg font-semibold text-center mb-2" style="color: var(--text-primary);" x-text="deleteModalTitle"></h3>
                    <p class="text-sm text-center" style="color: var(--text-muted);" x-text="deleteModalMessage"></p>

                    {{-- Item info --}}
                    <div class="mt-4 p-3 rounded-lg" style="background: var(--bg-input);">
                        <template x-if="!pendingBulkDelete && pendingDeleteRow">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-md flex items-center justify-center text-xs font-bold text-red-500" style="background: rgba(239, 68, 68, 0.1);" x-text="'#' + pendingDeleteRow.id"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" style="color: var(--text-primary);" x-text="getRowDisplayName(pendingDeleteRow)"></p>
                                    <p class="text-xs truncate" style="color: var(--text-muted);" x-text="getRowSubtitle(pendingDeleteRow)"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="pendingBulkDelete">
                            <p class="text-sm text-center" style="color: var(--text-primary);">
                                <span class="font-semibold" x-text="selectedRows.length"></span> {{ __buildora('records') }} {{ __buildora('selected') }}
                            </p>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 pb-6 flex gap-3">
                    <button type="button"
                            @click="showDeleteModal = false"
                            class="flex-1 h-11 px-4 rounded-xl text-sm font-medium transition-all duration-200 hover:border-gray-400"
                            style="background: var(--bg-dropdown); border: 2px solid var(--border-color); color: var(--text-primary);">
                        {{ __buildora('cancel') }}
                    </button>
                    <button type="button"
                            @click="confirmDelete()"
                            class="flex-1 h-11 px-4 rounded-xl text-sm font-medium text-white transition-all duration-200 flex items-center justify-center gap-2 hover:bg-red-600"
                            style="background: #ef4444;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __buildora('Delete') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        {{-- Search --}}
        <div class="relative w-full max-w-md">
            <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2" style="color: var(--text-muted);"></i>
            <input type="text"
                   x-model="search"
                   @input="debouncedFetchData()"
                   placeholder="{{ __buildora('Search...') }}"
                   class="w-full pl-12 pr-4 py-3 rounded-xl transition-all duration-200 focus:outline-none"
                   style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
                   onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)';"
                   onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
        </div>

        {{-- Bulk Actions --}}
        <div class="flex items-center gap-3" x-show="selectedRows.length > 0" x-transition>
            <span class="text-sm font-medium" style="color: var(--text-muted);">
                <span x-text="selectedRows.length"></span> {{ __buildora('selected') }}
            </span>
            <select x-show="bulkActions.length > 0"
                    x-model="selectedBulkAction"
                    class="px-4 py-2.5 rounded-xl transition-all duration-200"
                    style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                <option value="">{{ __buildora('Select Action') }}</option>
                <template x-for="action in bulkActions" :key="action.url">
                    <option :value="action.url" x-text="action.label"></option>
                </template>
            </select>
            <button @click="executeBulkAction"
                    class="px-5 py-2.5 btn-primary text-white rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ __buildora('Apply') }}
            </button>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="rounded-2xl overflow-hidden shadow-sm" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <th class="py-4 px-4 text-left" style="background: var(--bg-input);">
                            <input type="checkbox"
                                   @click="toggleAllSelection($event)"
                                   class="w-4 h-4 rounded cursor-pointer"
                                   style="accent-color: #667eea;">
                        </th>
                        <template x-for="(col, index) in columns" :key="index">
                            <th @click="toggleSort(col)"
                                class="py-4 px-4 text-left text-xs font-semibold uppercase tracking-wider"
                                :class="{ 'cursor-pointer hover:bg-black/5 dark:hover:bg-white/5': col.sortable, 'cursor-default': !col.sortable }"
                                style="background: var(--bg-input); color: var(--text-muted);">
                                <div class="flex items-center gap-2">
                                    <span x-text="col.label"></span>
                                    <template x-if="col.sortable">
                                        <span class="text-xs">
                                            <i class="fa-solid fa-sort opacity-30" x-show="sortBy !== col.name"></i>
                                            <i class="fa-solid fa-sort-up" style="color: #667eea;" x-show="sortBy === col.name && sortDirection === 'asc'"></i>
                                            <i class="fa-solid fa-sort-down" style="color: #667eea;" x-show="sortBy === col.name && sortDirection === 'desc'"></i>
                                        </span>
                                    </template>
                                </div>
                            </th>
                        </template>
                        <th class="py-4 px-4 text-right text-xs font-semibold uppercase tracking-wider" style="background: var(--bg-input); color: var(--text-muted);">
                            {{ __buildora('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loading --}}
                    <tr x-show="isLoading">
                        <td colspan="100%" class="text-center py-12">
                            <div class="flex flex-col items-center gap-3">
                                <i class="fa-solid fa-spinner fa-spin text-2xl" style="color: #667eea;"></i>
                                <span class="text-sm font-medium" style="color: var(--text-muted);">{{ __buildora('Loading...') }}</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Empty State --}}
                    <tr x-show="!isLoading && data.length === 0">
                        <td colspan="100%" class="text-center py-12">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center" style="background: var(--bg-input);">
                                    <i class="fa-solid fa-inbox text-2xl" style="color: var(--text-muted);"></i>
                                </div>
                                <span class="text-sm font-medium" style="color: var(--text-muted);">{{ __buildora('No records found') }}</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Data Rows --}}
                    <template x-for="(row, rowIndex) in data" :key="rowIndex">
                        <tr class="transition-colors duration-150 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]"
                            :style="'border-bottom: 1px solid var(--border-color);'">
                            <td class="py-3 px-4" style="color: var(--text-primary);">
                                <input type="checkbox"
                                       :value="row.id"
                                       x-model="selectedRows"
                                       class="w-4 h-4 rounded cursor-pointer"
                                       style="accent-color: #667eea;">
                            </td>
                            <template x-for="(col, colIndex) in columns" :key="colIndex">
                                <td x-html="formatCell(row, col.name)" class="py-3 px-4 text-sm" style="color: var(--text-primary);"></td>
                            </template>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Inline Edit Button --}}
                                    <template x-if="inlineEditing">
                                        <button @click="openInlineEdit(row)"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
                                                style="color: var(--text-muted);"
                                                title="{{ __buildora('Edit') }}">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                    </template>

                                    {{-- Inline Delete Button --}}
                                    <template x-if="inlineDelete">
                                        <button @click="triggerInlineDelete(row)"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200 hover:bg-red-500/10"
                                                style="color: #ef4444;"
                                                title="{{ __buildora('Delete') }}">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </template>

                                    {{-- Regular Row Actions (only when not inline editing) --}}
                                    <template x-if="!inlineEditing">
                                        <template x-for="(action, actionIndex) in row.actions" :key="actionIndex">
                                            <button @click="handleAction(action, row)"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200"
                                                    :class="{
                                                        'hover:bg-black/5 dark:hover:bg-white/5': action.method === 'GET',
                                                        'hover:bg-red-500/10': action.method === 'DELETE'
                                                    }"
                                                    :style="action.method === 'DELETE' ? 'color: #ef4444;' : 'color: var(--text-muted);'"
                                                    :title="action.label">
                                                <i :class="action.icon" class="text-sm"></i>
                                            </button>
                                        </template>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
        <div class="text-sm" style="color: var(--text-muted);">
            {{ __buildora('Page') }} <span x-text="pagination.current_page" class="font-medium" style="color: var(--text-primary);"></span>
            {{ __buildora('of') }} <span x-text="pagination.last_page" class="font-medium" style="color: var(--text-primary);"></span>
            <span class="mx-2">•</span>
            <span x-text="pagination.total" class="font-medium" style="color: var(--text-primary);"></span> {{ __buildora('records') }}
        </div>
        <div class="flex flex-wrap items-center gap-4">
            {{-- Per page --}}
            <div class="flex items-center gap-2">
                <label class="text-sm" style="color: var(--text-muted);">{{ __buildora('Per page') }}:</label>
                <select x-model="pagination.per_page"
                        @change="updatePerPage"
                        class="px-3 py-2 rounded-lg text-sm transition-all duration-200"
                        style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <template x-for="option in paginationOptions" :key="option">
                        <option :value="option" x-text="option"></option>
                    </template>
                </select>
            </div>
            {{-- Navigation --}}
            <div class="flex items-center gap-2">
                <button @click="prevPage"
                        :disabled="isLoading || pagination.current_page === 1"
                        class="px-4 py-2 text-sm rounded-lg font-medium transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-black/5 dark:hover:bg-white/5"
                        style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <i class="fa-solid fa-chevron-left mr-1 text-xs"></i>
                    {{ __buildora('Previous') }}
                </button>
                <button @click="nextPage"
                        :disabled="isLoading || pagination.current_page === pagination.last_page"
                        class="px-4 py-2 text-sm rounded-lg font-medium transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-black/5 dark:hover:bg-white/5"
                        style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                    {{ __buildora('Next') }}
                    <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dataTable(customEndpoint = null, inlineEditing = false, inlineDelete = false, componentKey = null) {
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
            pagination: { current_page: 1, per_page: 25, total: 0, last_page: 1 },
            paginationOptions: [10, 25, 50, 100],
            endpoint: customEndpoint,
            inlineEditing: inlineEditing,
            inlineDelete: inlineDelete,
            componentKey: componentKey,
            debouncedFetchData: null,
            selectedRows: [],
            selectedBulkAction: '',
            bulkActions: [],
            isLoading: false,
            _initialized: false,
            showDeleteModal: false,
            deleteModalTitle: '',
            deleteModalMessage: '',
            pendingDeleteAction: null,
            pendingDeleteRow: null,
            pendingBulkDelete: false,
            toast: { show: false, message: '', type: 'success' },

            init() {
                if (this._initialized) return;
                this._initialized = true;

                this.debouncedFetchData = debounce(() => {
                    this.fetchData();
                }, 300);
                this.fetchData();
            },

            // Inline editing: trigger parent panel's openEditModal
            openInlineEdit(row) {
                if (this.inlineEditing && row.id) {
                    // Find parent panel component and call openEditModal
                    const panel = this.$el.closest('[x-data*="inlineRelationPanel"]');
                    if (panel && panel.__x) {
                        panel.__x.$data.openEditModal(row.id);
                    }
                }
            },

            // Inline delete: trigger parent panel's deleteItem
            triggerInlineDelete(row) {
                if (this.inlineDelete && row.id) {
                    const panel = this.$el.closest('[x-data*="inlineRelationPanel"]');
                    if (panel && panel.__x) {
                        panel.__x.$data.deleteItem(row.id);
                    }
                }
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
                        this.pagination = json.pagination ?? this.pagination;
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
                if (action.method === 'GET') {
                    window.location.href = action.url;
                } else if (action.method === 'DELETE') {
                    this.pendingDeleteAction = action;
                    this.pendingDeleteRow = row;
                    this.pendingBulkDelete = false;
                    this.deleteModalTitle = '{{ __buildora('Delete') }}';
                    this.deleteModalMessage = action.confirm || '{{ __buildora('confirm_delete') }}';
                    this.showDeleteModal = true;
                }
            },

            getRowDisplayName(row) {
                if (!row) return '';
                // Try common name fields
                return row.name || row.title || row.label || row.email || row.username || 'Item #' + row.id;
            },

            getRowSubtitle(row) {
                if (!row) return '';
                // Try to get a secondary field
                if (row.email && row.name) return row.email;
                if (row.description) return row.description.substring(0, 50) + (row.description.length > 50 ? '...' : '');
                if (row.created_at) return row.created_at;
                return 'ID: ' + row.id;
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            confirmDelete() {
                const self = this;

                if (this.pendingBulkDelete) {
                    const action = this.bulkActions.find(a => a.url === this.selectedBulkAction);
                    if (action) {
                        window.location.href = action.url + '?' + new URLSearchParams({ ids: this.selectedRows }).toString();
                    }
                    this.showDeleteModal = false;
                } else if (this.pendingDeleteAction) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const url = this.pendingDeleteAction.url;

                    this.showDeleteModal = false;

                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            self.showToast(data.message, 'success');
                            self.fetchData();
                        } else {
                            self.showToast(data.message || '{{ __buildora('Delete failed') }}', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        self.showToast('{{ __buildora('An error occurred') }}', 'error');
                    });
                }

                this.pendingDeleteAction = null;
                this.pendingDeleteRow = null;
                this.pendingBulkDelete = false;
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
                if (this.pagination.current_page < this.pagination.last_page) {
                    this.pagination.current_page++;
                    this.fetchData();
                }
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

                if (action.method === 'DELETE') {
                    this.pendingBulkDelete = true;
                    this.deleteModalTitle = '{{ __buildora('Delete') }}';
                    this.deleteModalMessage = '{{ __buildora('Are you sure you want to delete the selected records?') }}';
                    this.showDeleteModal = true;
                    return;
                }

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
