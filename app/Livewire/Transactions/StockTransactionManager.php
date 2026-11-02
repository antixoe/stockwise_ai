<?php

namespace App\Livewire\Transactions;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockTransactionService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Livewire\WithPagination;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class StockTransactionManager extends Component
{
    use WithPagination;
    public string $type = 'receipt';
    public array $form = ['product_id' => '', 'quantity' => '', 'unit_cost' => '', 'occurred_at' => '', 'reference' => '', 'notes' => ''];
    public string $search = '';
    public bool $confirming = false;
    public ?string $errorMessage = null;
    protected function rules(): array { return ['type' => ['required', 'in:receipt,issue'], 'form.product_id' => ['required', 'exists:products,id'], 'form.quantity' => ['required', 'numeric', 'gt:0'], 'form.unit_cost' => ['nullable', 'numeric', 'min:0'], 'form.occurred_at' => ['required', 'date'], 'form.reference' => ['nullable', 'max:255'], 'form.notes' => ['nullable', 'string']]; }
    public function mount(): void { abort_unless(auth()->user()->hasRole('admin', 'warehouse_staff'), 403); $this->form['occurred_at'] = today()->toDateString(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function askConfirmation(): void { $this->validate(); $this->errorMessage = null; $this->confirming = true; }
    public function save(StockTransactionService $service): void { $this->validate(); $this->confirming = false; $this->errorMessage = null; try { $service->post([...$this->form, 'type' => $this->type, 'idempotency_key' => (string) Str::uuid()], auth()->id()); $this->reset('form'); $this->form['occurred_at'] = today()->toDateString(); $this->dispatch('notify', message: 'Transaksi stok berhasil dicatat.', type: 'success'); } catch (InsufficientStockException|QueryException $e) { $this->errorMessage = $e instanceof InsufficientStockException ? $e->getMessage() : 'Transaksi gagal disimpan. Periksa data dan coba lagi.'; } }
    public function render()
    {
        return view('livewire.transactions.stock-transaction-manager', ['products' => Product::where('is_active', true)->orderBy('name')->get(), 'transactions' => StockTransaction::with(['product', 'user'])->when($this->search, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$this->search.'%')->orWhere('sku', 'like', '%'.$this->search.'%')))->latest('occurred_at')->paginate(10)]);
    }
}
