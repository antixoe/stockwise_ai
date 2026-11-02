<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = ['code', 'name', 'contact_name', 'email', 'phone', 'default_lead_time_days', 'is_active'];
    protected function casts(): array { return ['default_lead_time_days' => 'decimal:2', 'is_active' => 'boolean']; }
    public function products() { return $this->belongsToMany(Product::class, 'product_suppliers')->using(ProductSupplier::class)->withPivot(['supplier_sku', 'lead_time_days', 'minimum_order_quantity', 'order_multiple', 'last_unit_cost', 'is_primary'])->withTimestamps(); }
    public function productSuppliers() { return $this->hasMany(ProductSupplier::class); }
    public function stockTransactions() { return $this->hasMany(StockTransaction::class); }
    public function recommendations() { return $this->hasMany(Recommendation::class); }
}
