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

    // ─── Access ──────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_stocktake_list(): void
    {
        $this->actingAs($this->accountant)->get(route('stocktakes.index'))->assertOk();
    }

    public function test_unauthenticated_cannot_view_stocktakes(): void
    {
        $this->get(route('stocktakes.index'))->assertRedirect('/login');
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
