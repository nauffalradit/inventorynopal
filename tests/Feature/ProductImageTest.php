<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Foto',
            'email' => 'admin_foto@nopal.local',
            'google_id' => 'google-foto-'.uniqid(),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function productData(array $over = []): array
    {
        return array_merge([
            'sku' => 'IMG-'.strtoupper(uniqid()),
            'name' => 'Barang Foto',
            'category' => 'Umum',
            'unit' => 'pcs',
            'stock' => 10,
            'minimum_stock' => 2,
            'price' => 10000,
            'location' => 'A1',
        ], $over);
    }

    private function fakeImage(string $name = 'barang.png'): UploadedFile
    {
        // PNG 1x1 asli (tanpa butuh ekstensi GD)
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_admin_can_upload_product_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $data = $this->productData(['sku' => 'IMG-UPLOAD']);

        $this->actingAs($admin)
            ->post(route('products.store'), array_merge($data, [
                'image' => $this->fakeImage(),
            ]))
            ->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'IMG-UPLOAD')->firstOrFail();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('products.store'), array_merge($this->productData(), [
                'image' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('image');

        $this->assertEquals(0, Product::count());
    }

    public function test_update_replaces_and_destroy_removes_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('products.store'), array_merge($this->productData(['sku' => 'IMG-GANTI']), [
                'image' => $this->fakeImage('lama.png'),
            ]))
            ->assertRedirect();

        $product = Product::where('sku', 'IMG-GANTI')->firstOrFail();
        $oldPath = $product->image_path;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($admin)
            ->put(route('products.update', $product), array_merge($this->productData(['sku' => 'IMG-GANTI']), [
                'image' => $this->fakeImage('baru.png'),
            ]))
            ->assertRedirect(route('products.index'));

        $product->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image_path);

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        Storage::disk('public')->assertMissing($product->image_path);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
