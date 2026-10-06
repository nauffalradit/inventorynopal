@extends('layouts.app')
@section('content')
<h1>Buat Order Penjualan</h1><x-card><form method="post" action="{{ route('orders.store') }}">@csrf
<div class="form-grid"><x-field label="Nama pelanggan"><input name="customer_name" required></x-field><x-field label="Email pelanggan"><input type="email" name="customer_email" required></x-field></div><h2 style="margin-top:22px">Item order</h2><div class="form-grid"><x-field label="Barang"><select name="items[0][product_id]" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} — Rp {{ number_format($product->price,0,',','.') }}</option>@endforeach</select></x-field><x-field label="Jumlah"><input type="number" name="items[0][quantity]" min="1" value="1" required></x-field></div><div class="actions"><x-btn variant="primary" type="submit">Buat Order</x-btn></div></form></x-card>
@endsection
