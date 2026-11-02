<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\StockTransactionFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'supplier_id', 'user_id', 'reversal_of_id', 'idempotency_key', 'type', 'status', 'quantity', 'unit_cost', 'occurred_at', 'reference', 'notes', 'metadata', 'posted_at'];
    protected function casts(): array { return ['quantity' => 'decimal:3', 'unit_cost' => 'decimal:4', 'occurred_at' => 'date', 'metadata' => 'array', 'posted_at' => 'datetime']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function reversalOf() { return $this->belongsTo(self::class, 'reversal_of_id'); }
    public function reversals() { return $this->hasMany(self::class, 'reversal_of_id'); }
}
