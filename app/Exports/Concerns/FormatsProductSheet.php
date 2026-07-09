<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Style dùng chung cho sheet "Sản phẩm" ở cả file mẫu (ProductTemplateDataSheet)
 * và file xuất Excel (ProductsExport) — đảm bảo hai file đồng nhất giao diện
 * và có cùng cấu trúc cột nên có thể sửa rồi import lại được.
 */
trait FormatsProductSheet
{
    protected function productHeadings(): array
    {
        return ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'];
    }

    protected function productColumnWidths(): array
    {
        return ['A' => 30, 'B' => 16, 'C' => 18, 'D' => 20, 'E' => 15, 'F' => 16, 'G' => 36];
    }

    protected function styleProductHeader(Worksheet $sheet, int $headerRow = 1): void
    {
        $range = 'A' . $headerRow . ':G' . $headerRow;

        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '15803D']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        foreach ($this->productColumnWidths() as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->setAutoFilter($range);
        $sheet->getSheetView()->setZoomScale(100);
    }

    protected function styleProductDataRows(Worksheet $sheet, int $firstRow, int $lastRow): void
    {
        $range = 'A' . $firstRow . ':G' . $lastRow;

        $sheet->getStyle($range)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("F{$firstRow}:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G{$firstRow}:G{$lastRow}")->getAlignment()->setWrapText(true);
    }
}
