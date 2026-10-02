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
    <x-card title="Kirim Notifikasi" class="notif-form">
        <p class="muted sender-line">Dari: <strong>{{ $sender }}</strong> (pengirim sistem, tidak dapat diubah).</p>
        <form method="post" action="{{ route('notifications.store') }}">
            @csrf
            <div class="form-grid">
                <x-field label="Channel" name="channel" hint="Label jalur arsip — pengiriman tercatat di log.">
                    <select name="channel" required>
                        <option value="internal">Internal</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </x-field>
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
            <x-field label="Subjek" name="subject" style="margin-top:14px;display:grid">
                <input name="subject" value="{{ old('subject') }}" required>
            </x-field>
            <x-field label="Pesan" name="message" style="margin-top:14px;display:grid">
                <textarea name="message" required>{{ old('message') }}</textarea>
            </x-field>
            <div class="actions"><x-btn variant="primary" type="submit">Kirim</x-btn></div>
        </form>
    </x-card>
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

    <x-table class="notif-table" :headers="['Channel', 'Penerima', 'Subjek', 'Status', 'Dibaca', 'Waktu', 'Aksi']">
        @forelse ($notifications as $notification)
            <tr>
                <td><x-badge>{{ $notification->channel }}</x-badge></td>
                <td>{{ $notification->recipient }}</td>
                <td><a class="btn" href="{{ route('notifications.show', $notification) }}">{{ $notification->subject }}</a></td>
                <td>{{ $notification->status }}</td>
                <td>{{ $notification->read_at?->format('d M Y H:i') ?: '—' }}</td>
                <td class="when muted">{{ $notification->sent_at?->format('d M Y H:i') ?: $notification->created_at->format('d M Y H:i') }}</td>
                <td>
                    @if($notification->read_at === null && (auth()->user()?->isAdmin() || $notification->recipient === auth()->user()?->email))
                    <form class="inline" method="post" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn" type="submit">Tandai dibaca</button>
                    </form>
                    @endif
                    @if(auth()->user()?->isAdmin())
                    <form class="inline" method="post" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn" type="submit">Hapus</button>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <x-empty-state :colspan="7" message="Belum ada notifikasi." />
        @endforelse
    </x-table>
    <div style="margin-top:14px">{{ $notifications->links() }}</div>
@endsection
