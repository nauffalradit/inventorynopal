@extends('layouts.app')

@section('content')
    <h1>Detail Notifikasi</h1>

    <x-card title="{{ $notification->subject }}">
        <table>
            <tbody>
                <tr><th style="width:140px">Penerima</th><td>{{ $notification->recipient }}</td></tr>
                <tr><th>Channel</th><td><x-badge>{{ $notification->channel }}</x-badge></td></tr>
                <tr><th>Status</th><td><x-badge>{{ $notification->status }}</x-badge></td></tr>
                <tr><th>Dibuat</th><td>{{ $notification->created_at->format('d M Y H:i') }}</td></tr>
                <tr><th>Terkirim</th><td>{{ $notification->sent_at?->format('d M Y H:i') ?: '-' }}</td></tr>
                <tr><th>Dibaca</th><td>{{ $notification->read_at?->format('d M Y H:i') ?: 'Belum dibaca' }}</td></tr>
            </tbody>
        </table>
    </x-card>

    <x-card title="Isi Pesan">
        <p style="white-space:pre-wrap">{{ $notification->message }}</p>
    </x-card>

    <div class="actions">
        <x-btn href="{{ route('notifications.index') }}">Kembali</x-btn>
        @if($notification->read_at === null && (auth()->user()?->isAdmin() || $notification->recipient === auth()->user()?->email))
        <form class="inline" method="post" action="{{ route('notifications.read', $notification) }}">
            @csrf
            @method('PATCH')
            <x-btn variant="primary" type="submit">Tandai dibaca</x-btn>
        </form>
        @endif
    </div>
@endsection
