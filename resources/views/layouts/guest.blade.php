<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-gradient-to-br from-navy-600 via-navy-500 to-gold-400">
            <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-2xl p-10">
                <div class="text-center mb-7">
                    <h1 class="text-xl font-bold text-navy-600">
                        CV. <span class="text-gold-400">Basyid Creative Architecture</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">Management Administrasi Customer Service</p>
                </div>

                {{ $slot }}
            </div>

            <p class="text-xs text-white/60 mt-6">&copy; {{ date('Y') }} Basyid Group. All Rights Reserved.</p>
        </div>
    </body>
</html>
