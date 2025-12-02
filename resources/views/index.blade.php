@extends('buildora::layouts.buildora')

@section('content')
<div x-data="pageActionsHandler()">

    <x-buildora::widgets :resource="$resource" visibility="index" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
             class="bg-primary text-primary-foreground p-4 rounded-lg shadow-md mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-buildora-icon icon="fa fa-check-circle" class="text-primary-foreground text-xl" />
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-primary-foreground ml-4 hover:opacity-75">
                <x-buildora-icon icon="fa fa-times" />
            </button>
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-6 text-foreground">
        {{ $resource->title() }}
    </h1>

    <div class="flex justify-between items-center mb-4">
        <!-- Page Actions (links) -->
        @if(isset($pageActions) && count($pageActions) > 0)
            <div class="flex gap-2">
                @foreach($pageActions as $action)
                    <button
                        @click="executePageAction({{ json_encode($action->toArray()) }})"
                        class="inline-flex items-center px-4 py-2
                            @if($action->getStyle() === 'primary') bg-primary text-primary-foreground
                            @elseif($action->getStyle() === 'secondary') bg-secondary text-secondary-foreground
                            @elseif($action->getStyle() === 'success') bg-green-600 text-white
                            @elseif($action->getStyle() === 'danger') bg-red-600 text-white
                            @elseif($action->getStyle() === 'warning') bg-yellow-600 text-white
                            @endif
                            font-semibold rounded-lg shadow-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition duration-200 ease-in-out">
                        <x-buildora-icon :icon="$action->getIcon()" class="mr-2" />
                        {{ $action->getLabel() }}
                    </button>
                @endforeach
            </div>
        @else
            <div></div>
        @endif

        <!-- Create Button (rechts) -->
        @can(Str::kebab($model) . '.create')
            <a href="{{ route('buildora.create', ['resource' => $model]) }}"
               class="inline-flex items-center px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-lg shadow-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition duration-200 ease-in-out">
                <x-buildora-icon icon="fa fa-plus" class="mr-2" />
                {{ __buildora('create') }} {{ $resource->title() }}
            </a>
        @endcan
    </div>

    <x-buildora::datatable :columns="$columns"/>

    <!-- Status Modal -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showModal = false">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             @click="showModal = false"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6"
                 @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <span x-text="modalTitle"></span>
                    </h3>
                    <button @click="showModal = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-buildora-icon icon="fa fa-times" class="text-xl" />
                    </button>
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <!-- Loading -->
                    <div x-show="isLoading" class="flex items-center gap-3 text-blue-600">
                        <div class="animate-spin">
                            <x-buildora-icon icon="fa fa-spinner" class="text-2xl" />
                        </div>
                        <span class="font-medium">Bezig met synchroniseren...</span>
                    </div>

                    <!-- Success -->
                    <div x-show="!isLoading && modalSuccess"
                         class="flex items-center gap-3 text-green-600 dark:text-green-400">
                        <x-buildora-icon icon="fa fa-check-circle" class="text-2xl" />
                        <span class="font-semibold" x-text="modalMessage"></span>
                    </div>

                    <!-- Error -->
                    <div x-show="!isLoading && !modalSuccess && modalMessage"
                         class="flex items-center gap-3 text-red-600 dark:text-red-400">
                        <x-buildora-icon icon="fa fa-exclamation-circle" class="text-2xl" />
                        <span class="font-semibold" x-text="modalMessage"></span>
                    </div>
                </div>

                <!-- Details -->
                <div x-show="modalDetails" class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400"
                                 x-text="modalDetails.registered || 0"></div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Geregistreerd</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400"
                                 x-text="modalDetails.skipped || 0"></div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Overgeslagen</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                                 x-text="modalDetails.total || 0"></div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Totaal</div>
                        </div>
                    </div>
                </div>

                <!-- Output Log -->
                <div x-show="modalOutput.length > 0" class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Output:</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm max-h-64 overflow-y-auto">
                        <template x-for="(line, index) in modalOutput" :key="index">
                            <div x-text="line" class="mb-1"></div>
                        </template>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Sluiten
                    </button>
                    <button x-show="!isLoading && modalSuccess"
                            @click="window.location.reload()"
                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:opacity-90 transition">
                        Vernieuwen
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
