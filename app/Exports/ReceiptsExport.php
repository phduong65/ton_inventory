<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceiptsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        $from = $this->filters['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $this->filters['date_to']   ?? now()->format('Y-m-d');

        return TransactionDetail::with(['product.category', 'transaction.supplier'])
            ->whereHas('transaction', function ($q) use ($from, $to) {
                $q->where('type', 'IN')
                  ->where('status', 'approved')
                  ->whereBetween('date', [$from, $to])
                  ->when($this->filters['supplier_id'] ?? null, fn ($q) => $q->where('supplier_id', $this->filters['supplier_id']));
            })
            ->when($this->filters['product_id'] ?? null, fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->get()
            ->sortByDesc(fn ($r) => $r->transaction?->date)
            ->values();
    }

    public function headings(): array
    {
        return ['Ngày', 'Số phiếu', 'Nhà cung cấp', 'Danh mục', 'Sản phẩm', 'ĐVT', 'SL', 'Đơn giá', 'CK%', 'VAT%', 'Thành tiền'];
    }

    public function map($row): array
    {
        return [
            $row->transaction?->date?->format('d/m/Y'),
            $row->transaction?->code,
            $row->transaction?->supplier?->name,
            $row->product?->category?->name,
            $row->product?->name,
            $row->product?->unit,
            $row->qty,
            $row->price,
            $row->discount,
            $row->vat,
            $row->amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
