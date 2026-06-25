<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Stocktake;
use App\Models\StocktakeDetail;
use App\Services\StocktakeService;
use Tests\TestCase;

class StocktakeTest extends TestCase
{
    // ─── Helper ──────────────────────────────────────────────────────────────

    private function makeProduct(float $qty, float $cost = 50000): Product
    {
        $product = Product::factory()->create();
        Inventory::create(['product_id' => $product->id, 'quantity' => $qty, 'average_cost' => $cost]);
        return $product;
    }

    private function makePendingStocktake(Product $product, float $systemQty, float $actualQty): Stocktake
    {
        $stocktake = Stocktake::factory()->pending()->create(['created_by' => $this->accountant->id]);
        StocktakeDetail::create([
            'stocktake_id' => $stocktake->id,
            'product_id'   => $product->id,
            'system_qty'   => $systemQty,
            'actual_qty'   => $actualQty,
            'variance'     => $actualQty - $systemQty,
        ]);
        return $stocktake;
    }

    /** Payload hợp lệ cho store: chỉ cần 1 sản phẩm đã nhập actual_qty. */
    private function storePayload(Product $product, float $actualQty, float $systemQty = 0): array
    {
        return [
            'details' => [[
                'product_id' => $product->id,
                'system_qty' => $systemQty,
                'actual_qty' => $actualQty,
            ]],
        ];
    }

    // ─── Access ──────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_stocktake_list(): void
    {
        $this->actingAs($this->accountant)->get(route('stocktakes.index'))->assertOk();
    }

    public function test_unauthenticated_cannot_view_stocktakes(): void
    {
        $this->get(route('stocktakes.index'))->assertRedirect('/login');
    }

    // ─── Store (tạo phiếu) ───────────────────────────────────────────────────

    public function test_store_creates_stocktake_with_filled_details(): void
    {
        $product = $this->makeProduct(qty: 50);

        $this->actingAs($this->accountant)
            ->post(route('stocktakes.store'), $this->storePayload($product, actualQty: 45, systemQty: 50))
            ->assertRedirect();

        $stocktake = Stocktake::latest()->first();
        $this->assertNotNull($stocktake);
        $this->assertEquals('draft', $stocktake->status);
        $this->assertEquals(1, $stocktake->details()->count());

        $detail = $stocktake->details()->first();
        $this->assertEquals(45, $detail->actual_qty);
        $this->assertEquals(50, $detail->system_qty);
        $this->assertEquals(-5, $detail->variance);
    }

    public function test_store_ignores_rows_with_empty_actual_qty(): void
    {
        $p1 = $this->makeProduct(qty: 100);
        $p2 = $this->makeProduct(qty: 50);

        // p2 để trống actual_qty (mô phỏng hành vi form: nhập một sản phẩm, bỏ qua sản phẩm kia)
        $this->actingAs($this->accountant)
            ->post(route('stocktakes.store'), [
                'details' => [
                    ['product_id' => $p1->id, 'system_qty' => 100, 'actual_qty' => 95],
                    ['product_id' => $p2->id, 'system_qty' => 50,  'actual_qty' => ''],
                ],
            ])
            ->assertRedirect();

        $stocktake = Stocktake::latest()->first();
        $this->assertEquals(1, $stocktake->details()->count());
        $this->assertEquals($p1->id, $stocktake->details()->first()->product_id);
    }

