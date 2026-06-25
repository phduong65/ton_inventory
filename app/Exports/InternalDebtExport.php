<?php

namespace App\Exports;

use App\Models\Destination;
use App\Models\StockLedger;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InternalDebtExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private Collection $destinations;
    private Collection $reportRows;

    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        $month = (int) ($this->filters['month'] ?? now()->month);
        $year  = (int) ($this->filters['year']  ?? now()->year);

        $this->destinations = Destination::all()->keyBy('id');

        $rows = StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($month, $year) {
                $q->where('status', 'approved')
                  ->whereMonth('date', $month)
                  ->whereYear('date', $year);
            })
            ->get();

        $this->reportRows = $rows->groupBy('product_id')->map(function ($items) {
            $product = $items->first()->product;
            $byDest  = $items->groupBy(fn ($sl) => $sl->transaction?->destination_id);
            $destData = [];

            foreach ($this->destinations as $destId => $dest) {
                $destItems        = $byDest->get($destId, collect());
                $destData[$destId] = [
                    'dest_name' => $dest->name,
                    'qty'       => (float) abs($destItems->sum('qty')),
                    'value'     => (float) abs($destItems->sum(fn ($sl) => $sl->qty * $sl->cost_price)),
                ];
            }

            return ['product' => $product, 'destData' => $destData];
        })->filter(fn ($r) => $r['product'] !== null)
          ->sortBy('product.name')
          ->values();

        return $this->reportRows;
    }

    public function headings(): array
    {
        $headers = ['Danh mục', 'Tên sản phẩm', 'ĐVT'];

        foreach ($this->destinations as $dest) {
            $headers[] = $dest->name . ' - SL';
            $headers[] = $dest->name . ' - Giá trị';
        }

        $headers[] = 'Tổng giá trị';

        return $headers;
    }

    public function map($row): array
    {
        $data = [
            $row['product']->category?->name ?? '',
            $row['product']->name,
            $row['product']->unit?->name ?? '',
        ];

        $totalValue = 0;
        foreach ($row['destData'] as $dest) {
            $data[] = $dest['qty'];
            $data[] = $dest['value'];
            $totalValue += $dest['value'];
        }
        $data[] = $totalValue;

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
