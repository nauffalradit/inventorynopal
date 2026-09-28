<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        // Keep All: paginate cache 30s per page untuk percepat pindah page & refresh
        $page = (int) request()->input('page', 1);
        $products = Cache::remember("products:index:page:$page", 30, fn () => Product::latest()->paginate(15)
        );

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        Gate::authorize('admin');

        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $this->validated($request, null);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        Product::create($data);
        DashboardController::flushDashboard();
        $this->flushProductIndexCache();

        return to_route('products.index')->with('status', 'Barang berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        Gate::authorize('admin');

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $this->validated($request, $product);
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        $product->update($data);
        DashboardController::flushDashboard();
        $this->flushProductIndexCache();

        return to_route('products.index')->with('status', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('admin');
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        DashboardController::flushDashboard();
        $this->flushProductIndexCache();

        return to_route('products.index')->with('status', 'Barang berhasil dihapus.');
    }

    private function flushProductIndexCache(): void
    {
        // file driver tidak support tags — flush key page 1..20 yang mungkin ada
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("products:index:page:$i");
        }
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $skuRule = ['required', 'string', 'max:80', 'unique:products,sku'.($product ? ','.$product->id : '')];

        return $request->validate([
            'sku' => $skuRule,
            'name' => ['required', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:40'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
