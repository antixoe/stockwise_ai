<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();
        session()->flash('status', 'Selamat datang kembali di workspace StockWise AI.');

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div x-data="{ showPassword: false }" class="p-7 sm:p-9">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-xl font-black text-indigo-600"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></div>
            <p class="text-xs font-black uppercase tracking-[.18em] text-indigo-600">Selamat datang kembali</p>
            <h1 class="mt-2 text-3xl font-black tracking-[-.04em] text-slate-950">Masuk ke workspace</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Pantau stok, forecast, dan keputusan restock tim Anda.</p>
        </div>
        <button type="button" x-on:click="$dispatch('close-login-modal')" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup login"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="label">Email kerja</label>
            <input wire:model="form.email" id="email" class="input" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@perusahaan.com">
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between"><label for="password" class="label mb-0">Password</label>@if (Route::has('password.request'))<button type="button" x-on:click="$dispatch('open-password-modal')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lupa password?</button>@endif</div>
            <div class="relative"><input wire:model="form.password" id="password" class="input pr-12" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Masukkan password"><button type="button" x-on:click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-indigo-600" x-bind:aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"><i class="bi" x-bind:class="showPassword ? 'bi-eye-slash' : 'bi-eye'" aria-hidden="true"></i></button></div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm text-slate-500"><input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember"><span>Ingat saya di perangkat ini</span></label>

        <button type="submit" wire:loading.attr="disabled" wire:target="login" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-70"><span wire:loading.remove wire:target="login">Masuk ke workspace <i class="bi bi-arrow-right" aria-hidden="true"></i></span><span wire:loading wire:target="login">Memeriksa akses...</span></button>
    </form>

    @if (Route::has('register'))
        <p class="mt-7 text-center text-sm text-slate-500">Belum punya akses? <a href="{{ route('register') }}" wire:navigate class="font-bold text-indigo-600 hover:text-indigo-800">Daftarkan workspace</a></p>
    @endif
</div>
