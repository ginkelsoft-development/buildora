<!DOCTYPE html>
<html lang="en" x-data x-init="
    const theme = localStorage.getItem('theme') ?? 'system';
    if (theme === 'dark') document.documentElement.classList.add('dark');
    if (theme === 'light') document.documentElement.classList.remove('dark');
    if (theme === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', isDark);
    }
">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Buildora') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{ Ginkelsoft\Buildora\Buildora::css() }}
    {{ Ginkelsoft\Buildora\Buildora::js() }}

    @yield('head')
</head>

<body class="bg-base text-foreground font-sans antialiased"
      x-data="{ showScrollButton: false, scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }) } }"
      x-init="window.addEventListener('scroll', () => { showScrollButton = window.scrollY > 200 })">


<div id="app" x-data="{ openSidebar: true }">
    {{-- HEADER --}}
    <header class="bg-base text-base-foreground p-4 px-8 flex justify-between items-center border-b border-border"
            :class="{'ml-64': openSidebar, 'ml-0': !openSidebar}">
        <div class="flex items-center gap-4">
            <button @click="openSidebar = !openSidebar" class="text-2xl">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="text-xl font-semibold">{{ config('app.name', 'Buildora') }}</h1>
        </div>

        <x-buildora::global-search />

        <form method="POST" action="{{ route('buildora.locale.switch') }}" class="flex items-center gap-4">
            @csrf

            <div x-data="{ openLang: false }" class="relative mr-2">
                <button @click="openLang = !openLang"
                        type="button"
                        class="appearance-none bg-base dark:bg-primary border border-border text-foreground dark:text-primary-foreground py-1.5 px-3 pr-2 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary flex items-center gap-2"
                >
                    {{ ['en' => 'English', 'nl' => 'Nederlands', 'es' => 'Español', 'de' => 'Deutsch', 'fy' => 'Frysk'][buildora_session_get('locale', 'nl')] }}
                    <svg class="h-4 w-4 ml-1 text-muted-foreground fill-current" viewBox="0 0 20 20">
                        <path d="M5.516 7.548l4.484 4.484 4.484-4.484-1.06-1.06L10 9.91 6.576 6.488z" />
                    </svg>
                </button>

                <div x-show="openLang"
                     x-transition
                     @click.outside="openLang = false"
                     class="absolute right-0 mt-2 w-56 border border-border rounded-md shadow-lg z-50
                bg-base dark:bg-primary text-foreground dark:text-primary-foreground"
                >
                    <ul class="py-1 text-sm">
                        @foreach(['en' => 'English', 'nl' => 'Nederlands', 'es' => 'Español', 'de' => 'Deutsch', 'fy' => 'Frysk'] as $code => $language)
                            <li>
                                <form method="POST" action="{{ route('buildora.locale.switch') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $code }}">
                                    <button type="submit"
                                            class="w-full text-left px-4 py-2 hover:bg-muted flex items-center gap-2
                                @if(buildora_session_get('locale', 'nl') === $code) font-semibold @endif"
                                    >
                                        <i class="fa-solid fa-language w-4"></i> {{ $language }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- THEMA SWITCHER --}}
            <div x-data="{ open: false, mode: localStorage.getItem('theme') ?? 'system' }"
                 x-init="
            if (mode === 'dark') document.documentElement.classList.add('dark');
            if (mode === 'light') document.documentElement.classList.remove('dark');
            if (mode === 'system') document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
         "
                 class="relative"
            >
                <button @click="open = !open"
                        type="button"
                        class="text-xl text-muted-foreground hover:text-primary transition focus:outline-none"
                        :title="`Thema: ${mode}`"
                >
                    <template x-if="mode === 'light'">
                        <i class="fa-solid fa-sun"></i>
                    </template>
                    <template x-if="mode === 'dark'">
                        <i class="fa-solid fa-moon"></i>
                    </template>
                    <template x-if="mode === 'system'">
                        <i class="fa-solid fa-desktop"></i>
                    </template>
                </button>

                <div x-show="open"
                     @click.outside="open = false"
                     class="absolute right-0 mt-2 w-56 border border-border rounded-md shadow-lg z-50
            bg-base text-foreground
            dark:bg-primary dark:text-foreground"
                >
                    <ul class="py-1 text-sm text-foreground">
                        <li>
                            <a href="#"
                               @click.prevent="
                            mode = 'light';
                            localStorage.setItem('theme', 'light');
                            document.documentElement.classList.remove('dark');
                            open = false;
                       "
                               class="flex items-center gap-2 px-4 py-2 hover:bg-muted"
                            >
                                <i class="fa-solid fa-sun w-4"></i> Licht
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               @click.prevent="
                            mode = 'dark';
                            localStorage.setItem('theme', 'dark');
                            document.documentElement.classList.add('dark');
                            open = false;
                       "
                               class="flex items-center gap-2 px-4 py-2 hover:bg-muted"
                            >
                                <i class="fa-solid fa-moon w-4"></i> Donker
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               @click.prevent="
                            mode = 'system';
                            localStorage.setItem('theme', 'system');
                            document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
                            open = false;
                       "
                               class="flex items-center gap-2 px-4 py-2 hover:bg-muted"
                            >
                                <i class="fa-solid fa-desktop w-4"></i> Systeem
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </header>

    {{-- BREADCRUMB --}}
    <div class="px-8 py-4 bg-muted text-muted-foreground border-t border-b border-border"
         :class="{'ml-64': openSidebar, 'ml-0': !openSidebar}">
        @includeIf('buildora::components.breadcrumb')
    </div>

    <div class="flex min-h-screen">
        {{-- SIDEBAR --}}
        <aside
                class="flex flex-col h-full w-64 bg-secondary text-secondary-foreground p-4 transform transition-transform duration-300 fixed top-0 left-0 bottom-0"
                :class="{'-translate-x-full': !openSidebar, 'translate-x-0': openSidebar}"
                x-transition:enter="transition-transform ease-out duration-300"
                x-transition:leave="transition-transform ease-in duration-300"
        >
            @include('buildora::components.navigation')
        </aside>

        {{-- CONTENT --}}
        <main :class="{'ml-64': openSidebar, 'ml-0': !openSidebar}" class="w-full p-8 transition-all duration-300">
            <div class="flex-1 py-6 text-foreground">
                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- SCROLL BUTTON --}}
<button
        x-show="showScrollButton"
        @click="scrollToTop"
        class="fixed bottom-8 right-8 w-16 h-16 bg-primary text-primary-foreground p-4 rounded-lg shadow-lg hover:opacity-90 transition duration-300 focus:outline-none focus:ring-2 focus:ring-ring">
    <i class="fa-solid fa-arrow-up"></i>
</button>

{{-- FOOTER --}}
<div class="text-xs text-muted-foreground text-center py-4">
    Buildora v{{ config('buildora.version', 'dev') }}
</div>

</body>
</html>
