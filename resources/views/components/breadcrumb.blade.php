<nav class="text-sm font-medium text-muted-foreground">
    <ul class="flex items-center space-x-2">
        <li>
            <a href="{{ route('buildora.dashboard') }}" class="hover:text-foreground transition-colors">
                <x-buildora-icon icon="fa fa-home" class="text-muted-foreground" />
                {{ __buildora('Dashboard') }}
            </a>
        </li>

        @foreach($buildoraBreadcrumbs as $index => $item)
            <li class="flex items-center">
                <x-buildora-icon icon="fa fa-chevron-right" class="text-muted-foreground text-xs mx-2" />

                @if($index < count($buildoraBreadcrumbs) - 1)
                    <a href="{{ $item['url'] }}" class="hover:text-foreground transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-foreground font-semibold">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
