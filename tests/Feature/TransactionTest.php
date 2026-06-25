<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Unit;
use App\Services\TransactionService;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function makeProduct(float $qty = 0, float $cost = 0): Product
    {
        $product = Product::factory()->create();
        Inventory::create(['product_id' => $product->id, 'quantity' => $qty, 'average_cost' => $cost]);
        return $product;
    }

    private function unitId(): int
    {
        return Unit::firstOrCreate(['code' => 'CAI'], ['name' => 'Cái'])->id;
    }

    private function makePendingInbound(Product $product, float $qty, float $price): Transaction
    {
        $supplier = Supplier::factory()->create();
        $tx = Transaction::factory()->pending()->create([
            'type'        => 'IN',
            'supplier_id' => $supplier->id,
            'created_by'  => $this->admin->id,
        ]);
        TransactionDetail::create([
            'transaction_id'    => $tx->id,
            'product_id'        => $product->id,
            'unit_id'           => $product->unit_id ?? $this->unitId(),
            'conversion_factor' => 1,
            'base_qty'          => $qty,
            'qty'               => $qty,
            'price'             => $price,
            'discount'          => 0,
            'vat'               => 0,
            'amount'            => $qty * $price,
        ]);
        return $tx;
    }

    private function makePendingOutbound(Product $product, float $qty): Transaction
    {
        $destination = Destination::firstOrCreate(['name' => 'Kho 43']);
        $tx = Transaction::factory()->pending()->create([
            'type'           => 'OUT',
            'destination_id' => $destination->id,
            'created_by'     => $this->admin->id,
        ]);
        TransactionDetail::create([
            'transaction_id'    => $tx->id,
            'product_id'        => $product->id,
            'unit_id'           => $product->unit_id ?? $this->unitId(),
            'conversion_factor' => 1,
            'base_qty'          => $qty,
            'qty'               => $qty,
            'price'             => 0,
            'discount'          => 0,
            'vat'               => 0,
            'amount'            => 0,
        ]);
        return $tx;
    }

    // ─── List & Access ──────────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_transaction_list(): void
    {
        $this->actingAs($this->admin)->get(route('transactions.index'))->assertOk();
    }

    public function test_unauthenticated_cannot_view_transactions(): void
    {
        $this->get(route('transactions.index'))->assertRedirect('/login');
    }

    // ─── Create ─────────────────────────────────────────────────────────────────

    public function test_accountant_can_create_transaction(): void
    {
        $product  = $this->makeProduct();
        $supplier = Supplier::factory()->create();
        $unitId   = $product->unit_id ?? $this->unitId();

        $this->actingAs($this->accountant)->post(route('transactions.store'), [
            'type'        => 'IN',
            'date'        => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'details'     => [[
                'product_id'        => $product->id,
                'unit_id'           => $unitId,
                'conversion_factor' => 1,
                'qty'               => 10,
                'price'             => 50000,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('transactions', ['type' => 'IN', 'status' => 'draft']);
    }

    public function test_supervisor_cannot_create_transaction(): void
    {
        $product  = $this->makeProduct();
        $supplier = Supplier::factory()->create();
        $unitId   = $product->unit_id ?? $this->unitId();

        $this->actingAs($this->supervisor)->post(route('transactions.store'), [
            'type'        => 'IN',
            'date'        => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'details'     => [[
                'product_id'        => $product->id,
                'unit_id'           => $unitId,
                'conversion_factor' => 1,
                'qty'               => 10,
                'price'             => 50000,
            ]],
        ])->assertForbidden();
    }

    public function test_store_validation_fails_without_details(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->accountant)->post(route('transactions.store'), [
            'type'        => 'IN',
            'date'        => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'details'     => [],
        ])->assertSessionHasErrors('details');
    }

    // ─── Submit ─────────────────────────────────────────────────────────────────

    public function test_submit_changes_status_to_pending(): void
    {
        $product = $this->makeProduct();
        $supplier = Supplier::factory()->create();
        $tx = Transaction::factory()->create([
            'type'        => 'IN',
            'status'      => 'draft',
            'supplier_id' => $supplier->id,
            'created_by'  => $this->accountant->id,
        ]);
        TransactionDetail::create(['transaction_id' => $tx->id, 'product_id' => $product->id, 'unit_id' => $this->unitId(), 'conversion_factor' => 1, 'base_qty' => 5, 'qty' => 5, 'price' => 10000, 'discount' => 0, 'vat' => 0, 'amount' => 50000]);

        $this->actingAs($this->accountant)
             ->post(route('transactions.submit', $tx))
             ->assertRedirect();

        $this->assertEquals('pending', $tx->fresh()->status);
    }

    // ─── Approve Inbound ────────────────────────────────────────────────────────

    public function test_approve_inbound_increases_inventory(): void
    {
        $product = $this->makeProduct(qty: 0, cost: 0);
        $tx      = $this->makePendingInbound($product, qty: 50, price: 100000);

        $this->actingAs($this->manager)->post(route('transactions.approve', $tx));

        $this->assertEquals(50, $product->inventory->fresh()->quantity);
        $this->assertEquals('approved', $tx->fresh()->status);
    }

    public function test_approve_inbound_updates_moving_average_cost(): void
    {
        $product = $this->makeProduct(qty: 100, cost: 50000);
        $tx      = $this->makePendingInbound($product, qty: 50, price: 60000);

        $this->actingAs($this->manager)->post(route('transactions.approve', $tx));

        $inventory = $product->inventory->fresh();
        $this->assertEquals(150, $inventory->quantity);
        // (100*50000 + 50*60000) / 150 = 53333.33
        $this->assertEqualsWithDelta(53333.33, $inventory->average_cost, 1.0);
    }

    public function test_approve_inbound_inserts_stock_ledger(): void
    {
        $product = $this->makeProduct(qty: 10, cost: 50000);
        $tx      = $this->makePendingInbound($product, qty: 30, price: 80000);

        $this->actingAs($this->manager)->post(route('transactions.approve', $tx));

        $ledger = StockLedger::where('product_id', $product->id)->latest('id')->first();
        $this->assertNotNull($ledger);
        $this->assertEquals('IN', $ledger->type);
        $this->assertEquals(30, $ledger->qty);
        $this->assertEquals(10, $ledger->before_qty);
        $this->assertEquals(40, $ledger->after_qty);
    }

    // ─── Approve Outbound ───────────────────────────────────────────────────────

    public function test_approve_outbound_decreases_inventory(): void
    {
        $product = $this->makeProduct(qty: 100, cost: 50000);
        $tx      = $this->makePendingOutbound($product, qty: 30);

        $this->actingAs($this->manager)->post(route('transactions.approve', $tx));

        $this->assertEquals(70, $product->inventory->fresh()->quantity);
    }

    public function test_approve_outbound_inserts_stock_ledger_with_negative_qty(): void
    {
        $product = $this->makeProduct(qty: 50, cost: 40000);
        $tx      = $this->makePendingOutbound($product, qty: 20);

        $this->actingAs($this->manager)->post(route('transactions.approve', $tx));

        $ledger = StockLedger::where('product_id', $product->id)->latest('id')->first();
        $this->assertEquals('OUT', $ledger->type);
        $this->assertEquals(-20, $ledger->qty);
        $this->assertEquals(50, $ledger->before_qty);
        $this->assertEquals(30, $ledger->after_qty);
        $this->assertEquals(40000, $ledger->cost_price);
    }

    public function test_approve_outbound_fails_when_insufficient_stock(): void
    {
        $product = $this->makeProduct(qty: 5, cost: 50000);
        $tx      = $this->makePendingOutbound($product, qty: 10);

        $this->actingAs($this->manager)->post(route('transactions.approve', $tx));

        // Inventory không thay đổi
        $this->assertEquals(5, $product->inventory->fresh()->quantity);
        // Status vẫn pending
        $this->assertEquals('pending', $tx->fresh()->status);
    }

    // ─── Idempotency ─────────────────────────────────────────────────────────────

    public function test_double_approve_is_idempotent(): void
    {
        $product = $this->makeProduct(qty: 0, cost: 0);
        $tx      = $this->makePendingInbound($product, qty: 10, price: 50000);

        $service = app(TransactionService::class);

        $this->actingAs($this->manager);
        $service->approve($tx);

        $this->expectException(\RuntimeException::class);
        $service->approve($tx);
    }

    public function test_double_approve_does_not_double_inventory(): void
    {
        $product = $this->makeProduct(qty: 0, cost: 0);
        $tx      = $this->makePendingInbound($product, qty: 10, price: 50000);

        $service = app(TransactionService::class);
        $this->actingAs($this->manager);
        $service->approve($tx);

        try {
            $service->approve($tx);
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertEquals(10, $product->inventory->fresh()->quantity);
    }

    // ─── Permissions ──────────────────────────────────────────────────────────

    public function test_accountant_cannot_approve_transaction(): void
    {
        $product = $this->makeProduct(qty: 0);
        $tx      = $this->makePendingInbound($product, qty: 5, price: 10000);

        $this->actingAs($this->accountant)
             ->post(route('transactions.approve', $tx))
             ->assertForbidden();

        $this->assertEquals('pending', $tx->fresh()->status);
    }

    public function test_supervisor_cannot_approve_transaction(): void
    {
        $product = $this->makeProduct(qty: 0);
        $tx      = $this->makePendingInbound($product, qty: 5, price: 10000);

        $this->actingAs($this->supervisor)
             ->post(route('transactions.approve', $tx))
             ->assertForbidden();
    }

    public function test_manager_can_approve_transaction(): void
    {
        $product = $this->makeProduct(qty: 0);
        $tx      = $this->makePendingInbound($product, qty: 5, price: 10000);

        $this->actingAs($this->manager)
             ->post(route('transactions.approve', $tx))
             ->assertRedirect();

        $this->assertEquals('approved', $tx->fresh()->status);
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    public function test_can_delete_draft_transaction(): void
    {
        $tx = Transaction::factory()->create(['status' => 'draft', 'created_by' => $this->admin->id]);

        $this->actingAs($this->admin)
             ->delete(route('transactions.destroy', $tx))
             ->assertRedirect();

        $this->assertSoftDeleted('transactions', ['id' => $tx->id]);
    }

    public function test_cannot_delete_approved_transaction(): void
    {
        $tx = Transaction::factory()->approved()->create(['created_by' => $this->admin->id]);

        $this->actingAs($this->admin)
             ->delete(route('transactions.destroy', $tx))
             ->assertRedirect();

        $this->assertDatabaseHas('transactions', ['id' => $tx->id, 'deleted_at' => null]);
    }
}
