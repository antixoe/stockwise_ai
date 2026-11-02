<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesHistory extends Model
{
    /** @use HasFactory<\Database\Factories\SalesHistoryFactory> */
    use HasFactory;

    protected $table = 'sales_history';
    protected $fillable = ['product_id', 'demand_date', 'quantity_demanded', 'quantity_sold', 'unit_price', 'promotion_flag', 'stock_available', 'source', 'features'];
    protected function casts(): array { return ['demand_date' => 'date', 'quantity_demanded' => 'decimal:3', 'quantity_sold' => 'decimal:3', 'unit_price' => 'decimal:4', 'promotion_flag' => 'boolean', 'stock_available' => 'decimal:3', 'features' => 'array']; }
    public function product() { return $this->belongsTo(Product::class); }
}
