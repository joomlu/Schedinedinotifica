@props([
    'name',
    'id' => null,
    'value' => null,
    'variant' => 'single', // single | range | period-start | period-end | checkin | checkout | birth
    'group' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'minDate' => null,
    'maxDate' => null,
    'placeholder' => null,
    'format' => 'Y-m-d',
    'altFormat' => 'd/m/Y',
    'altInput' => true,
    'defaultDate' => null,
])
@php
    $effectiveDefaultDate = $defaultDate ?? $value;
    $role = null;
    if (in_array($variant, ['period-start', 'checkin'], true)) {
        $role = 'start';
    } elseif (in_array($variant, ['period-end', 'checkout'], true)) {
        $role = 'end';
    }
@endphp

<input
    type="text"
    data-ui="calendario"
    data-provider="flatpickr"
    data-calendar-variant="{{ $variant }}"
    data-date-format="{{ $format }}"
    data-default-date="{{ $effectiveDefaultDate }}"
    data-deafult-date="{{ $effectiveDefaultDate }}"
    @if($variant === 'range') data-range-date="true" @endif
    @if($group) data-calendar-group="{{ $group }}" @endif
    @if($role) data-calendar-role="{{ $role }}" @endif
    @if($altInput) data-alt-input="1" @endif
    @if($altFormat) data-alt-format="{{ $altFormat }}" @endif
    @if($minDate) data-min-date="{{ $minDate }}" @endif
    @if($maxDate) data-max-date="{{ $maxDate }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    name="{{ $name }}"
    value="{{ $value }}"
    @if($id) id="{{ $id }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    {{ $attributes->merge(['class' => 'form-control']) }}
/>
