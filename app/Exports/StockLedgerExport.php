<?php

namespace App\Exports;

use App\Models\StockLedger;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockLedgerExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        return StockLedger::with(['product', 'transaction'])
            ->when($this->filters['product_id'] ?? null, fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->when($this->filters['type'] ?? null,       fn ($q) => $q->where('type', $this->filters['type']))
            ->when($this->filters['date_from'] ?? null,  fn ($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to'] ?? null,    fn ($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Ngày', 'Số phiếu', 'Sản phẩm', 'Loại', 'SL', 'Tồn trước', 'Tồn sau', 'Giá vốn (đ)'];
    }

    public function map($row): array
    {
        $typeLabel = match($row->type) {
            'IN'         => 'Nhập',
            'OUT'        => 'Xuất',
            'ADJUSTMENT' => 'Điều chỉnh',
            default      => $row->type,
        };

        return [
            $row->created_at?->format('d/m/Y H:i'),
            $row->transaction?->code ?? 'Kiểm kê',
            $row->product?->name,
            $typeLabel,
            $row->qty,
            $row->before_qty,
            $row->after_qty,
            $row->cost_price,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
