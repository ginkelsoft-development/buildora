@php
    use Ginkelsoft\Buildora\Support\NavigationBuilder;

    $hasChildren = isset($item['children']) && is_array($item['children']);
    $url = NavigationBuilder::getNavigationItemUrl($item);
    $isActive = NavigationBuilder::isActive($item);
    $isParentActive = NavigationBuilder::isParentActive($item);

    $active = $isActive ? 'bg-gray-700 text-blue-500 font-semibold' : 'text-gray-700 dark:text-gray-200';
    $parent = $isParentActive ? 'bg-gray-700' : '';
@endphp

<li x-data="{ open: {{ $isParentActive ? 'true' : 'false' }} }" class="relative">
    @if ($hasChildren)
        <button @click="open = !open"
                class="w-full flex items-center justify-between p-2 rounded-lg {{ $parent }} hover:bg-gray-700 hover:text-white transition duration-200">
            <span class="flex items-center gap-2">
                <x-buildora-icon icon="{{ $item['icon'] }}" class="text-gray-400" />
                <span class="text-sm text-gray-200">{{ $item['label'] }}</span>
            </span>
            <i class="fa fa-chevron-down text-gray-400 transform transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
        </button>

        <ul x-show="open" x-collapse class="ml-4 mt-2 space-y-2">
            @foreach ($item['children'] as $child)
                @include('buildora::components.navigation-item', ['item' => $child])
            @endforeach
        </ul>
    @else
        <a href="{{ $url }}"
           class="flex items-center gap-2 p-2 rounded-lg {{ $active }} hover:bg-gray-700 hover:text-white transition duration-200">
            <x-buildora-icon icon="{{ $item['icon'] }}" class="text-gray-400" />
            <span class="text-sm text-gray-200">{{ $item['label'] }}</span>
        </a>
    @endif
</li>
