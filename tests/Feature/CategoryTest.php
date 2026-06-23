<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    public function test_authenticated_user_can_view_categories(): void
    {
        $this->actingAs($this->accountant)->get(route('categories.index'))->assertOk();
    }

    public function test_unauthenticated_cannot_view_categories(): void
    {
        $this->get(route('categories.index'))->assertRedirect('/login');
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Danh mục test',
            'sort' => 1,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Danh mục test']);
    }

    public function test_can_create_child_category(): void
    {
        $parent = Category::create(['name' => 'Cha', 'sort' => 0]);

        $this->actingAs($this->admin)->post(route('categories.store'), [
            'parent_id' => $parent->id,
            'name'      => 'Con',
            'sort'      => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', ['parent_id' => $parent->id, 'name' => 'Con']);
    }

    public function test_supervisor_cannot_create_category(): void
    {
        $this->actingAs($this->supervisor)->post(route('categories.store'), [
            'name' => 'Không được tạo',
        ])->assertForbidden();
    }

    public function test_store_validation_requires_name(): void
    {
        $this->actingAs($this->admin)
             ->post(route('categories.store'), [])
             ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_category(): void
    {
        $cat = Category::create(['name' => 'Tên cũ', 'sort' => 0]);

        $this->actingAs($this->admin)->put(route('categories.update', $cat), [
            'name' => 'Tên mới',
            'sort' => 5,
        ])->assertRedirect();

        $this->assertEquals('Tên mới', $cat->fresh()->name);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $cat = Category::create(['name' => 'Xóa được', 'sort' => 0]);

        $this->actingAs($this->admin)
             ->delete(route('categories.destroy', $cat))
             ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $cat     = Category::create(['name' => 'Có sản phẩm', 'sort' => 0]);
        $product = Product::factory()->create(['category_id' => $cat->id]);
        Inventory::create(['product_id' => $product->id, 'quantity' => 0, 'average_cost' => 0]);

        $this->actingAs($this->admin)
             ->delete(route('categories.destroy', $cat))
             ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }
}
