<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\CatalogService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}
    public function index() { $this->authorize('viewAny', Supplier::class); return response()->json(Supplier::withCount('products')->orderBy('name')->paginate(25)); }
    public function store(StoreSupplierRequest $request) { return response()->json($this->catalog->createSupplier($request->validated(), $request), 201); }
    public function show(Supplier $supplier) { $this->authorize('view', $supplier); return response()->json($supplier->load('products')); }
    public function update(UpdateSupplierRequest $request, Supplier $supplier) { $this->authorize('update', $supplier); return response()->json($this->catalog->updateSupplier($supplier, $request->validated(), $request)); }
    public function attachProduct(Request $request, Supplier $supplier, Product $product) { $this->authorize('update', $supplier); $data = $request->validate(['lead_time_days' => ['required', 'numeric', 'min:0'], 'minimum_order_quantity' => ['nullable', 'numeric', 'gt:0'], 'order_multiple' => ['nullable', 'numeric', 'gt:0'], 'last_unit_cost' => ['nullable', 'numeric', 'min:0'], 'is_primary' => ['sometimes', 'boolean'], 'supplier_sku' => ['nullable', 'string', 'max:255']]); $this->catalog->syncSupplier($product, $supplier, $data); return response()->json(['message' => 'Relasi supplier berhasil disimpan.']); }
    public function destroy(Supplier $supplier) { abort(405, 'Supplier tidak dihapus permanen; nonaktifkan melalui update.'); }
}
