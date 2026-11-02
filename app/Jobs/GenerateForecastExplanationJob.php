<?php

namespace App\Jobs;

use App\Models\Forecast;
use App\Models\ForecastExplanation;
use App\Services\AiForecastClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateForecastExplanationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $forecastId) {}

    public function handle(AiForecastClient $client): void
    {
        $forecast = Forecast::with('product')->findOrFail($this->forecastId);
        if ($forecast->status !== 'success') {
            return;
        }
        $response = $client->explain((string) $forecast->product_id, $forecast->forecast_date->toDateString());
        ForecastExplanation::updateOrCreate(
            ['forecast_id' => $forecast->id],
            [
                'explainer_name' => $response['explainer_name'] ?? 'shap.TreeExplainer',
                'explainer_version' => $response['explainer_version'] ?? null,
                'feature_contributions' => $response['contributions'] ?? [],
                'global_importance' => $response['global_importance'] ?? [],
                'base_value' => ['value' => $response['baseline_prediction'] ?? null, 'reconstructed_prediction' => $response['reconstructed_prediction'] ?? null],
                'status' => $response['status'] === 'success' ? 'available' : $response['status'],
                'limitation_note' => $response['limitation_note'] ?? 'Kontribusi bukan hubungan sebab-akibat.',
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Forecast explanation job failed', ['forecast_id' => $this->forecastId, 'error' => $exception->getMessage()]);
        ForecastExplanation::updateOrCreate(['forecast_id' => $this->forecastId], ['explainer_name' => 'shap.TreeExplainer', 'status' => 'failed', 'limitation_note' => 'Explanation tidak tersedia karena service AI gagal.']);
    }
}
