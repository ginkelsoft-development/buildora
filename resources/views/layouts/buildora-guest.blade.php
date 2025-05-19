<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Buildora') }}</title>

    <!-- FontAwesome van externe CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{ Ginkelsoft\Buildora\Buildora::css() }} {{-- laat deze staan --}}
    {{ Ginkelsoft\Buildora\Buildora::js() }}

    @yield('head')
</head>

<body class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white min-h-screen font-sans antialiased">
@yield('content')

<footer class="text-xs text-white text-center py-4">
    Buildora v{{ config('buildora.version', 'dev') }}
</footer>
</body>
</html>
