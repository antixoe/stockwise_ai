<?php

namespace App\Livewire\Forecasts;

use App\Jobs\RunForecastInferenceJob;
use App\Models\Forecast;
use App\Models\Product;
use App\Models\SalesHistory;
use Illuminate\Support\Collection;
use Livewire\Component;

class ForecastManager extends Component
{
    public ?int $productId = null;
    public int $horizon = 7;
    public Collection $products;
    public Collection $forecasts;
    public array $chart = ['labels' => [], 'actual' => [], 'forecast' => []];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin', 'manager'), 403);
        $this->products = Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'sku', 'name']);
        $this->productId = $this->products->first()?->id;
        $this->loadForecasts();
    }

    public function updatedProductId(): void { $this->loadForecasts(); }

    public function runForecast(): void
    {
        $this->validate(['productId' => ['required', 'integer', 'exists:products,id'], 'horizon' => ['required', 'integer', 'in:7,30']]);
        RunForecastInferenceJob::dispatch($this->productId, $this->horizon);
        session()->flash('status', 'Forecast dijadwalkan. Hasil akan muncul setelah worker menyelesaikan proses.');
    }

    private function loadForecasts(): void
    {
        $this->forecasts = $this->productId ? Forecast::where('product_id', $this->productId)->where('status', 'success')->latest('created_at')->limit(30)->get() : collect();
        $actual = $this->productId ? SalesHistory::where('product_id', $this->productId)->orderBy('demand_date')->latest('demand_date')->limit(90)->get()->reverse() : collect();
        $this->chart = ['labels' => $this->forecasts->pluck('forecast_date')->map->toDateString()->values(), 'actual' => $actual->pluck('quantity_demanded')->map(fn ($v) => (float) $v)->values(), 'forecast' => $this->forecasts->pluck('predicted_quantity')->map(fn ($v) => (float) $v)->values()];
    }

    public function render()
    {
        return view('livewire.forecasts.forecast-manager')->layout('layouts.app', ['title' => 'Demand Forecasting']);
    }
}
