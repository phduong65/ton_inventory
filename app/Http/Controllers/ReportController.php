<?php

namespace App\Http\Controllers;

use App\Exports\DestinationInventoryExport;
use App\Exports\IssuesExport;
use App\Exports\ReceiptsExport;
use App\Exports\SummaryExport;
use App\Models\Destination;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function receipts(Request $request): View
    {
        $from = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->date_to   ?? now()->format('Y-m-d');

        $rows = TransactionDetail::with(['product.category', 'product.unit', 'transaction.supplier'])
            ->whereHas('transaction', function ($q) use ($from, $to, $request) {
                $q->where('type', 'IN')
                  ->where('status', 'approved')
                  ->whereBetween('date', [$from, $to])
                  ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id));
            })
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->get()
            ->sortByDesc(fn ($r) => $r->transaction?->date?->format('Y-m-d') . '-' . str_pad($r->transaction_id, 10, '0', STR_PAD_LEFT))
            ->values();

        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::active()->orderBy('name')->get();

        return view('reports.receipts', compact('rows', 'suppliers', 'products', 'from', 'to'));
    }

    public function issues(Request $request): View
    {
        $from = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->date_to   ?? now()->format('Y-m-d');

        $rows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($from, $to, $request) {
                $q->where('status', 'approved')
                  ->whereBetween('date', [$from, $to])
                  ->when($request->destination_id, fn ($q) => $q->where('destination_id', $request->destination_id));
            })
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->get()
            ->sortByDesc(fn ($r) => $r->transaction?->date?->format('Y-m-d') . '-' . str_pad($r->transaction_id, 10, '0', STR_PAD_LEFT))
            ->values();

        $destinations = Destination::all();
        $products     = Product::active()->orderBy('name')->get();

        return view('reports.issues', compact('rows', 'destinations', 'products', 'from', 'to'));
    }

    public function inventory(Request $request): View
    {
        $asOf         = $request->as_of ?? now()->format('Y-m-d');
        $today        = now()->format('Y-m-d');
        $destinations = Destination::orderBy('name')->get();

        // ── Chế độ kho con ───────────────────────────────────────────────
        if ($request->filled('destination_id')) {
            $ledgerRows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
                ->where('type', 'OUT')
                ->whereHas('transaction', function ($q) use ($asOf, $request) {
                    $q->where('status', 'approved')
                      ->whereDate('date', '<=', $asOf)
                      ->where('destination_id', $request->destination_id);
                })
                ->get();

            $destItems = $ledgerRows
                ->groupBy('product_id')
                ->map(function ($rows) {
                    $qty   = $rows->sum(fn ($r) => abs($r->qty));
                    $value = $rows->sum(fn ($r) => abs($r->qty) * ($r->cost_price ?? 0));
                    return (object) [
                        'product'      => $rows->first()->product,
                        'quantity'     => $qty,
                        'average_cost' => $qty > 0 ? $value / $qty : 0,
                    ];
                })
                ->filter(fn ($r) => $r->product !== null && $r->quantity > 0)
                ->sortBy('product.name')
                ->values();

            $totalValue        = $destItems->sum(fn ($i) => $i->quantity * $i->average_cost);
            $activeDestination = $destinations->firstWhere('id', $request->destination_id);

            return view('reports.inventory', compact(
                'destItems', 'activeDestination', 'destinations', 'asOf', 'today', 'totalValue'
            ));
        }

        // ── Chế độ Kho Tổng (40) ─────────────────────────────────────────
        if ($asOf >= $today) {
            $items = Inventory::with(['product.category', 'product.unit'])
                ->whereHas('product', fn ($q) => $q->where('status', 'active'))
                ->get()
                ->map(fn ($inv) => (object) [
                    'product'      => $inv->product,
                    'quantity'     => $inv->quantity,
                    'average_cost' => $inv->average_cost,
                ])
                ->sortBy('product.name')
                ->values();
        } else {
            $lastIds = StockLedger::whereHas('transaction', function ($q) use ($asOf) {
                $q->where('status', 'approved')->whereDate('date', '<=', $asOf);
            })
            ->select('product_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('product_id')
            ->pluck('last_id', 'product_id');

            $items = StockLedger::with(['product.category', 'product.unit'])
                ->whereIn('id', $lastIds->isEmpty() ? [0] : $lastIds->values())
                ->get()
                ->map(fn ($sl) => (object) [
                    'product'      => $sl->product,
                    'quantity'     => $sl->after_qty,
                    'average_cost' => $sl->cost_price,
                ])
                ->filter(fn ($i) => $i->product !== null && $i->quantity > 0)
                ->sortBy('product.name')
                ->values();
        }

        $totalValue = $items->sum(fn ($i) => $i->quantity * $i->average_cost);

        return view('reports.inventory', compact('items', 'destinations', 'asOf', 'today', 'totalValue'));
    }

    public function summary(Request $request): View
    {
        $from = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->date_to   ?? now()->format('Y-m-d');

        // Opening stock: last stock_ledger entry per product before period start
        $openingLastIds = StockLedger::whereHas('transaction', function ($q) use ($from) {
            $q->where('status', 'approved')->whereDate('date', '<', $from);
        })
        ->select('product_id', DB::raw('MAX(id) as last_id'))
        ->groupBy('product_id')
        ->pluck('last_id', 'product_id');

        $openingStock = $openingLastIds->isEmpty()
            ? collect()
            : StockLedger::whereIn('id', $openingLastIds->values())->pluck('after_qty', 'product_id');

        // Activity in period grouped by product + type
        $periodActivity = StockLedger::whereHas('transaction', function ($q) use ($from, $to) {
            $q->where('status', 'approved')->whereBetween('date', [$from, $to]);
        })
        ->select('product_id', 'type', DB::raw('SUM(qty) as total_qty'))
        ->groupBy('product_id', 'type')
        ->get()
        ->groupBy('product_id');

        $productIds = $periodActivity->keys()
            ->merge($openingLastIds->keys())
            ->unique();

        $products = Product::with(['category', 'unit'])
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $rows = $productIds->map(function ($productId) use ($products, $openingStock, $periodActivity) {
            $product  = $products->get($productId);
            if (! $product) return null;

            $activity = $periodActivity->get($productId, collect());
            $inQty    = (float) $activity->where('type', 'IN')->sum('total_qty');
            $outQty   = abs((float) $activity->where('type', 'OUT')->sum('total_qty'));
            $adjQty   = (float) $activity->where('type', 'ADJUSTMENT')->sum('total_qty');
            $openQty  = (float) $openingStock->get($productId, 0);
            $closeQty = $openQty + $inQty - $outQty + $adjQty;

            return compact('product', 'openQty', 'inQty', 'outQty', 'adjQty', 'closeQty');
        })->filter()->values();

        return view('reports.summary', compact('rows', 'from', 'to'));
    }

    public function internalDebt(Request $request): View
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        $destinations = Destination::all()->keyBy('id');

        $rows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($month, $year) {
                $q->where('status', 'approved')
                  ->whereMonth('date', $month)
                  ->whereYear('date', $year);
            })
            ->get();

        $reportRows = $rows->groupBy('product_id')->map(function ($items) use ($destinations) {
            $product  = $items->first()->product;
            $byDest   = $items->groupBy(fn ($sl) => $sl->transaction?->destination_id);
            $destData = [];

            foreach ($destinations as $destId => $dest) {
                $destItems        = $byDest->get($destId, collect());
                $destData[$destId] = [
                    'qty'   => (float) abs($destItems->sum('qty')),
                    'value' => (float) abs($destItems->sum(fn ($sl) => $sl->qty * $sl->cost_price)),
                ];
            }

            $totalValue = collect($destData)->sum('value');
            return compact('product', 'destData', 'totalValue');
        })->filter(fn ($r) => $r['product'] !== null)
          ->sortBy('product.name')
          ->values();

        $destTotals = [];
        foreach ($destinations as $destId => $dest) {
            $destTotals[$destId] = [
                'qty'   => $reportRows->sum(fn ($r) => $r['destData'][$destId]['qty']   ?? 0),
                'value' => $reportRows->sum(fn ($r) => $r['destData'][$destId]['value'] ?? 0),
            ];
        }

        $grandTotal = collect($destTotals)->sum('value');

        return view('reports.internal-debt', compact(
            'reportRows', 'destinations', 'destTotals', 'grandTotal', 'month', 'year'
        ));
    }

    public function exportReceipts(Request $request): BinaryFileResponse
    {
        $from = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->date_to   ?? now()->format('Y-m-d');

        return Excel::download(new ReceiptsExport($request->all()), "bao-cao-nhap-{$from}_{$to}.xlsx");
    }

    public function exportIssues(Request $request): BinaryFileResponse
    {
        $from = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->date_to   ?? now()->format('Y-m-d');

        return Excel::download(new IssuesExport($request->all()), "bao-cao-xuat-{$from}_{$to}.xlsx");
    }

    public function exportSummary(Request $request): BinaryFileResponse
    {
        $from = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->date_to   ?? now()->format('Y-m-d');

        return Excel::download(new SummaryExport($request->all()), "nhap-xuat-ton-{$from}_{$to}.xlsx");
    }

    public function destinationInventory(Request $request): View
    {
        $asOf  = $request->as_of ?? now()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $ledgerRows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($asOf, $request) {
                $q->where('status', 'approved')
                  ->whereDate('date', '<=', $asOf)
                  ->when($request->destination_id, fn ($q) => $q->where('destination_id', $request->destination_id));
            })
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->get();

        $destinations = Destination::orderBy('name')->get();
        $products     = Product::active()->orderBy('name')->get();

        // Group: destination_id → product_id → { product, qty, avg_cost, value }
        $grouped = $ledgerRows
            ->groupBy(fn ($sl) => $sl->transaction?->destination_id)
            ->map(function ($destRows) {
                return $destRows
                    ->groupBy('product_id')
                    ->map(function ($rows) {
                        $qty   = $rows->sum(fn ($r) => abs($r->qty));
                        $value = $rows->sum(fn ($r) => abs($r->qty) * $r->cost_price);
                        return (object) [
                            'product'  => $rows->first()->product,
                            'qty'      => $qty,
                            'avg_cost' => $qty > 0 ? $value / $qty : 0,
                            'value'    => $value,
                        ];
                    })
                    ->filter(fn ($r) => $r->product !== null && $r->qty > 0)
                    ->sortBy('product.name')
                    ->values();
            })
            ->filter(fn ($rows) => $rows->isNotEmpty());

        return view('reports.destination-inventory', compact(
            'grouped', 'destinations', 'asOf', 'today', 'products'
        ));
    }

    public function exportDestinationInventory(Request $request): BinaryFileResponse
    {
        $asOf = $request->as_of ?? now()->format('Y-m-d');

        return Excel::download(
            new DestinationInventoryExport($request->all()),
            "ton-kho-con-{$asOf}.xlsx"
        );
    }
}
