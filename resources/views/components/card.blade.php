@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'noPadding' => false,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $subtitle || $headerActions)
    <div class="card-header align-items-center d-flex">
        <div class="flex-grow-1">
            @if($title)
                <h4 class="card-title mb-0">{{ $title }}</h4>
            @endif
            @if($subtitle)
                <p class="text-muted mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if($headerActions)
            <div class="flex-shrink-0">
                {{ $headerActions }}
            </div>
        @endif
    </div>
    @endif
    
    <div class="{{ $noPadding ? 'card-body p-0' : 'card-body' }}">
        {{ $slot }}
    </div>
</div>
