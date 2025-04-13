@php
    use Ginkelsoft\Buildora\Support\NavigationBuilder;

    $hasChildren = isset($item['children']) && is_array($item['children']);
    $url = NavigationBuilder::getNavigationItemUrl($item);
    $isActive = NavigationBuilder::isActive($item);
    $isParentActive = NavigationBuilder::isParentActive($item);

    $active = $isActive ? 'bg-muted text-primary font-semibold' : 'text-foreground';
    $parent = $isParentActive ? 'bg-muted' : '';
@endphp

<li x-data="{ open: {{ $isParentActive ? 'true' : 'false' }} }" class="relative">
    @if ($hasChildren)
        <button @click="open = !open"
                class="w-full flex items-center justify-between p-2 rounded-lg {{ $parent }} hover:bg-muted/70 text-foreground transition duration-200">
            <span class="flex items-center gap-2">
                <x-buildora-icon icon="{{ $item['icon'] }}" class="text-muted-foreground" />
                <span class="text-sm text-foreground">{{ $item['label'] }}</span>
            </span>
            <i class="fa fa-chevron-down text-muted-foreground transform transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>

        <ul x-show="open" x-collapse class="ml-4 mt-2 space-y-2">
            @foreach ($item['children'] as $child)
                @include('buildora::components.navigation-item', ['item' => $child])
            @endforeach
        </ul>
    @else
        <a href="{{ $url }}"
           class="flex items-center gap-2 p-2 rounded-lg {{ $active }} hover:bg-muted/70 transition duration-200">
            <x-buildora-icon icon="{{ $item['icon'] }}" class="text-muted-foreground" />
            <span class="text-sm text-foreground">{{ $item['label'] }}</span>
        </a>
    @endif
</li>
