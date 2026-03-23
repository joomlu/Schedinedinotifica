@php
    $previous = $previous ?? null;
    $next = $next ?? null;
    $isEdit = $isEdit ?? false;
    $showComponentiSave = $showComponentiSave ?? false;
    $showCircuitSaveButtons = $showCircuitSaveButtons ?? true;
    $primarySaveLabel = $primarySaveLabel ?? 'Salva';
@endphp

<div class="d-flex align-items-start gap-3 mt-4 flex-wrap">
    @if($previous)
        <button type="button" class="btn btn-soft-secondary btn-label prevtab" data-previous="{{ $previous }}">
            <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>Indietro
        </button>
    @endif

    <div class="ms-auto d-flex align-items-start gap-2 flex-wrap">
        @if($showCircuitSaveButtons)
            @if($showComponentiSave)
                <button type="submit" formnovalidate class="btn btn-soft-secondary btn-label" id="save-componenti-btn">
                    <i class="ri-group-line label-icon align-middle fs-16 ms-2"></i>Salva componenti
                </button>
            @endif

            <button type="submit" name="save_mode" value="draft" formnovalidate class="btn btn-soft-primary btn-label">
                <i class="ri-draft-line label-icon align-middle fs-16 ms-2"></i>Salva a bozza
            </button>

            <button type="submit" name="save_mode" value="to_arrivi" formnovalidate class="btn btn-soft-info btn-label fw-semibold text-info-emphasis">
                <i class="ri-route-line label-icon align-middle fs-16 ms-2"></i>Salva a arrivi
            </button>

            <button type="submit" name="save_mode" value="full" class="btn btn-success btn-label">
                <i class="ri-save-line label-icon align-middle fs-16 ms-2"></i>Salva a schedina
            </button>
        @else
            <button type="submit" name="save_mode" value="web" formnovalidate class="btn btn-soft-primary btn-label">
                <i class="ri-save-line label-icon align-middle fs-16 ms-2"></i>{{ $primarySaveLabel }}
            </button>
        @endif

        @if($next)
            <button type="button" class="btn btn-soft-success btn-label right nexttab" data-nexttab="{{ $next }}">
                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>Avanti
            </button>
        @endif
    </div>
</div>
