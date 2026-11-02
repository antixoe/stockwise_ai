<?php

namespace App\Livewire\Recommendations;

use App\Models\Product;
use App\Models\Recommendation;
use App\Services\RecommendationDecisionService;
use App\Services\RestockDecisionService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Throwable;

class RecommendationManager extends Component
{
    public ?int $productId = null;
    public ?int $decisionRecommendationId = null;
    public string $decision = 'approved';
    public string $comment = '';
    public $finalQuantity = null;
    public Collection $products;
    public Collection $recommendations;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin', 'manager'), 403);
        $this->products = Product::where('is_active', true)->orderBy('name')->get(['id', 'sku', 'name']);
        $this->loadRecommendations();
    }

    public function generate(): void
    {
        $this->validate(['productId' => ['required', 'exists:products,id']]);
        try { app(RestockDecisionService::class)->generate(Product::findOrFail($this->productId)); session()->flash('status', 'Rekomendasi dihitung dari forecast dan posisi persediaan saat ini.'); $this->loadRecommendations(); }
        catch (Throwable $exception) { $this->addError('productId', $exception->getMessage()); }
    }

    public function openDecision(int $id, string $decision): void
    {
        $recommendation = Recommendation::findOrFail($id);
        $this->authorize('decide', $recommendation);
        $this->decisionRecommendationId = $id; $this->decision = $decision; $this->comment = ''; $this->finalQuantity = (float) $recommendation->reorder_quantity;
    }

    public function submitDecision(): void
    {
        $this->validate(['decisionRecommendationId' => ['required', 'exists:recommendations,id'], 'decision' => ['required', 'in:approved,rejected'], 'finalQuantity' => ['nullable', 'numeric', 'min:0'], 'comment' => ['nullable', 'string', 'max:2000']]);
        try { $recommendation = Recommendation::findOrFail($this->decisionRecommendationId); $this->authorize('decide', $recommendation); app(RecommendationDecisionService::class)->decide($recommendation, auth()->user(), $this->decision, (float) $this->finalQuantity, $this->comment ?: null); session()->flash('status', 'Keputusan tersimpan dan tercatat pada audit log.'); $this->decisionRecommendationId = null; $this->loadRecommendations(); }
        catch (Throwable $exception) { $this->addError('decisionRecommendationId', $exception->getMessage()); }
    }

    private function loadRecommendations(): void { $this->recommendations = Recommendation::with(['product', 'supplier', 'decision.user'])->latest('generated_at')->limit(50)->get(); }
    public function render() { return view('livewire.recommendations.recommendation-manager')->layout('layouts.app', ['title' => 'Rekomendasi Restock']); }
}
