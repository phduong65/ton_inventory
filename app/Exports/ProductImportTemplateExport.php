<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $categories = Category::orderBy('name')->pluck('name')->unique()->values()->all();
        $units      = Unit::orderBy('name')->pluck('name')->unique()->values()->all();

        if (empty($categories)) {
            $categories = ['Rượu', 'Bia', 'Nước ngọt', 'Thực phẩm'];
        }
        if (empty($units)) {
            $units = ['Cái', 'Chai', 'Thùng', 'Kg'];
        }

        return [
            'Sản phẩm' => new ProductTemplateDataSheet($categories, $units),
            'Hướng dẫn' => new ProductTemplateGuideSheet(),
        ];
    }
}
