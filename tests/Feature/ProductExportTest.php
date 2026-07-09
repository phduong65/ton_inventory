<?php

namespace Tests\Feature;

use App\Exports\ProductImportTemplateExport;
use App\Exports\ProductsExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProductExportTest extends TestCase
{
    // ─── Giao diện file export phải đồng nhất với file mẫu ─────────────────────

    public function test_export_sheet_styling_matches_import_template(): void
    {
        $exportPath = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';
        file_put_contents($exportPath, Excel::raw(new ProductsExport([]), \Maatwebsite\Excel\Excel::XLSX));
        $exportSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($exportPath)->getActiveSheet();

        $templatePath = tempnam(sys_get_temp_dir(), 'template_') . '.xlsx';
        file_put_contents($templatePath, Excel::raw(new ProductImportTemplateExport(), \Maatwebsite\Excel\Excel::XLSX));
        $templateSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath)->getSheetByName('Sản phẩm');

        $this->assertSame($templateSheet->getTitle(), $exportSheet->getTitle());
        $this->assertSame($templateSheet->getFreezePane(), $exportSheet->getFreezePane());
        $this->assertSame($templateSheet->getAutoFilter()->getRange(), $exportSheet->getAutoFilter()->getRange());

        $this->assertSame(
            $templateSheet->getStyle('A1')->getFill()->getStartColor()->getRGB(),
            $exportSheet->getStyle('A1')->getFill()->getStartColor()->getRGB()
        );
        $this->assertSame(
            $templateSheet->getStyle('A1')->getFont()->getColor()->getRGB(),
            $exportSheet->getStyle('A1')->getFont()->getColor()->getRGB()
        );
        $this->assertTrue($templateSheet->getStyle('A1')->getFont()->getBold());
        $this->assertTrue($exportSheet->getStyle('A1')->getFont()->getBold());

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $col) {
            $this->assertSame(
                $templateSheet->getColumnDimension($col)->getWidth(),
                $exportSheet->getColumnDimension($col)->getWidth(),
                "Độ rộng cột {$col} phải giống nhau giữa file mẫu và file export"
            );
        }
    }

    public function test_export_headings_match_template_headings(): void
    {
        $exportPath = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';
        file_put_contents($exportPath, Excel::raw(new ProductsExport([]), \Maatwebsite\Excel\Excel::XLSX));
        $exportSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($exportPath)->getActiveSheet();

        $templatePath = tempnam(sys_get_temp_dir(), 'template_') . '.xlsx';
        file_put_contents($templatePath, Excel::raw(new ProductImportTemplateExport(), \Maatwebsite\Excel\Excel::XLSX));
        $templateSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath)->getSheetByName('Sản phẩm');

        foreach (range('A', 'G') as $col) {
            $this->assertSame(
                $templateSheet->getCell("{$col}1")->getValue(),
                $exportSheet->getCell("{$col}1")->getValue(),
                "Tiêu đề cột {$col} phải giống nhau giữa file mẫu và file export"
            );
        }
    }

    // ─── Route / permission ─────────────────────────────────────────────────────

    public function test_admin_can_download_export(): void
    {
        Product::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('products.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_accountant_can_download_export(): void
    {
        $this->actingAs($this->accountant)
            ->get(route('products.export'))
            ->assertOk();
    }

    public function test_supervisor_can_download_export(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('products.export'))
            ->assertOk();
    }

    public function test_user_without_permission_cannot_download_export(): void
    {
        $user = User::factory()->create(); // không gán role nào → không có quyền export-products

        $this->actingAs($user)
            ->get(route('products.export'))
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_download_export(): void
    {
        $this->get(route('products.export'))->assertRedirect('/login');
    }

    // ─── Nội dung export — cột phải khớp file mẫu để có thể import lại ─────────

    public function test_headings_match_import_template_columns(): void
    {
        $export = new ProductsExport([]);

        $this->assertSame(
            ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'],
            $export->headings()
        );
    }

    public function test_collection_includes_product_data(): void
    {
        $category = Category::create(['name' => 'Bia']);
        $product  = Product::factory()->create([
            'name'          => 'Sản phẩm export',
            'sku'           => 'EXPORT-001',
            'category_id'   => $category->id,
            'default_price' => 99000,
        ]);

        $export = new ProductsExport([]);
        $row    = $export->collection()->firstWhere('id', $product->id);

        $this->assertNotNull($row);
        $mapped = $export->map($row);

        $this->assertSame('Sản phẩm export', $mapped[0]);
        $this->assertSame('EXPORT-001', $mapped[1]);
        $this->assertSame('Bia', $mapped[3]);
        $this->assertEquals(99000, $mapped[5]);
    }

    public function test_export_honors_search_filter(): void
    {
        Product::factory()->create(['name' => 'Coca Cola', 'sku' => 'CC-001']);
        Product::factory()->create(['name' => 'Bia Sài Gòn', 'sku' => 'BSG-001']);

        $export = new ProductsExport(['search' => 'Coca']);
        $names  = $export->collection()->pluck('name')->all();

        $this->assertContains('Coca Cola', $names);
        $this->assertNotContains('Bia Sài Gòn', $names);
    }

    public function test_export_honors_status_filter(): void
    {
        Product::factory()->create(['name' => 'Đang bán', 'status' => 'active']);
        Product::factory()->create(['name' => 'Ngừng bán', 'status' => 'inactive']);

        $export = new ProductsExport(['status' => 'inactive']);
        $names  = $export->collection()->pluck('name')->all();

        $this->assertContains('Ngừng bán', $names);
        $this->assertNotContains('Đang bán', $names);
    }

    // ─── Round-trip: export rồi import lại phải cập nhật đúng sản phẩm ─────────

    public function test_exported_file_can_be_reimported_to_update_product(): void
    {
        $product = Product::factory()->create([
            'sku'           => 'ROUNDTRIP-001',
            'name'          => 'Tên trước khi export',
            'default_price' => 10000,
        ]);

        $raw  = Excel::raw(new ProductsExport([]), \Maatwebsite\Excel\Excel::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'export_test_') . '.xlsx';
        file_put_contents($path, $raw);

        // Mô phỏng người dùng sửa giá trong Excel rồi import lại
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        // Tìm dòng chứa SKU ROUNDTRIP-001 và sửa cột tên (A) + giá (F)
        $highestRow = $sheet->getHighestRow();
        for ($i = 2; $i <= $highestRow; $i++) {
            if ($sheet->getCell("B{$i}")->getValue() === 'ROUNDTRIP-001') {
                $sheet->setCellValue("A{$i}", 'Tên đã sửa trong Excel');
                $sheet->setCellValue("F{$i}", 88888);
                break;
            }
        }
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        $file = new \Illuminate\Http\UploadedFile($path, 'export.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($this->admin)
            ->post(route('products.import'), ['file' => $file])
            ->assertRedirect(route('products.index'));

        $fresh = $product->fresh();
        $this->assertEquals('Tên đã sửa trong Excel', $fresh->name);
        $this->assertEquals(88888, $fresh->default_price);
        // Không tạo thêm bản ghi trùng SKU
        $this->assertSame(1, Product::where('sku', 'ROUNDTRIP-001')->count());
    }
}
