<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockTransactionRequest;
use App\Models\StockTransaction;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    public function __construct(private readonly StockTransactionService $transactions) {}
    public function index(Request $request) { $this->authorize('viewAny', StockTransaction::class); return response()->json(StockTransaction::with(['product', 'supplier', 'user'])->latest('occurred_at')->paginate(25)); }
    public function store(StoreStockTransactionRequest $request) { $this->authorize('create', StockTransaction::class); return response()->json($this->transactions->post($request->validated(), $request->user()->id), 201); }
    public function reverse(Request $request, StockTransaction $stockTransaction) { $this->authorize('reverse', $stockTransaction); $data = $request->validate(['idempotency_key' => ['required', 'uuid']]); return response()->json($this->transactions->reverse($stockTransaction, $request->user()->id, $data['idempotency_key'])); }
}
