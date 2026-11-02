<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastExplanation extends Model
{
    /** @use HasFactory<\Database\Factories\ForecastExplanationFactory> */
    use HasFactory;

    protected $fillable = ['forecast_id', 'explainer_name', 'explainer_version', 'feature_contributions', 'global_importance', 'base_value', 'status', 'limitation_note'];
    protected function casts(): array { return ['feature_contributions' => 'array', 'global_importance' => 'array', 'base_value' => 'array']; }
    public function forecast() { return $this->belongsTo(Forecast::class); }
}
