<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ReportOverview extends Component
{
    public string $period = '30';
    public function render()
    {
        $from = now()->subDays((int) $this->period)->startOfDay();
        return view('livewire.reports.report-overview', ['stock' => Product::with('inventory')->where('is_active', true)->orderBy('name')->limit(10)->get(), 'lowStock' => Product::with('inventory')->where('is_active', true)->whereHas('inventory', fn ($q) => $q->whereColumn('on_hand_quantity', '<=', 'products.reorder_point'))->orderBy('name')->get(), 'incoming' => StockTransaction::whereIn('type', ['receipt', 'return_in', 'adjustment_in'])->where('status', 'posted')->where('occurred_at', '>=', $from)->sum('quantity'), 'outgoing' => StockTransaction::whereIn('type', ['issue', 'return_out', 'adjustment_out'])->where('status', 'posted')->where('occurred_at', '>=', $from)->sum('quantity'), 'daily' => StockTransaction::query()->where('status', 'posted')->where('occurred_at', '>=', $from)->select('occurred_at')->selectRaw('SUM(quantity) as total')->groupBy('occurred_at')->orderBy('occurred_at')->get()]);
    }
}
