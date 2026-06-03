@extends('layouts.app')

@section('content')
    <div class="actions" style="justify-content:space-between; margin-top:0">
        <h1 style="margin:0">Pencatatan Barang</h1>
        <a class="btn primary" href="{{ route('products.create') }}">Tambah Barang</a>
    </div>

    <section class="card" style="margin-bottom:14px">
        <h2>Mutasi Stok</h2>
        <form method="post" action="{{ route('movements.store') }}">
            @csrf
            <div class="form-grid">
                <label>Barang
                    <select name="product_id" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Tipe
                    <select name="type" required>
                        <option value="in">Masuk</option>
                        <option value="out">Keluar</option>
                        <option value="adjustment">Penyesuaian</option>
                    </select>
                </label>
                <label>Jumlah
                    <input type="number" name="quantity" min="1" value="1" required>
                </label>
            </div>
            <label style="margin-top:12px">Catatan
                <textarea name="notes"></textarea>
            </label>
            <div class="actions"><button class="btn primary" type="submit">Catat Mutasi</button></div>
        </form>
    </section>

    <table>
        <thead>
            <tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Lokasi</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        @forelse ($products as $product)
            <tr>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category ?: '-' }}</td>
                <td @class(['danger' => $product->stock <= $product->minimum_stock])>{{ $product->stock }} {{ $product->unit }}</td>
                <td>{{ $product->minimum_stock }}</td>
                <td>{{ $product->location ?: '-' }}</td>
                <td>
                    <a class="btn" href="{{ route('products.edit', $product) }}">Edit</a>
                    <form class="inline" method="post" action="{{ route('products.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Belum ada barang.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:14px">{{ $products->links() }}</div>
@endsection
