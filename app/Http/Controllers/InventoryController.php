<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}
    public function index(Request $request) { $this->authorize('viewAny', Inventory::class); return response()->json($this->inventory->summary($request->integer('category_id') ?: null)); }
    public function show(Inventory $inventory) { $this->authorize('view', $inventory); return response()->json($inventory->load('product')); }
}
