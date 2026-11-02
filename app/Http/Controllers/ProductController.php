<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\CatalogService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}
    public function index(Request $request) { $this->authorize('viewAny', Product::class); return response()->json(Product::with(['category', 'inventory', 'suppliers'])->when($request->string('search')->toString(), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")))->orderBy('name')->paginate(25)); }
    public function store(StoreProductRequest $request) { $product = $this->catalog->createProduct($request->validated(), $request); return response()->json($product->load('inventory'), 201); }
    public function show(Product $product) { $this->authorize('view', $product); return response()->json($product->load(['category', 'inventory', 'suppliers'])); }
    public function update(UpdateProductRequest $request, Product $product) { $this->authorize('update', $product); return response()->json($this->catalog->updateProduct($product, $request->validated(), $request)); }
    public function destroy(Product $product) { abort(405, 'Produk tidak dihapus permanen; nonaktifkan melalui update.'); }
}
