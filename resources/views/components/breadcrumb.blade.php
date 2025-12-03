<nav aria-label="Breadcrumb" class="flex items-center text-sm">
    @foreach($buildoraBreadcrumbs as $index => $item)
        @if($index > 0)
            <span class="mx-2" style="color: var(--text-muted); opacity: 0.4;">/</span>
        @endif

        @if($index < count($buildoraBreadcrumbs) - 1)
            <a href="{{ $item['url'] }}"
               class="transition-colors hover:underline"
               style="color: var(--text-muted);">
                {{ $item['label'] }}
            </a>
        @else
            <span class="font-medium" style="color: var(--text-primary);">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
