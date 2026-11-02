<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    /** @use HasFactory<\Database\Factories\ForecastFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'run_uuid', 'forecast_date', 'data_as_of', 'predicted_quantity', 'lower_bound', 'upper_bound', 'unit', 'model_name', 'model_version', 'horizon', 'status', 'mae', 'wape', 'input_snapshot'];
    protected function casts(): array { return ['forecast_date' => 'date', 'data_as_of' => 'date', 'predicted_quantity' => 'decimal:3', 'lower_bound' => 'decimal:3', 'upper_bound' => 'decimal:3', 'mae' => 'decimal:6', 'wape' => 'decimal:6', 'input_snapshot' => 'array']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function explanation() { return $this->hasOne(ForecastExplanation::class); }
    public function recommendations() { return $this->hasMany(Recommendation::class); }
}
