<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>QR To Webhook</title>

        @vite('resources/css/app.css')
        @livewireStyles
    </head>
    <body>
        <div class="section">
            <div class="container">
                @yield('content')
            </div>
        </div>
        @livewireScripts
        @vite('resources/js/app.js')
    </body>
</html>
