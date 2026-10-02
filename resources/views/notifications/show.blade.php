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
                <tr><th>Dibaca</th><td>{{ $notification->read_at?->format('d M Y H:i') ?: 'Belum dibaca' }}</td>
            </tbody>
        </table>
    </section>

    <section class="card" style="margin-bottom:14px">
        <h2>Isi Pesan</h2>
        <p style="white-space:pre-wrap">{{ $notification->message }}</p>
    </section>

    <div class="actions">
        <a class="btn" href="{{ route('notifications.index') }}">Kembali</a>
        @if($notification->read_at === null && (auth()->user()?->isAdmin() || $notification->recipient === auth()->user()?->email))
        <form class="inline" method="post" action="{{ route('notifications.read', $notification) }}">
            @csrf
            @method('PATCH')
            <button class="btn primary" type="submit">Tandai dibaca</button>
        </form>
        @endif
    </div>
@endsection
