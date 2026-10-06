@props(['variant' => 'default', 'type' => 'button', 'href' => null])

{{--
    Varian ghost = tombol outline kecil untuk aksi baris (Detail/Hapus),
    mengikuti .btn dasar (min-height sentuh tetap 39px).
--}}

@php
    $class = match ($variant) {
        'primary' => 'btn primary',
        'ghost' => 'btn ghost',
        'ghost-danger' => 'btn ghost-danger',
        default => 'btn',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</button>
@endif
