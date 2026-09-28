<?php

namespace Tests\Feature;

use App\Models\NotificationMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'staff'): User
    {
        return User::create([
            'name' => ucfirst($role).' Stok',
            'email' => $role.'_'.uniqid().'@nopal.local',
            'google_id' => 'google-stok-'.uniqid(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function make(array $over = []): Product
    {
        return Product::create(array_merge([
            'sku' => 'STK-'.strtoupper(uniqid()),
            'name' => 'Barang Stok',
            'category' => 'Umum',
            'unit' => 'pcs',
            'stock' => 10,
            'minimum_stock' => 2,
            'price' => 10000,
            'location' => 'A1',
        ], $over));
    }

    private function move(User $user, Product $product, string $type = 'out', int $qty = 1): void
    {
        $this->actingAs($user)->post(route('movements.store'), [
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $qty,
        ])->assertRedirect();
    }

    public function test_dashboard_lists_low_stock_products(): void
    {
        $admin = $this->user('admin');
        $this->make(['sku' => 'STK-RENDAH', 'name' => 'Nopal Hampir Habis', 'stock' => 1, 'minimum_stock' => 5]);
        $this->make(['sku' => 'STK-AMAN', 'name' => 'Nopal Aman', 'stock' => 50, 'minimum_stock' => 5]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Nopal Hampir Habis')
            ->assertDontSee('Nopal Aman');
    }

    public function test_triggering_mutation_creates_single_notification_per_day(): void
    {
        $staff = $this->user();
        $product = $this->make(['sku' => 'STK-PICU', 'name' => 'Nopal Pemicu', 'stock' => 3, 'minimum_stock' => 2]);

        $this->move($staff, $product, 'out', 2); // sisa 1 <= 2 → picu
        $this->assertEquals(1, NotificationMessage::where('subject', 'Stok menipis: STK-PICU')->count());

        $this->move($staff, $product, 'out', 1); // hari sama → tidak duplikat
        $this->assertEquals(1, NotificationMessage::where('subject', 'Stok menipis: STK-PICU')->count());
    }

    public function test_staff_only_sees_own_low_stock_products(): void
    {
        $staffA = $this->user();
        $staffB = $this->user();
        $own = $this->make(['sku' => 'STK-MILIK', 'name' => 'Nopal Milik A', 'stock' => 5, 'minimum_stock' => 5]);
        $other = $this->make(['sku' => 'STK-ORANG', 'name' => 'Nopal Milik B', 'stock' => 5, 'minimum_stock' => 5]);

        $this->move($staffA, $own, 'in', 1);
        $this->move($staffB, $other, 'in', 1);

        $this->actingAs($staffA)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Nopal Milik A')
            ->assertDontSee('Nopal Milik B');
    }
}
