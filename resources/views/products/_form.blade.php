@csrf
<div class="form-grid">
    <x-field label="SKU" name="sku">
        <input name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
    </x-field>
    <x-field label="Nama Barang" name="name">
        <input name="name" value="{{ old('name', $product->name ?? '') }}" required>
    </x-field>
    <x-field label="Kategori" name="category">
        <input name="category" value="{{ old('category', $product->category ?? '') }}">
    </x-field>
    <x-field label="Satuan" name="unit">
        <input name="unit" value="{{ old('unit', $product->unit ?? 'pcs') }}" required>
    </x-field>
    <x-field label="Stok" name="stock">
        <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
    </x-field>
    <x-field label="Stok Minimum" name="minimum_stock">
        <input type="number" name="minimum_stock" min="0" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required>
    </x-field>
    <x-field label="Harga Jual (Rp)" name="price">
        <input type="number" name="price" min="0" value="{{ old('price', $product->price ?? 0) }}" required>
    </x-field>
    <x-field label="Lokasi" name="location">
        <input name="location" value="{{ old('location', $product->location ?? '') }}">
    </x-field>
    <x-field label="Foto Produk (maks 2MB)" name="image">
        @if(!empty($product->image_path))
            <img src="{{ asset('storage/'.$product->image_path) }}" alt="Foto {{ $product->name ?? '' }}" style="width:96px;height:96px;object-fit:cover;border-radius:8px;border:1px solid #dfe4ed">
        @endif
        <input type="file" name="image" accept="image/*">
    </x-field>
</div>
<div class="actions">
    <x-btn variant="primary" type="submit">Simpan</x-btn>
    <x-btn href="{{ route('products.index') }}">Kembali</x-btn>
</div>
