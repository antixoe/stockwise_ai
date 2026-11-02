<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductSupplier extends Pivot
{
    /** @use HasFactory<\Database\Factories\ProductSupplierFactory> */
    use HasFactory;

    protected $table = 'product_suppliers';
    protected $fillable = ['product_id', 'supplier_id', 'supplier_sku', 'lead_time_days', 'minimum_order_quantity', 'order_multiple', 'last_unit_cost', 'is_primary'];
    protected function casts(): array { return ['lead_time_days' => 'decimal:2', 'minimum_order_quantity' => 'decimal:3', 'order_multiple' => 'decimal:3', 'last_unit_cost' => 'decimal:4', 'is_primary' => 'boolean']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
}
