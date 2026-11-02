<?php

namespace App\Livewire\Explainability;

use App\Jobs\GenerateForecastExplanationJob;
use App\Models\Forecast;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExplanationManager extends Component
{
    public ?int $productId = null;
    public ?int $forecastId = null;
    public Collection $products;
    public Collection $forecasts;
    public ?array $explanation = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin', 'manager'), 403);
        $this->products = Product::where('is_active', true)->orderBy('name')->get(['id', 'sku', 'name']);
        $this->productId = $this->products->first()?->id;
        $this->loadForecasts();
    }

    public function updatedProductId(): void { $this->loadForecasts(); }
    public function updatedForecastId(): void { $this->loadExplanation(); }

    public function generateExplanation(): void
    {
        $this->validate(['forecastId' => ['required', 'integer', 'exists:forecasts,id']]);
        GenerateForecastExplanationJob::dispatch($this->forecastId);
        session()->flash('status', 'Penjelasan SHAP dijadwalkan. Jalankan queue worker untuk memprosesnya.');
    }

    private function loadForecasts(): void
    {
        $this->forecasts = $this->productId ? Forecast::where('product_id', $this->productId)->where('status', 'success')->orderByDesc('forecast_date')->limit(30)->get() : collect();
        $this->forecastId = $this->forecasts->first()?->id;
        $this->loadExplanation();
    }

    private function loadExplanation(): void
    {
        $this->explanation = $this->forecastId ? Forecast::with('explanation')->find($this->forecastId)?->explanation?->toArray() : null;
    }

    public function naturalLanguage(): string
    {
        $items = collect($this->explanation['feature_contributions'] ?? []);
        $positive = $items->where('direction', 'positive')->sortByDesc('contribution')->first();
        $negative = $items->where('direction', 'negative')->sortBy('contribution')->first();
        if (! $positive && ! $negative) return 'Penjelasan lokal belum memiliki kontribusi fitur yang dapat diringkas.';
        $text = 'Model memulai dari baseline dan menyesuaikannya berdasarkan fitur historis serta kalender.';
        if ($positive) $text .= ' Kontribusi terbesar yang menaikkan prediksi adalah '.($positive['label'] ?? $positive['feature']).'.';
        if ($negative) $text .= ' Kontribusi terbesar yang menurunkan prediksi adalah '.($negative['label'] ?? $negative['feature']).'.';
        return $text.' Ini adalah penjelasan perilaku model, bukan hubungan sebab-akibat.';
    }

    public function render()
    {
        return view('livewire.explainability.explanation-manager')->layout('layouts.app', ['title' => 'Explainable AI']);
    }
}
