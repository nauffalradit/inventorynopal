@extends('layouts.app')
@section('content')
<h1>Buat Order Penjualan</h1><form method="post" action="{{ route('orders.store') }}" class="card">@csrf
<div class="form-grid"><label>Nama pelanggan<input name="customer_name" required></label><label>Email pelanggan<input type="email" name="customer_email" required></label></div><h2 style="margin-top:22px">Item order</h2><div class="form-grid"><label>Barang<select name="items[0][product_id]" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }} — Rp {{ number_format($product->price,0,',','.') }}</option>@endforeach</select></label><label>Jumlah<input type="number" name="items[0][quantity]" min="1" value="1" required></label></div><div class="actions"><button class="btn primary" type="submit">Buat Order</button></div></form>
@endsection
