<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $query = StockLedger::with(['product', 'transaction'])
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc');

        $ledgers  = $query->paginate(30)->withQueryString();
        $products = Product::active()->orderBy('name')->get();

        return view('stock-ledger.index', compact('ledgers', 'products'));
    }
}
