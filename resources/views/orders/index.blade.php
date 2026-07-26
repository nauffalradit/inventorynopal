@extends('layouts.app')
@section('content')
<div class="actions" style="justify-content:space-between;margin-top:0"><h1 style="margin:0">Penjualan & Pembayaran</h1><a class="btn primary" href="{{ route('orders.create') }}">Buat Order</a></div>
<table><thead><tr><th>Order</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($orders as $order)<tr><td>{{ $order->number }}</td><td>{{ $order->customer_name }}</td><td>Rp {{ number_format($order->total_amount,0,',','.') }}</td><td><span class="badge">{{ $order->status }}</span></td><td><a class="btn" href="{{ route('orders.show',$order) }}">Detail</a></td></tr>@empty<tr><td colspan="5" class="muted">Belum ada order.</td></tr>@endforelse</tbody></table>
@endsection
