<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Unit;
use Tests\TestCase;

class ProductTest extends TestCase
{
    private function unitId(): int
    {
        return Unit::firstOrCreate(['code' => 'CAI'], ['name' => 'Cái'])->id;
    }

    // ─── List ────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_products(): void
    {
        $this->actingAs($this->accountant)->get(route('products.index'))->assertOk();
    }

    public function test_unauthenticated_cannot_view_products(): void
    {
        $this->get(route('products.index'))->assertRedirect('/login');
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->admin)->post(route('products.store'), [
            'sku'     => 'SKU-TEST-001',
            'name'    => 'Sản phẩm test',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', ['sku' => 'SKU-TEST-001']);
        $this->assertDatabaseHas('inventory', ['quantity' => 0]);
    }

    public function test_accountant_can_create_product(): void
    {
        $this->actingAs($this->accountant)->post(route('products.store'), [
            'sku'     => 'SKU-ACC-001',
            'name'    => 'Sản phẩm kế toán',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['sku' => 'SKU-ACC-001']);
    }

    public function test_supervisor_cannot_create_product(): void
    {
        $this->actingAs($this->supervisor)->post(route('products.store'), [
            'sku'     => 'SKU-NOPE',
            'name'    => 'Không được tạo',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertForbidden();
    }

    public function test_manager_cannot_create_product(): void
    {
        $this->actingAs($this->manager)->post(route('products.store'), [
            'sku'     => 'SKU-MGR',
            'name'    => 'Manager tạo',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertForbidden();
    }

    public function test_store_validation_requires_sku(): void
    {
        $this->actingAs($this->admin)->post(route('products.store'), [
            'name'    => 'Thiếu SKU',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertSessionHasErrors('sku');
    }

    public function test_store_validation_rejects_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'SKU-DUP']);

        $this->actingAs($this->admin)->post(route('products.store'), [
            'sku'     => 'SKU-DUP',
            'name'    => 'Trùng SKU',
            'unit_id' => $this->unitId(),
            'status'  => 'active',
        ])->assertSessionHasErrors('sku');
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Tên cũ']);
        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'     => $product->sku,
            'name'    => 'Tên mới',
            'unit_id' => $product->unit_id,
            'status'  => 'active',
        ])->assertRedirect(route('products.index'));

        $this->assertEquals('Tên mới', $product->fresh()->name);
    }

    public function test_accountant_can_update_product(): void
    {
        $product = Product::factory()->create();
        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->actingAs($this->accountant)->put(route('products.update', $product), [
            'sku'     => $product->sku,
            'name'    => 'Đã sửa',
            'unit_id' => $product->unit_id,
            'status'  => 'inactive',
        ])->assertRedirect();

        $this->assertEquals('inactive', $product->fresh()->status);
    }

    public function test_supervisor_cannot_update_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->supervisor)->put(route('products.update', $product), [
            'sku'     => $product->sku,
            'name'    => 'Cố sửa',
            'unit_id' => $product->unit_id,
            'status'  => 'active',
        ])->assertForbidden();
    }

    public function test_update_allows_same_sku_for_same_product(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-SAME']);
        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->actingAs($this->admin)->put(route('products.update', $product), [
            'sku'     => 'SKU-SAME',
            'name'    => 'Không đổi SKU',
            'unit_id' => $product->unit_id,
            'status'  => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => 'SKU-SAME']);
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    public function test_admin_can_soft_delete_product(): void
    {
        $product = Product::factory()->create();
        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->actingAs($this->admin)
             ->delete(route('products.destroy', $product))
             ->assertRedirect();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_cannot_delete_product_with_transaction_details(): void
    {
        $product  = Product::factory()->create();
        $supplier = Supplier::factory()->create();
        $tx       = Transaction::factory()->create([
            'type' => 'IN', 'status' => 'draft',
            'supplier_id' => $supplier->id, 'created_by' => $this->admin->id,
        ]);
        TransactionDetail::create([
            'transaction_id' => $tx->id, 'product_id' => $product->id,
            'qty' => 1, 'base_qty' => 1, 'conversion_factor' => 1,
            'price' => 0, 'discount' => 0, 'vat' => 0, 'amount' => 0,
        ]);

        $this->actingAs($this->admin)
             ->delete(route('products.destroy', $product))
             ->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_supervisor_cannot_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->supervisor)
             ->delete(route('products.destroy', $product))
             ->assertForbidden();
    }
}
