<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $over = []): User
    {
        return User::create(array_merge([
            'name' => 'Staff Cari',
            'email' => 'staff_cari@nopal.local',
            'google_id' => 'google-cari-'.uniqid(),
            'role' => 'staff',
            'is_active' => true,
        ], $over));
    }

    private function make(array $over = []): Product
    {
        return Product::create(array_merge([
            'sku' => 'CARI-'.strtoupper(uniqid()),
            'name' => 'Barang Cari',
            'category' => 'Umum',
            'unit' => 'pcs',
            'stock' => 10,
            'minimum_stock' => 2,
            'price' => 10000,
            'location' => 'A1',
        ], $over));
    }

    public function test_search_filters_by_sku_or_name(): void
    {
        $staff = $this->user();
        $this->make(['sku' => 'NOPAL-001', 'name' => 'Nopal Segar']);
        $this->make(['sku' => 'TST-002', 'name' => 'Tomat Cherry']);

        $this->actingAs($staff)->get(route('products.index', ['search' => 'nopal']))
            ->assertOk()
            ->assertSee('Nopal Segar')
            ->assertDontSee('Tomat Cherry');
    }

    public function test_category_filter_and_reset(): void
    {
        $staff = $this->user();
        $this->make(['sku' => 'K-A1', 'name' => 'Kopi Arabika', 'category' => 'Minuman']);
        $this->make(['sku' => 'K-B1', 'name' => 'Kopi Robusta', 'category' => 'Minuman']);
        $this->make(['sku' => 'S-C1', 'name' => 'Susu UHT', 'category' => 'Susu']);

        $this->actingAs($staff)->get(route('products.index', ['category' => 'Minuman']))
            ->assertOk()
            ->assertSee('Kopi Arabika')
            ->assertSee('Kopi Robusta')
            ->assertDontSee('Susu UHT');

        // tanpa filter semua tampil
        $this->actingAs($staff)->get(route('products.index'))
            ->assertOk()
            ->assertSee('Susu UHT');
    }

    public function test_pagination_keeps_filter_query(): void
    {
        $staff = $this->user();
        for ($i = 1; $i <= 17; $i++) {
            $this->make(['sku' => 'PG-'.$i, 'name' => 'Barang Paginasi '.$i]);
        }

        $response = $this->actingAs($staff)->get(route('products.index', ['search' => 'Paginasi', 'page' => 2]));
        $response->assertOk()->assertDontSee('Barang Paginasi 17'); // item terbaru ada di halaman 1
        // link paginasi halaman lain tetap membawa ?search=
        $this->assertStringContainsString('search=Paginasi', $response->getContent());
    }
}
