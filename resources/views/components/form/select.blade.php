@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'required' => false,
    'placeholder' => 'Seleziona...',
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
    
    <select 
        name="{{ $name }}" 
        id="{{ $name }}"
        {{ $attributes->merge(['class' => 'form-select']) }}
        @if($required) required @endif
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $key => $option)
            <option value="{{ is_array($option) ? $option['value'] : $key }}" 
                {{ old($name, $value) == (is_array($option) ? $option['value'] : $key) ? 'selected' : '' }}>
                {{ is_array($option) ? $option['label'] : $option }}
            </option>
        @endforeach
    </select>
    
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
