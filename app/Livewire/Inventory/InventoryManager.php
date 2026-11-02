<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\WithPagination;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InventoryManager extends Component
{
    use WithPagination;
    public string $search = '';
    public string $status = 'all';
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }
    public function render()
    {
        return view('livewire.inventory.inventory-manager', ['products' => Product::with(['inventory', 'category'])->where('is_active', true)->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('sku', 'like', '%'.$this->search.'%')))->when($this->status === 'critical', fn ($q) => $q->whereHas('inventory', fn ($i) => $i->whereColumn('on_hand_quantity', '<=', 'products.reorder_point')))->when($this->status === 'safe', fn ($q) => $q->whereHas('inventory', fn ($i) => $i->whereColumn('on_hand_quantity', '>', 'products.reorder_point')))->orderBy('name')->paginate(12)]);
    }
}
