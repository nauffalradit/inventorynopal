@extends('layouts.app')

@section('content')
    <h1>Detail Notifikasi</h1>

    <section class="card" style="margin-bottom:14px">
        <h2>{{ $notification->subject }}</h2>
        <table>
            <tbody>
                <tr><th style="width:140px">Penerima</th><td>{{ $notification->recipient }}</td></tr>
                <tr><th>Channel</th><td><span class="badge">{{ $notification->channel }}</span></td></tr>
                <tr><th>Status</th><td><span class="badge">{{ $notification->status }}</span></td></tr>
                <tr><th>Dibuat</th><td>{{ $notification->created_at->format('d M Y H:i') }}</td></tr>
                <tr><th>Terkirim</th><td>{{ $notification->sent_at?->format('d M Y H:i') ?: '-' }}</td></tr>
            </tbody>
        </table>
    </section>

    <section class="card" style="margin-bottom:14px">
        <h2>Isi Pesan</h2>
        <p style="white-space:pre-wrap">{{ $notification->message }}</p>
    </section>

    <div class="actions">
        <a class="btn" href="{{ route('notifications.index') }}">Kembali</a>
    </div>
@endsection
