<?php

namespace Tests\Feature;

use App\Exports\ProductImportTemplateExport;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    private function makeXlsxUpload(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);

        $path = tempnam(sys_get_temp_dir(), 'import_test_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'products.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    // ─── Preview ─────────────────────────────────────────────────────────────

    public function test_preview_reports_valid_missing_name_update_and_duplicate_rows(): void
    {
        $existing = Product::factory()->create(['sku' => 'EXIST-001', 'name' => 'Tên gốc']);

        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'],
            ['Sản phẩm hợp lệ', 'NEW-001', '', 'Bia', 'Chai', 100000, ''],
            ['', 'MISSING-NAME', '', '', '', 50000, ''],
            ['Trùng với DB', 'EXIST-001', '', '', '', 0, ''],
            ['Trùng trong file 1', 'DUP-IN-FILE', '', '', '', 0, ''],
            ['Trùng trong file 2', 'DUP-IN-FILE', '', '', '', 0, ''],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('products.import-preview'), ['file' => $file])
            ->assertOk();

        $data = $response->json();

        $this->assertSame(5, $data['total']);
        $this->assertSame(2, $data['valid']); // "Sản phẩm hợp lệ" + first "Trùng trong file 1"
        $this->assertSame(1, $data['update']); // "Trùng với DB" → sẽ cập nhật
        $this->assertSame(2, $data['skipped']); // thiếu tên + trùng SKU trong file

        $byName = collect($data['rows'])->keyBy('name');
        $this->assertSame('valid', $byName['Sản phẩm hợp lệ']['status']);
        $this->assertSame('Thiếu tên sản phẩm', $this->findMissingNameReason($data['rows']));
        $this->assertSame('update', $byName['Trùng với DB']['status']);
        $this->assertStringContainsString('sẽ cập nhật', $byName['Trùng với DB']['reason']);
        $this->assertSame('valid', $byName['Trùng trong file 1']['status']);
        $this->assertSame('invalid', $byName['Trùng trong file 2']['status']);
        $this->assertStringContainsString('Trùng SKU với dòng', $byName['Trùng trong file 2']['reason']);

        // Preview không được tạo hay sửa sản phẩm nào trong DB
        $this->assertDatabaseMissing('products', ['sku' => 'NEW-001']);
        $this->assertEquals('Tên gốc', $existing->fresh()->name);
    }

    private function findMissingNameReason(array $rows): string
    {
        foreach ($rows as $row) {
            if ($row['sku'] === 'MISSING-NAME') {
                return $row['reason'];
            }
        }
        return '';
    }

    public function test_preview_requires_file(): void
    {
        $this->actingAs($this->admin)
            ->post(route('products.import-preview'), [])
            ->assertSessionHasErrors('file');
    }

    public function test_supervisor_cannot_preview_import(): void
    {
        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU'],
            ['Sản phẩm', 'SKU-X'],
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('products.import-preview'), ['file' => $file])
            ->assertForbidden();
    }

    // ─── Import thật ─────────────────────────────────────────────────────────

    public function test_import_creates_new_rows_and_skips_invalid(): void
    {
        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'],
            ['Sản phẩm mới', 'NEW-002', '', 'Bia', 'Chai', 120000, 'Mô tả test'],
            ['', 'NO-NAME', '', '', '', 0, ''],
        ]);

        $this->actingAs($this->admin)
            ->post(route('products.import'), ['file' => $file])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'NEW-002', 'name' => 'Sản phẩm mới']);
        $this->assertDatabaseMissing('products', ['sku' => 'NO-NAME']);
    }

    public function test_import_creates_inventory_row_for_new_product(): void
    {
        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'],
            ['Sản phẩm mới có tồn kho', 'NEW-INV-001', '', '', 'Cái', 10000, ''],
        ]);

        $this->actingAs($this->admin)
            ->post(route('products.import'), ['file' => $file])
            ->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'NEW-INV-001')->first();
        $this->assertNotNull($product);
        $this->assertDatabaseHas('inventory', ['product_id' => $product->id, 'quantity' => 0]);
    }

    public function test_import_updates_existing_product_when_sku_matches(): void
    {
        $existing = Product::factory()->create([
            'sku'           => 'EXIST-002',
            'name'          => 'Tên cũ',
            'default_price' => 1000,
        ]);

        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'],
            ['Tên đã cập nhật', 'EXIST-002', '', 'Bia', 'Chai', 250000, 'Mô tả mới'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('products.import'), ['file' => $file])
            ->assertRedirect(route('products.index'));

        // Không tạo thêm sản phẩm mới — chỉ cập nhật bản ghi hiện có
        $this->assertSame(1, Product::where('sku', 'EXIST-002')->count());

        $fresh = $existing->fresh();
        $this->assertEquals('Tên đã cập nhật', $fresh->name);
        $this->assertEquals(250000, $fresh->default_price);
        $this->assertEquals('Mô tả mới', $fresh->description);
    }

    public function test_import_skips_row_with_sku_belonging_to_trashed_product(): void
    {
        $trashed = Product::factory()->create(['sku' => 'TRASHED-001']);
        $trashed->delete();

        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU', 'Barcode', 'Danh mục', 'Đơn vị tính', 'Giá mặc định', 'Mô tả'],
            ['Không thể tạo', 'TRASHED-001', '', '', '', 0, ''],
        ]);

        $this->actingAs($this->admin)
            ->post(route('products.import'), ['file' => $file])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['sku' => 'TRASHED-001', 'deleted_at' => null]);
    }

    public function test_supervisor_cannot_import(): void
    {
        $file = $this->makeXlsxUpload([
            ['Tên sản phẩm (*)', 'SKU'],
            ['Sản phẩm', 'SKU-Y'],
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('products.import'), ['file' => $file])
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-Y']);
    }

    // ─── File mẫu ────────────────────────────────────────────────────────────

    public function test_template_download_is_valid_and_passes_preview_and_import(): void
    {
        $this->actingAs($this->admin)
            ->get(route('products.import-template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = tempnam(sys_get_temp_dir(), 'template_test_') . '.xlsx';
        $raw  = Excel::raw(new ProductImportTemplateExport(), \Maatwebsite\Excel\Excel::XLSX);
        file_put_contents($path, $raw);

        $file = new UploadedFile($path, 'mau-import-san-pham.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $preview = $this->actingAs($this->admin)
            ->post(route('products.import-preview'), ['file' => $file])
            ->assertOk()
            ->json();

        $this->assertSame(3, $preview['valid'], 'File mẫu phải có đúng 3 dòng sản phẩm ví dụ hợp lệ.');
        $this->assertSame(0, $preview['skipped']);

        $file2 = new UploadedFile($path, 'mau-import-san-pham.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($this->admin)
            ->post(route('products.import'), ['file' => $file2])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'CH-12-001']);
        $this->assertDatabaseHas('products', ['sku' => 'BIA-HEI-330']);
        $this->assertDatabaseHas('products', ['sku' => 'NGK-COCA-330']);
    }
}
