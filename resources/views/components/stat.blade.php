@props(['label' => ''])

{{--
    Satu angka ringkasan (dipakai berderet di .stats-strip header list).
    Nilai lewat slot agar format bebas (Rp, jumlah, persen).
--}}

<div class="stat-item"><small>{{ $label }}</small><b>{{ $slot }}</b></div>
