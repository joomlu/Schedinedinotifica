@props([
    'name',
    'id' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'format' => 'd/m/Y',
    'enableTime' => false,
    'time24hr' => false,
    'minDate' => null,
    'maxDate' => null,
    'altInput' => false,
    'altFormat' => null,
])

<input
    type="text"
    data-ui="datepicker"
    data-provider="flatpickr"
    name="{{ $name }}"
    value="{{ $value }}"
    @if($id) id="{{ $id }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($format) data-format="{{ $format }}" @endif
    @if($enableTime) data-enable-time="1" @endif
    @if($time24hr) data-time-24hr="1" @endif
    @if($minDate) data-min-date="{{ $minDate }}" @endif
    @if($maxDate) data-max-date="{{ $maxDate }}" @endif
    @if($altInput) data-alt-input="1" @endif
    @if($altFormat) data-alt-format="{{ $altFormat }}" @endif
    {{ $attributes->merge(['class' => 'form-control']) }}
/>
