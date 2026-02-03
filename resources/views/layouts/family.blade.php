<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-funeral-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Area Famiglia') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-funeral-900">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">

        <!-- Agency Brand Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
            <span class="text-2xl font-bold tracking-tight text-funeral-900">Agenzia<span
                    class="text-accent-gold">Funebre</span></span>
            <p class="mt-2 text-sm text-funeral-500">Area Riservata Famiglie</p>
        </div>

        <!-- Main Content Card -->
        <div class="sm:mx-auto sm:w-full sm:max-w-[600px]">
            <div class="bg-white py-8 px-4 shadow sm:rounded-xl sm:px-10 border border-funeral-100">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-funeral-400">
                &copy; {{ date('Y') }} Agenzia Funebre. Tutti i diritti riservati.<br>
                Privacy Policy &bull; Termini di Servizio
            </div>
        </div>
    </div>
</body>

</html>