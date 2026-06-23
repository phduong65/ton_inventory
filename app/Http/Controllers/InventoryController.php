<?php

namespace App\Http\Controllers;

use App\Exports\InventoryExport;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inventory::with(['product.category'])
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->select('inventory.*')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('products.name', 'like', "%{$request->search}%")
                  ->orWhere('products.sku', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn($q) => $q->where('products.category_id', $request->category_id))
            ->when($request->has_stock, fn($q) => $q->where('inventory.quantity', '>', 0))
            ->orderBy('products.name');

        $items      = $query->paginate(30)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $totalValue = Inventory::selectRaw('SUM(quantity * average_cost) as total')->value('total') ?? 0;

        return view('inventory.index', compact('items', 'categories', 'totalValue'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filename = 'ton-kho-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new InventoryExport($request->only(['search', 'category_id', 'has_stock'])), $filename);
    }
}
