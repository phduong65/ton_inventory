<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        $from = $this->filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $this->filters['date_to']   ?? now()->format('Y-m-d');

        $openingLastIds = StockLedger::whereHas('transaction', function ($q) use ($from) {
            $q->where('status', 'approved')->whereDate('date', '<', $from);
        })
        ->select('product_id', DB::raw('MAX(id) as last_id'))
        ->groupBy('product_id')
        ->pluck('last_id', 'product_id');

        $openingStock = $openingLastIds->isEmpty()
            ? collect()
            : StockLedger::whereIn('id', $openingLastIds->values())->pluck('after_qty', 'product_id');

        $periodActivity = StockLedger::whereHas('transaction', function ($q) use ($from, $to) {
            $q->where('status', 'approved')->whereBetween('date', [$from, $to]);
        })
        ->select('product_id', 'type', DB::raw('SUM(qty) as total_qty'))
        ->groupBy('product_id', 'type')
        ->get()
        ->groupBy('product_id');

        $productIds = $periodActivity->keys()->merge($openingLastIds->keys())->unique();

        $products = Product::with(['category', 'unit'])
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        return $productIds->map(function ($productId) use ($products, $openingStock, $periodActivity) {
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
    }

    public function headings(): array
    {
        return ['SKU', 'Sản phẩm', 'ĐVT', 'Danh mục', 'Tồn đầu kỳ', 'Nhập', 'Xuất', 'Điều chỉnh', 'Tồn cuối kỳ'];
    }

    public function map($row): array
    {
        return [
            $row['product']->sku,
            $row['product']->name,
            $row['product']->unit?->name,
            $row['product']->category?->name,
            $row['openQty'],
            $row['inQty'],
            $row['outQty'],
            $row['adjQty'],
            $row['closeQty'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
