<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImportPreview implements ToCollection, WithHeadingRow
{
    private const MAX_ROWS_RETURNED = 300;

    public array $rows = [];
    public int $total = 0;
    public int $valid = 0;   // sẽ tạo mới
    public int $update = 0;  // sẽ cập nhật (trùng SKU với sản phẩm đang hoạt động)
    public int $skipped = 0;
    public bool $truncated = false;

    public function collection(Collection $collection): void
    {
        $seenSkus = [];

        foreach ($collection as $index => $row) {
            $rowNumber = $index + 2; // dòng 1 là header

            $name = trim($row['ten_san_pham'] ?? $row['name'] ?? '');
            $sku = trim($row['sku'] ?? '');
            $barcode = trim($row['barcode'] ?? '');
            $category = trim($row['danh_muc'] ?? $row['category'] ?? '');
            $unit = trim($row['don_vi_tinh'] ?? $row['unit'] ?? '');
            $price = trim((string) ($row['gia_mac_dinh'] ?? $row['default_price'] ?? ''));

            // Bỏ qua hoàn toàn dòng trống (không chứa dữ liệu gì)
            if ($name === '' && $sku === '' && $barcode === '' && $category === '' && $price === '') {
                continue;
            }

            $status = 'valid';
            $reason = null;

            if ($name === '') {
                $status = 'invalid';
                $reason = 'Thiếu tên sản phẩm';
            } elseif ($sku !== '' && isset($seenSkus[$sku])) {
                $status = 'invalid';
                $reason = "Trùng SKU với dòng {$seenSkus[$sku]} trong file";
            } elseif ($sku !== '' && Product::where('sku', $sku)->exists()) {
                $status = 'update';
                $reason = 'SKU đã tồn tại — sẽ cập nhật sản phẩm hiện có';
            } elseif ($sku !== '' && Product::onlyTrashed()->where('sku', $sku)->exists()) {
                $status = 'invalid';
                $reason = 'SKU thuộc sản phẩm đã xóa — không thể tạo mới hoặc cập nhật';
            }

            if ($status !== 'invalid' && $sku !== '') {
                $seenSkus[$sku] = $rowNumber;
            }

            match ($status) {
                'valid'   => $this->valid++,
                'update'  => $this->update++,
                default   => $this->skipped++,
            };
            $this->total++;

            if (count($this->rows) < self::MAX_ROWS_RETURNED) {
                $this->rows[] = [
                    'row' => $rowNumber,
                    'name' => $name ?: null,
                    'sku' => $sku ?: null,
                    'category' => $category ?: null,
                    'unit' => $unit ?: null,
                    'price' => $price !== '' ? $price : null,
                    'status' => $status,
                    'reason' => $reason,
                ];
            } else {
                $this->truncated = true;
            }
        }
    }
}
