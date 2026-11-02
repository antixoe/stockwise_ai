<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
<nav x-data="{ open: false }" x-on:toggle-sidebar.window="open = true" class="fixed inset-y-0 z-40 w-72 -translate-x-full border-r border-slate-200 bg-white transition lg:translate-x-0" :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex h-full flex-col">
        <div class="flex h-20 items-center justify-between border-b border-slate-100 px-6">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">S</span>
                <span><span class="block text-base font-bold tracking-tight text-slate-900">StockWise</span><span class="block text-[11px] font-medium uppercase tracking-[.18em] text-indigo-500">AI inventory</span></span>
            </a>
            <button @click="open = false" class="rounded-lg p-2 text-slate-400 lg:hidden">✕</button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-6">
            <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[.2em] text-slate-400">Workspace</p>
            <div class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="▦">Dashboard</x-sidebar-link>
                <x-sidebar-link href="{{ route('products.index') }}" :active="request()->routeIs('products.*')" icon="□">Produk</x-sidebar-link>
                <x-sidebar-link href="{{ route('suppliers.index') }}" :active="request()->routeIs('suppliers.*')" icon="⌁">Supplier</x-sidebar-link>
                <x-sidebar-link href="{{ route('inventory.index') }}" :active="request()->routeIs('inventory.*')" icon="▤">Persediaan</x-sidebar-link>
                <x-sidebar-link href="{{ route('stock-transactions.index') }}" :active="request()->routeIs('stock-transactions.*')" icon="↕">Transaksi stok</x-sidebar-link>
                <x-sidebar-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')" icon="◒">Laporan</x-sidebar-link>
            </div>
            <p class="mb-3 mt-8 px-3 text-[10px] font-semibold uppercase tracking-[.2em] text-slate-400">Decision support</p>
            <div class="space-y-1">
                <x-sidebar-link href="{{ route('explainability.index') }}" :active="request()->routeIs('explainability.*')" icon="?">Explainable AI</x-sidebar-link>
                <x-sidebar-link href="{{ route('forecasts.index') }}" :active="request()->routeIs('forecasts.*')" icon="✦">Forecast AI</x-sidebar-link>
                <x-sidebar-link href="{{ route('recommendations.index') }}" :active="request()->routeIs('recommendations.*')" icon="↗">Rekomendasi Restock</x-sidebar-link>
            </div>
            @if(auth()->user()->hasRole('admin'))
                <p class="mb-3 mt-8 px-3 text-[10px] font-semibold uppercase tracking-[.2em] text-slate-400">Administration</p>
                <x-sidebar-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')" icon="◎">Pengguna</x-sidebar-link>
                <x-sidebar-link href="{{ route('internal.audit-logs.index') }}" :active="request()->routeIs('internal.audit-logs.*')" icon="≡">Audit log</x-sidebar-link>
            @endif
        </div>
        <div class="border-t border-slate-100 p-4">
            <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ str_replace('_', ' ', ucfirst(auth()->user()->role)) }}</p></div>
                <x-dropdown align="right" width="48"><x-slot name="trigger"><button class="text-slate-400">•••</button></x-slot><x-slot name="content"><x-dropdown-link :href="route('profile')" wire:navigate>Profil</x-dropdown-link><button wire:click="logout" class="w-full text-start"><x-dropdown-link>Keluar</x-dropdown-link></button></x-slot></x-dropdown>
            </div>
        </div>
    </div>
</nav>
<div class="sticky top-0 z-30 flex h-16 items-center border-b border-slate-200 bg-white/90 px-4 backdrop-blur lg:hidden"><button @click="$dispatch('toggle-sidebar')" class="rounded-lg p-2 text-xl text-slate-600">☰</button><span class="ml-3 font-semibold text-slate-900">StockWise AI</span></div>
</div>
