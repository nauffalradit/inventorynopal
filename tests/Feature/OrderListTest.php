<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'staff'): User
    {
        return User::create([
            'name' => ucfirst($role).' Order '.uniqid(),
            'email' => strtolower($role).'_order_'.uniqid().'@nopal.local',
            'google_id' => 'google-order-'.uniqid(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function order(User $owner, array $over = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $owner->id,
            'number' => 'ORD-'.strtoupper(uniqid()),
            'customer_name' => 'Pelanggan',
            'customer_email' => 'pelanggan@nopal.local',
            'total_amount' => 20000,
            'status' => 'pending',
        ], $over));
    }

    public function test_admin_sees_summary_stats_and_card_rows(): void
    {
        $admin = $this->user('admin');
        $paid = $this->order($admin, ['status' => 'paid', 'total_amount' => 50000]);
        $pending = $this->order($admin, ['status' => 'pending', 'total_amount' => 20000]);

        $this->actingAs($admin)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Sudah dibayar', false)
            ->assertSee('Rp 50.000', false)
            ->assertSee('Menunggu pembayaran', false)
            ->assertSee($paid->number, false)
            ->assertSee($pending->number, false)
            ->assertSee('>Detail</a>', false);
    }

    public function test_staff_stats_cover_only_own_orders(): void
    {
        $admin = $this->user('admin');
        $me = $this->user('staff');
        $other = $this->user('staff');
        $mine = $this->order($me, ['status' => 'paid', 'total_amount' => 30000]);
        $theirs = $this->order($other, ['status' => 'paid', 'total_amount' => 90000]);

        $this->actingAs($me)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Rp 30.000', false)
            ->assertSee($mine->number, false)
            ->assertDontSee($theirs->number, false)
            ->assertDontSee('Rp 90.000', false);

        $this->actingAs($admin)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Rp 120.000', false);
    }

    public function test_delete_button_only_for_unpaid_orders(): void
    {
        $admin = $this->user('admin');
        $this->order($admin, ['status' => 'paid']);

        $this->actingAs($admin)->get(route('orders.index'))
            ->assertOk()
            ->assertDontSee('Hapus', false);
    }

    public function test_stats_refresh_after_store_flushes_cache(): void
    {
        $admin = $this->user('admin');
        $product = Product::create([
            'sku' => 'ORD-STAT-'.strtoupper(uniqid()),
            'name' => 'Barang Stat',
            'unit' => 'pcs',
            'stock' => 10,
            'minimum_stock' => 1,
            'price' => 15000,
        ]);

        $this->actingAs($admin)->get(route('orders.index'))->assertOk();
        $this->actingAs($admin)->post(route('orders.store'), [
            'customer_name' => 'Pelanggan Stat',
            'customer_email' => 'stat@nopal.local',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertRedirect();

        // Tanpa flush, GET kedua akan menampilkan statistik basi (0 order).
        $this->actingAs($admin)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Jumlah order', false)
            ->assertSee('<b>1</b>', false);
    }
}
