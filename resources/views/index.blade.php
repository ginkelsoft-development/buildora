@extends('buildora::layouts.buildora')

@section('content')
<div x-data="pageActionsHandler()">

    <x-buildora::widgets :resource="$resource" visibility="index" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="p-4 rounded-xl mb-6 flex items-center justify-between"
             style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%); border: 1px solid rgba(34, 197, 94, 0.3);">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(34, 197, 94, 0.2);">
                    <i class="fa-solid fa-check text-green-500"></i>
                </div>
                <span class="font-medium" style="color: var(--text-primary);">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="color: var(--text-muted);">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                {{ $resource->title() }}
            </h1>
        </div>

        <div class="flex items-center gap-3">
            {{-- Page Actions --}}
            @if(isset($pageActions) && count($pageActions) > 0)
                @foreach($pageActions as $action)
                    <button
                        @click="executePageAction({{ json_encode($action->toArray()) }})"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all duration-200
                            @if($action->getStyle() === 'primary') btn-primary text-white
                            @elseif($action->getStyle() === 'secondary') hover:bg-black/5 dark:hover:bg-white/5
                            @elseif($action->getStyle() === 'success') text-white
                            @elseif($action->getStyle() === 'danger') text-white
                            @elseif($action->getStyle() === 'warning') text-white
                            @endif"
                        @if($action->getStyle() === 'secondary') style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);"
                        @elseif($action->getStyle() === 'success') style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);"
                        @elseif($action->getStyle() === 'danger') style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"
                        @elseif($action->getStyle() === 'warning') style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"
                        @endif>
                        <i class="{{ $action->getIcon() }}"></i>
                        {{ $action->getLabel() }}
                    </button>
                @endforeach
            @endif

            {{-- Create Button --}}
            @can(Str::kebab($model) . '.create')
                <a href="{{ route('buildora.create', ['resource' => $model]) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 btn-primary text-white font-medium rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fa-solid fa-plus"></i>
                    {{ __buildora('create') }} {{ $resource->title() }}
                </a>
            @endcan
        </div>
    </div>

    <x-buildora::datatable :columns="$columns"/>

    {{-- Status Modal --}}
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showModal = false">
        {{-- Backdrop --}}
        <div class="fixed inset-0 transition-opacity" style="background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);"
             @click="showModal = false"></div>

        {{-- Modal Content --}}
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl p-6"
                 style="background: var(--bg-dropdown); border: 1px solid var(--border-color);"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold" style="color: var(--text-primary);">
                        <span x-text="modalTitle"></span>
                    </h3>
                    <button @click="showModal = false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-black/5 dark:hover:bg-white/5"
                            style="color: var(--text-muted);">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                {{-- Status --}}
                <div class="mb-6">
                    {{-- Loading --}}
                    <div x-show="isLoading" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(102, 126, 234, 0.1);">
                            <i class="fa-solid fa-spinner fa-spin" style="color: #667eea;"></i>
                        </div>
                        <span class="font-medium" style="color: var(--text-primary);">{{ __buildora('Processing...') }}</span>
                    </div>

                    {{-- Success --}}
                    <div x-show="!isLoading && modalSuccess" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(34, 197, 94, 0.1);">
                            <i class="fa-solid fa-check text-green-500"></i>
                        </div>
                        <span class="font-semibold" style="color: var(--text-primary);" x-text="modalMessage"></span>
                    </div>

                    {{-- Error --}}
                    <div x-show="!isLoading && !modalSuccess && modalMessage" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(239, 68, 68, 0.1);">
                            <i class="fa-solid fa-exclamation-circle text-red-500"></i>
                        </div>
                        <span class="font-semibold" style="color: var(--text-primary);" x-text="modalMessage"></span>
                    </div>
                </div>

                {{-- Details --}}
                <div x-show="modalDetails" class="mb-6 p-4 rounded-xl" style="background: var(--bg-input); border: 1px solid var(--border-color);">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-500" x-text="modalDetails.registered || 0"></div>
                            <div class="text-sm" style="color: var(--text-muted);">{{ __buildora('Registered') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" style="color: var(--text-muted);" x-text="modalDetails.skipped || 0"></div>
                            <div class="text-sm" style="color: var(--text-muted);">{{ __buildora('Skipped') }}</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" style="color: #667eea;" x-text="modalDetails.total || 0"></div>
                            <div class="text-sm" style="color: var(--text-muted);">{{ __buildora('Total') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Output Log --}}
                <div x-show="modalOutput.length > 0" class="mb-6">
                    <h4 class="text-sm font-semibold mb-2" style="color: var(--text-secondary);">Output:</h4>
                    <div class="p-4 rounded-xl font-mono text-sm max-h-64 overflow-y-auto" style="background: #0f0f1a; color: #22c55e;">
                        <template x-for="(line, index) in modalOutput" :key="index">
                            <div x-text="line" class="mb-1"></div>
                        </template>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false"
                            class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 hover:bg-black/5 dark:hover:bg-white/5"
                            style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                        {{ __buildora('Close') }}
                    </button>
                    <button x-show="!isLoading && modalSuccess"
                            @click="window.location.reload()"
                            class="px-5 py-2.5 btn-primary text-white rounded-xl font-medium transition-all duration-200 hover:shadow-lg">
                        {{ __buildora('Refresh') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function pageActionsHandler() {
    return {
        showModal: false,
        isLoading: false,
        modalTitle: '',
        modalMessage: '',
        modalSuccess: false,
        modalOutput: [],
        modalDetails: null,

        executePageAction(action) {
            // Confirmation if needed
            if (action.confirmMessage && !confirm(action.confirmMessage)) {
                return;
            }

            this.showModal = true;
            this.isLoading = true;
            this.modalTitle = action.label;
            this.modalMessage = '';
            this.modalSuccess = false;
            this.modalOutput = [];
            this.modalDetails = null;

            // Build URL
            const url = action.route.startsWith('http')
                ? action.route
                : '{{ config("buildora.route_prefix", "buildora") }}/' + action.route.replace('buildora.', '').replace(/\./g, '/');

            // Execute action
            fetch(url, {
                method: action.method || 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: action.method !== 'GET' ? JSON.stringify(action.parameters || {}) : null
            })
            .then(response => response.json())
            .then(data => {
                this.isLoading = false;
                this.modalSuccess = data.success || false;
                this.modalMessage = data.message || 'Actie uitgevoerd';
                this.modalOutput = data.output || [];
                this.modalDetails = data.details || null;
            })
            .catch(error => {
                this.isLoading = false;
                this.modalSuccess = false;
                this.modalMessage = 'Er is een fout opgetreden: ' + error.message;
            });
        }
    }
}
</script>
@endsection
