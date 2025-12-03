<!DOCTYPE html>
<html lang="en" class="dark" x-data x-init="
    const theme = localStorage.getItem('theme') ?? 'dark';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{ Ginkelsoft\Buildora\Buildora::css() }}
    {{ Ginkelsoft\Buildora\Buildora::js() }}

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Light mode */
        :root {
            --bg-body: #f9fafb;
            --bg-header: rgba(255, 255, 255, 0.95);
            --bg-sidebar: linear-gradient(180deg, #f3f4f6 0%, #e5e7eb 100%);
            --bg-dropdown: #ffffff;
            --bg-input: #ffffff;
            --bg-hover: rgba(0, 0, 0, 0.05);
            --border-color: rgba(0, 0, 0, 0.1);
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #6b7280;
            --sidebar-text: #374151;
            --sidebar-text-muted: #6b7280;
            --sidebar-hover: rgba(0, 0, 0, 0.05);
            --sidebar-active: rgba(102, 126, 234, 0.1);
            --sidebar-border: rgba(0, 0, 0, 0.1);
        }

        /* Dark mode */
        .dark {
            --bg-body: #0f0f1a;
            --bg-header: rgba(15, 15, 26, 0.95);
            --bg-sidebar: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            --bg-dropdown: #1a1a2e;
            --bg-input: rgba(255, 255, 255, 0.05);
            --bg-hover: rgba(255, 255, 255, 0.05);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-muted: rgba(255, 255, 255, 0.5);
            --sidebar-text: #ffffff;
            --sidebar-text-muted: rgba(255, 255, 255, 0.7);
            --sidebar-hover: rgba(255, 255, 255, 0.05);
            --sidebar-active: rgba(255, 255, 255, 0.1);
            --sidebar-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: var(--bg-body);
            color: var(--text-primary);
        }

        .header-bg {
            background: var(--bg-header);
            backdrop-filter: blur(10px);
        }

        .sidebar-bg {
            background: var(--bg-sidebar);
        }

        .dropdown-menu {
            background: var(--bg-dropdown);
            border: 1px solid var(--border-color);
        }

        .dropdown-item:hover {
            background: var(--bg-hover);
        }

        .input-styled {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .input-styled:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-primary:hover {
            box-shadow: 0 10px 40px -10px rgba(102, 126, 234, 0.5);
        }

        /* Sidebar specific */
        .sidebar-text { color: var(--sidebar-text); }
        .sidebar-text-muted { color: var(--sidebar-text-muted); }
        .sidebar-border { border-color: var(--sidebar-border); }
        .sidebar-hover:hover { background: var(--sidebar-hover); }
        .sidebar-active { background: var(--sidebar-active); }

        /* Light mode sidebar adjustments */
        :root .sidebar-logo { filter: none; }
        .dark .sidebar-logo { filter: brightness(0) invert(1); }
    </style>

    @yield('head')
</head>

<body class="antialiased min-h-screen">

<div id="app"
     x-data="{ openSidebar: true, showScrollButton: false }"
     x-init="window.addEventListener('scroll', () => { showScrollButton = window.scrollY > 200 })"
     class="min-h-screen flex flex-col">
    {{-- HEADER - Modern glassmorphism style --}}
    <header class="h-16 flex-shrink-0 z-50 relative">
        {{-- Gradient border bottom --}}
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-purple-500/20 to-transparent"></div>

        <div class="h-full px-4 lg:px-6" style="background: var(--bg-header); backdrop-filter: blur(12px);">
            <div class="flex items-center justify-between h-full">
                {{-- Left: Logo & Sidebar Toggle --}}
                <div class="flex items-center gap-3">
                    {{-- Sidebar Toggle --}}
                    <button @click="openSidebar = !openSidebar"
                            class="w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200 hover:bg-gradient-to-br hover:from-purple-500/10 hover:to-indigo-500/10 group"
                            style="color: var(--text-muted);">
                        <i class="fa-solid transition-transform duration-300 group-hover:text-indigo-400" :class="openSidebar ? 'fa-sidebar rotate-0' : 'fa-bars'"></i>
                    </button>

                    {{-- Logo --}}
                    <a href="{{ route('buildora.dashboard') }}" class="flex items-center gap-3 px-2 py-1.5 rounded-xl transition-all duration-200 hover:bg-white/5">
                        <img src="{{ route('buildora.asset', ['file' => 'buildora.png']) }}" alt="Buildora" class="h-8 w-auto object-contain sidebar-logo">
                        <span class="font-semibold text-base hidden lg:block" style="color: var(--text-primary);">
                            {{ config('app.name', 'Buildora') }}
                        </span>
                    </a>
                </div>

                {{-- Center: Global Search --}}
                <div class="flex-1 max-w-2xl mx-4 lg:mx-8">
                    <x-buildora::global-search />
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-1">
                    {{-- Language Switcher --}}
                    <div x-data="{ openLang: false }" class="relative">
                        <button @click="openLang = !openLang"
                                type="button"
                                class="w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200 hover:bg-gradient-to-br hover:from-purple-500/10 hover:to-indigo-500/10"
                                style="color: var(--text-muted);">
                            <i class="fa-solid fa-globe"></i>
                        </button>

                        <div x-show="openLang"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             @click.outside="openLang = false"
                             class="absolute right-0 mt-2 w-48 rounded-2xl shadow-2xl z-50 overflow-hidden"
                             style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
                            <div class="p-2">
                                @foreach(['en' => ['English', '🇬🇧'], 'nl' => ['Nederlands', '🇳🇱'], 'es' => ['Español', '🇪🇸'], 'de' => ['Deutsch', '🇩🇪'], 'fy' => ['Frysk', '🏴']] as $code => $lang)
                                    <form method="POST" action="{{ route('buildora.locale.switch') }}">
                                        @csrf
                                        <input type="hidden" name="locale" value="{{ $code }}">
                                        <button type="submit"
                                                class="w-full text-left px-3 py-2.5 text-sm rounded-xl flex items-center gap-3 transition-all duration-200 {{ buildora_session_get('locale', 'nl') === $code ? 'bg-gradient-to-r from-indigo-500/10 to-purple-500/10' : 'hover:bg-white/5' }}"
                                                style="color: var(--text-secondary);">
                                            <span class="text-lg">{{ $lang[1] }}</span>
                                            <span>{{ $lang[0] }}</span>
                                            @if(buildora_session_get('locale', 'nl') === $code)
                                                <i class="fa-solid fa-check ml-auto text-indigo-400"></i>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Theme Switcher --}}
                    <div x-data="{ open: false, mode: localStorage.getItem('theme') ?? 'dark' }"
                         x-init="
                            if (mode === 'dark') document.documentElement.classList.add('dark');
                            if (mode === 'light') document.documentElement.classList.remove('dark');
                            if (mode === 'system') document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
                         "
                         class="relative">
                        <button @click="open = !open"
                                type="button"
                                class="w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200 hover:bg-gradient-to-br hover:from-purple-500/10 hover:to-indigo-500/10"
                                style="color: var(--text-muted);">
                            <template x-if="mode === 'light'"><i class="fa-solid fa-sun text-amber-400"></i></template>
                            <template x-if="mode === 'dark'"><i class="fa-solid fa-moon text-indigo-400"></i></template>
                            <template x-if="mode === 'system'"><i class="fa-solid fa-circle-half-stroke"></i></template>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             @click.outside="open = false"
                             class="absolute right-0 mt-2 w-40 rounded-2xl shadow-2xl z-50 overflow-hidden"
                             style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
                            <div class="p-2">
                                <button @click="mode = 'light'; localStorage.setItem('theme', 'light'); document.documentElement.classList.remove('dark'); open = false;"
                                        class="w-full text-left px-3 py-2.5 text-sm rounded-xl flex items-center gap-3 transition-all duration-200 hover:bg-white/5"
                                        style="color: var(--text-secondary);">
                                    <i class="fa-solid fa-sun text-amber-400"></i>
                                    {{ __buildora('Light') }}
                                </button>
                                <button @click="mode = 'dark'; localStorage.setItem('theme', 'dark'); document.documentElement.classList.add('dark'); open = false;"
                                        class="w-full text-left px-3 py-2.5 text-sm rounded-xl flex items-center gap-3 transition-all duration-200 hover:bg-white/5"
                                        style="color: var(--text-secondary);">
                                    <i class="fa-solid fa-moon text-indigo-400"></i>
                                    {{ __buildora('Dark') }}
                                </button>
                                <button @click="mode = 'system'; localStorage.setItem('theme', 'system'); document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches); open = false;"
                                        class="w-full text-left px-3 py-2.5 text-sm rounded-xl flex items-center gap-3 transition-all duration-200 hover:bg-white/5"
                                        style="color: var(--text-secondary);">
                                    <i class="fa-solid fa-circle-half-stroke text-gray-400"></i>
                                    {{ __buildora('System') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="w-px h-8 mx-2 bg-gradient-to-b from-transparent via-white/10 to-transparent"></div>

                    {{-- User Menu --}}
                    @auth
                        <div x-data="{ openUser: false }" class="relative">
                            <button @click="openUser = !openUser"
                                    class="flex items-center gap-3 rounded-xl transition-all duration-200 hover:bg-gradient-to-br hover:from-purple-500/10 hover:to-indigo-500/10 px-2 py-1.5 group">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full blur opacity-0 group-hover:opacity-50 transition-opacity duration-300"></div>
                                    <img src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=667eea&color=fff&size=36' }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="w-9 h-9 rounded-full ring-2 ring-white/10 relative">
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-medium" style="color: var(--text-primary);">{{ auth()->user()->name }}</p>
                                    <p class="text-xs" style="color: var(--text-muted);">{{ __buildora('Administrator') }}</p>
                                </div>
                                <i class="fa-solid fa-chevron-down text-xs hidden md:block transition-transform duration-200" :class="openUser ? 'rotate-180' : ''" style="color: var(--text-muted);"></i>
                            </button>

                            <div x-show="openUser"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-2"
                                 @click.outside="openUser = false"
                                 class="absolute right-0 mt-2 w-56 rounded-2xl shadow-2xl z-50 overflow-hidden"
                                 style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
                                {{-- User Info Header --}}
                                <div class="p-4 bg-gradient-to-br from-indigo-500/10 to-purple-500/10">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=667eea&color=fff&size=48' }}"
                                             alt="{{ auth()->user()->name }}"
                                             class="w-12 h-12 rounded-xl ring-2 ring-white/20">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold truncate" style="color: var(--text-primary);">{{ auth()->user()->name }}</p>
                                            <p class="text-xs truncate" style="color: var(--text-muted);">{{ auth()->user()->email }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Menu Items --}}
                                <div class="p-2">
                                    <a href="{{ route('buildora.profile.edit') }}"
                                       class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-all duration-200 hover:bg-white/5"
                                       style="color: var(--text-secondary);">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(99, 102, 241, 0.1);">
                                            <i class="fa-solid fa-user-gear text-indigo-400 text-xs"></i>
                                        </div>
                                        {{ __buildora('Profile Settings') }}
                                    </a>
                                    <a href="{{ route('buildora.dashboard') }}"
                                       class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-all duration-200 hover:bg-white/5"
                                       style="color: var(--text-secondary);">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(16, 185, 129, 0.1);">
                                            <i class="fa-solid fa-gauge-high text-emerald-400 text-xs"></i>
                                        </div>
                                        {{ __buildora('Dashboard') }}
                                    </a>
                                </div>

                                {{-- Logout --}}
                                <div class="p-2" style="border-top: 1px solid var(--border-color);">
                                    <form method="POST" action="{{ route('buildora.logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-all duration-200 hover:bg-red-500/10 text-red-400">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-500/10">
                                                <i class="fa-solid fa-right-from-bracket text-xs"></i>
                                            </div>
                                            {{ __buildora('Log out') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- BODY: Sidebar + Content --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- SIDEBAR --}}
        <aside :class="openSidebar ? 'w-64' : 'w-0'"
               class="flex-shrink-0 overflow-y-auto overflow-x-hidden sidebar-bg transition-all duration-300 ease-in-out"
               style="border-right: 1px solid var(--border-color);">
            <div class="w-64">
                @include('buildora::components.navigation')
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto flex flex-col min-w-0">
            {{-- BREADCRUMB BAR --}}
            <div class="flex items-center gap-4 px-6 py-3 flex-shrink-0" style="border-bottom: 1px solid var(--border-color);">
                {{-- Breadcrumb --}}
                @includeIf('buildora::components.breadcrumb')
            </div>

            {{-- CONTENT --}}
            <div class="flex-1 p-6">
                @yield('content')
            </div>

            {{-- FOOTER --}}
            <div class="text-xs text-center py-4 flex-shrink-0" style="color: var(--text-muted);">
                Buildora v{{ config('buildora.version', 'dev') }}
            </div>
        </main>
    </div>
</div>

{{-- SCROLL BUTTON --}}
<button x-show="showScrollButton"
        x-transition
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-8 right-8 w-12 h-12 text-white flex items-center justify-center rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 focus:outline-none btn-primary">
    <i class="fa-solid fa-arrow-up"></i>
</button>

</body>
</html>
