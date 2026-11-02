<?php

namespace App\Livewire\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Services\CatalogService;
use Livewire\WithPagination;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProductManager extends Component
{
    use WithPagination;
    public string $search = '';
    public string $categoryId = '';
    public string $sort = 'name';
    public string $direction = 'asc';
    public bool $showForm = false;
    public ?int $editingId = null;
    public array $form = ['category_id' => '', 'sku' => '', 'name' => '', 'unit' => 'pcs', 'unit_price' => 0, 'reorder_point' => 0, 'safety_stock' => 0, 'minimum_order_quantity' => 1, 'order_multiple' => 1, 'is_active' => true];
    protected function rules(): array { $unique = $this->editingId ? ','.$this->editingId : ''; return ['form.category_id' => ['required', 'exists:categories,id'], 'form.sku' => ['required', 'max:255', 'unique:products,sku'.$unique], 'form.name' => ['required', 'max:255'], 'form.unit' => ['required', 'max:32'], 'form.unit_price' => ['required', 'numeric', 'min:0'], 'form.reorder_point' => ['required', 'numeric', 'min:0'], 'form.safety_stock' => ['required', 'numeric', 'min:0'], 'form.minimum_order_quantity' => ['required', 'numeric', 'gt:0'], 'form.order_multiple' => ['required', 'numeric', 'gt:0']]; }
    public function mount(): void { abort_unless(auth()->user()->hasRole('admin'), 403); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategoryId(): void { $this->resetPage(); }
    public function sortBy(string $field): void { if ($this->sort === $field) $this->direction = $this->direction === 'asc' ? 'desc' : 'asc'; else { $this->sort = $field; $this->direction = 'asc'; } }
    public function create(): void { $this->resetForm(); $this->showForm = true; }
    public function edit(int $id): void { $product = Product::findOrFail($id); $this->editingId = $id; $this->form = $product->only(array_keys($this->form)); $this->showForm = true; }
    public function save(CatalogService $catalog): void { $this->validate(); $this->editingId ? $catalog->updateProduct(Product::findOrFail($this->editingId), $this->form) : $catalog->createProduct($this->form); $this->showForm = false; $this->resetForm(); $this->dispatch('notify', message: 'Produk berhasil disimpan.', type: 'success'); }
    public function archive(int $id): void { $product = Product::findOrFail($id); $product->update(['is_active' => false]); $this->dispatch('notify', message: 'Produk diarsipkan.', type: 'success'); }
    private function resetForm(): void { $this->editingId = null; $this->form = ['category_id' => '', 'sku' => '', 'name' => '', 'unit' => 'pcs', 'unit_price' => 0, 'reorder_point' => 0, 'safety_stock' => 0, 'minimum_order_quantity' => 1, 'order_multiple' => 1, 'is_active' => true]; $this->resetValidation(); }
    public function render()
    {
        return view('livewire.catalog.product-manager', ['products' => Product::with(['category', 'inventory'])->where('is_active', true)->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('sku', 'like', '%'.$this->search.'%')))->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))->orderBy($this->sort, $this->direction)->paginate(10), 'categories' => Category::where('is_active', true)->orderBy('name')->get()]);
    }
}
