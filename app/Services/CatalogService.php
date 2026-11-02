<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CatalogService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function createCategory(array $data, ?Request $request = null): Category
    {
        $category = Category::create($data);
        $this->audit->record('category.created', $category, [], $category->only(['id', 'name', 'code']), $request);
        return $category;
    }

    public function updateCategory(Category $category, array $data, ?Request $request = null): Category
    {
        $old = $category->only(array_keys($data));
        $category->update($data);
        $this->audit->record('category.updated', $category, $old, $category->only(array_keys($data)), $request);
        return $category->refresh();
    }

    public function createProduct(array $data, ?Request $request = null): Product
    {
        $product = Product::create($data);
        $product->inventory()->create();
        $this->audit->record('product.created', $product, [], $product->only(['id', 'sku', 'name']), $request);
        return $product;
    }

    public function updateProduct(Product $product, array $data, ?Request $request = null): Product
    {
        $old = $product->only(array_keys($data));
        $product->update($data);
        $this->audit->record('product.updated', $product, $old, $product->only(array_keys($data)), $request);
        return $product->refresh();
    }

    public function createSupplier(array $data, ?Request $request = null): Supplier
    {
        $supplier = Supplier::create($data);
        $this->audit->record('supplier.created', $supplier, [], $supplier->only(['id', 'code', 'name']), $request);
        return $supplier;
    }

    public function updateSupplier(Supplier $supplier, array $data, ?Request $request = null): Supplier
    {
        $old = $supplier->only(array_keys($data));
        $supplier->update($data);
        $this->audit->record('supplier.updated', $supplier, $old, $supplier->only(array_keys($data)), $request);
        return $supplier->refresh();
    }

    public function syncSupplier(Product $product, Supplier $supplier, array $pivot): void
    {
        $product->suppliers()->syncWithoutDetaching([$supplier->id => $pivot]);
    }
}
