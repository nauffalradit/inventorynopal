@extends('layouts.app')

@section('content')
    <div class="actions" style="justify-content:space-between; margin-top:0">
        <h1 style="margin:0">Pencatatan Barang</h1>
        @if(auth()->user()?->isAdmin())
        <x-btn variant="primary" href="{{ route('products.create') }}">Tambah Barang</x-btn>
        @endif
    </div>

    <x-card title="Cari & Saring Barang">
        <form method="get" action="{{ route('products.index') }}">
            <div class="form-grid">
                <x-field label="Pencarian (SKU / Nama)">
                    <input name="search" value="{{ $search ?? '' }}" placeholder="cth: NOPAL atau TST-001">
                </x-field>
                <x-field label="Kategori">
                    <select name="category">
                        <option value="">Semua kategori</option>
                        @foreach (($categories ?? []) as $cat)
                            <option value="{{ $cat }}" @selected(($category ?? '') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </x-field>
            </div>
            <div class="actions">
                <x-btn variant="primary" type="submit">Terapkan</x-btn>
                <x-btn href="{{ route('products.index') }}">Atur ulang</x-btn>
            </div>
        </form>
    </x-card>

    <x-card title="Mutasi Stok">
        <form method="post" action="{{ route('movements.store') }}">
            @csrf
            <div class="form-grid">
                <x-field label="Barang">
                    <select name="product_id" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Tipe">
                    <select name="type" required>
                        <option value="in">Masuk</option>
                        <option value="out">Keluar</option>
                        @if(auth()->user()?->isAdmin())
                        <option value="adjustment">Penyesuaian</option>
                        @endif
                    </select>
                </x-field>
                <x-field label="Jumlah">
                    <input type="number" name="quantity" min="1" value="1" required>
                </x-field>
            </div>
            <x-field label="Catatan" style="margin-top:12px">
                <textarea name="notes"></textarea>
            </x-field>
            <div class="actions"><x-btn variant="primary" type="submit">Catat Mutasi</x-btn></div>
        </form>
    </x-card>

    <x-table :headers="['Foto', 'SKU', 'Nama', 'Kategori', 'Stok', 'Minimum', 'Lokasi', 'Aksi']">
        @forelse ($products as $product)
            <tr>
                <td>
                    @if($product->image_path)
                        <a href="{{ asset('storage/'.$product->image_path) }}" target="_blank" rel="noopener" title="Buka ukuran penuh">
                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="Foto {{ $product->name }}" style="width:72px;height:72px;object-fit:cover;border-radius:8px">
                        </a>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category ?: '-' }}</td>
                <td @class(['danger' => $product->stock <= $product->minimum_stock])>{{ $product->stock }} {{ $product->unit }}</td>
                <td>{{ $product->minimum_stock }}</td>
                <td>{{ $product->location ?: '-' }}</td>
                <td>
                    @if(auth()->user()?->isAdmin())
                    <x-btn href="{{ route('products.edit', $product) }}">Edit</x-btn>
                    <form class="inline" method="post" action="{{ route('products.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit">Hapus</x-btn>
                    </form>
                    @else
                    <span class="muted">—</span>
                    @endif
                </td>
            </tr>
        @empty
            <x-empty-state :colspan="8" message="Belum ada barang." />
        @endforelse
    </x-table>

    <div style="margin-top:14px">{{ $products->links() }}</div>
@endsection
