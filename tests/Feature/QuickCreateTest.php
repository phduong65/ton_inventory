<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Tests\TestCase;

class QuickCreateTest extends TestCase
{
    private function unitId(): int
    {
        return Unit::firstOrCreate(['code' => 'CAI'], ['name' => 'Cái'])->id;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Quick-add Product
    // ═══════════════════════════════════════════════════════════════════════

    public function test_admin_can_quick_create_product(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Bia Heineken lon',
                'unit_id' => $this->unitId(),
            ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'name', 'sku', 'baseUnitId', 'baseUnitName', 'stock', 'conversions']);

        $this->assertDatabaseHas('products', ['name' => 'Bia Heineken lon', 'status' => 'active']);
        $this->assertDatabaseHas('inventory', ['quantity' => 0]);
    }

    public function test_accountant_can_quick_create_product(): void
    {
        $this->actingAs($this->accountant)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Nước suối',
                'unit_id' => $this->unitId(),
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Nước suối');
    }

    public function test_supervisor_cannot_quick_create_product(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Không được tạo',
                'unit_id' => $this->unitId(),
            ])
            ->assertForbidden();
    }

    public function test_manager_cannot_quick_create_product(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Không được tạo',
                'unit_id' => $this->unitId(),
            ])
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_quick_create_product(): void
    {
        $this->postJson(route('quick.products.store'), [
            'name'    => 'Ẩn danh',
            'unit_id' => $this->unitId(),
        ])->assertRedirect(route('login'));
    }

    public function test_quick_product_auto_generates_sku_when_empty(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Sản phẩm không SKU',
                'unit_id' => $this->unitId(),
            ]);

        $response->assertOk();
        $sku = $response->json('sku');
        $this->assertNotEmpty($sku);
        $this->assertStringStartsWith('SP-', $sku);
        $this->assertDatabaseHas('products', ['sku' => $sku]);
    }

    public function test_quick_product_uses_provided_sku(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Sản phẩm có SKU',
                'sku'     => 'MY-SKU-999',
                'unit_id' => $this->unitId(),
            ])
            ->assertOk()
            ->assertJsonPath('sku', 'MY-SKU-999');

        $this->assertDatabaseHas('products', ['sku' => 'MY-SKU-999']);
    }

    public function test_quick_product_rejects_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'DUP-SKU']);

        $this->actingAs($this->admin)
            ->post(route('quick.products.store'), [
                'name'    => 'Trùng SKU',
                'sku'     => 'DUP-SKU',
                'unit_id' => $this->unitId(),
            ])
            ->assertSessionHasErrors('sku');
    }

    public function test_quick_product_validation_requires_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('quick.products.store'), [
                'unit_id' => $this->unitId(),
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_quick_product_validation_requires_valid_unit(): void
    {
        $this->actingAs($this->admin)
            ->post(route('quick.products.store'), [
                'name'    => 'Sản phẩm',
                'unit_id' => 99999,
            ])
            ->assertSessionHasErrors('unit_id');
    }

    public function test_quick_product_sets_category_when_provided(): void
    {
        $category = Category::create(['name' => 'Đồ uống', 'sort' => 1]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('quick.products.store'), [
                'name'        => 'Coca Cola',
                'unit_id'     => $this->unitId(),
                'category_id' => $category->id,
            ]);

        $response->assertOk()->assertJsonPath('category', 'Đồ uống');
        $this->assertDatabaseHas('products', ['name' => 'Coca Cola', 'category_id' => $category->id]);
    }

    public function test_quick_product_creates_inventory_with_zero_stock(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('quick.products.store'), [
                'name'    => 'Hàng mới toanh',
                'unit_id' => $this->unitId(),
            ]);

        $response->assertOk()->assertJsonPath('stock', 0);

        $product = Product::where('name', 'Hàng mới toanh')->first();
        $this->assertNotNull($product);
        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'quantity'   => 0,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Quick-add Supplier
    // ═══════════════════════════════════════════════════════════════════════

    public function test_admin_can_quick_create_supplier(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('quick.suppliers.store'), [
                'name' => 'Công ty TNHH Nhanh',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'name', 'code']);

        $this->assertDatabaseHas('suppliers', ['name' => 'Công ty TNHH Nhanh']);
    }

    public function test_accountant_can_quick_create_supplier(): void
    {
        $this->actingAs($this->accountant)
            ->postJson(route('quick.suppliers.store'), [
                'name' => 'NCC Kế Toán',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'NCC Kế Toán');
    }

    public function test_supervisor_cannot_quick_create_supplier(): void
    {
        $this->actingAs($this->supervisor)
            ->postJson(route('quick.suppliers.store'), ['name' => 'Nope'])
            ->assertForbidden();
    }

    public function test_manager_cannot_quick_create_supplier(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('quick.suppliers.store'), ['name' => 'Nope'])
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_quick_create_supplier(): void
    {
        $this->postJson(route('quick.suppliers.store'), ['name' => 'Ẩn danh'])
            ->assertRedirect(route('login'));
    }

    public function test_quick_supplier_auto_generates_code_when_empty(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('quick.suppliers.store'), [
                'name' => 'NCC Không Mã',
            ]);

        $response->assertOk();
        $code = $response->json('code');
        $this->assertNotEmpty($code);
        $this->assertStringStartsWith('NCC-', $code);
        $this->assertDatabaseHas('suppliers', ['code' => $code]);
    }

    public function test_quick_supplier_uses_provided_code(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('quick.suppliers.store'), [
                'name' => 'NCC Có Mã',
                'code' => 'MY-NCC-01',
            ])
            ->assertOk()
            ->assertJsonPath('code', 'MY-NCC-01');

        $this->assertDatabaseHas('suppliers', ['code' => 'MY-NCC-01']);
    }

    public function test_quick_supplier_rejects_duplicate_code(): void
    {
        Supplier::factory()->create(['code' => 'DUP-NCC']);

        $this->actingAs($this->admin)
            ->post(route('quick.suppliers.store'), [
                'name' => 'Trùng mã',
                'code' => 'DUP-NCC',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_quick_supplier_validation_requires_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('quick.suppliers.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_quick_supplier_stores_optional_fields(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('quick.suppliers.store'), [
                'name'  => 'NCC Đầy Đủ',
                'phone' => '0912345678',
                'email' => 'ncc@test.com',
            ])
            ->assertOk();

        $this->assertDatabaseHas('suppliers', [
            'name'  => 'NCC Đầy Đủ',
            'phone' => '0912345678',
            'email' => 'ncc@test.com',
        ]);
    }
}
