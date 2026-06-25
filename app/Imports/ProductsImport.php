<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ProductsImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $imported = 0;
    public int $skipped  = 0;

    public function model(array $row): ?Product
    {
        $name = trim($row['ten_san_pham'] ?? $row['name'] ?? '');
        if (!$name) { $this->skipped++; return null; }

        $sku = trim($row['sku'] ?? '');
        if ($sku && Product::withTrashed()->where('sku', $sku)->exists()) {
            $this->skipped++;
            return null;
        }

        $unitName = trim($row['don_vi_tinh'] ?? $row['unit'] ?? 'Cái');
        $unit = Unit::firstOrCreate(['name' => $unitName]);

        $categoryName = trim($row['danh_muc'] ?? $row['category'] ?? '');
        $category = null;
        if ($categoryName) {
            $category = Category::firstOrCreate(['name' => $categoryName, 'parent_id' => null]);
        }

        $this->imported++;

        return new Product([
            'name'          => $name,
            'sku'           => $sku ?: null,
            'barcode'       => trim($row['barcode'] ?? '') ?: null,
            'category_id'   => $category?->id,
            'unit_id'       => $unit->id,
            'default_price' => (float) preg_replace('/[^0-9.]/', '', $row['gia_mac_dinh'] ?? $row['default_price'] ?? 0),
            'description'   => trim($row['mo_ta'] ?? $row['description'] ?? '') ?: null,
            'status'        => 'active',
        ]);
    }
}
