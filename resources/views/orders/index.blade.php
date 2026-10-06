@extends('layouts.app')
@section('content')
<div class="actions" style="justify-content:space-between;margin-top:0">
    <div><h1 style="margin:0">Penjualan & Pembayaran</h1><p class="muted" style="margin:2px 0 0">Semua order dan status pembayarannya.</p></div>
    <x-btn variant="primary" href="{{ route('orders.create') }}">+ Buat order</x-btn>
</div>
<x-card>
    <div class="stats-strip" style="border:0;padding:0 0 6px;">
        <x-stat label="Sudah dibayar">Rp {{ number_format($stats['paid_sum'], 0, ',', '.') }}</x-stat>
        <x-stat label="Jumlah order">{{ $stats['count'] }}</x-stat>
        <x-stat label="Menunggu pembayaran">{{ $stats['waiting'] }}</x-stat>
    </div>
    <div class="rows">
        @forelse($orders as $order)
        <div class="row-item">
            <div class="row-grow"><b>{{ $order->number }}</b><span class="muted">{{ $order->customer_name }} · {{ $order->customer_email }}</span></div>
            <span class="row-amt">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            @if($order->status === 'paid')
                <x-badge tone="success">Berhasil</x-badge>
            @elseif($order->status === 'pending')
                <x-badge tone="warn">Menunggu pembayaran</x-badge>
            @else
                <x-badge tone="danger">{{ $order->status }}</x-badge>
            @endif
            <div class="row-acts">
                <x-btn variant="ghost" href="{{ route('orders.show', $order) }}">Detail</x-btn>
                @if($order->status !== 'paid')
                <form class="inline" method="post" action="{{ route('orders.destroy', $order) }}">@csrf @method('DELETE')<x-btn variant="ghost-danger" type="submit">Hapus</x-btn></form>
                @endif
            </div>
        </div>
        @empty
        <p class="muted rows-empty">Belum ada order.</p>
        @endforelse
    </div>
</x-card>
<div style="margin-top:14px">{{ $orders->links() }}</div>
@endsection
