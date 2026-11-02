<?php

namespace App\Livewire\Dashboard;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Overview extends Component
{
    public function render()
    {
        $periodStart = now()->subDays(13)->startOfDay();
        $transactions = StockTransaction::query()->where('occurred_at', '>=', $periodStart)->selectRaw("occurred_at, type, SUM(quantity) as total")->groupBy('occurred_at', 'type')->orderBy('occurred_at')->get();
        $labels = collect(range(13, 0))->map(fn ($days) => now()->subDays($days)->format('d M'))->values();
        $byDate = $transactions->groupBy(fn ($row) => $row->occurred_at->format('d M'));
        $chart = ['labels' => $labels, 'incoming' => $labels->map(fn ($label) => (float) $byDate->get($label, collect())->whereIn('type', ['receipt', 'return_in', 'adjustment_in'])->sum('total')), 'outgoing' => $labels->map(fn ($label) => (float) $byDate->get($label, collect())->whereIn('type', ['issue', 'return_out', 'adjustment_out'])->sum('total'))];
        return view('livewire.dashboard.overview', [
            'productCount' => Product::where('is_active', true)->count(),
            'stockTotal' => (float) DB::table('inventories')->sum('on_hand_quantity'),
            'criticalCount' => Product::where('is_active', true)->whereHas('inventory', fn ($q) => $q->whereColumn('on_hand_quantity', '<=', 'products.reorder_point'))->count(),
            'incomingCount' => StockTransaction::whereIn('type', ['receipt', 'return_in', 'adjustment_in'])->where('status', 'posted')->count(),
            'outgoingCount' => StockTransaction::whereIn('type', ['issue', 'return_out', 'adjustment_out'])->where('status', 'posted')->count(),
            'activities' => AuditLog::with('user')->latest()->limit(6)->get(),
            'lastUpdated' => StockTransaction::latest('updated_at')->value('updated_at') ?? Product::latest('updated_at')->value('updated_at'),
            'chart' => $chart,
        ]);
    }
}
