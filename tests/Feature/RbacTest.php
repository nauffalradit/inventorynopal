<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $over = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin Test',
            'email' => 'admin_test@nopal.local',
            'google_id' => 'google-admin-'.uniqid(),
            'role' => 'admin',
            'is_active' => true,
        ], $over));
    }

    private function staff(array $over = []): User
    {
        return User::create(array_merge([
            'name' => 'Staff Test',
            'email' => 'staff_test@nopal.local',
            'google_id' => 'google-staff-'.uniqid(),
            'role' => 'staff',
            'is_active' => true,
        ], $over));
    }

    private function productData(array $over = []): array
    {
        return array_merge([
            'sku' => 'TST-'.strtoupper(uniqid()),
            'name' => 'Barang Test',
            'category' => 'Umum',
            'unit' => 'pcs',
            'stock' => 10,
            'minimum_stock' => 2,
            'price' => 10000,
            'location' => 'A1',
        ], $over);
    }

    // 1. Admin dapat mengakses fitur administratif
    public function test_admin_can_access_admin_features(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('products.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)
            ->post(route('products.store'), $this->productData())
            ->assertRedirect(route('products.index'));
    }

    // 2. Staff dapat mengakses fitur operasional
    public function test_staff_can_access_operational_features(): void
    {
        $staff = $this->staff();
        $product = Product::create($this->productData());

        $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('products.index'))->assertOk();
        $this->actingAs($staff)->get(route('orders.index'))->assertOk();
        $this->actingAs($staff)
            ->post(route('movements.store'), [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 2,
            ])
            ->assertRedirect();
    }

    // 3. Staff ditolak ketika mencoba endpoint administratif
    public function test_staff_is_forbidden_from_admin_endpoints(): void
    {
        $staff = $this->staff();
        $product = Product::create($this->productData());

        $this->actingAs($staff)->get(route('products.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('products.store'), $this->productData())->assertForbidden();
        $this->actingAs($staff)->post(route('reports.store'))->assertForbidden();
        $this->actingAs($staff)->post(route('notifications.store'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // 4. Staff tidak dapat mengubah role dirinya sendiri
    public function test_staff_cannot_promote_self_to_admin(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)
            ->patch(route('admin.users.role', $staff), ['role' => 'admin'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'role' => 'staff']);
    }

    // 5. Staff tidak dapat menghapus barang
    public function test_staff_cannot_delete_product(): void
    {
        $staff = $this->staff();
        $product = Product::create($this->productData());

        $this->actingAs($staff)
            ->delete(route('products.destroy', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // 6. Duplicate SKU harus ditolak
    public function test_duplicate_sku_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('products.store'), $this->productData(['sku' => 'DUPL-001']))
            ->assertRedirect(route('products.index'));

        $this->actingAs($admin)
            ->post(route('products.store'), $this->productData(['sku' => 'DUPL-001']))
            ->assertSessionHasErrors('sku');

        $this->assertEquals(1, Product::where('sku', 'DUPL-001')->count());
    }

    // 7. Authentication/session tetap bekerja setelah logout
    public function test_session_works_after_logout(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('logout'))->assertRedirect('/login');
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
