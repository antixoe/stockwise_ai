<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CatalogService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}
    public function index() { $this->authorize('viewAny', Category::class); return response()->json(Category::query()->orderBy('name')->paginate(25)); }
    public function store(StoreCategoryRequest $request) { $category = $this->catalog->createCategory($request->validated(), $request); return response()->json($category, 201); }
    public function show(Category $category) { $this->authorize('view', $category); return response()->json($category); }
    public function update(UpdateCategoryRequest $request, Category $category) { $this->authorize('update', $category); return response()->json($this->catalog->updateCategory($category, $request->validated(), $request)); }
    public function destroy(Category $category) { abort(405, 'Kategori tidak dihapus permanen; nonaktifkan melalui update.'); }
}
