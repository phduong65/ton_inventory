<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending_count'    => Transaction::pending()->count(),
            'total_products'   => Inventory::where('quantity', '>', 0)->count(),
            'total_stock_value'=> Inventory::selectRaw('SUM(quantity * average_cost) as total')->value('total') ?? 0,
            'today_in'         => Transaction::in()->approved()->whereDate('date', today())->count(),
            'today_out'        => Transaction::out()->approved()->whereDate('date', today())->count(),
        ];

        $recentTransactions = Transaction::with(['supplier', 'destination', 'createdBy'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', [
            'pendingCount'    => $stats['pending_count'],
            'totalProducts'   => $stats['total_products'],
            'totalStockValue' => $stats['total_stock_value'],
            'todayIn'         => $stats['today_in'],
            'todayOut'        => $stats['today_out'],
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
