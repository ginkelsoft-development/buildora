<nav class="text-sm font-medium text-gray-500 dark:text-gray-400">
    <ul class="flex items-center space-x-2">
        <li>
            <a href="{{ route('buildora.dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">
                <x-buildora-icon icon="fa fa-home" class="text-gray-400"/> {{ __buildora('Dashboard') }}
            </a>
        </li>

        @foreach($breadcrumbs as $index => $breadcrumb)
            <li class="flex items-center">
                <x-buildora-icon icon="fa fa-chevron-right" class="text-gray-400 text-xs mx-2"/>

                @if($index < count($breadcrumbs) - 1)
                    <a href="{{ $breadcrumb['url'] }}" class="hover:text-gray-700 dark:hover:text-gray-200">
                        {{ $breadcrumb['label'] }}
                    </a>
                @else
                    <span class="text-gray-700 dark:text-gray-300 font-semibold">
                        {{ $breadcrumb['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
