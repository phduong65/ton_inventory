<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q       = trim($request->q ?? '');
        $results = [];

        if (strlen($q) >= 2) {
            $results['transactions'] = Transaction::with(['supplier', 'destination'])
                ->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                          ->orWhere('note', 'like', "%{$q}%");
                })
                ->latest()
                ->limit(8)
                ->get();

            $results['products'] = Product::with(['category', 'unit'])
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('sku', 'like', "%{$q}%")
                          ->orWhere('barcode', 'like', "%{$q}%");
                })
                ->limit(8)
                ->get();

            $results['suppliers'] = Supplier::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();
        }

        return view('search.index', compact('q', 'results'));
    }
}
