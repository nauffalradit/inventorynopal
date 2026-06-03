<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->firstOrCreate(
            ['sku' => 'ITM-001'],
            [
                'name' => 'Barang Contoh',
                'category' => 'Umum',
                'unit' => 'pcs',
                'stock' => 25,
                'minimum_stock' => 5,
                'location' => 'Gudang Utama',
            ]
        );
    }
}
