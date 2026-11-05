@props([
    'name',
    'id' => null,
    'required' => false,
    'disabled' => false,
    'placeholder' => 'Seleziona...',
    'allowClear' => false,
    'minSearch' => null,
])
@php
    // Bloquea usos legacy que intenten forzar múltiples o tags
    $sanitizedAttributes = $attributes->except(['multiple', 'data-tags', 'tags']);
    $allowClearFlag = $allowClear === false ? '0' : '1';
@endphp

<select
    data-ui="select-search"
    name="{{ $name }}"
    @if($id) id="{{ $id }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    data-placeholder="{{ $placeholder }}"
    data-allow-clear="{{ $allowClearFlag }}"
    @if(!is_null($minSearch)) data-min-search="{{ $minSearch }}" @endif
    {{ $sanitizedAttributes->merge(['class' => 'form-select']) }}
>
    {{ $slot }}
</select>
