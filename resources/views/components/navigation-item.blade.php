@php
    use Ginkelsoft\Buildora\Support\NavigationBuilder;

    $hasChildren = isset($item['children']) && is_array($item['children']);
    $url = NavigationBuilder::getNavigationItemUrl($item);
    $isActive = NavigationBuilder::isActive($item);
    $isParentActive = NavigationBuilder::isParentActive($item);
@endphp

<li x-data="{ open: {{ $isParentActive ? 'true' : 'false' }} }" class="relative">
    @if ($hasChildren)
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors sidebar-hover sidebar-text-muted {{ $isParentActive ? 'sidebar-active' : '' }}">
            <span class="flex items-center gap-3">
                <x-buildora-icon icon="{{ $item['icon'] }}" class="w-5 h-5" />
                <span class="text-sm">{{ __buildora($item['label']) }}</span>
            </span>
            <i class="fa fa-chevron-down text-xs transform transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>

        <ul x-show="open" x-collapse class="ml-4 mt-1 space-y-1 pl-3" style="border-left: 1px solid var(--sidebar-border);">
            @foreach ($item['children'] as $child)
                @include('buildora::components.navigation-item', ['item' => $child])
            @endforeach
        </ul>
    @else
        <a href="{{ $url }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors sidebar-hover {{ $isActive ? 'sidebar-active sidebar-text' : 'sidebar-text-muted' }}">
            <x-buildora-icon icon="{{ $item['icon'] }}" class="w-5 h-5" />
            <span class="text-sm">{{ __buildora($item['label']) }}</span>
        </a>
    @endif
</li>
