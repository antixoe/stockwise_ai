<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    /** @use HasFactory<\Database\Factories\RecommendationFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'forecast_id', 'supplier_id', 'recommendation_uuid', 'status', 'recommended_order_date', 'expected_receipt_date', 'reorder_quantity', 'demand_during_lead_time', 'on_hand_at_calculation', 'reserved_at_calculation', 'incoming_at_calculation', 'safety_stock_at_calculation', 'lead_time_days', 'rule_version', 'input_snapshot', 'rationale', 'generated_at'];
    protected function casts(): array { return ['recommended_order_date' => 'date', 'expected_receipt_date' => 'date', 'reorder_quantity' => 'decimal:3', 'demand_during_lead_time' => 'decimal:3', 'on_hand_at_calculation' => 'decimal:3', 'reserved_at_calculation' => 'decimal:3', 'incoming_at_calculation' => 'decimal:3', 'safety_stock_at_calculation' => 'decimal:3', 'lead_time_days' => 'decimal:2', 'input_snapshot' => 'array', 'generated_at' => 'datetime']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function forecast() { return $this->belongsTo(Forecast::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function decision() { return $this->hasOne(RecommendationDecision::class); }
}
