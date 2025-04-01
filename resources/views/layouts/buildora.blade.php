<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Buildora') }}</title>

    <!-- FontAwesome van externe CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @vite([
        'resources/vendor/buildora/css/buildora.css',
        'resources/vendor/buildora/js/buildora.js',
    ])

    @yield('head')
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-200"
      x-data="{ showScrollButton: false, scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }) } }"
      x-init="window.addEventListener('scroll', () => { showScrollButton = window.scrollY > 200 })">
<div id="app" x-data="{ openSidebar: true}">

    <!-- Header sectie die de volledige breedte moet hebben, afhankelijk van de sidebar -->
    <header class="bg-white text-black p-4 px-8 flex justify-between items-center" :class="{'ml-64': openSidebar, 'ml-0': !openSidebar}">
        <div class="flex items-center gap-4">
            <!-- Hamburger menu icon, zichtbaar op mobiele schermen -->
            <button @click="openSidebar = !openSidebar" class="text-2xl">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="text-xl font-semibold">{{ config('app.name', 'Buildora') }}</h1>
        </div>

        <x-buildora::global-search />
    </header>

    <!-- Breadcrumbs strak onder de header -->
    <div class="mb-0 px-8 py-4 bg-gray-50 dark:bg-gray-800 border-t-1 border-b-1 border-t-gray-200 border-b-gray-200" :class="{'ml-64': openSidebar, 'ml-0': !openSidebar}">
        @includeIf('buildora::components.breadcrumb')
    </div>

    <div class="flex min-h-screen">
        <!-- Sidebar die aan de linkerkant blijft -->
        <aside
            class="flex flex-col h-full w-64 bg-gray-800 text-white p-4 transform transition-transform duration-300 fixed top-0 left-0 bottom-0"
            :class="{'-translate-x-full': !openSidebar, 'translate-x-0': openSidebar}"
            x-transition:enter="transition-transform ease-out duration-300"
            x-transition:leave="transition-transform ease-in duration-300"
        >
            <!-- Navigatie Links -->
            @include('buildora::components.navigation')
        </aside>

        <!-- Main content area naast de sidebar -->
        <main :class="{'ml-64': openSidebar, 'ml-0': !openSidebar}" class="w-full p-8 transition-all duration-300">
            <!-- Content -->
            <div class="flex-1 py-6 dark:bg-gray-900">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<button
    x-show="showScrollButton"
    @click="scrollToTop"
    class="fixed bottom-8 right-8 w-16 h-16 bg-slate-400 text-white p-4 rounded-lg shadow-lg hover:bg-slate-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-slate-500">
    <i class="fa-solid fa-arrow-up"></i> <!-- FontAwesome up icon -->
</button>
</body>
</html>
