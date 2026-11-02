<div>
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-indigo-600">Administration</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Audit log</h1>
            <p class="mt-2 text-sm text-slate-500">Riwayat aktivitas penting yang tercatat di workspace StockWise AI.</p>
        </div>
        <div class="relative w-full sm:w-72">
            <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
            <input wire:model.live.debounce.300ms="search" class="input pl-9" placeholder="Cari action..." aria-label="Cari audit log">
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Action</th><th class="px-5 py-3">Pengguna</th><th class="px-5 py-3">Target</th><th class="px-5 py-3">IP</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="align-top hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $log->created_at?->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $log->action }}</span></td>
                            <td class="px-5 py-4 font-medium text-slate-700">{{ $log->user?->name ?? 'Sistem' }}<div class="text-xs font-normal text-slate-400">{{ $log->user?->email ?? '—' }}</div></td>
                            <td class="px-5 py-4 text-slate-600">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500"><i class="bi bi-inbox mb-2 block text-2xl text-slate-300" aria-hidden="true"></i>Belum ada audit log yang tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
