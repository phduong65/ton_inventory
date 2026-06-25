<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending_count'     => Transaction::pending()->count(),
            'total_products'    => Inventory::where('quantity', '>', 0)->count(),
            'total_stock_value' => Inventory::selectRaw('SUM(quantity * average_cost) as total')->value('total') ?? 0,
            'today_in'          => Transaction::in()->approved()->whereDate('date', today())->count(),
            'today_out'         => Transaction::out()->approved()->whereDate('date', today())->count(),
        ];

        $recentTransactions = Transaction::with(['supplier', 'destination', 'createdBy'])
            ->latest()
            ->take(10)
            ->get();

        $chartData      = $this->getMonthlyChartData(6);
        $warehouseData  = $this->getWarehouseValueData(6);

        $lowStockItems = Inventory::with(['product'])
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->select('inventory.*')
            ->where('products.min_stock', '>', 0)
            ->whereColumn('inventory.quantity', '<=', 'products.min_stock')
            ->whereNull('products.deleted_at')
            ->where('products.status', 'active')
            ->orderByRaw('inventory.quantity / products.min_stock ASC')
            ->limit(10)
            ->get();

        return view('dashboard.index', [
            'pendingCount'       => $stats['pending_count'],
            'totalProducts'      => $stats['total_products'],
            'totalStockValue'    => $stats['total_stock_value'],
            'todayIn'            => $stats['today_in'],
            'todayOut'           => $stats['today_out'],
            'recentTransactions' => $recentTransactions,
            'chartLabels'        => $chartData['labels'],
            'chartIn'            => $chartData['in'],
            'chartOut'           => $chartData['out'],
            'warehouseLabels'    => $warehouseData['labels'],
            'warehouseDatasets'  => $warehouseData['datasets'],
            'lowStockItems'      => $lowStockItems,
        ]);
    }

    private function getMonthlyChartData(int $months): array
    {
        $labels  = [];
        $inData  = [];
        $outData = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date      = now()->subMonths($i);
            $labels[]  = $date->format('m/Y');
            $inData[]  = (int) Transaction::in()->approved()->whereYear('date', $date->year)->whereMonth('date', $date->month)->count();
            $outData[] = (int) Transaction::out()->approved()->whereYear('date', $date->year)->whereMonth('date', $date->month)->count();
        }

        return ['labels' => $labels, 'in' => $inData, 'out' => $outData];
    }

    private function getWarehouseValueData(int $months): array
    {
        $startDate   = now()->subMonths($months - 1)->startOfMonth();
        $destinations = Destination::orderBy('name')->get();

        // Labels
        $labels = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->subMonths($i)->format('m/Y');
        }

        // Kho Tổng (40): value of approved IN transactions per month
        // Group by date (DB-agnostic), then aggregate by month in PHP
        $inRawRows = DB::table('transaction_details')
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transactions.status', 'approved')
            ->where('transactions.type', 'IN')
            ->where('transactions.date', '>=', $startDate)
            ->select('transactions.date as tx_date', DB::raw('SUM(transaction_details.amount) as total'))
            ->groupBy('transactions.date')
            ->get();

        $inRowMap = [];
        foreach ($inRawRows as $row) {
            $ym = date('Y-n', strtotime($row->tx_date));
            $inRowMap[$ym] = ($inRowMap[$ym] ?? 0) + (float) $row->total;
        }

        // Build Kho Tổng values
        $inValues = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d          = now()->subMonths($i);
            $key        = $d->year . '-' . $d->month;
            $inValues[] = round($inRowMap[$key] ?? 0);
        }

        // OUT to each destination per month
        $outRawRows = DB::table('transaction_details')
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transactions.status', 'approved')
            ->where('transactions.type', 'OUT')
            ->whereNotNull('transactions.destination_id')
            ->where('transactions.date', '>=', $startDate)
            ->select('transactions.destination_id as dest_id', 'transactions.date as tx_date', DB::raw('SUM(transaction_details.amount) as total'))
            ->groupBy('transactions.destination_id', 'transactions.date')
            ->get();

        $outRows = [];
        foreach ($outRawRows as $row) {
            $ym = date('Y-n', strtotime($row->tx_date));
            $outRows[$row->dest_id][$ym] = ($outRows[$row->dest_id][$ym] ?? 0) + (float) $row->total;
        }

        $colors = ['#22c55e', '#f97316', '#8b5cf6', '#ec4899', '#06b6d4'];

        $datasets = [
            [
                'label'           => 'Kho Tổng (nhập)',
                'data'            => $inValues,
                'borderColor'     => '#3b82f6',
                'backgroundColor' => 'rgba(59,130,246,0.08)',
                'fill'            => true,
                'tension'         => 0.3,
                'pointRadius'     => 4,
                'borderWidth'     => 2,
            ],
        ];

        foreach ($destinations as $idx => $dest) {
            $destMap = $outRows[$dest->id] ?? [];

            $values = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $d        = now()->subMonths($i);
                $key      = $d->year . '-' . $d->month;
                $values[] = round($destMap[$key] ?? 0);
            }

            $color      = $colors[$idx % count($colors)];
            $datasets[] = [
                'label'           => $dest->name,
                'data'            => $values,
                'borderColor'     => $color,
                'backgroundColor' => 'rgba(0,0,0,0)',
                'fill'            => false,
                'tension'         => 0.3,
                'pointRadius'     => 4,
                'borderWidth'     => 2,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }
}
