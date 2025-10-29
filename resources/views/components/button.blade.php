@props([
    'href' => '#',
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
    'size' => '', // sm, lg
    'icon' => null,
    'outline' => false,
])

<a 
    href="{{ $href }}" 
    {{ $attributes->merge(['class' => 'btn btn-' . ($outline ? 'outline-' : '') . $variant . ($size ? ' btn-' . $size : '')]) }}
>
    @if($icon)
        <i class="{{ $icon }} align-middle me-1"></i>
    @endif
    {{ $slot }}
</a>
