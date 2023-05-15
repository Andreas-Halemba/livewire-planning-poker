<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme="cupcake"
>

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-base-300">
    <div class="flex flex-col items-center justify-center min-h-screen sm:pt-0">
        <div class="w-11/12 max-w-md rounded-2xl sm:shadow-xl md:w-full card bg-base-100">
            <figure>
                <x-application-logo/>
            </figure>
            <div class="max-w-md card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
