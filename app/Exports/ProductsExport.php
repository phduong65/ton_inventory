<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsProductSheet;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithEvents
{
    use FormatsProductSheet;

    private const HEADER_ROW = 1;

    public function __construct(private array $filters) {}

    public function collection(): Collection
    {
        return Product::with(['category', 'unit'])
            ->when($this->filters['search'] ?? null, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('barcode', 'like', '%' . $this->filters['search'] . '%');
            }))
            ->when($this->filters['category_id'] ?? null, fn ($q) => $q->where('category_id', $this->filters['category_id']))
            ->when($this->filters['status'] ?? null, fn ($q) => $q->where('status', $this->filters['status']))
            ->orderBy('name')
            ->get();
    }

    public function title(): string
    {
        return 'Sản phẩm';
    }

    public function headings(): array
    {
        // Cột phải khớp chính xác với file mẫu (ProductTemplateDataSheet) để có thể import lại.
        return $this->productHeadings();
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku,
            $product->barcode,
            $product->category?->name,
            $product->unit?->name,
            $product->default_price,
            $product->description,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->styleProductHeader($sheet, self::HEADER_ROW);

                if ($lastRow > self::HEADER_ROW) {
                    $this->styleProductDataRows($sheet, self::HEADER_ROW + 1, $lastRow);
                }

                $sheet->setSelectedCell('A' . (self::HEADER_ROW + 1));
            },
        ];
    }
}
