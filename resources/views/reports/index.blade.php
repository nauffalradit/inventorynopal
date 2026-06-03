@extends('layouts.app')

@section('content')
    <div class="actions" style="justify-content:space-between; margin-top:0">
        <h1 style="margin:0">Cetak Laporan</h1>
        <form method="post" action="{{ route('reports.store') }}">
            @csrf
            <button class="btn primary" type="submit">Buat Laporan PDF</button>
        </form>
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
                        <span class="muted">Menunggu worker</span>
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
