<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>
        @vite('resources/css/app.css')
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <main class="w-full md:w-3/4 max-w-2xl mx-auto py-8 px-4">
            <div class="text-center mb-8">
                <img src="/UoG_colour.png" alt="University of Glasgow Logo" class="h-20 object-contain mx-auto">
            </div>
            @yield('content')
        </main>
        @fluxScripts
    </body>
</html>
