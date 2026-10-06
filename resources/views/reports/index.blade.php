@extends('layouts.app')

@section('content')
    <div class="actions" style="justify-content:space-between; margin-top:0">
        <h1 style="margin:0">Cetak Laporan</h1>
        @if(auth()->user()?->isAdmin())
        <form method="post" action="{{ route('reports.store') }}">
            @csrf
            <x-btn variant="primary" type="submit">Buat Laporan PDF</x-btn>
        </form>
        @endif
    </div>

    <x-table :headers="['Judul', 'Status', 'Dibuat', 'Selesai', 'Aksi']">
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->title }}</td>
                <td><x-badge>{{ $report->status }}</x-badge></td>
                <td>{{ $report->created_at->format('d M Y H:i') }}</td>
                <td>{{ $report->generated_at?->format('d M Y H:i') ?: '-' }}</td>
                <td>
                    @if ($report->status === 'completed')
                        <x-btn href="{{ route('reports.show', $report) }}">Buka PDF</x-btn>
                    @else
                        <span class="muted">Menunggu diproses</span>
                    @endif
                    @if(auth()->user()?->isAdmin())
                    <form class="inline" method="post" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Hapus laporan ini? File PDF-nya ikut terhapus.')">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit">Hapus</x-btn>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <x-empty-state :colspan="5" message="Belum ada laporan." />
        @endforelse
    </x-table>
    <div style="margin-top:14px">{{ $reports->links() }}</div>
@endsection
