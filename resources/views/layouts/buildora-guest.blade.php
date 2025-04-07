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

<body class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
@yield('content')

</body>
</html>
