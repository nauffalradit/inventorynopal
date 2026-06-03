@extends('layouts.app')

@section('content')
    <h1>Notif & Komunikasi</h1>

    <section class="card" style="margin-bottom:14px">
        <h2>Kirim Notifikasi</h2>
        <form method="post" action="{{ route('notifications.store') }}">
            @csrf
            <div class="form-grid">
                <label>Channel
                    <select name="channel" required>
                        <option value="internal">Internal</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </label>
                <label>Penerima
                    <input name="recipient" required>
                </label>
                <label>Subjek
                    <input name="subject" required>
                </label>
            </div>
            <label style="margin-top:12px">Pesan
                <textarea name="message" required></textarea>
            </label>
            <div class="actions"><button class="btn primary" type="submit">Kirim</button></div>
        </form>
    </section>

    <table>
        <thead><tr><th>Channel</th><th>Penerima</th><th>Subjek</th><th>Status</th><th>Waktu</th></tr></thead>
        <tbody>
        @forelse ($notifications as $notification)
            <tr>
                <td><span class="badge">{{ $notification->channel }}</span></td>
                <td>{{ $notification->recipient }}</td>
                <td>{{ $notification->subject }}</td>
                <td>{{ $notification->status }}</td>
                <td>{{ $notification->sent_at?->format('d M Y H:i') ?: $notification->created_at->format('d M Y H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Belum ada notifikasi.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $notifications->links() }}</div>
@endsection
