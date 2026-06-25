<?php

namespace App\Exports;

use App\Models\StockLedger;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IssuesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        $from = $this->filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $this->filters['date_to']   ?? now()->format('Y-m-d');

        return StockLedger::with(['product.category', 'product.unit', 'transaction.destination'])
            ->where('type', 'OUT')
            ->whereHas('transaction', function ($q) use ($from, $to) {
                $q->where('status', 'approved')
                  ->whereBetween('date', [$from, $to])
                  ->when($this->filters['destination_id'] ?? null, fn ($q) => $q->where('destination_id', $this->filters['destination_id']));
            })
            ->when($this->filters['product_id'] ?? null, fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->get()
            ->sortByDesc(fn ($r) => $r->transaction?->date)
            ->values();
    }

    public function headings(): array
    {
        return ['Ngày', 'Số phiếu', 'Điểm nhận', 'Danh mục', 'Sản phẩm', 'ĐVT', 'SL xuất', 'Giá vốn', 'Giá trị'];
    }

    public function map($row): array
    {
        $qty = abs($row->qty);
        return [
            $row->transaction?->date?->format('d/m/Y'),
            $row->transaction?->code,
            $row->transaction?->destination?->name,
            $row->product?->category?->name,
            $row->product?->name,
            $row->product?->unit?->name,
            $qty,
            $row->cost_price,
            $qty * $row->cost_price,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
