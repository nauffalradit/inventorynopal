@props(['label' => '', 'name' => '', 'hint' => null])

<label {{ $attributes }}>
    {{ $label }}
    {{ $slot }}
    @if($hint)<span class="field-note muted">{{ $hint }}</span>@endif
    @if($name && $errors->has($name))<span class="error">{{ $errors->first($name) }}</span>@endif
</label>