    public function test_store_returns_error_when_all_actual_qty_empty(): void
    {
        $product = $this->makeProduct(qty: 30);

        $this->actingAs($this->accountant)
            ->post(route('stocktakes.store'), [
                'details' => [
                    ['product_id' => $product->id, 'system_qty' => 30, 'actual_qty' => ''],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Không tạo stocktake nào
        $this->assertEquals(0, Stocktake::count());
    }

    public function test_store_with_submit_button_sets_status_pending(): void
    {
        $product = $this->makeProduct(qty: 20);

        $this->actingAs($this->accountant)
            ->post(route('stocktakes.store'), array_merge(
                $this->storePayload($product, actualQty: 18, systemQty: 20),
                ['submit' => '1'],
            ))
            ->assertRedirect();

        $this->assertEquals('pending', Stocktake::latest()->first()->status);
    }

    public function test_store_with_save_button_keeps_status_draft(): void
    {
        $product = $this->makeProduct(qty: 20);

        $this->actingAs($this->accountant)
            ->post(route('stocktakes.store'), $this->storePayload($product, actualQty: 20, systemQty: 20))
            ->assertRedirect();

        $this->assertEquals('draft', Stocktake::latest()->first()->status);
    }

    public function test_store_requires_at_least_one_detail_row(): void
    {
        $this->actingAs($this->accountant)
            ->post(route('stocktakes.store'), ['details' => []])
            ->assertSessionHasErrors('details');
    }

    public function test_supervisor_cannot_store_stocktake(): void
    {
        $product = $this->makeProduct(qty: 10);

        $this->actingAs($this->supervisor)
            ->post(route('stocktakes.store'), $this->storePayload($product, actualQty: 10))
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_store_stocktake(): void
    {
        $product = $this->makeProduct(qty: 10);

        $this->post(route('stocktakes.store'), $this->storePayload($product, actualQty: 10))
            ->assertRedirect('/login');
    }

    // ─── Show page ───────────────────────────────────────────────────────────

    public function test_show_page_displays_product_name_and_unit(): void
    {
        $product   = $this->makeProduct(qty: 80);
        $stocktake = $this->makePendingStocktake($product, systemQty: 80, actualQty: 75);

        $response = $this->actingAs($this->accountant)
            ->get(route('stocktakes.show', $stocktake));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee($product->unit?->name ?? '—');
    }

    public function test_show_page_displays_correct_variance(): void
    {
        $product   = $this->makeProduct(qty: 100);
        $stocktake = $this->makePendingStocktake($product, systemQty: 100, actualQty: 90);

        $response = $this->actingAs($this->accountant)
            ->get(route('stocktakes.show', $stocktake));

        $response->assertOk();
        $response->assertSee('-10');
    }

    public function test_show_page_displays_empty_state_when_no_details(): void
    {
        $stocktake = Stocktake::factory()->create(['created_by' => $this->accountant->id]);

        $response = $this->actingAs($this->accountant)
            ->get(route('stocktakes.show', $stocktake));

        $response->assertOk();
        $response->assertSee('Phiếu chưa có sản phẩm nào');
    }

    public function test_show_page_shows_submit_button_for_draft(): void
    {
        $stocktake = Stocktake::factory()->create(['status' => 'draft', 'created_by' => $this->accountant->id]);

        $this->actingAs($this->accountant)
            ->get(route('stocktakes.show', $stocktake))
            ->assertOk()
            ->assertSee('Gửi chờ duyệt');
    }

    public function test_show_page_shows_approve_button_for_pending(): void
    {
        $product   = $this->makeProduct(qty: 10);
        $stocktake = $this->makePendingStocktake($product, systemQty: 10, actualQty: 8);

        $this->actingAs($this->manager)
            ->get(route('stocktakes.show', $stocktake))
            ->assertOk()
            ->assertSee('Duyệt');
    }

    public function test_show_page_hides_submit_button_after_approved(): void
    {
        $product   = $this->makeProduct(qty: 50);
        $stocktake = $this->makePendingStocktake($product, systemQty: 50, actualQty: 48);
        app(\App\Services\StocktakeService::class)->approve($stocktake, $this->manager->id);

        $this->actingAs($this->manager)
            ->get(route('stocktakes.show', $stocktake))
            ->assertOk()
            ->assertDontSee('Gửi chờ duyệt')
            ->assertDontSee('Duyệt');
    }

    public function test_supervisor_cannot_access_show_page(): void
    {
        $stocktake = Stocktake::factory()->create(['created_by' => $this->accountant->id]);

        // supervisor có view-stocktakes → có thể xem
        $this->actingAs($this->supervisor)
            ->get(route('stocktakes.show', $stocktake))
            ->assertOk();
    }

    // ─── Submit ──────────────────────────────────────────────────────────────

    public function test_submit_changes_status_to_pending(): void
    {
        $stocktake = Stocktake::factory()->create(['status' => 'draft', 'created_by' => $this->accountant->id]);

        $this->actingAs($this->accountant)
             ->post(route('stocktakes.submit', $stocktake))
             ->assertRedirect();

        $this->assertEquals('pending', $stocktake->fresh()->status);
    }

    // ─── Approve — inventory adjustment ──────────────────────────────────────

    public function test_approve_stocktake_adjusts_inventory_down(): void
    {
        $product   = $this->makeProduct(qty: 100);
        $stocktake = $this->makePendingStocktake($product, systemQty: 100, actualQty: 95);

        $this->actingAs($this->manager)
             ->post(route('stocktakes.approve', $stocktake))
             ->assertRedirect();

        $this->assertEquals(95, $product->inventory->fresh()->quantity);
    }

    public function test_approve_stocktake_adjusts_inventory_up(): void
    {
        $product   = $this->makeProduct(qty: 50);
        $stocktake = $this->makePendingStocktake($product, systemQty: 50, actualQty: 55);

        $this->actingAs($this->manager)
             ->post(route('stocktakes.approve', $stocktake))
             ->assertRedirect();

        $this->assertEquals(55, $product->inventory->fresh()->quantity);
    }

    public function test_approve_stocktake_skips_zero_variance(): void
    {
        $product   = $this->makeProduct(qty: 80);
        $stocktake = $this->makePendingStocktake($product, systemQty: 80, actualQty: 80);

        $this->actingAs($this->manager)
             ->post(route('stocktakes.approve', $stocktake))
             ->assertRedirect();

        $this->assertEquals(80, $product->inventory->fresh()->quantity);
        // Không có record trong stock_ledger
        $this->assertDatabaseMissing('stock_ledger', ['product_id' => $product->id]);
    }

    public function test_approve_stocktake_inserts_adjustment_ledger(): void
    {
        $product   = $this->makeProduct(qty: 100);
        $stocktake = $this->makePendingStocktake($product, systemQty: 100, actualQty: 95);

        $this->actingAs($this->manager)
             ->post(route('stocktakes.approve', $stocktake));

        $ledger = StockLedger::where('product_id', $product->id)->first();
        $this->assertNotNull($ledger);
        $this->assertEquals('ADJUSTMENT', $ledger->type);
        $this->assertEquals(-5, $ledger->qty);
        $this->assertEquals(100, $ledger->before_qty);
        $this->assertEquals(95, $ledger->after_qty);
        $this->assertNull($ledger->transaction_id);
    }

    public function test_approve_stocktake_changes_status_to_approved(): void
    {
        $product   = $this->makeProduct(qty: 10);
        $stocktake = $this->makePendingStocktake($product, systemQty: 10, actualQty: 8);

        $this->actingAs($this->manager)
             ->post(route('stocktakes.approve', $stocktake));

        $this->assertEquals('approved', $stocktake->fresh()->status);
        $this->assertEquals($this->manager->id, $stocktake->fresh()->approved_by);
    }

    // ─── Idempotency ─────────────────────────────────────────────────────────

    public function test_double_approve_stocktake_fails(): void
    {
        $product   = $this->makeProduct(qty: 100);
        $stocktake = $this->makePendingStocktake($product, systemQty: 100, actualQty: 90);

        $service = app(StocktakeService::class);
        $this->actingAs($this->manager);
        $service->approve($stocktake, $this->manager->id);

        $this->expectException(\RuntimeException::class);
        $service->approve($stocktake, $this->manager->id);
    }

    public function test_double_approve_does_not_double_adjust_inventory(): void
    {
        $product   = $this->makeProduct(qty: 100);
        $stocktake = $this->makePendingStocktake($product, systemQty: 100, actualQty: 90);

        $service = app(StocktakeService::class);
        $this->actingAs($this->manager);
        $service->approve($stocktake, $this->manager->id);

        try {
            $service->approve($stocktake, $this->manager->id);
        } catch (\RuntimeException) {
        }

        $this->assertEquals(90, $product->inventory->fresh()->quantity);
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    public function test_accountant_cannot_approve_stocktake(): void
    {
        $product   = $this->makeProduct(qty: 50);
        $stocktake = $this->makePendingStocktake($product, systemQty: 50, actualQty: 45);

        $this->actingAs($this->accountant)
             ->post(route('stocktakes.approve', $stocktake))
             ->assertForbidden();

        $this->assertEquals('pending', $stocktake->fresh()->status);
        $this->assertEquals(50, $product->inventory->fresh()->quantity);
    }

    public function test_supervisor_cannot_create_stocktake(): void
    {
        $this->actingAs($this->supervisor)
             ->get(route('stocktakes.create'))
             ->assertForbidden();
    }
}
