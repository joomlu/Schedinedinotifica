@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'helpText' => null,
])

<div class="mb-3">
    @if($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
    >
    
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    
    @if($helpText)
        <div class="form-text">{{ $helpText }}</div>
    @endif
</div>
