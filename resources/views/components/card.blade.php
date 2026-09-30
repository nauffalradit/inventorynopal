@props(['title' => null])

<section {{ $attributes->merge(['class' => 'card']) }} style="margin-bottom:14px;{{ $attributes->get('style') }}">
    @if($title)<h2>{{ $title }}</h2>@endif
    {{ $slot }}
</section>
