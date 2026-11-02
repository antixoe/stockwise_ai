<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="p-7 sm:p-9">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-xl font-black text-indigo-600"><i class="bi bi-key" aria-hidden="true"></i></div>
            <p class="text-xs font-black uppercase tracking-[.18em] text-indigo-600">Pemulihan akses</p>
            <h1 class="mt-2 text-3xl font-black tracking-[-.04em] text-slate-950">Lupa password?</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan email kerja Anda dan kami akan mengirimkan tautan untuk membuat password baru.</p>
        </div>
        <button type="button" x-on:click="$dispatch('close-password-modal')" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup pemulihan password"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <div>
            <label for="reset-email" class="label">Email kerja</label>
            <input wire:model="email" id="reset-email" class="input" type="email" name="email" required autofocus autocomplete="email" placeholder="nama@perusahaan.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="sendPasswordResetLink" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-70"><span wire:loading.remove wire:target="sendPasswordResetLink">Kirim tautan reset <i class="bi bi-send" aria-hidden="true"></i></span><span wire:loading wire:target="sendPasswordResetLink">Mengirim...</span></button>
    </form>

    <button type="button" x-on:click="$dispatch('close-password-modal'); $nextTick(() => $dispatch('open-login-modal'))" class="mt-7 flex w-full items-center justify-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke login</button>
</div>
