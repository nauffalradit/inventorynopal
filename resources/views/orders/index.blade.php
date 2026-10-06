@extends('layouts.app')
@section('content')
<div class="actions" style="justify-content:space-between;margin-top:0"><h1 style="margin:0">Penjualan & Pembayaran</h1><x-btn variant="primary" href="{{ route('orders.create') }}">Buat Order</x-btn></div>
<x-table :headers="['Order', 'Pelanggan', 'Total', 'Status', 'Aksi']">@forelse($orders as $order)<tr><td>{{ $order->number }}</td><td>{{ $order->customer_name }}</td><td>Rp {{ number_format($order->total_amount,0,',','.') }}</td><td><x-badge>{{ $order->status === 'paid' ? 'Berhasil' : ($order->status === 'pending' ? 'Menunggu pembayaran' : $order->status) }}</x-badge></td><td><x-btn href="{{ route('orders.show',$order) }}">Detail</x-btn>@if($order->status !== 'paid')<form class="inline" method="post" action="{{ route('orders.destroy',$order) }}">@csrf @method('DELETE')<x-btn type="submit">Hapus</x-btn></form>@endif</td></tr>@empty<x-empty-state :colspan="5" message="Belum ada order." />@endforelse</x-table>
<div style="margin-top:14px">{{ $orders->links() }}</div>
@endsection
