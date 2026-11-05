@props([
    'title',
    'subtitle' => null,
    'showSearch' => true,
    'searchPlaceholder' => 'Cerca...',
    'searchId' => 'table-search',
    // Compat: legacy props createText/createTarget restano supportati
    'createButtonText' => null,
    'createText' => null,
    'createButtonTarget' => null,
    'createTarget' => null,
    'createButtonSize' => 'md', // sm|md
    'resultsId' => null,
])

@php
    $btnText = $createButtonText ?? $createText ?? 'Nuovo';
    $btnTarget = $createButtonTarget ?? $createTarget;
    $btnSizeClass = $createButtonSize === 'sm' ? 'btn-sm' : '';
@endphp

<div class="d-flex flex-column gap-3 mb-3">
    <div class="d-flex flex-column">
        <h5 class="mb-0">{{ $title }}</h5>
        @if($subtitle)
            <small class="text-muted">{{ $subtitle }}</small>
        @endif
    </div>

    @if($showSearch || $btnTarget)
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
            @if($showSearch)
                <div style="width: 360px; max-width: 100%;">
                    <div class="input-group position-relative">
                        <span class="input-group-text bg-light border-end-0"><i class="ri-search-line"></i></span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="{{ $searchId }}"
                               placeholder="{{ $searchPlaceholder }}"
                               autocomplete="off">
                        <button class="btn btn-light border" type="button" id="{{ $searchId }}-clear" aria-label="Pulisci" style="display:none;">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="mt-1 d-flex align-items-center gap-2 text-muted small" id="{{ $searchId }}-meta" style="display:none;">
                        @if($resultsId)
                            <span class="badge bg-light text-body" id="{{ $resultsId }}" style="display:none;"></span>
                        @endif
                    </div>
                </div>
            @endif

            @if($btnTarget)
                <button type="button" class="btn btn-primary {{ $btnSizeClass }} flex-shrink-0" data-bs-toggle="modal" data-bs-target="#{{ $btnTarget }}">
                    <i class="ri-add-line align-middle me-1"></i> {{ $btnText }}
                </button>
            @endif
        </div>
    @endif
</div>
