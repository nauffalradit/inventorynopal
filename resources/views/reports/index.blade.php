@extends('layouts.app')

@section('content')
    <div class="actions" style="justify-content:space-between; margin-top:0">
        <h1 style="margin:0">Cetak Laporan</h1>
        @if(auth()->user()?->isAdmin())
        <form method="post" action="{{ route('reports.store') }}">
            @csrf
            <button class="btn primary" type="submit">Buat Laporan PDF</button>
        </form>
        @endif
    </div>

    <table>
        <thead><tr><th>Judul</th><th>Status</th><th>Dibuat</th><th>Selesai</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->title }}</td>
                <td><span class="badge">{{ $report->status }}</span></td>
                <td>{{ $report->created_at->format('d M Y H:i') }}</td>
                <td>{{ $report->generated_at?->format('d M Y H:i') ?: '-' }}</td>
                <td>
                    @if ($report->status === 'completed')
                        <a class="btn" href="{{ route('reports.show', $report) }}">Buka PDF</a>
                    @else
                        <span class="muted">Menunggu diproses</span>
                    @endif
                    @if(auth()->user()?->isAdmin())
                    <form class="inline" method="post" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Hapus laporan ini? File PDF-nya ikut terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Hapus</button>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Belum ada laporan.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $reports->links() }}</div>
@endsection
