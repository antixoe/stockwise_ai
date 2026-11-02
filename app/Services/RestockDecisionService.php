<?php

namespace App\Services;

use App\Models\Forecast;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Recommendation;
use App\Models\ProductSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RestockDecisionService
{
    public const RULE_VERSION = 'reorder-point-v1';

    public function generate(Product $product): Recommendation
    {
        return DB::transaction(function () use ($product) {
            $existing = Recommendation::where('product_id', $product->id)->where('status', 'pending')->latest('id')->first();
            if ($existing) return $existing;

            $inventory = Inventory::where('product_id', $product->id)->lockForUpdate()->first();
            if (! $inventory) $inventory = Inventory::create(['product_id' => $product->id, 'on_hand_quantity' => 0, 'reserved_quantity' => 0, 'incoming_quantity' => 0]);
            $supplierLink = ProductSupplier::with('supplier')->where('product_id', $product->id)->orderByDesc('is_primary')->first();
            $leadTime = (float) ($supplierLink?->lead_time_days ?? $supplierLink?->supplier?->default_lead_time_days ?? 0);
            if ($leadTime <= 0) throw new InvalidArgumentException('Lead time supplier harus lebih besar dari nol.');
            $forecasts = Forecast::where('product_id', $product->id)->where('status', 'success')->whereDate('forecast_date', '>=', today())->orderBy('forecast_date')->get();
            if ($forecasts->isEmpty()) throw new InvalidArgumentException('Forecast sukses belum tersedia untuk produk ini.');
            $demandDuringLeadTime = $this->demandDuringLeadTime($forecasts, $leadTime);
            $safetyStock = max(0, (float) $product->safety_stock);
            $inventoryPosition = (float) $inventory->on_hand_quantity - (float) $inventory->reserved_quantity + (float) $inventory->incoming_quantity;
            $reorderPoint = $demandDuringLeadTime + $safetyStock;
            $target = $reorderPoint;
            $rawQuantity = max(0, $target - $inventoryPosition);
            $minimum = (float) ($supplierLink?->minimum_order_quantity ?? $product->minimum_order_quantity ?? 0);
            $multiple = (float) ($supplierLink?->order_multiple ?? $product->order_multiple ?? 1);
            if ($multiple <= 0) throw new InvalidArgumentException('Order multiple harus lebih besar dari nol.');
            $quantity = $rawQuantity > 0 ? max($rawQuantity, $minimum) : 0;
            $quantity = $quantity > 0 ? ceil(($quantity / $multiple) - 0.0000001) * $multiple : 0;
            $priority = $inventoryPosition <= 0 ? 'critical' : ($inventoryPosition < $reorderPoint ? 'high' : 'normal');
            $forecast = $forecasts->first();
            return Recommendation::create([
                'product_id' => $product->id, 'forecast_id' => $forecast->id, 'supplier_id' => $supplierLink?->supplier_id,
                'recommendation_uuid' => (string) Str::uuid(), 'status' => $quantity > 0 ? 'pending' : 'not_eligible',
                'recommended_order_date' => today(), 'expected_receipt_date' => today()->addDays((int) ceil($leadTime)),
                'reorder_quantity' => round($quantity, 3), 'demand_during_lead_time' => round($demandDuringLeadTime, 3),
                'on_hand_at_calculation' => $inventory->on_hand_quantity, 'reserved_at_calculation' => $inventory->reserved_quantity,
                'incoming_at_calculation' => $inventory->incoming_quantity, 'safety_stock_at_calculation' => round($safetyStock, 3),
                'lead_time_days' => $leadTime, 'rule_version' => self::RULE_VERSION,
                'input_snapshot' => ['inventory_position' => $inventoryPosition, 'reorder_point' => $reorderPoint, 'target_inventory_level' => $target, 'minimum_order_quantity' => $minimum, 'order_multiple' => $multiple, 'priority' => $priority, 'forecast_model_version' => $forecast->model_version],
                'rationale' => "Reorder point = demand selama lead time ({$demandDuringLeadTime}) + safety stock ({$safetyStock}). Inventory position = on-hand - reserved + incoming ({$inventoryPosition}). Target inventory level = reorder point; quantity dibulatkan sesuai minimum dan kelipatan order.",
                'generated_at' => now(),
            ]);
        });
    }

    public function demandDuringLeadTime($forecasts, float $leadTime): float
    {
        $days = (int) ceil($leadTime);
        $selected = $forecasts->take($days);
        if ($selected->count() >= $days) return (float) $selected->sum(fn ($forecast) => (float) $forecast->predicted_quantity);
        $average = (float) $forecasts->avg(fn ($forecast) => (float) $forecast->predicted_quantity);
        return (float) $selected->sum(fn ($forecast) => (float) $forecast->predicted_quantity) + max(0, $days - $selected->count()) * $average;
    }
}
