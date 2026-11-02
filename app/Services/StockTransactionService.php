<?php

namespace App\Services;

use App\Exceptions\DuplicateStockTransactionException;
use App\Exceptions\InsufficientStockException;
use App\Models\Inventory;
use App\Models\StockTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransactionService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function post(array $data, ?int $userId = null): StockTransaction
    {
        $key = (string) ($data['idempotency_key'] ?? Str::uuid());

        return DB::transaction(function () use ($data, $userId, $key) {
            $existing = StockTransaction::where('idempotency_key', $key)->first();
            if ($existing) {
                if ($existing->product_id === (int) $data['product_id'] && (float) $existing->quantity === (float) $data['quantity']) {
                    return $existing;
                }
                throw new DuplicateStockTransactionException('Idempotency key sudah digunakan untuk transaksi berbeda.');
            }

            $inventory = Inventory::where('product_id', $data['product_id'])->lockForUpdate()->firstOrCreate(
                ['product_id' => $data['product_id']],
                ['on_hand_quantity' => 0, 'reserved_quantity' => 0, 'incoming_quantity' => 0]
            );
            $inventory = Inventory::whereKey($inventory->id)->lockForUpdate()->first();
            $quantity = (float) $data['quantity'];
            $type = $data['type'];
            $available = (float) $inventory->on_hand_quantity - (float) $inventory->reserved_quantity;
            $incoming = in_array($type, ['receipt', 'return_in', 'adjustment_in'], true);
            if (!$incoming && $available < $quantity) {
                throw new InsufficientStockException('Stok tersedia tidak mencukupi.');
            }

            $transaction = StockTransaction::create([
                ...$data,
                'idempotency_key' => $key,
                'user_id' => $userId,
                'status' => 'posted',
                'posted_at' => now(),
            ]);
            $inventory->on_hand_quantity = $incoming ? (float) $inventory->on_hand_quantity + $quantity : (float) $inventory->on_hand_quantity - $quantity;
            $inventory->version = (int) $inventory->version + 1;
            $inventory->save();
            $this->audit->record('stock_transaction.posted', $transaction, [], $transaction->only(['id', 'product_id', 'type', 'quantity']));

            return $transaction->fresh(['product']);
        }, 3);
    }

    public function reverse(StockTransaction $transaction, int $userId, string $idempotencyKey): StockTransaction
    {
        return DB::transaction(function () use ($transaction, $userId, $idempotencyKey) {
            if ($transaction->status !== 'posted' || $transaction->reversals()->exists()) {
                throw new \DomainException('Transaksi tidak dapat dibalik.');
            }
            $reverseType = in_array($transaction->type, ['receipt', 'return_in', 'adjustment_in'], true) ? 'adjustment_out' : 'adjustment_in';
            $reversal = $this->post([
                'product_id' => $transaction->product_id,
                'supplier_id' => $transaction->supplier_id,
                'type' => $reverseType,
                'quantity' => $transaction->quantity,
                'unit_cost' => $transaction->unit_cost,
                'occurred_at' => today(),
                'reference' => 'REVERSAL-'.$transaction->id,
                'notes' => 'Reversal transaksi #'.$transaction->id,
                'metadata' => ['reversal_of_id' => $transaction->id],
                'reversal_of_id' => $transaction->id,
                'idempotency_key' => $idempotencyKey,
            ], $userId);
            $transaction->update(['status' => 'reversed']);
            return $reversal;
        });
    }
}
