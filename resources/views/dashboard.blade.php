@extends('layouts.app')

@section('content')
    <h1>Dashboard Inventory</h1>

    <section class="grid stats">
        <div class="card metric">Jenis Barang<strong>{{ number_format($productCount) }}</strong></div>
        <div class="card metric">Total Stok<strong>{{ number_format($totalStock) }}</strong></div>
        <div class="card metric">Stok Menipis<strong>{{ number_format($lowStockCount) }}</strong></div>
        @if($isAdminView ?? true)
        <div class="card metric">Laporan Pending<strong>{{ number_format($pendingReports) }}</strong></div>
        @endif
    </section>

    <section class="grid two" style="margin-top:14px">
        <x-card title="Mutasi Terakhir">
            <x-table :headers="['Barang', 'Tipe', 'Qty', 'Sisa', 'Oleh', 'Waktu']">
                @forelse ($recentMovements as $movement)
                    <tr>
                        <td>{{ $movement->product?->name }}</td>
                        <td><x-badge>{{ $movement->type }}</x-badge></td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->balance_after }}</td>
                        <td>{{ auth()->user()?->isAdmin() ? ($movement->creator?->name ?? '—') : '—' }}</td>
                        <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <x-empty-state :colspan="6" message="Belum ada mutasi." />
                @endforelse
            </x-table>
        </x-card>
        <x-card title="Komunikasi Terakhir">
            <x-table :headers="['Tujuan', 'Status']">
                @forelse ($recentNotifications as $notification)
                    <tr>
                        <td>{{ $notification->recipient }}<br><span class="muted">{{ $notification->subject }}</span></td>
                        <td><x-badge>{{ $notification->status }}</x-badge></td>
                    </tr>
                @empty
                    <x-empty-state :colspan="2" message="Belum ada notifikasi." />
                @endforelse
            </x-table>
        </x-card>
    </section>
    <x-card title="Perlu Restock ⚠️" style="margin-top:14px">
        <x-table :headers="['Foto', 'SKU', 'Nama', 'Stok', 'Minimum']">
            @forelse ($lowStockProducts as $product)
                <tr>
                    <td>
                        @if($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="Foto {{ $product->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px">
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td class="danger">{{ $product->stock }} {{ $product->unit }}</td>
                    <td>{{ $product->minimum_stock }}</td>
                </tr>
            @empty
                <x-empty-state :colspan="5" message="Semua stok aman." />
            @endforelse
        </x-table>
    </x-card>
@endsection
