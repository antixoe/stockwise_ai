<?php

namespace App\Livewire\AuditLogs;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = AuditLog::with('user')
            ->when($this->search, fn ($query) => $query->where('action', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(20);

        return view('livewire.audit-logs.audit-log-manager', compact('logs'))
            ->layout('layouts.app', ['title' => 'Audit log']);
    }
}
