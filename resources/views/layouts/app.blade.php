<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'StockWise AI' }} · StockWise AI</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-50">
            <livewire:layout.navigation />
            <main class="lg:pl-72 min-h-screen">
                <div class="px-4 py-4 sm:px-6 lg:px-8">
                    @if (isset($header))
                        <div class="mb-6">{{ $header }}</div>
                    @endif
                {{ $slot }}
                </div>
            </main>
        </div>
        <x-toast :message="session('status')" />
    </body>
</html>
