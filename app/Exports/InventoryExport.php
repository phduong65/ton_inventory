<?php

namespace App\Exports;

use App\Models\Inventory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        return Inventory::with(['product.category'])
            ->join('products', 'products.id', '=', 'inventory.product_id')
            ->select('inventory.*')
            ->where('products.status', 'active')
            ->when($this->filters['search'] ?? null, fn ($q) => $q->where(function ($q) {
                $q->where('products.name', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('products.sku',  'like', '%' . $this->filters['search'] . '%');
            }))
            ->when($this->filters['category_id'] ?? null, fn ($q) => $q->where('products.category_id', $this->filters['category_id']))
            ->when($this->filters['has_stock'] ?? null, fn ($q) => $q->where('inventory.quantity', '>', 0))
            ->orderBy('products.name')
            ->get();
    }

    public function headings(): array
    {
        return ['SKU', 'Tên sản phẩm', 'Danh mục', 'ĐVT', 'SL tồn', 'Giá vốn (đ)', 'Giá trị tồn (đ)'];
    }

    public function map($row): array
    {
        return [
            $row->product?->sku,
            $row->product?->name,
            $row->product?->category?->name ?? '—',
            $row->product?->unit,
            $row->quantity,
            $row->average_cost,
            round($row->quantity * $row->average_cost),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
