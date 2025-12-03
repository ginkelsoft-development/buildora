<ul class="space-y-1 py-4 px-3">
    @foreach(\Ginkelsoft\Buildora\Support\NavigationBuilder::getNavigation() as $item)
        @include('buildora::components.navigation-item', ['item' => $item])
    @endforeach
</ul>
