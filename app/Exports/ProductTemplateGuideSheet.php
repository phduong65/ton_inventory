<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateGuideSheet implements FromArray, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Hướng dẫn';
    }

    public function array(): array
    {
        return [
            ['HƯỚNG DẪN IMPORT SẢN PHẨM', ''],
            [''],
            ['Cột', 'Mô tả'],
            ['ten_san_pham', 'Bắt buộc. Tên sản phẩm, không được để trống.'],
            ['sku', 'Không bắt buộc, nhưng nên nhập để tránh tạo trùng sản phẩm. Nếu SKU đã tồn tại, dòng đó sẽ bị bỏ qua.'],
            ['barcode', 'Không bắt buộc. Mã vạch sản phẩm.'],
            ['danh_muc', 'Không bắt buộc. Gõ tên danh mục. Nếu danh mục chưa có, hệ thống sẽ tự tạo mới.'],
            ['don_vi_tinh', 'Không bắt buộc, mặc định là "Cái" nếu để trống. Gõ tên đơn vị tính.'],
            ['gia_mac_dinh', 'Không bắt buộc. Chỉ nhập số (VD: 150000), không nhập dấu chấm/phẩy hoặc chữ "đ".'],
            ['mo_ta', 'Không bắt buộc. Mô tả ngắn về sản phẩm.'],
            [''],
            ['LƯU Ý', ''],
            ['1.', 'Không thay đổi tên các cột ở dòng tiêu đề trong sheet "Sản phẩm".'],
            ['2.', 'Xóa các dòng ví dụ (tô màu vàng nhạt) trước khi nhập dữ liệu thật.'],
            ['3.', 'Mỗi dòng tương ứng với 1 sản phẩm.'],
            ['4.', 'Sau khi điền xong, lưu file (.xlsx) và tải lên ở màn hình Import Excel.'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle('A3:B3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
                ]);

                $sheet->getStyle('A12:B12')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
                ]);

                $sheet->getStyle('A4:B10')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                ]);
                $sheet->getStyle('A4:A10')->applyFromArray(['font' => ['bold' => true]]);

                $sheet->getStyle('A13:B16')->applyFromArray([
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                ]);

                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(85);

                foreach (range(4, 10) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(30);
                }

                $sheet->getSheetView()->setZoomScale(100);
                $sheet->freezePane('A4');
            },
        ];
    }
}
