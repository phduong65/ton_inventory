<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockLedger;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function approve(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if (! $transaction->isPending()) {
                throw new \RuntimeException('Phiếu này đã được xử lý hoặc không ở trạng thái chờ duyệt.');
            }

            match ($transaction->type) {
                'IN'         => $this->processInbound($transaction),
                'OUT'        => $this->processOutbound($transaction),
                'ADJUSTMENT' => $this->processAdjustment($transaction),
            };

            $transaction->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaction)
                ->log('approved');
        });
    }

    public function reject(Transaction $transaction, string $reason): void
    {
        DB::transaction(function () use ($transaction, $reason) {
            $transaction = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if (! $transaction->isPending()) {
                throw new \RuntimeException('Phiếu này không ở trạng thái chờ duyệt.');
            }

            $transaction->update([
                'status'          => 'rejected',
                'rejected_reason' => $reason,
                'approved_by'     => auth()->id(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaction)
                ->withProperties(['reason' => $reason])
                ->log('rejected');
        });
    }

    public function submit(Transaction $transaction): void
    {
        if (! $transaction->isDraft()) {
            throw new \RuntimeException('Chỉ có thể submit phiếu ở trạng thái nháp.');
        }

        $transaction->update(['status' => 'pending']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($transaction)
            ->log('submitted');
    }

    private function processInbound(Transaction $transaction): void
    {
        $transaction->load('details');

        foreach ($transaction->details as $detail) {
            $inventory = Inventory::lockForUpdate()->firstOrCreate(
                ['product_id' => $detail->product_id],
                ['quantity' => 0, 'average_cost' => 0]
            );

            $beforeQty = $inventory->quantity;

            $totalValue = ($inventory->quantity * $inventory->average_cost)
                        + ($detail->qty * $detail->price);
            $newQty     = $inventory->quantity + $detail->qty;
            $newAvgCost = $newQty > 0 ? $totalValue / $newQty : $detail->price;

            $inventory->update([
                'quantity'     => $newQty,
                'average_cost' => $newAvgCost,
                'updated_at'   => now(),
            ]);

            StockLedger::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $detail->product_id,
                'type'           => 'IN',
                'qty'            => $detail->qty,
                'before_qty'     => $beforeQty,
                'after_qty'      => $newQty,
                'cost_price'     => $detail->price,
                'created_at'     => now(),
            ]);
        }
    }

    private function processOutbound(Transaction $transaction): void
    {
        $transaction->load('details.product');

        foreach ($transaction->details as $detail) {
            $inventory = Inventory::lockForUpdate()
                ->where('product_id', $detail->product_id)
                ->first();

            if (! $inventory || $inventory->quantity < $detail->qty) {
                $available = $inventory ? $inventory->quantity : 0;
                throw new \RuntimeException(
                    "Không đủ tồn kho: {$detail->product->name} (tồn: {$available}, cần: {$detail->qty})"
                );
            }
        }

        foreach ($transaction->details as $detail) {
            $inventory = Inventory::lockForUpdate()
                ->where('product_id', $detail->product_id)
                ->first();

            $beforeQty = $inventory->quantity;
            $newQty    = $inventory->quantity - $detail->qty;

            $inventory->update([
                'quantity'   => $newQty,
                'updated_at' => now(),
            ]);

            StockLedger::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $detail->product_id,
                'type'           => 'OUT',
                'qty'            => -$detail->qty,
                'before_qty'     => $beforeQty,
                'after_qty'      => $newQty,
                'cost_price'     => $inventory->average_cost,
                'created_at'     => now(),
            ]);
        }
    }

    private function processAdjustment(Transaction $transaction): void
    {
        $transaction->load('details');

        foreach ($transaction->details as $detail) {
            $inventory = Inventory::lockForUpdate()->firstOrCreate(
                ['product_id' => $detail->product_id],
                ['quantity' => 0, 'average_cost' => 0]
            );

            $beforeQty = $inventory->quantity;
            $newQty    = $inventory->quantity + $detail->qty;

            $inventory->update([
                'quantity'   => $newQty,
                'updated_at' => now(),
            ]);

            StockLedger::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $detail->product_id,
                'type'           => 'ADJUSTMENT',
                'qty'            => $detail->qty,
                'before_qty'     => $beforeQty,
                'after_qty'      => $newQty,
                'cost_price'     => $inventory->average_cost,
                'created_at'     => now(),
            ]);
        }
    }
}
