<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Buildora') }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{ Ginkelsoft\Buildora\Buildora::css() }}
    {{ Ginkelsoft\Buildora\Buildora::js() }}

    @yield('head')
</head>

<body class="bg-gray-950 text-white min-h-screen font-sans antialiased">
@yield('content')
</body>
</html>
