<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'Invoice Manager') }}</title>

    @stack('head-scripts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800">

    <div x-data="{ sidebarOpen: false }" class="min-h-screen">

        {{-- Mobile sidebar backdrop --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
             @click="sidebarOpen = false"
             style="display:none"></div>

        @include('partials.sidebar')

        <div class="lg:pl-64 flex flex-col min-h-screen">

            @include('partials.topbar')

            <main class="flex-1">
                @isset($header)
                    <header class="bg-white border-b border-slate-200">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                @if (session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium px-4 py-3">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                        <div class="rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium px-4 py-3">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

</body>
</html>
