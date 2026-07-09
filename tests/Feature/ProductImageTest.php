<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    private function unitId(): int
    {
        return Unit::firstOrCreate(['code' => 'CAI'], ['name' => 'Cái'])->id;
    }

    // ── Upload on create ────────────────────────────────────────────────────────

    public function test_store_with_image_saves_path_on_public_disk(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('products.store'), [
            'sku'     => 'SKU-IMG-001',
            'name'    => 'Sản phẩm có ảnh',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
            'image'   => UploadedFile::fake()->image('photo.jpg', 400, 400),
        ])->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'SKU-IMG-001')->first();

        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_store_without_image_leaves_image_null(): void
    {
        $this->actingAs($this->admin)->post(route('products.store'), [
            'sku'     => 'SKU-NOIMG-001',
            'name'    => 'Sản phẩm không ảnh',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertRedirect();

        $product = Product::where('sku', 'SKU-NOIMG-001')->first();
        $this->assertNull($product->image);
        $this->assertNull($product->image_url);
    }

    public function test_image_url_accessor_uses_public_disk(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $path    = 'products/test-image.jpg';
        Storage::disk('public')->put($path, 'fake-image-content');
        $product->update(['image' => $path]);

        $expected = Storage::disk('public')->url($path);

        $this->assertEquals($expected, $product->fresh()->image_url);
        $this->assertStringContainsString('/storage/', $product->fresh()->image_url);
    }

    public function test_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('products.store'), [
            'sku'     => 'SKU-BADFILE',
            'name'    => 'File không hợp lệ',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
            'image'   => UploadedFile::fake()->create('document.pdf', 100),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-BADFILE']);
    }

    public function test_rejects_oversized_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('products.store'), [
            'sku'     => 'SKU-TOOBIG',
            'name'    => 'Ảnh quá lớn',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
            'image'   => UploadedFile::fake()->image('huge.jpg')->size(3000), // > 2048 KB
        ])->assertSessionHasErrors('image');
    }

    // ── Replace on update ───────────────────────────────────────────────────────

    public function test_update_with_new_image_replaces_old_file(): void
    {
        Storage::fake('public');

        $product  = Product::factory()->create();
        $oldPath  = 'products/old-image.jpg';
        Storage::disk('public')->put($oldPath, 'old-content');
        $product->update(['image' => $oldPath]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'     => $product->sku,
            'name'    => $product->name,
            'unit_id' => $product->unit_id,
            'status'  => 'active',
            'image'   => UploadedFile::fake()->image('new-image.jpg'),
        ])->assertRedirect();

        $fresh = $product->fresh();
        $this->assertNotEquals($oldPath, $fresh->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($fresh->image);
    }

    public function test_update_without_image_keeps_existing_image(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $path    = 'products/keep-me.jpg';
        Storage::disk('public')->put($path, 'content');
        $product->update(['image' => $path]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'     => $product->sku,
            'name'    => 'Tên mới nhưng giữ ảnh',
            'unit_id' => $product->unit_id,
            'status'  => 'active',
        ])->assertRedirect();

        $this->assertEquals($path, $product->fresh()->image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_update_with_remove_image_flag_deletes_file(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $path    = 'products/to-remove.jpg';
        Storage::disk('public')->put($path, 'content');
        $product->update(['image' => $path]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'          => $product->sku,
            'name'         => $product->name,
            'unit_id'      => $product->unit_id,
            'status'       => 'active',
            'remove_image' => '1',
        ])->assertRedirect();

        $this->assertNull($product->fresh()->image);
        Storage::disk('public')->assertMissing($path);
    }

    // ── Display ──────────────────────────────────────────────────────────────────

    public function test_index_page_renders_uploaded_image(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create(['name' => 'Sản phẩm hiển thị ảnh']);
        $path    = 'products/shown.jpg';
        Storage::disk('public')->put($path, 'content');
        $product->update(['image' => $path]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee($product->fresh()->image_url, escape: false);
    }

    public function test_index_page_shows_placeholder_when_no_image(): void
    {
        Product::factory()->create(['name' => 'Không có ảnh']);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('ph-image');
    }
}
