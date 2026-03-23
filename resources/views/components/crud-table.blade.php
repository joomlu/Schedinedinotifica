@props([
    'title',
    'subtitle' => null,
    'searchPlaceholder' => '',
    'searchId',
    'createText',
    'createTarget',
    'createHref' => null,
    'tableId',
    'paginator',
    'createDisabled' => false,
    'createTooltip' => null,
])

@php
    $emptyRowId = $tableId . 'EmptyRow';
    $searchMetaId = $searchId . 'Meta';
    $searchResultsId = $searchId . 'Results';
    $searchClearId = $searchId . 'Clear';
    $isPaginatorValid = $paginator && method_exists($paginator, 'links') && method_exists($paginator, 'firstItem');
    $rowCount = $paginator && method_exists($paginator, 'count') ? $paginator->count() : 0;
    $currentQuery = trim((string) request('q', ''));
@endphp

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-column gap-3">
            <div>
                <h5 class="card-title mb-0">{{ $title }}</h5>
                @if(isset($subtitle))
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>

            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div class="d-flex flex-wrap align-items-start gap-2 flex-grow-1">
                    @isset($topbarLeft)
                        <div class="d-flex align-items-center">{{ $topbarLeft }}</div>
                    @endisset

                    <div class="crud-table-search-wrap" style="width: 360px; max-width: 100%;">
                        <div class="input-group position-relative">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ri-search-line"></i>
                            </span>
                            <input
                                type="text"
                                id="{{ $searchId }}"
                                class="form-control border-start-0"
                                placeholder="{{ $searchPlaceholder }}"
                                value="{{ $currentQuery }}"
                                autocomplete="off"
                            >
                            <button
                                class="btn btn-light border"
                                type="button"
                                id="{{ $searchClearId }}"
                                aria-label="Pulisci"
                                style="{{ $currentQuery !== '' ? '' : 'display:none;' }}"
                            >
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                        <div
                            class="mt-1 d-flex align-items-center gap-2 text-muted small"
                            id="{{ $searchMetaId }}"
                            style="display:none;"
                        >
                            <span class="badge bg-light text-body" id="{{ $searchResultsId }}" style="display:none;"></span>
                        </div>
                    </div>

                    @isset($topbarRight)
                        <div class="d-flex align-items-center">{{ $topbarRight }}</div>
                    @endisset
                </div>

                @if($createDisabled)
                    <span class="d-inline-block flex-shrink-0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $createTooltip }}">
                        <button type="button" class="btn btn-primary" disabled aria-disabled="true">
                            <i class="ri-add-line align-middle me-1"></i>
                            {{ $createText }}
                        </button>
                    </span>
                @elseif($createHref)
                    <a
                        href="{{ $createHref }}"
                        class="btn btn-primary flex-shrink-0"
                    >
                        <i class="ri-add-line align-middle me-1"></i>
                        {{ $createText }}
                    </a>
                @else
                    <button
                        type="button"
                        class="btn btn-primary flex-shrink-0"
                        data-bs-toggle="modal"
                        data-bs-target="{{ $createTarget }}"
                    >
                        <i class="ri-add-line align-middle me-1"></i>
                        {{ $createText }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="{{ $tableId }}">
                <thead>
                    {{ $columns }}
                </thead>
                <tbody>
                    {{ $rows }}
                    <tr id="{{ $emptyRowId }}" data-empty-state="1" class="{{ $rowCount ? 'd-none' : '' }}" style="{{ $rowCount ? 'display:none;' : '' }}">
                        <td colspan="100%" class="text-center text-muted">Nessun risultato.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($isPaginatorValid)
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div class="text-muted small d-flex align-items-center mb-0" style="min-height: 38px; line-height: 1;">
                    Mostrando {{ $paginator->firstItem() ?? 0 }}&ndash;{{ $paginator->lastItem() ?? 0 }} di {{ $paginator->total() }} risultati
                </div>
                <div class="ms-auto d-flex align-items-center" style="min-height: 38px;">
                    {{ $paginator->links('vendor.pagination.bootstrap-5-clean') }}
                </div>
            </div>
        @elseif(app()->environment('local'))
            <div class="alert alert-danger mt-3" role="alert">
                Usa paginate() nel controller e passa un paginator valido a x-crud-table.
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const input = document.getElementById('{{ $searchId }}');
            const clearBtn = document.getElementById('{{ $searchClearId }}');
            if (!input) return;

            let debounceTimer = null;
            const submitSearch = function () {
                const url = new URL(window.location.href);
                const q = (input.value || '').trim();

                if (q) {
                    url.searchParams.set('q', q);
                } else {
                    url.searchParams.delete('q');
                }

                // On new search always start from first page
                url.searchParams.delete('page');
                window.location.assign(url.toString());
            };

            input.addEventListener('input', function () {
                if (clearBtn) {
                    clearBtn.style.display = input.value.trim() ? '' : 'none';
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitSearch, 350);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                clearTimeout(debounceTimer);
                submitSearch();
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    input.value = '';
                    clearBtn.style.display = 'none';
                    submitSearch();
                });
            }
        } catch (e) {
            console.error('crud-table filter init error', e);
        }
    });
</script>
@endpush
