<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'on_hand_quantity', 'reserved_quantity', 'incoming_quantity', 'version'];
    protected function casts(): array { return ['on_hand_quantity' => 'decimal:3', 'reserved_quantity' => 'decimal:3', 'incoming_quantity' => 'decimal:3', 'version' => 'integer']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function getAvailableQuantityAttribute(): string { return (string) ((float) $this->on_hand_quantity - (float) $this->reserved_quantity); }
}
