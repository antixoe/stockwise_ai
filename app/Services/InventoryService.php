<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;

class InventoryService
{
    public function summary(?int $categoryId = null)
    {
        return Product::query()
            ->with(['category', 'inventory'])
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(25);
    }

    public function getOrCreate(Product $product): Inventory
    {
        return $product->inventory()->firstOrCreate([], [
            'on_hand_quantity' => 0,
            'reserved_quantity' => 0,
            'incoming_quantity' => 0,
        ]);
    }
}
