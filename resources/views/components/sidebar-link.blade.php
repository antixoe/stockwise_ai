@props(['href', 'active' => false, 'icon' => '•'])
<a href="{{ $href }}" wire:navigate class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
    <span class="flex h-7 w-7 items-center justify-center rounded-lg text-base {{ $active ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">{{ $icon }}</span>{{ $slot }}
</a>
