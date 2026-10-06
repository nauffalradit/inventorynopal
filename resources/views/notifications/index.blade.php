@extends('layouts.app')

@section('content')
    <h1>Notif & Komunikasi</h1>

    @if(auth()->user()?->isAdmin())
    <style>
        /* Scoped halaman notifikasi: hanya sizing/spacing/type — tanpa ubah warna global */
        .notif-form h2 { font-size:17px; }
        .notif-form .sender-line { margin:0 0 18px; font-size:13px; }
        .notif-pills { display:flex; flex-wrap:wrap; gap:8px; margin:4px 0 2px; }
        .notif-pills label { display:inline-flex; align-items:center; min-height:41px; padding:8px 14px; border:1px solid var(--line-strong); border-radius:999px; background:var(--surface); cursor:pointer; font-size:13px; font-weight:500; }
        .notif-pills input { position:absolute; opacity:0; pointer-events:none; }
        .notif-pills label:has(input:checked) { border-color:var(--accent); background:var(--accent-soft); color:var(--accent); }
        .notif-pills label:has(input:focus-visible) { outline:2px solid var(--accent); outline-offset:1px; }
        .notif-form textarea[name="message"] { min-height:140px; }
        .notif-form .field-note { display:block; margin-top:6px; font-size:12px; }
        .notif-table th, .notif-table td { padding:16px 14px; }
        .notif-table .when { white-space:nowrap; font-size:12px; }
        /* Daftar centang penerima (x-check-list): token U1, tanpa angka baru */
        .check-list input[data-check-search] { margin-bottom:8px; }
        label.check-all { display:flex; align-items:center; gap:8px; min-height:41px; padding:8px 12px; border:1px dashed var(--line-strong); border-radius:var(--radius-md); cursor:pointer; }
        label.check-all input { width:auto; min-height:0; margin:0; accent-color:var(--accent); }
        .check-options { border:1px solid var(--line-strong); border-radius:var(--radius-md); margin-top:8px; max-height:200px; overflow:auto; }
        label.check-row { display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid var(--line); cursor:pointer; }
        label.check-row:last-child { border-bottom:0; }
        label.check-row:hover { background:var(--canvas); }
        label.check-row input { width:auto; min-height:0; margin:0; padding:0; accent-color:var(--accent); }
        .check-avatar { width:30px; height:30px; border-radius:50%; background:var(--accent-soft); color:var(--accent); display:grid; place-items:center; font-weight:700; font-size:12px; flex:none; }
        .check-id { min-width:0; flex:1; display:grid; }
        .check-id strong { font-weight:600; }
        .check-id .muted { font-size:12px; overflow-wrap:anywhere; }
        .check-empty { padding:12px; }
        .check-hidden { display:none !important; }
        /* Panel pratinjau */
        .notif-preview .preview-from { margin:0 0 12px; font-size:12px; }
        .preview-box { border:1px solid var(--line); border-radius:var(--radius-lg); padding:16px; background:var(--canvas); }
        .preview-box h3 { margin:8px 0 6px; font-size:15px; overflow-wrap:anywhere; }
        .preview-box p { margin:0; white-space:pre-wrap; overflow-wrap:anywhere; }
        .preview-sum { display:grid; gap:8px; margin:14px 0 0; font-size:13px; }
        .preview-sum div { display:flex; justify-content:space-between; gap:12px; }
        .preview-sum dt { color:var(--muted); }
        .preview-sum dd { margin:0; font-weight:650; }
        .preview-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
        .char-count { font-size:12px; }
    </style>
    <div class="grid two" style="align-items:start">
    <x-card title="Kirim Notifikasi" class="notif-form">
        <p class="muted sender-line">Dari: <strong>{{ $sender }}</strong> (pengirim sistem, tidak dapat diubah).</p>
        <form id="notif-form" method="post" action="{{ route('notifications.store') }}">
            @csrf
            <div class="form-grid">
                <x-field label="Channel" name="channel" hint="Label jalur arsip — pengiriman tercatat di log.">
                    <select name="channel" required>
                        <option value="internal">Internal</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </x-field>
                <div style="grid-column:span 2">
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
                <span class="muted" style="font-size:12px;font-weight:650">Penerima spesifik</span>
                <x-check-list name="recipients" :options="$users" :selected="old('recipients', [])" />
                <span class="field-note muted">Cari lalu centang, maks 50. Hanya tampil saat cakupan "Akun tertentu".</span>
                @error('recipients') <span class="error">{{ $message }}</span> @enderror
                @error('recipients.*') <span class="error">{{ $message }}</span> @enderror
            </div>
            <x-field label="Subjek" name="subject" style="margin-top:14px;display:grid">
                <input name="subject" value="{{ old('subject') }}" required>
            </x-field>
            <x-field label="Pesan" name="message" style="margin-top:14px;display:grid">
                <textarea name="message" required>{{ old('message') }}</textarea>
            </x-field>
            <div class="actions" style="justify-content:space-between">
                <span class="muted char-count" data-char-count>0 karakter</span>
                <x-btn variant="primary" type="submit" id="notif-send">Kirim</x-btn>
            </div>
        </form>
    </x-card>
    <x-card title="Pratinjau" class="notif-preview">
        <p class="muted preview-from">Dari <strong>{{ $sender }}</strong></p>
        <div class="preview-box">
            <h3 data-preview-subject>Subjek pesan</h3>
            <p class="muted" data-preview-message>Isi pesan akan tampil di sini.</p>
        </div>
        <dl class="preview-sum">
            <div><dt>Channel</dt><dd data-preview-channel>Internal</dd></div>
            <div><dt>Penerima</dt><dd data-preview-count>0 akun</dd></div>
        </dl>
        <div class="preview-chips" data-preview-chips></div>
    </x-card>
    </div>
    <script>
    /* Kotak penerima bersyarat + search + pratinjau live (progressive enhancement;
       validasi tetap di server, tombol aktif lagi saat form valid). */
    (() => {
        const form = document.getElementById('notif-form');
        if (!form) return;
        const box = document.getElementById('recipients-box');
        const search = box.querySelector('[data-check-search]');
        const all = box.querySelector('[data-check-all]');
        const rows = [...box.querySelectorAll('.check-row')];
        const boxes = rows.map((r) => r.querySelector('input[type="checkbox"]'));
        const channel = form.querySelector('select[name="channel"]');
        const subject = form.querySelector('input[name="subject"]');
        const message = form.querySelector('textarea[name="message"]');
        const send = document.getElementById('notif-send');
        const prev = {
            subject: document.querySelector('[data-preview-subject]'),
            message: document.querySelector('[data-preview-message]'),
            channel: document.querySelector('[data-preview-channel]'),
            count: document.querySelector('[data-preview-count]'),
            chips: document.querySelector('[data-preview-chips]'),
            chars: form.querySelector('[data-char-count]'),
        };
        const scope = () => form.querySelector('input[name="scope"]:checked')?.value || 'specific';
        const targets = () => {
            const s = scope();
            if (s === 'specific') return boxes.filter((b) => b.checked);
            if (s === 'all') return boxes.slice();

            return boxes.filter((b) => b.closest('.check-row').dataset.role === s);
        };
        const syncBox = () => {
            const show = scope() === 'specific';
            box.style.display = show ? '' : 'none';
            boxes.forEach((b) => { b.disabled = !show; });
        };
        const syncAll = () => {
            const visible = boxes.filter((b) => !b.closest('.check-row').classList.contains('check-hidden'));
            const checked = visible.filter((b) => b.checked);
            all.checked = visible.length > 0 && checked.length === visible.length;
            all.indeterminate = checked.length > 0 && checked.length < visible.length;
        };
        const syncPreview = () => {
            const list = targets();
            prev.count.textContent = list.length + ' akun';
            prev.channel.textContent = channel.options[channel.selectedIndex]?.text || channel.value;
            prev.subject.textContent = subject.value || 'Subjek pesan';
            prev.message.textContent = message.value || 'Isi pesan akan tampil di sini.';
            prev.chars.textContent = message.value.length + ' karakter';
            prev.chips.innerHTML = list.slice(0, 8).map((b) => {
                const first = (b.closest('.check-row').dataset.name || '').split(' ')[0];
                const safe = document.createElement('span');
                safe.className = 'badge';
                safe.textContent = first;
                return safe.outerHTML;
            }).join('') + (list.length > 8 ? '<span class="badge">+' + (list.length - 8) + '</span>' : '');
            send.disabled = !(list.length > 0 && subject.value.trim() !== '' && message.value.trim() !== '');
        };
        const syncPills = () => {
            form.querySelectorAll('.notif-pills label').forEach((lab) => {
                lab.setAttribute('aria-pressed', lab.querySelector('input:checked') ? 'true' : 'false');
            });
        };
        const sync = () => { syncBox(); syncAll(); syncPreview(); syncPills(); };
        form.querySelectorAll('input[name="scope"]').forEach((r) => r.addEventListener('change', sync));
        search.addEventListener('input', () => {
            const q = search.value.toLowerCase();
            rows.forEach((r) => r.classList.toggle('check-hidden', !(r.dataset.search || '').includes(q)));
            syncAll();
        });
        all.addEventListener('change', () => {
            boxes.forEach((b) => {
                if (!b.closest('.check-row').classList.contains('check-hidden')) b.checked = all.checked;
            });
            syncPreview();
        });
        boxes.forEach((b) => b.addEventListener('change', () => { syncAll(); syncPreview(); }));
        [subject, message, channel].forEach((el) => el.addEventListener('input', syncPreview));
        sync();
    })();
    </script>
    @endif

    <x-table class="notif-table" :headers="['Channel', 'Penerima', 'Subjek', 'Status', 'Dibaca', 'Waktu', 'Aksi']">
        @forelse ($notifications as $notification)
            <tr>
                <td><x-badge>{{ $notification->channel }}</x-badge></td>
                <td>{{ $notification->recipient }}</td>
                <td><x-btn href="{{ route('notifications.show', $notification) }}">{{ $notification->subject }}</x-btn></td>
                <td><x-badge>{{ $notification->status }}</x-badge></td>
                <td>{{ $notification->read_at?->format('d M Y H:i') ?: '—' }}</td>
                <td class="when muted">{{ $notification->sent_at?->format('d M Y H:i') ?: $notification->created_at->format('d M Y H:i') }}</td>
                <td>
                    @if($notification->read_at === null && (auth()->user()?->isAdmin() || $notification->recipient === auth()->user()?->email))
                    <form class="inline" method="post" action="{{ route('notifications.read', $notification) }}">
                        @csrf
                        @method('PATCH')
                        <x-btn type="submit">Tandai dibaca</x-btn>
                    </form>
                    @endif
                    @if(auth()->user()?->isAdmin())
                    <form class="inline" method="post" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit">Hapus</x-btn>
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
