<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-funeral-base">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Agenzia Funebre') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-funeral-600" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-funeral-900/80 z-40 lg:hidden" aria-hidden="true"
        @click="sidebarOpen = false"></div>

    <!-- Mobile Sidebar -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-funeral-900 overflow-y-auto lg:hidden">
        <div class="flex items-center justify-between px-6 py-4 bg-funeral-950/50">
            <span class="text-xl font-semibold text-white tracking-wide">Agenzia<span
                    class="text-accent-gold">Funebre</span></span>
            <button type="button" class="-m-2.5 p-2.5 text-funeral-400 hover:text-white" @click="sidebarOpen = false">
                <span class="sr-only">Chiudi menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @include('layouts.navigation')
    </div>

    <!-- Desktop Sidebar -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <div class="flex grow flex-col bg-funeral-900 overflow-y-auto border-r border-funeral-800">
            <div class="flex h-16 shrink-0 items-center px-6 bg-funeral-950/30">
                <span class="text-xl font-semibold text-white tracking-wide">Agenzia<span
                        class="text-accent-gold">Funebre</span></span>
            </div>
            @include('layouts.navigation')
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:pl-72 flex flex-col min-h-full transition-all duration-300">
        <!-- Top Bar -->
        <div
            class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between gap-x-4 border-b border-funeral-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
            <button type="button" class="-m-2.5 p-2.5 text-funeral-700 lg:hidden" @click="sidebarOpen = true">
                <span class="sr-only">Apri menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="flex flex-1 items-center justify-end gap-x-4 lg:gap-x-6">
                <!-- Profile dropdown placeholder -->
                <div class="flex items-center gap-x-4 lg:gap-x-6">
                    <div class="h-6 w-px bg-funeral-200" aria-hidden="true"></div>
                    <div class="flex items-center gap-x-3">
                        <span class="text-sm font-semibold leading-6 text-funeral-900">Admin</span>
                        <div
                            class="h-8 w-8 rounded-full bg-funeral-700 flex items-center justify-center text-white text-xs border border-funeral-600">
                            A</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="py-10">
            <div class="px-4 sm:px-6 lg:px-8">
                <!-- Header Slot -->
                @if (isset($header))
                    <header class="mb-8">
                        {{ $header }}
                    </header>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>