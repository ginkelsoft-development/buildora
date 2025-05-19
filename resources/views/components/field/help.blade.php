@if ($field->getHelpText())
    <div x-data="{ show: false }"
         class="absolute inset-y-0 right-3 flex items-center"
         @mouseenter="show = true"
         @mouseleave="show = false">
        <button type="button"
                class="text-blue-500 hover:text-blue-700 transition focus:outline-none"
                aria-label="Help">
            <i class="fas fa-info-circle text-base"></i>
        </button>
        <div x-show="show"
             x-transition
             class="absolute right-0 top-[75%] w-64 p-3 bg-yellow-50 text-sm text-gray-800 border border-yellow-300 rounded-lg shadow z-50">
            <i class="fas fa-info-circle text-base text-gray-400"></i> {{ $field->getHelpText() }}
        </div>
    </div>
@endif
