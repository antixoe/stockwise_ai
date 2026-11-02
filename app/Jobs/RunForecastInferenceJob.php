<?php

namespace App\Jobs;

use App\Models\Forecast;
use App\Models\Product;
use App\Models\SalesHistory;
use App\Services\AiForecastClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RunForecastInferenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $productId, public int $horizon) {}

    public function handle(AiForecastClient $client): void
    {
        $product = Product::findOrFail($this->productId);
        $asOf = now()->toDateString();
        $history = SalesHistory::query()->where('product_id', $product->id)
            ->whereDate('demand_date', '>=', now()->subDays(config('ai.history_days'))->toDateString())
            ->orderBy('demand_date')->get()->map(fn (SalesHistory $row) => [
                'product_id' => (string) $product->id,
                'date' => $row->demand_date->toDateString(),
                'quantity_demanded' => (float) $row->quantity_demanded,
                'quantity_sold' => $row->quantity_sold === null ? null : (float) $row->quantity_sold,
                'price' => $row->unit_price === null ? null : (float) $row->unit_price,
                'promotion' => (int) ($row->promotion_flag ?? 0),
                'stock_available' => $row->stock_available === null ? null : (float) $row->stock_available,
            ])->values()->all();

        $response = $client->forecast((string) $product->id, $this->horizon, $history, $asOf);
        $runUuid = (string) Str::uuid();
        $rows = collect($response['predictions'] ?? [])->map(fn (array $prediction) => [
            'product_id' => $product->id,
            'run_uuid' => $runUuid,
            'forecast_date' => $prediction['date'],
            'data_as_of' => $response['data_as_of'] ?? $asOf,
            'predicted_quantity' => max(0, (float) $prediction['predicted_quantity']),
            'lower_bound' => null,
            'upper_bound' => null,
            'unit' => $product->unit,
            'model_name' => $response['model_name'] ?? 'unavailable',
            'model_version' => $response['model_version'] ?? 'unavailable',
            'horizon' => (string) $this->horizon,
            'status' => $response['status'] ?? 'model_error',
            'mae' => data_get($response, 'metrics.mae'),
            'wape' => data_get($response, 'metrics.wape'),
            'input_snapshot' => json_encode(['request_id' => $response['request_id'] ?? null, 'history_rows' => count($history), 'data_type' => 'historical_sales_history'], JSON_THROW_ON_ERROR),
            'created_at' => now(), 'updated_at' => now(),
        ])->all();

        if ($rows !== []) {
            Forecast::upsert($rows, ['run_uuid', 'product_id', 'forecast_date'], ['predicted_quantity', 'data_as_of', 'model_name', 'model_version', 'status', 'mae', 'wape', 'input_snapshot', 'updated_at']);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Forecast inference job failed', ['product_id' => $this->productId, 'horizon' => $this->horizon, 'error' => $exception->getMessage()]);
    }
}
