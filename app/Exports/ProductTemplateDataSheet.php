<?php

namespace App\Exports;

use App\Exports\Concerns\FormatsProductSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateDataSheet implements FromArray, WithTitle, WithEvents
{
    use FormatsProductSheet;

    private const HEADER_ROW = 1;
    private const FIRST_DATA_ROW = 2;
    private const SAMPLE_ROWS = 3;
    private const LAST_STYLED_ROW = 300;

    public function __construct(private array $categories, private array $units)
    {
    }

    public function title(): string
    {
        return 'Sản phẩm';
    }

    public function array(): array
    {
        return [
            $this->productHeadings(),
            ['Chivas Regal 12 năm', 'CH-12-001', '8935049501234', $this->categories[0] ?? 'Rượu', $this->units[0] ?? 'Chai', 850000, 'Whisky Scotland 12 năm tuổi'],
            ['Heineken lon 330ml', 'BIA-HEI-330', '8934588123456', $this->categories[1] ?? 'Bia', $this->units[1] ?? 'Thùng', 350000, 'Bia lon, thùng 24 lon'],
            ['Coca Cola lon 330ml', 'NGK-COCA-330', '8934588654321', $this->categories[2] ?? 'Nước ngọt', $this->units[2] ?? 'Thùng', 180000, 'Nước ngọt có gas'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = self::FIRST_DATA_ROW + self::SAMPLE_ROWS - 1;

                $this->styleProductHeader($sheet, self::HEADER_ROW);

                // Sample data rows styling — tô vàng để người dùng biết cần xóa trước khi import
                $sampleRange = 'A' . self::FIRST_DATA_ROW . ':G' . $lastDataRow;
                $sheet->getStyle($sampleRange)->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '92400E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF9C3']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('F' . self::FIRST_DATA_ROW . ':F' . $lastDataRow)
                    ->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('G' . self::FIRST_DATA_ROW . ':G' . $lastDataRow)
                    ->getAlignment()->setWrapText(true);

                // Vùng trống sẵn sàng để nhập tay dữ liệu thật
                $blankRange = 'A' . ($lastDataRow + 1) . ':G' . self::LAST_STYLED_ROW;
                $sheet->getStyle($blankRange)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                ]);
                $sheet->getStyle('F' . ($lastDataRow + 1) . ':F' . self::LAST_STYLED_ROW)
                    ->getNumberFormat()->setFormatCode('#,##0');

                // Ghi chú trên từng cột header — nhập tay, không dùng dropdown
                $notes = [
                    'A' => 'Bắt buộc. Tên sản phẩm không được để trống.',
                    'B' => 'Không bắt buộc. Nên nhập để tránh trùng sản phẩm khi import.',
                    'C' => 'Không bắt buộc. Mã vạch sản phẩm.',
                    'D' => 'Không bắt buộc. Gõ tên danh mục. Nếu danh mục chưa tồn tại, hệ thống sẽ tự tạo mới.',
                    'E' => 'Không bắt buộc. Gõ tên đơn vị tính. Mặc định là "Cái" nếu để trống.',
                    'F' => 'Chỉ nhập số, ví dụ 150000. Không nhập dấu chấm/phẩy hoặc chữ "đ".',
                    'G' => 'Không bắt buộc. Mô tả ngắn về sản phẩm.',
                ];
                foreach ($notes as $col => $text) {
                    $comment = $sheet->getComment($col . self::HEADER_ROW);
                    $comment->getText()->createTextRun($text);
                    $comment->setWidth('220pt');
                    $comment->setHeight('60pt');
                }

                $sheet->setSelectedCell('A' . self::FIRST_DATA_ROW);
            },
        ];
    }
}
