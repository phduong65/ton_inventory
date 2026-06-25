<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Kiểm tra tại sao trang transactions/{id} không hiển thị ảnh đã upload.
 *
 * Root cause #1 (đã fix):
 *   TransactionAttachment::getUrlAttribute() gọi Storage::url($path) — dùng default
 *   disk ('local') thay vì 'public' → URL sai → <img src=""> vỡ.
 *   Fix: Storage::disk('public')->url($path).
 *
 * Root cause #2 (đã fix):
 *   Trên browser, handleFiles() clear file input sau khi đọc (e.target.value = ''),
 *   sau đó syncInput() dùng DataTransfer để set lại — không reliable cho form submit.
 *   Kết quả: form POST đến server không có file nào → DB trống.
 *   Fix: dùng fetch() + FormData, gắn files trực tiếp từ Alpine previews state.
 */
class TransactionAttachmentTest extends TestCase
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makeInboundTransaction(): Transaction
    {
        return Transaction::factory()->create([
            'type'        => 'IN',
            'status'      => 'draft',
            'supplier_id' => Supplier::factory()->create()->id,
            'created_by'  => $this->admin->id,
        ]);
    }

    /** Tạo bản ghi attachment + đặt file giả lên public disk. */
    private function attachFakeImage(Transaction $tx, string $filename = 'photo.jpg'): TransactionAttachment
    {
        $path = 'transactions/' . $tx->id . '/' . $filename;
        Storage::disk('public')->put($path, 'fake-image-content');

        return TransactionAttachment::create([
            'transaction_id' => $tx->id,
            'path'           => $path,
            'original_name'  => $filename,
            'size'           => 1024,
            'mime_type'      => 'image/jpeg',
        ]);
    }

    // ── 1. Upload → bản ghi DB ─────────────────────────────────────────────────

    public function test_store_with_image_creates_attachment_record_in_database(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $this->actingAs($this->accountant)->post(route('transactions.store'), [
            'type'        => 'IN',
            'date'        => now()->toDateString(),
            'supplier_id' => Supplier::factory()->create()->id,
            'details'     => [[
                'product_id'        => $product->id,
                'unit_id'           => $product->unit_id,
                'conversion_factor' => 1,
                'qty'               => 5,
                'price'             => 100000,
            ]],
            'images' => [UploadedFile::fake()->image('receipt.jpg', 800, 600)],
        ]);

        $tx = Transaction::latest()->first();

        $this->assertNotNull($tx, 'Transaction phải được tạo sau khi POST');
        $this->assertDatabaseHas('transaction_attachments', [
            'transaction_id' => $tx->id,
            'original_name'  => 'receipt.jpg',
            'mime_type'      => 'image/jpeg',
        ]);
    }

    // ── 2. File lưu đúng disk ──────────────────────────────────────────────────

    public function test_uploaded_image_is_stored_on_public_disk_not_local(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $this->actingAs($this->accountant)->post(route('transactions.store'), [
            'type'        => 'IN',
            'date'        => now()->toDateString(),
            'supplier_id' => Supplier::factory()->create()->id,
            'details'     => [[
                'product_id'        => $product->id,
                'unit_id'           => $product->unit_id,
                'conversion_factor' => 1,
                'qty'               => 2,
                'price'             => 50000,
            ]],
            'images' => [UploadedFile::fake()->image('bill.png')],
        ]);

        $attachment = Transaction::latest()->first()?->attachments()->first();

        $this->assertNotNull($attachment, 'Attachment record phải tồn tại trong DB');

        // File phải nằm trên disk 'public', không phải 'local'
        Storage::disk('public')->assertExists($attachment->path);
    }

    // ── 3. URL attribute – root cause ──────────────────────────────────────────

    /**
     * BUG GỐC: getUrlAttribute() gọi Storage::url($path).
     * Khi default disk là 'local', Storage::url() không trả về /storage/…
     * mà có thể throw hoặc trả về đường dẫn hệ thống file → <img> vỡ.
     *
     * FIX: Storage::disk('public')->url($path) luôn trả về /storage/…
     */
    public function test_attachment_url_attribute_uses_public_disk(): void
    {
        Storage::fake('public');

        $tx         = $this->makeInboundTransaction();
        $attachment = $this->attachFakeImage($tx, 'invoice.jpg');

        $expected = Storage::disk('public')->url($attachment->path);

        $this->assertEquals(
            $expected,
            $attachment->url,
            'getUrlAttribute() phải dùng Storage::disk(\'public\')->url() thay vì Storage::url()'
        );
    }

    public function test_attachment_url_starts_with_storage_prefix(): void
    {
        Storage::fake('public');

        $tx         = $this->makeInboundTransaction();
        $attachment = $this->attachFakeImage($tx);

        // /storage/ là web path do public disk tạo ra (có storage:link)
        $this->assertStringContainsString('/storage/', $attachment->url);
    }

    public function test_attachment_url_contains_the_file_path(): void
    {
        Storage::fake('public');

        $tx   = $this->makeInboundTransaction();
        $path = 'transactions/' . $tx->id . '/doc.jpg';
        Storage::disk('public')->put($path, 'x');

        $att = TransactionAttachment::create([
            'transaction_id' => $tx->id,
            'path'           => $path,
            'original_name'  => 'doc.jpg',
            'size'           => 10,
            'mime_type'      => 'image/jpeg',
        ]);

        $this->assertStringContainsString($path, $att->url);
    }

    // ── 4. Show page – render ảnh ─────────────────────────────────────────────

    public function test_show_page_renders_img_tag_with_correct_url(): void
    {
        Storage::fake('public');

        $tx         = $this->makeInboundTransaction();
        $attachment = $this->attachFakeImage($tx, 'receipt.jpg');

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.show', $tx));

        $response->assertOk();
        // URL phải xuất hiện trong HTML, không bị escape
        $response->assertSee($attachment->url, escape: false);
    }

    public function test_show_page_renders_all_uploaded_images(): void
    {
        Storage::fake('public');

        $tx = $this->makeInboundTransaction();
        $a1 = $this->attachFakeImage($tx, 'front.jpg');
        $a2 = $this->attachFakeImage($tx, 'back.jpg');
        $a3 = $this->attachFakeImage($tx, 'detail.jpg');

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.show', $tx));

        $response->assertOk();
        $response->assertSee('Ảnh đính kèm');
        $response->assertSee($a1->url, escape: false);
        $response->assertSee($a2->url, escape: false);
        $response->assertSee($a3->url, escape: false);
    }

    public function test_show_page_hides_attachment_section_when_no_images(): void
    {
        // Không fake storage – không có file nào được upload
        $tx = $this->makeInboundTransaction();

        $response = $this->actingAs($this->admin)
            ->get(route('transactions.show', $tx));

        $response->assertOk();
        $this->assertEquals(0, $tx->attachments()->count());
        $response->assertDontSee('Ảnh đính kèm');
    }

    // ── 5. Controller eager-load attachments ───────────────────────────────────

    public function test_show_controller_loads_attachments_relationship(): void
    {
        Storage::fake('public');

        $tx = $this->makeInboundTransaction();
        $this->attachFakeImage($tx);

        $this->actingAs($this->admin)
            ->get(route('transactions.show', $tx))
            ->assertOk();

        // Kiểm tra relationship được load đúng số lượng
        $freshTx = Transaction::with('attachments')->find($tx->id);
        $this->assertTrue($freshTx->relationLoaded('attachments'));
        $this->assertEquals(1, $freshTx->attachments->count());
    }

    // ── 6. isImage() helper ────────────────────────────────────────────────────

    public function test_is_image_returns_true_for_image_mime_types(): void
    {
        foreach (['image/jpeg', 'image/png', 'image/webp', 'image/gif'] as $mime) {
            $att = new TransactionAttachment(['mime_type' => $mime]);
            $this->assertTrue($att->isImage(), "isImage() phải trả về true cho {$mime}");
        }
    }

    public function test_is_image_returns_false_for_non_image_mime(): void
    {
        foreach (['application/pdf', 'text/plain', 'application/zip'] as $mime) {
            $att = new TransactionAttachment(['mime_type' => $mime]);
            $this->assertFalse($att->isImage(), "isImage() phải trả về false cho {$mime}");
        }
    }

    // ── 7. Upload nhiều ảnh cùng lúc ──────────────────────────────────────────

    public function test_store_creates_one_attachment_per_uploaded_file(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $this->actingAs($this->accountant)->post(route('transactions.store'), [
            'type'        => 'IN',
            'date'        => now()->toDateString(),
            'supplier_id' => Supplier::factory()->create()->id,
            'details'     => [[
                'product_id'        => $product->id,
                'unit_id'           => $product->unit_id,
                'conversion_factor' => 1,
                'qty'               => 3,
                'price'             => 30000,
            ]],
            'images' => [
                UploadedFile::fake()->image('img1.jpg'),
                UploadedFile::fake()->image('img2.jpg'),
                UploadedFile::fake()->image('img3.jpg'),
            ],
        ]);

        $tx = Transaction::latest()->first();
        $this->assertEquals(3, $tx->attachments()->count());
    }
}
