@props(['name' => 'recipients', 'options' => [], 'selected' => [], 'searchPlaceholder' => 'Cari nama atau email'])

{{--
    Daftar centang penerima — reusable (dipakai halaman notifikasi, kontrak U3).
    Kontrak server tetap: field `name[]`, validasi max 50 + exists users aktif.
    Opsi = model User (name, email, role); selected = array email (old input).
--}}

@php
    $selected = collect($selected)->map(fn ($v) => (string) $v)->all();
    $initials = function ($label) {
        $words = preg_split('/\s+/', trim((string) $label), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $out .= mb_strtoupper(mb_substr($w, 0, 1));
        }

        return $out !== '' ? $out : '?';
    };
@endphp

<div {{ $attributes->merge(['class' => 'check-list']) }} data-check-list="{{ $name }}">
    <input type="text" data-check-search placeholder="{{ $searchPlaceholder }}" autocomplete="off" aria-label="{{ $searchPlaceholder }}">
    <label class="check-all"><input type="checkbox" data-check-all><span>Pilih semua</span></label>
    <div class="check-options" role="group" aria-label="Daftar penerima">
        @forelse ($options as $option)
            <label
                class="check-row"
                data-search="{{ mb_strtolower($option->name.' '.$option->email) }}"
                data-name="{{ $option->name }}"
                data-role="{{ $option->role }}"
            >
                <input type="checkbox" name="{{ $name }}[]" value="{{ $option->email }}" @checked(in_array($option->email, $selected, true))>
                <span class="check-avatar" aria-hidden="true">{{ $initials($option->name) }}</span>
                <span class="check-id"><strong>{{ $option->name }}</strong><span class="muted">{{ $option->email }}</span></span>
                <x-badge>{{ $option->role }}</x-badge>
            </label>
        @empty
            <p class="muted check-empty">Belum ada akun aktif.</p>
        @endforelse
    </div>
</div>
