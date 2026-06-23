<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\Transaction;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    public function test_authenticated_user_can_view_suppliers(): void
    {
        $this->actingAs($this->accountant)->get(route('suppliers.index'))->assertOk();
    }

    public function test_unauthenticated_cannot_view_suppliers(): void
    {
        $this->get(route('suppliers.index'))->assertRedirect('/login');
    }

    public function test_accountant_can_create_supplier(): void
    {
        $this->actingAs($this->accountant)->post(route('suppliers.store'), [
            'code' => 'NCC-TEST',
            'name' => 'Nhà cung cấp test',
        ])->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['code' => 'NCC-TEST']);
    }

    public function test_supervisor_cannot_create_supplier(): void
    {
        $this->actingAs($this->supervisor)->post(route('suppliers.store'), [
            'code' => 'NCC-NOPE',
            'name' => 'Không tạo được',
        ])->assertForbidden();
    }

    public function test_manager_cannot_create_supplier(): void
    {
        $this->actingAs($this->manager)->post(route('suppliers.store'), [
            'code' => 'NCC-MGR',
            'name' => 'Manager tạo',
        ])->assertForbidden();
    }

    public function test_store_validation_requires_code_and_name(): void
    {
        $this->actingAs($this->accountant)
             ->post(route('suppliers.store'), [])
             ->assertSessionHasErrors(['code', 'name']);
    }

    public function test_store_validation_rejects_duplicate_code(): void
    {
        Supplier::factory()->create(['code' => 'NCC-DUP']);

        $this->actingAs($this->accountant)->post(route('suppliers.store'), [
            'code' => 'NCC-DUP',
            'name' => 'Trùng mã',
        ])->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Tên cũ']);

        $this->actingAs($this->admin)->put(route('suppliers.update', $supplier), [
            'code' => $supplier->code,
            'name' => 'Tên mới',
        ])->assertRedirect();

        $this->assertEquals('Tên mới', $supplier->fresh()->name);
    }

    public function test_update_allows_same_code_for_same_supplier(): void
    {
        $supplier = Supplier::factory()->create(['code' => 'NCC-SAME']);

        $this->actingAs($this->admin)->put(route('suppliers.update', $supplier), [
            'code' => 'NCC-SAME',
            'name' => 'Đổi tên, giữ code',
        ])->assertRedirect();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'code' => 'NCC-SAME']);
    }

    public function test_admin_can_soft_delete_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin)
             ->delete(route('suppliers.destroy', $supplier))
             ->assertRedirect();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_cannot_delete_supplier_with_transactions(): void
    {
        $supplier = Supplier::factory()->create();
        Transaction::factory()->create([
            'type'        => 'IN',
            'status'      => 'draft',
            'supplier_id' => $supplier->id,
            'created_by'  => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
             ->delete(route('suppliers.destroy', $supplier))
             ->assertRedirect();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'deleted_at' => null]);
    }
}
