@extends('layouts.app')

@section('content')
    <h1>Dashboard Inventory</h1>

    <section class="grid stats">
        <div class="card metric">Jenis Barang<strong>{{ number_format($productCount) }}</strong></div>
        <div class="card metric">Total Stok<strong>{{ number_format($totalStock) }}</strong></div>
        <div class="card metric">Stok Menipis<strong>{{ number_format($lowStockCount) }}</strong></div>
        <div class="card metric">Laporan Pending<strong>{{ number_format($pendingReports) }}</strong></div>
    </section>

    <section class="grid two" style="margin-top:14px">
        <div class="card">
            <h2>Mutasi Terakhir</h2>
            <table>
                <thead><tr><th>Barang</th><th>Tipe</th><th>Qty</th><th>Sisa</th><th>Waktu</th></tr></thead>
                <tbody>
                @forelse ($recentMovements as $movement)
                    <tr>
                        <td>{{ $movement->product?->name }}</td>
                        <td><span class="badge">{{ $movement->type }}</span></td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->balance_after }}</td>
                        <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada mutasi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <h2>Komunikasi Terakhir</h2>
            <table>
                <thead><tr><th>Tujuan</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($recentNotifications as $notification)
                    <tr>
                        <td>{{ $notification->recipient }}<br><span class="muted">{{ $notification->subject }}</span></td>
                        <td><span class="badge">{{ $notification->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">Belum ada notifikasi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
