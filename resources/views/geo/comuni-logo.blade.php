@extends('layouts.master')

@section('title') Geo Comuni - Logo @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Geo @endslot
        @slot('title') Logo Comuni @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('geo.comuni.logo') }}" class="mb-3" id="geo-comuni-search-form">
                        <x-table-topbar
                            title="Geo Comuni"
                            subtitle="Carica stemma/logo del Comune"
                            searchPlaceholder="Cerca per Comune o CAP"
                            searchId="geo-comuni-search"
                        />
                        <input type="hidden" name="q" value="{{ $q }}" id="geo-comuni-hidden-q">
                    </form>

                    <div id="geo-comuni-results-panel">
                        <div class="table-responsive">
                            <table class="table align-middle table-striped">
                                <thead>
                                    <tr>
                                        <th>Comune</th>
                                        <th>Provincia</th>
                                        <th>Codice ISTAT</th>
                                        <th>Logo attuale</th>
                                        <th class="text-end">Azione</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($comuni as $comune)
                                        <tr>
                                            <td class="fw-semibold">{{ $comune->nome }}</td>
                                            <td>{{ optional($comune->provincia)->sigla }}</td>
                                            <td>{{ $comune->codice_istat }}</td>
                                            <td>
                                                @php $logoComune = $comune->logo_citta ?? $comune->logo; @endphp
                                                @if($logoComune)
                                                    <img src="{{ asset($logoComune) }}" alt="Logo {{ $comune->nome }}" style="max-height:50px;" class="img-fluid">
                                                @else
                                                    <span class="text-muted">Nessun logo</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('geo.comuni.logo.store', $comune->id) }}" enctype="multipart/form-data" class="d-inline-flex align-items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="q" value="{{ $q }}">
                                                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-control form-control-sm" required>
                                                    <button type="submit" class="btn btn-primary btn-sm">Carica</button>
                                                </form>
                                                @if($logoComune)
                                                    <form method="POST" action="{{ route('geo.comuni.logo.destroy', $comune->id) }}" class="d-inline" data-confirm-label="{{ 'il logo del comune di ' . $comune->nome }}">
                                                        @csrf
                                                        <input type="hidden" name="q" value="{{ $q }}">
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Rimuovi</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">Nessun comune trovato</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3" id="geo-comuni-pagination">
                            {{ $comuni->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('geo-comuni-search');
        const hidden = document.getElementById('geo-comuni-hidden-q');
        const resultsPanel = document.getElementById('geo-comuni-results-panel');
        const pagination = document.getElementById('geo-comuni-pagination');
        const form = document.getElementById('geo-comuni-search-form');

        if (!input || !hidden || !resultsPanel || !pagination) {
            return;
        }

        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
            });
        }

        input.setAttribute('name', 'q');
        input.value = hidden.value || '';

        const clearBtn = document.getElementById('geo-comuni-search-clear');
        const setClearButtonState = () => {
            if (!clearBtn) {
                return;
            }
            clearBtn.style.display = input.value.trim() ? '' : 'none';
        };

        const preserveFocus = () => {
            if (document.activeElement !== input) {
                input.focus();
            }
            const caretPosition = input.value.length;
            input.setSelectionRange(caretPosition, caretPosition);
        };

        let debounceTimer = null;
        let requestId = 0;

        const applyHtml = (html) => {
            const parser = new DOMParser();
            const parsed = parser.parseFromString(html, 'text/html');
            const nextResults = parsed.getElementById('geo-comuni-results-panel');
            const nextPagination = parsed.getElementById('geo-comuni-pagination');
            const nextHidden = parsed.getElementById('geo-comuni-hidden-q');

            if (nextResults) {
                resultsPanel.innerHTML = nextResults.innerHTML;
            }
            if (nextPagination) {
                pagination.innerHTML = nextPagination.innerHTML;
            }
            if (nextHidden) {
                hidden.value = nextHidden.value;
            }

            preserveFocus();
        };

        const submitSearch = function () {
            const url = new URL(window.location.href);
            const q = (input.value || '').trim();

            if (q !== '') {
                url.searchParams.set('q', q);
            } else {
                url.searchParams.delete('q');
            }

            url.searchParams.delete('page');
            history.replaceState({}, '', url.toString());

            const currentRequestId = ++requestId;
            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                credentials: 'same-origin'
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Search request failed');
                    }
                    return response.text();
                })
                .then((html) => {
                    if (currentRequestId !== requestId) {
                        return;
                    }
                    applyHtml(html);
                })
                .catch(() => {
                    console.warn('Geo Comuni search refresh failed.');
                });
        };

        input.addEventListener('input', function () {
            hidden.value = input.value;
            setClearButtonState();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitSearch, 350);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }
            event.preventDefault();
            clearTimeout(debounceTimer);
            submitSearch();
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                hidden.value = '';
                setClearButtonState();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitSearch, 350);
                preserveFocus();
            });
        }

        setClearButtonState();
        preserveFocus();
    });
</script>
@endsection
