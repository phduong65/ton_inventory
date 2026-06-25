<?php

namespace App\Http\Controllers;

use App\Exports\DestinationInventoryExport;
use App\Exports\InventoryExport;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::orderBy('name')->get();

        // ── Chế độ kho con (có destination_id) ──────────────────────────
        if ($request->filled('destination_id')) {
            return $this->destinationMode($request, $destinations);
        }

        // ── Chế độ Kho Tổng (mặc định) ──────────────────────────────────
        $query = Inventory::with(['product.category', 'product.unit'])
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->select('inventory.*')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('products.name', 'like', "%{$request->search}%")
                  ->orWhere('products.sku', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn($q) => $q->where('products.category_id', $request->category_id))
            ->when($request->has_stock, fn($q) => $q->where('inventory.quantity', '>', 0))
            ->when($request->low_stock, fn($q) => $q
                ->where('products.min_stock', '>', 0)
                ->whereColumn('inventory.quantity', '<=', 'products.min_stock')
            )
            ->orderBy('products.name');

        $items         = $query->paginate(30)->withQueryString();
        $categories    = Category::orderBy('name')->get();
        $totalValue    = Inventory::selectRaw('SUM(quantity * average_cost) as total')->value('total') ?? 0;
        $lowStockCount = Inventory::join('products', 'products.id', '=', 'inventory.product_id')
            ->where('products.min_stock', '>', 0)
            ->whereColumn('inventory.quantity', '<=', 'products.min_stock')
            ->whereNull('products.deleted_at')
            ->count();

        return view('inventory.index', compact(
            'items', 'categories', 'destinations', 'totalValue', 'lowStockCount'
        ));
    }

    /** Tồn kho lũy kế tại các kho con (tính từ stock_ledger OUT). */
    private function destinationMode(Request $request, $destinations): View
    {
        $asOf  = $request->as_of ?? now()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $ledgerRows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($asOf, $request) {
                $q->where('status', 'approved')
                  ->whereDate('date', '<=', $asOf)
                  ->where('destination_id', $request->destination_id);
            })
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->get();

        $products = Product::active()->orderBy('name')->get();

        // Group theo product_id
        $destRows = $ledgerRows
            ->groupBy('product_id')
            ->map(function ($rows) {
                $qty   = $rows->sum(fn($r) => abs($r->qty));
                $value = $rows->sum(fn($r) => abs($r->qty) * ($r->cost_price ?? 0));
                return (object) [
                    'product'  => $rows->first()->product,
                    'qty'      => $qty,
                    'avg_cost' => $qty > 0 ? $value / $qty : 0,
                    'value'    => $value,
                ];
            })
            ->filter(fn($r) => $r->product !== null && $r->qty > 0)
            ->sortBy('product.name')
            ->values();

        $activeDestination = $destinations->firstWhere('id', $request->destination_id);

        return view('inventory.index', compact(
            'destinations', 'destRows', 'activeDestination', 'asOf', 'today', 'products'
        ));
    }

    public function export(Request $request): BinaryFileResponse
    {
        // Export kho con
        if ($request->filled('destination_id')) {
            $asOf     = $request->as_of ?? now()->format('Y-m-d');
            $filename = 'ton-kho-con-' . $asOf . '.xlsx';
            return Excel::download(new DestinationInventoryExport($request->all()), $filename);
        }

        // Export Kho Tổng
        $filename = 'ton-kho-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new InventoryExport($request->only(['search', 'category_id', 'has_stock', 'low_stock'])), $filename);
    }
}
