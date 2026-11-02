<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="Masuk ke workspace StockWise AI untuk mengelola persediaan dengan keputusan yang lebih jelas.">
        <title>Masuk — StockWise AI</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f7f8fc] font-sans text-slate-900 antialiased">
        <main x-data="{ open: true }" x-init="$watch('open', value => document.body.classList.toggle('overflow-hidden', value))" x-on:close-login-modal.window="open = false" x-on:close-password-modal.window="open = false" class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6">
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-indigo-200/60 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-cyan-100/80 blur-3xl"></div>

            <a href="{{ url('/') }}" class="absolute left-5 top-5 z-10 flex items-center gap-3 sm:left-8 sm:top-8" aria-label="Kembali ke beranda StockWise AI">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-black text-white shadow-lg shadow-indigo-200">S</span>
                <span><span class="block text-sm font-black tracking-tight text-slate-950">StockWise AI</span><span class="block text-[10px] font-bold uppercase tracking-[.22em] text-indigo-500">Inventory intelligence</span></span>
            </a>

            <div x-show="open" x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative z-10 w-full max-w-md">
                <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/95 shadow-2xl shadow-indigo-200/50 backdrop-blur">
                    {{ $slot }}
                </div>
            </div>

            <div x-show="open" x-cloak class="absolute bottom-5 left-0 right-0 text-center text-xs font-medium text-slate-400 sm:bottom-7">Workspace persediaan yang dapat ditinjau dan dijelaskan</div>
        </main>
        @livewireScripts
    </body>
</html>
