@csrf
<div class="form-grid">
    <label>SKU
        <input name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
        @error('sku') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Nama Barang
        <input name="name" value="{{ old('name', $product->name ?? '') }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Kategori
        <input name="category" value="{{ old('category', $product->category ?? '') }}">
        @error('category') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Satuan
        <input name="unit" value="{{ old('unit', $product->unit ?? 'pcs') }}" required>
        @error('unit') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Stok
        <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
        @error('stock') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Stok Minimum
        <input type="number" name="minimum_stock" min="0" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required>
        @error('minimum_stock') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Harga Jual (Rp)
        <input type="number" name="price" min="0" value="{{ old('price', $product->price ?? 0) }}" required>
        @error('price') <span class="error">{{ $message }}</span> @enderror
    </label>
    <label>Lokasi
        <input name="location" value="{{ old('location', $product->location ?? '') }}">
        @error('location') <span class="error">{{ $message }}</span> @enderror
    </label>
</div>
<div class="actions">
    <button class="btn primary" type="submit">Simpan</button>
    <a class="btn" href="{{ route('products.index') }}">Kembali</a>
</div>
