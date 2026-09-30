@props(['variant' => 'default', 'type' => 'button', 'href' => null])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn'.($variant === 'primary' ? ' primary' : '')]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn'.($variant === 'primary' ? ' primary' : '')]) }}>{{ $slot }}</button>
@endif
