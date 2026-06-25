<?php

namespace App\Exports;

use App\Models\Destination;
use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DestinationInventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        $asOf = $this->filters['as_of'] ?? now()->format('Y-m-d');

        $ledgerRows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($asOf) {
                $q->where('status', 'approved')
                  ->whereDate('date', '<=', $asOf)
                  ->when($this->filters['destination_id'] ?? null, fn ($q) => $q->where('destination_id', $this->filters['destination_id']));
            })
            ->when($this->filters['product_id'] ?? null, fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->get();

        $destinations = Destination::orderBy('name')->get()->keyBy('id');

        $rows = collect();

        $grouped = $ledgerRows
            ->groupBy(fn ($sl) => $sl->transaction?->destination_id)
            ->map(function ($destRows) {
                return $destRows
                    ->groupBy('product_id')
                    ->map(function ($pRows) {
                        $qty   = $pRows->sum(fn ($r) => abs($r->qty));
                        $value = $pRows->sum(fn ($r) => abs($r->qty) * $r->cost_price);
                        return (object) [
                            'product'  => $pRows->first()->product,
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

        foreach ($grouped as $destId => $items) {
            $dest = $destinations->get($destId);
            foreach ($items as $item) {
                $rows->push((object) [
                    'destination' => $dest?->name ?? '—',
                    'item'        => $item,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Kho nhận', 'SKU', 'Sản phẩm', 'Danh mục', 'ĐVT', 'SL lũy kế', 'Giá vốn TB', 'Giá trị'];
    }

    public function map($row): array
    {
        return [
            $row->destination,
            $row->item->product?->sku,
            $row->item->product?->name,
            $row->item->product?->category?->name,
            $row->item->product?->unit?->name,
            $row->item->qty,
            round($row->item->avg_cost),
            round($row->item->value),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
