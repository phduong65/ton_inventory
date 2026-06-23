<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockLedger;
use App\Models\Stocktake;
use Illuminate\Support\Facades\DB;

class StocktakeService
{
    public function submit(Stocktake $stocktake): void
    {
        if ($stocktake->status !== 'draft') {
            throw new \RuntimeException('Chỉ có thể submit phiếu ở trạng thái nháp.');
        }
        $stocktake->update(['status' => 'pending']);
    }

    public function approve(Stocktake $stocktake, int $approvedBy): void
    {
        DB::transaction(function () use ($stocktake) {
            $stocktake = Stocktake::lockForUpdate()->findOrFail($stocktake->id);

            if (! $stocktake->isPending()) {
                throw new \RuntimeException('Phiếu kiểm kê không ở trạng thái chờ duyệt.');
            }

            $stocktake->load('details');

            foreach ($stocktake->details as $detail) {
                if ($detail->variance == 0) {
                    continue;
                }

                $inventory = Inventory::lockForUpdate()->firstOrCreate(
                    ['product_id' => $detail->product_id],
                    ['quantity' => 0, 'average_cost' => 0]
                );

                $beforeQty = $inventory->quantity;

                $inventory->update([
                    'quantity'   => $detail->actual_qty,
                    'updated_at' => now(),
                ]);

                StockLedger::create([
                    'transaction_id' => null,
                    'product_id'     => $detail->product_id,
                    'type'           => 'ADJUSTMENT',
                    'qty'            => $detail->variance,
                    'before_qty'     => $beforeQty,
                    'after_qty'      => $detail->actual_qty,
                    'cost_price'     => $inventory->average_cost,
                    'created_at'     => now(),
                ]);
            }

            $stocktake->update([
                'status'      => 'approved',
                'approved_by' => $approvedBy,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($stocktake)
                ->log('approved');
        });
    }
}
