@props(['tone' => 'default'])

{{--
    Tone memakai token pendamping (success/warn/info/danger).
    Default = ungu badge lama, output identik byte (tanpa style).
--}}

@php
    $tones = [
        'success' => 'background:var(--success-bg);color:var(--success-text);',
        'warn' => 'background:var(--warn-bg);color:var(--warn-text);',
        'info' => 'background:var(--accent-soft);color:var(--accent);',
        'danger' => 'background:var(--danger-bg);color:var(--danger-text);',
    ];
    $toneStyle = $tones[$tone] ?? '';
@endphp

@if($toneStyle !== '')
<span {{ $attributes->merge(['class' => 'badge', 'style' => $toneStyle]) }}>{{ $slot }}</span>
@else
<span {{ $attributes->merge(['class' => 'badge']) }}>{{ $slot }}</span>
@endif
