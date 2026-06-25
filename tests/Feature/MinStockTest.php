<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Unit;
use Tests\TestCase;

class MinStockTest extends TestCase
{
    // ─── Helper ──────────────────────────────────────────────────────────────

    private function unitId(): int
    {
        return Unit::firstOrCreate(['code' => 'CAI'], ['name' => 'Cái'])->id;
    }

    private function makeProduct(float $qty, ?float $minStock = null): Product
    {
        $product = Product::factory()->create(['min_stock' => $minStock]);
        Inventory::create(['product_id' => $product->id, 'quantity' => $qty, 'average_cost' => 50000]);
        return $product->load('inventory');
    }

    // ─── isBelowMinStock() ───────────────────────────────────────────────────

    public function test_is_below_min_stock_returns_true_when_quantity_below_threshold(): void
    {
        $product = $this->makeProduct(qty: 5, minStock: 10);
        $this->assertTrue($product->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_true_when_quantity_equals_threshold(): void
    {
        $product = $this->makeProduct(qty: 10, minStock: 10);
        $this->assertTrue($product->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_false_when_quantity_above_threshold(): void
    {
        $product = $this->makeProduct(qty: 20, minStock: 10);
        $this->assertFalse($product->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_false_when_min_stock_is_null(): void
    {
        $product = $this->makeProduct(qty: 0, minStock: null);
        $this->assertFalse($product->isBelowMinStock());
    }

    public function test_is_below_min_stock_returns_false_when_min_stock_is_zero(): void
    {
        $product = $this->makeProduct(qty: 0, minStock: 0);
        $this->assertFalse($product->isBelowMinStock());
    }

    // ─── Store — min_stock lưu đúng ──────────────────────────────────────────

    public function test_store_product_saves_min_stock(): void
    {
        $this->actingAs($this->accountant)->post(route('products.store'), [
            'sku'       => 'SKU-MIN-001',
            'name'      => 'Sản phẩm test ngưỡng',
            'unit_id'   => $this->unitId(),
            'status'    => 'active',
            'min_stock' => 20,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['sku' => 'SKU-MIN-001', 'min_stock' => 20]);
    }

    public function test_store_product_without_min_stock_saves_null(): void
    {
        $this->actingAs($this->accountant)->post(route('products.store'), [
            'sku'     => 'SKU-MIN-002',
            'name'    => 'Sản phẩm không ngưỡng',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['sku' => 'SKU-MIN-002', 'min_stock' => null]);
    }

    // ─── Update — min_stock cập nhật đúng ────────────────────────────────────

    public function test_update_product_saves_min_stock(): void
    {
        $product = Product::factory()->create(['min_stock' => null]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'       => $product->sku,
            'name'      => $product->name,
            'unit_id'   => $product->unit_id,
            'status'    => 'active',
            'min_stock' => 50,
        ])->assertRedirect();

        $this->assertEquals(50, $product->fresh()->min_stock);
    }

    public function test_update_product_can_clear_min_stock(): void
    {
        $product = Product::factory()->create(['min_stock' => 30]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'       => $product->sku,
            'name'      => $product->name,
            'unit_id'   => $product->unit_id,
            'status'    => 'active',
            'min_stock' => '', // form field cleared → ConvertEmptyStringsToNull → null
        ])->assertRedirect();

        $this->assertNull($product->fresh()->min_stock);
    }

    public function test_min_stock_must_not_be_negative(): void
    {
        $this->actingAs($this->accountant)->post(route('products.store'), [
            'sku'       => 'SKU-NEG-001',
            'name'      => 'Sản phẩm ngưỡng âm',
            'unit_id'   => $this->unitId(),
            'status'    => 'active',
            'min_stock' => -5,
        ])->assertSessionHasErrors('min_stock');

        $this->assertDatabaseMissing('products', ['sku' => 'SKU-NEG-001']);
    }

    // ─── Inventory index — lowStockCount ─────────────────────────────────────

    public function test_inventory_index_passes_correct_low_stock_count(): void
    {
        $this->makeProduct(qty: 3,  minStock: 10);  // dưới ngưỡng
        $this->makeProduct(qty: 15, minStock: 10);  // trên ngưỡng
        $this->makeProduct(qty: 0,  minStock: null); // không có ngưỡng
        $this->makeProduct(qty: 0,  minStock: 0);   // ngưỡng = 0, không tính

        $response = $this->actingAs($this->accountant)->get(route('inventory.index'));

        $response->assertOk();
        $response->assertViewHas('lowStockCount', 1);
    }

    public function test_low_stock_count_is_zero_when_all_above_threshold(): void
    {
        $this->makeProduct(qty: 50, minStock: 10);
        $this->makeProduct(qty: 20, minStock: 10);

        $response = $this->actingAs($this->accountant)->get(route('inventory.index'));

        $response->assertViewHas('lowStockCount', 0);
    }

    // ─── Inventory index — filter low_stock ──────────────────────────────────

    public function test_low_stock_filter_returns_only_below_threshold_products(): void
    {
        $below = $this->makeProduct(qty: 2,  minStock: 20);
        $above = $this->makeProduct(qty: 30, minStock: 20);

        $response = $this->actingAs($this->accountant)
            ->get(route('inventory.index', ['low_stock' => 1]));

        $response->assertOk();
        $response->assertSee($below->name);
        $response->assertDontSee($above->name);
    }

    public function test_low_stock_filter_excludes_products_without_threshold(): void
    {
        $noThreshold  = $this->makeProduct(qty: 0,  minStock: null);
        $zeroThreshold = $this->makeProduct(qty: 0, minStock: 0);
        $below        = $this->makeProduct(qty: 1,  minStock: 5);

        $response = $this->actingAs($this->accountant)
            ->get(route('inventory.index', ['low_stock' => 1]));

        $response->assertSee($below->name);
        $response->assertDontSee($noThreshold->name);
        $response->assertDontSee($zeroThreshold->name);
    }

    public function test_low_stock_filter_includes_products_at_threshold(): void
    {
        $atThreshold    = $this->makeProduct(qty: 10, minStock: 10);
        $aboveThreshold = $this->makeProduct(qty: 11, minStock: 10);

        $response = $this->actingAs($this->accountant)
            ->get(route('inventory.index', ['low_stock' => 1]));

        $response->assertSee($atThreshold->name);
        $response->assertDontSee($aboveThreshold->name);
    }

    // ─── Inventory view — hiển thị cảnh báo ──────────────────────────────────

    public function test_inventory_view_shows_warning_badge_for_below_threshold_product(): void
    {
        $this->makeProduct(qty: 3, minStock: 10);

        $response = $this->actingAs($this->accountant)->get(route('inventory.index'));

        $response->assertOk();
        // Header badge "X sản phẩm dưới ngưỡng" only appears when lowStockCount > 0
        $response->assertSee('sản phẩm dưới ngưỡng');
    }

    public function test_inventory_view_hides_warning_badge_for_above_threshold_product(): void
    {
        $this->makeProduct(qty: 20, minStock: 10); // above threshold — no badge

        $response = $this->actingAs($this->accountant)->get(route('inventory.index'));

        $response->assertOk();
        // Filter label always says "Dưới ngưỡng"; only the header badge says "sản phẩm dưới ngưỡng"
        $response->assertDontSee('sản phẩm dưới ngưỡng');
    }

    public function test_inventory_view_shows_threshold_value_in_column(): void
    {
        $this->makeProduct(qty: 5, minStock: 25);

        $response = $this->actingAs($this->accountant)->get(route('inventory.index'));

        $response->assertOk();
        $response->assertSee('25'); // cột Ngưỡng
    }

    public function test_inventory_view_shows_no_badge_when_no_threshold_set(): void
    {
        $this->makeProduct(qty: 10, minStock: null);

        $response = $this->actingAs($this->accountant)->get(route('inventory.index'));

        $response->assertOk();
        $response->assertDontSee('sản phẩm dưới ngưỡng');
    }

    // ─── Unauthenticated / permissions ───────────────────────────────────────

    public function test_unauthenticated_cannot_access_inventory(): void
    {
        $this->get(route('inventory.index'))->assertRedirect('/login');
    }

    public function test_supervisor_can_view_inventory_with_low_stock_warning(): void
    {
        $this->makeProduct(qty: 1, minStock: 10);

        $this->actingAs($this->supervisor)
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Dưới ngưỡng');
    }
}
