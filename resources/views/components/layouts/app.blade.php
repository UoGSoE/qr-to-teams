<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>QR To Webhook</title>

        @vite('resources/css/app.css')
        @livewireStyles
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <main class="w-full md:w-3/4 mx-auto py-8 px-4">
            {{ $slot }}
        </main>

        @livewireScripts
        @vite('resources/js/app.js')
        @fluxScripts
    </body>
</html>
