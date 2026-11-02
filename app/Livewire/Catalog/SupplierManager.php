<?php

namespace App\Livewire\Catalog;

use App\Models\Supplier;
use App\Services\CatalogService;
use Livewire\WithPagination;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierManager extends Component
{
    use WithPagination;
    public string $search = '';
    public bool $showForm = false;
    public ?int $editingId = null;
    public array $form = ['code' => '', 'name' => '', 'contact_name' => '', 'email' => '', 'phone' => '', 'default_lead_time_days' => 0, 'is_active' => true];
    protected function rules(): array { $unique = $this->editingId ? ','.$this->editingId : ''; return ['form.code' => ['required', 'max:255', 'unique:suppliers,code'.$unique], 'form.name' => ['required', 'max:255'], 'form.contact_name' => ['nullable', 'max:255'], 'form.email' => ['nullable', 'email'], 'form.phone' => ['nullable', 'max:64'], 'form.default_lead_time_days' => ['nullable', 'numeric', 'min:0']]; }
    public function mount(): void { abort_unless(auth()->user()->hasRole('admin'), 403); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function create(): void { $this->resetForm(); $this->showForm = true; }
    public function edit(int $id): void { $supplier = Supplier::findOrFail($id); $this->editingId = $id; $this->form = $supplier->only(array_keys($this->form)); $this->showForm = true; }
    public function save(CatalogService $catalog): void { $this->validate(); $this->editingId ? $catalog->updateSupplier(Supplier::findOrFail($this->editingId), $this->form) : $catalog->createSupplier($this->form); $this->showForm = false; $this->resetForm(); $this->dispatch('notify', message: 'Supplier berhasil disimpan.', type: 'success'); }
    private function resetForm(): void { $this->editingId = null; $this->form = ['code' => '', 'name' => '', 'contact_name' => '', 'email' => '', 'phone' => '', 'default_lead_time_days' => 0, 'is_active' => true]; $this->resetValidation(); }
    public function render()
    {
        return view('livewire.catalog.supplier-manager', ['suppliers' => Supplier::withCount('products')->where('is_active', true)->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('code', 'like', '%'.$this->search.'%'))->orderBy('name')->paginate(10)]);
    }
}
