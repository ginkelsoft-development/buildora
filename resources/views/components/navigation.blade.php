@auth
    <!-- Gebruikersweergave -->
    <div class="mt-8 pb-6 border-b border-border px-4">
        <div class="w-full flex flex-col items-center text-foreground">
            <img
                    src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                    alt="{{ auth()->user()->name }}"
                    class="w-16 h-16 rounded-full object-cover ring-2 ring-muted"
            >
            <span class="mt-2 text-sm font-semibold text-center">
                {{ auth()->user()->name }}
            </span>
        </div>
    </div>
@endauth

<ul class="space-y-3 mt-6">
    @foreach(\Ginkelsoft\Buildora\Support\NavigationBuilder::getNavigation() as $item)
        @include('buildora::components.navigation-item', ['item' => $item])
    @endforeach
</ul>

@auth
    <div class="mt-auto px-4 pb-2">
        <form method="POST" action="{{ route('buildora.logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center justify-center px-4 py-2 text-sm text-destructive-foreground bg-destructive hover:opacity-90 rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
                </svg>
                {{ __buildora('Log out') }}
            </button>
        </form>
    </div>
@endauth
