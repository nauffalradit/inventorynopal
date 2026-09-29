@extends('layouts.app')

@section('content')
    <h1>Notif & Komunikasi</h1>

    @if(auth()->user()?->isAdmin())
    <section class="card" style="margin-bottom:14px">
        <h2>Kirim Notifikasi</h2>
        <p class="muted">Dari: <strong>{{ $sender }}</strong> (pengirim sistem, tidak dapat diubah). Penerima dipilih dari akun terdaftar & aktif — tidak ketik manual.</p>
        <form method="post" action="{{ route('notifications.store') }}">
            @csrf
            <div class="form-grid">
                <label>Channel (label jalur arsip — pengiriman tercatat di log)
                    <select name="channel" required>
                        <option value="internal">Internal</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                    @error('channel') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Cakupan penerima
                    <select name="scope" required>
                        <option value="specific">Akun tertentu (pilih di bawah)</option>
                        <option value="all">Semua akun aktif</option>
                        <option value="admin">Semua admin</option>
                        <option value="staff">Semua staff</option>
                    </select>
                    @error('scope') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Penerima spesifik (tahan Ctrl/klik untuk pilih banyak, maks 50)
                    <select name="recipients[]" multiple size="6">
                        @foreach ($users as $user)
                            <option value="{{ $user->email }}">{{ $user->name }} — {{ $user->email }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                    @error('recipients') <span class="error">{{ $message }}</span> @enderror
                    @error('recipients.*') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>
            <div class="form-grid" style="margin-top:12px">
                <label>Subjek
                    <input name="subject" required>
                    @error('subject') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>
            <label style="margin-top:12px">Pesan
                <textarea name="message" required></textarea>
                @error('message') <span class="error">{{ $message }}</span> @enderror
            </label>
            <div class="actions"><button class="btn primary" type="submit">Kirim</button></div>
        </form>
    </section>
    @endif

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
