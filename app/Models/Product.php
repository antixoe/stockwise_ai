<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'sku', 'name', 'unit', 'unit_price', 'reorder_point', 'safety_stock', 'minimum_order_quantity', 'order_multiple', 'is_active'];
    protected function casts(): array { return ['unit_price' => 'decimal:4', 'reorder_point' => 'decimal:3', 'safety_stock' => 'decimal:3', 'minimum_order_quantity' => 'decimal:3', 'order_multiple' => 'decimal:3', 'is_active' => 'boolean']; }
    public function category() { return $this->belongsTo(Category::class); }
    public function suppliers() { return $this->belongsToMany(Supplier::class, 'product_suppliers')->using(ProductSupplier::class)->withPivot(['supplier_sku', 'lead_time_days', 'minimum_order_quantity', 'order_multiple', 'last_unit_cost', 'is_primary'])->withTimestamps(); }
    public function productSuppliers() { return $this->hasMany(ProductSupplier::class); }
    public function inventory() { return $this->hasOne(Inventory::class); }
    public function stockTransactions() { return $this->hasMany(StockTransaction::class); }
    public function salesHistories() { return $this->hasMany(SalesHistory::class); }
    public function forecasts() { return $this->hasMany(Forecast::class); }
    public function recommendations() { return $this->hasMany(Recommendation::class); }
}
