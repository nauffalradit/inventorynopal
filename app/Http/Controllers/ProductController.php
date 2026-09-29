<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $category = trim((string) ($filters['category'] ?? ''));
        $page = (int) request()->input('page', 1);

        // Kombinasi filter tak terbatas → query terfilter jalan live (sku unik + index),
        // listing polos tetap cache 30s seperti sebelumnya.
        if ($search === '' && $category === '') {
            $products = Cache::remember("products:index:page:$page", 30, fn () => Product::latest()->paginate(15));
        } else {
            $products = Product::query()
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('sku', 'like', "%$search%")->orWhere('name', 'like', "%$search%")))
                ->when($category !== '', fn ($q) => $q->where('category', $category))
                ->latest()
                ->paginate(15)
                ->withQueryString();
        }

        $categories = Cache::remember('products:categories', 60, fn () => Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'));

        return view('products.index', compact('products', 'categories', 'search', 'category'));
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
        // Riwayat order dilindungi FK restrictOnDelete — tolak halus, bukan 500.
        $orderCount = DB::table('order_items')->where('product_id', $product->id)->count();
        abort_if($orderCount > 0, 422, "Barang tidak dapat dihapus karena sudah tercatat di $orderCount order. Biarkan sebagai arsip riwayat.");
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
