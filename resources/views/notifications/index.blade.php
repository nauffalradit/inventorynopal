@extends('layouts.app')

@section('content')
    <h1>Notif & Komunikasi</h1>

    @if(auth()->user()?->isAdmin())
    <style>
        /* Scoped halaman notifikasi: hanya sizing/spacing/type — tanpa ubah warna global */
        .notif-form h2 { font-size:17px; }
        .notif-form .sender-line { margin:0 0 18px; font-size:13px; }
        .notif-pills { display:flex; flex-wrap:wrap; gap:10px; margin:4px 0 2px; }
        .notif-pills label { display:inline-flex; align-items:center; min-height:41px; padding:8px 16px; border:1px solid #dfe4ed; border-radius:999px; background:#fff; cursor:pointer; font-size:13px; font-weight:650; }
        .notif-pills input { width:auto; min-height:0; margin:0 8px 0 0; padding:0; accent-color:#5b5ce2; }
        .notif-pills label:has(input:checked) { border-color:#5b5ce2; }
        .notif-form select[multiple] { min-height:180px; padding:10px 11px; font-size:13px; }
        .notif-form textarea[name="message"] { min-height:140px; }
        .notif-form .field-note { display:block; margin-top:6px; font-size:12px; }
        .notif-table th, .notif-table td { padding:16px 14px; }
        .notif-table .when { white-space:nowrap; font-size:12px; }
    </style>
    <section class="card notif-form" style="margin-bottom:14px">
        <h2>Kirim Notifikasi</h2>
        <p class="muted sender-line">Dari: <strong>{{ $sender }}</strong> (pengirim sistem, tidak dapat diubah).</p>
        <form method="post" action="{{ route('notifications.store') }}">
            @csrf
            <div class="form-grid">
                <label>Channel
                    <select name="channel" required>
                        <option value="internal">Internal</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                    <span class="field-note muted">Label jalur arsip — pengiriman tercatat di log.</span>
                    @error('channel') <span class="error">{{ $message }}</span> @enderror
                </label>
                <div>
                    <span class="muted" style="font-size:12px;font-weight:650">Cakupan penerima</span>
                    <div class="notif-pills" role="radiogroup" aria-label="Cakupan penerima">
                        <label><input type="radio" name="scope" value="specific" checked>Akun tertentu</label>
                        <label><input type="radio" name="scope" value="all">Semua akun aktif</label>
                        <label><input type="radio" name="scope" value="admin">Semua admin</label>
                        <label><input type="radio" name="scope" value="staff">Semua staff</label>
                    </div>
                    @error('scope') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div id="recipients-box" style="margin-top:14px">
                <label>Penerima spesifik
                    <select name="recipients[]" multiple>
                        @foreach ($users as $user)
                            <option value="{{ $user->email }}">{{ $user->name }} — {{ $user->email }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                    <span class="field-note muted">Tahan Ctrl/klik untuk pilih banyak, maks 50. Hanya tampil saat cakupan "Akun tertentu".</span>
                    @error('recipients') <span class="error">{{ $message }}</span> @enderror
                    @error('recipients.*') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>
            <label style="margin-top:14px">Subjek
                <input name="subject" value="{{ old('subject') }}" required>
                @error('subject') <span class="error">{{ $message }}</span> @enderror
            </label>
            <label style="margin-top:14px">Pesan
                <textarea name="message" required>{{ old('message') }}</textarea>
                @error('message') <span class="error">{{ $message }}</span> @enderror
            </label>
            <div class="actions"><button class="btn primary" type="submit">Kirim</button></div>
        </form>
    </section>
    <script>
    /* Tampilkan kotak penerima hanya untuk cakupan "Akun tertentu". */
    (() => {
        const box = document.getElementById('recipients-box');
        if (!box) return;
        const select = box.querySelector('select');
        const sync = () => {
            const scope = document.querySelector('input[name="scope"]:checked')?.value || 'specific';
            const show = scope === 'specific';
            box.style.display = show ? '' : 'none';
            if (select) select.disabled = !show;
        };
        document.querySelectorAll('input[name="scope"]').forEach((r) => r.addEventListener('change', sync));
        sync();
    })();
    </script>
    @endif

    <table class="notif-table">
        <thead><tr><th>Channel</th><th>Penerima</th><th>Subjek</th><th>Status</th><th>Waktu</th></tr></thead>
        <tbody>
        @forelse ($notifications as $notification)
            <tr>
                <td><span class="badge">{{ $notification->channel }}</span></td>
                <td>{{ $notification->recipient }}</td>
                <td>{{ $notification->subject }}</td>
                <td>{{ $notification->status }}</td>
                <td class="when muted">{{ $notification->sent_at?->format('d M Y H:i') ?: $notification->created_at->format('d M Y H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Belum ada notifikasi.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $notifications->links() }}</div>
@endsection
