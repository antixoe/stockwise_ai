<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiForecastClient
{
    public function forecast(string $productId, int $horizon, array $history, ?string $dataAsOf = null): array
    {
        if (! in_array($horizon, [7, 30], true)) {
            throw new RuntimeException('Horizon forecast tidak didukung.');
        }

        return $this->signedPost(config('ai.forecast_url'), [
            'schema_version' => 'forecast.v1',
            'product_id' => $productId,
            'horizon' => $horizon,
            'data_as_of' => $dataAsOf,
            'data' => $history,
        ]);
    }

    public function explain(string $productId, string $forecastDate): array
    {
        return $this->signedPost(config('ai.explain_url'), [
            'schema_version' => 'explain.v1',
            'product_id' => $productId,
            'forecast_date' => $forecastDate,
        ]);
    }

    private function signedPost(string $url, array $data): array
    {
        $requestId = (string) Str::uuid();
        $nonce = (string) Str::uuid();
        $timestamp = (string) now()->timestamp;
        $payload = ['request_id' => $requestId] + $data;
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $timestamp.'.'.$nonce.'.'.$body, (string) config('ai.secret'));

        return Http::withHeaders([
            'Accept' => 'application/json',
            'X-StockWise-Timestamp' => $timestamp,
            'X-StockWise-Nonce' => $nonce,
            'X-StockWise-Signature' => $signature,
        ])->timeout(config('ai.timeout'))->retry(config('ai.retry_times'), 250)->withBody($body, 'application/json')
            ->post($url)->throw()->json();
    }
}
