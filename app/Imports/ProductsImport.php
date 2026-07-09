<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ProductsImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $imported = 0; // sản phẩm tạo mới
    public int $updated  = 0; // sản phẩm được cập nhật (trùng SKU)
    public int $skipped  = 0;

    public function model(array $row): ?Product
    {
        $name = trim($row['ten_san_pham'] ?? $row['name'] ?? '');
        if (!$name) { $this->skipped++; return null; }

        $sku = trim($row['sku'] ?? '');

        $unitName = trim($row['don_vi_tinh'] ?? $row['unit'] ?? 'Cái');
        $unit = Unit::firstWhere('name', $unitName)
            ?? Unit::create(['name' => $unitName, 'code' => $this->generateUnitCode($unitName)]);

        $categoryName = trim($row['danh_muc'] ?? $row['category'] ?? '');
        $category = null;
        if ($categoryName) {
            $category = Category::firstOrCreate(['name' => $categoryName, 'parent_id' => null]);
        }

        $attributes = [
            'name'          => $name,
            'barcode'       => trim($row['barcode'] ?? '') ?: null,
            'category_id'   => $category?->id,
            'unit_id'       => $unit->id,
            'default_price' => (float) preg_replace('/[^0-9.]/', '', (string) ($row['gia_mac_dinh'] ?? $row['default_price'] ?? 0)),
            'description'   => trim($row['mo_ta'] ?? $row['description'] ?? '') ?: null,
        ];

        // Trùng SKU với sản phẩm đang hoạt động → cập nhật thay vì tạo mới
        if ($sku) {
            $existing = Product::where('sku', $sku)->first();
            if ($existing) {
                $existing->update($attributes);
                $this->updated++;
                return null;
            }

            // SKU thuộc về sản phẩm đã xóa mềm → không thể tạo mới (trùng khóa duy nhất trong DB)
            if (Product::onlyTrashed()->where('sku', $sku)->exists()) {
                $this->skipped++;
                return null;
            }
        }

        $product = Product::create($attributes + [
            'sku'    => $sku ?: null,
            'status' => 'active',
        ]);

        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->imported++;

        return null;
    }

    private function generateUnitCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, '_')) ?: 'DVT';
        $code = $base;
        $suffix = 1;
        while (Unit::where('code', $code)->exists()) {
            $suffix++;
            $code = $base . '_' . $suffix;
        }

        return substr($code, 0, 20);
    }
}
