@extends('layouts.master')

@section('title') Tassa di soggiorno @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Configurazioni @endslot
        @slot('title') Tassa di soggiorno @endslot
    @endcomponent

    <div class="row config-page">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <x-table-topbar
                        title="Tassa di soggiorno"
                        subtitle="{{ $struttura->nome_struttura ?? 'Struttura' }} — {{ $struttura->citta }}"
                        :showSearch="false"
                    />

                    <div class="d-flex align-items-center gap-3 mb-3">
                        @php $logoComune = $struttura->logo_citta ?? null; @endphp
                        @if($logoComune)
                            <img src="{{ asset($logoComune) }}" alt="Logo città" style="max-height:60px;" class="rounded shadow-sm">
                        @endif
                        <div>
                            <div>{{ $struttura->nome_struttura }}</div>
                            <div class="text-muted">{{ $struttura->citta }} ({{ $struttura->provincia }})</div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-dati" data-bs-toggle="tab" data-bs-target="#pane-dati" type="button" role="tab" aria-controls="pane-dati" aria-selected="true">Dati tassa</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-regole" data-bs-toggle="tab" data-bs-target="#pane-regole" type="button" role="tab" aria-controls="pane-regole" aria-selected="false">Regole calcolo</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-esenzioni" data-bs-toggle="tab" data-bs-target="#pane-esenzioni" type="button" role="tab" aria-controls="pane-esenzioni" aria-selected="false">Esenzioni</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-export" data-bs-toggle="tab" data-bs-target="#pane-export" type="button" role="tab" aria-controls="pane-export" aria-selected="false">Anteprima export</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="pane-dati" role="tabpanel" aria-labelledby="tab-dati">
                            <form method="POST" action="{{ route('tassa_di_soggiorno.update') }}" class="row g-4">
                                @csrf
                                @method('PUT')
                                <div class="col-lg-8">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Aliquota (€ per notte)</label>
                                            <input type="number" step="0.01" min="0" max="9999" name="tassa_soggiorno" class="form-control" value="{{ old('tassa_soggiorno', $tassa->tassa_soggiorno) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Giorni massimo imponibili</label>
                                            <input type="number" min="0" max="365" name="giorni_massimo" class="form-control" value="{{ old('giorni_massimo', $tassa->giorni_massimo) }}">
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Data inizio periodo tassa</label>
                                            <x-calendario name="inizio" variant="period-start" group="tassa-periodo" :value="old('inizio', optional($tassa->inizio)->format('Y-m-d'))" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Data fine periodo tassa</label>
                                            <x-calendario name="fine" variant="period-end" group="tassa-periodo" :value="old('fine', optional($tassa->fine)->format('Y-m-d'))" />
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Età massima bambini</label>
                                            <input type="number" min="0" max="120" name="max_age_children" class="form-control" value="{{ old('max_age_children', $tassa->max_age_children) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Età minima adulti</label>
                                            <input type="number" min="0" max="120" name="min_age_adult" class="form-control" value="{{ old('min_age_adult', $tassa->min_age_adult) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card border shadow-sm h-100 mb-0">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <h5 class="card-title mb-0">
                                                    <span class="section-title-help">Periodo e regole
                                                        <x-ui.help title="Periodo tassa">
                                                            Il periodo di applicazione limita le notti tassabili alle sole date comprese tra inizio e fine. Se il soggiorno supera i giorni massimo, le notti eccedenti vengono conteggiate con codice 777 senza generare importo.
                                                        </x-ui.help>
                                                    </span>
                                                </h5>
                                            </div>
                                            <div class="text-muted small mb-3">Configurazione operativa della struttura corrente.</div>
                                            <div class="border rounded-3 p-3 bg-light-subtle mb-3">
                                                <div class="text-muted small">Comune / località</div>
                                                <div class="fw-semibold">{{ $struttura->citta }}{{ !empty($struttura->localita) ? ' - ' . $struttura->localita : '' }}</div>
                                            </div>
                                            <div class="border rounded-3 p-3 bg-warning-subtle text-warning-emphasis">
                                                <div class="fw-semibold mb-1">Codice 777</div>
                                                <div class="small mb-0">Non è un'esenzione. Il sistema lo usa automaticamente per segnalare i pernottamenti oltre il limite massimo imponibile.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Note</label>
                                    <textarea name="note" class="form-control" rows="4" placeholder="Note operative o specifiche del Comune">{{ old('note', $tassa->note) }}</textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Salva configurazione</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="pane-regole" role="tabpanel" aria-labelledby="tab-regole">
                            <div class="alert alert-info mb-0">
                                <ul class="mb-0">
                                    <li>Le notti imponibili sono limitate da "Giorni massimo imponibili".</li>
                                    <li>Le notti eccedenti sono conteggiate come "oltre giorni max" con codice 777 senza generare tassa.</li>
                                    <li>Le esenzioni impostate nella tab dedicata identificano solo soggetti non paganti.</li>
                                    <li>Aliquota, età e periodo di applicazione vengono applicati alla struttura corrente.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-esenzioni" role="tabpanel" aria-labelledby="tab-esenzioni">
                            @if(!isset($esenzioni))
                                <div class="alert alert-warning mb-0">Tabella esenzioni non presente: eseguire le migrazioni.</div>
                            @else
                                <div class="alert alert-secondary d-flex align-items-start gap-2">
                                    <i class="ri-information-line fs-5 mt-1"></i>
                                    <div>
                                        Qui vanno solo le esenzioni reali, cioè i soggetti non paganti. Il codice <strong>777</strong> non è configurabile qui perché viene gestito automaticamente dal sistema come pernottamenti oltre il limite massimo imponibile.
                                    </div>
                                </div>
                                @if($canManageEsenzioni)
                                    <div class="card border mb-3">
                                        <div class="card-header bg-light">Nuova esenzione</div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('tassa_esenzioni.store') }}" class="row g-3 align-items-end">
                                                @csrf
                                                <div class="col-md-2">
                                                    <label class="form-label">Codice</label>
                                                    <input type="text" name="codice" class="form-control" maxlength="50" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Descrizione</label>
                                                    <input type="text" name="descrizione" class="form-control" maxlength="255" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Ordine</label>
                                                    <input type="number" name="ordine" class="form-control" min="0" max="10000" value="100">
                                                </div>
                                                <div class="col-md-3 d-flex gap-3">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="attivo" value="1" id="new-attivo" checked>
                                                        <label class="form-check-label" for="new-attivo">Attivo</label>
                                                    </div>
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="richiede_nota" value="1" id="new-nota">
                                                        <label class="form-check-label" for="new-nota">Richiede nota</label>
                                                    </div>
                                                    <button type="submit" class="btn btn-success ms-auto mt-3">Aggiungi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-light border d-flex align-items-start gap-2">
                                        <i class="ri-eye-line fs-5 mt-1 text-primary"></i>
                                        <div>
                                            Le voci non paganti e informative sono gestite da admin e super admin. La struttura può solo consultarle.
                                        </div>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table align-middle" id="esenzioni-table">
                                        <thead>
                                            <tr>
                                                <th>Codice</th>
                                                <th>Descrizione</th>
                                                <th>Ordine</th>
                                                <th>Attivo</th>
                                                <th>Richiede nota</th>
                                                @if($canManageEsenzioni)
                                                    <th class="text-end">Azioni</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($esenzioni as $esenzione)
                                                @if($canManageEsenzioni)
                                                    <form id="update-esenzione-{{ $esenzione->id }}" method="POST" action="{{ route('tassa_esenzioni.update', $esenzione->id) }}" class="d-none">
                                                        @csrf
                                                        @method('PUT')
                                                    </form>
                                                    <form id="delete-esenzione-{{ $esenzione->id }}" method="POST" action="{{ route('tassa_esenzioni.destroy', $esenzione->id) }}" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                                <tr>
                                                    @if($canManageEsenzioni)
                                                        <td style="width: 120px"><input type="text" name="codice" value="{{ $esenzione->codice }}" class="form-control form-control-sm" form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td><input type="text" name="descrizione" value="{{ $esenzione->descrizione }}" class="form-control form-control-sm" form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td style="width: 110px"><input type="number" name="ordine" value="{{ $esenzione->ordine }}" class="form-control form-control-sm" min="0" max="10000" form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td class="text-center"><input type="checkbox" name="attivo" value="1" {{ $esenzione->attivo ? 'checked' : '' }} form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td class="text-center"><input type="checkbox" name="richiede_nota" value="1" {{ $esenzione->richiede_nota ? 'checked' : '' }} form="update-esenzione-{{ $esenzione->id }}"></td>
                                                        <td class="text-end">
                                                            <div class="d-inline-flex gap-2">
                                                                <button type="submit" class="btn btn-outline-primary btn-sm" form="update-esenzione-{{ $esenzione->id }}">Salva</button>
                                                                <button type="submit" class="btn btn-outline-danger btn-sm" form="delete-esenzione-{{ $esenzione->id }}">Elimina</button>
                                                            </div>
                                                        </td>
                                                    @else
                                                        <td class="fw-semibold">{{ $esenzione->codice }}</td>
                                                        <td>{{ $esenzione->descrizione }}</td>
                                                        <td>{{ $esenzione->ordine }}</td>
                                                        <td class="text-center">{{ $esenzione->attivo ? 'Sì' : 'No' }}</td>
                                                        <td class="text-center">{{ $esenzione->richiede_nota ? 'Sì' : 'No' }}</td>
                                                    @endif
                                                </tr>
                                            @empty
                                            @endforelse
                                            <tr id="esenzioni-empty-row" class="{{ $esenzioni->count() ? 'd-none' : '' }}" data-empty-state="1">
                                                <td colspan="{{ $canManageEsenzioni ? 6 : 5 }}" class="text-muted text-center">Nessuna esenzione configurata</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted small" id="esenzioni-counter" data-base-text="{{ $esenzioni->count() ? 'Mostrando '.$esenzioni->firstItem().'–'.$esenzioni->lastItem().' di '.$esenzioni->total().' risultati' : 'Nessun risultato' }}">
                                        @if($esenzioni->count())
                                            Mostrando {{ $esenzioni->firstItem() }}–{{ $esenzioni->lastItem() }} di {{ $esenzioni->total() }} risultati
                                        @else
                                            Nessun risultato
                                        @endif
                                    </div>
                                    <div>
                                        {{ $esenzioni->links('pagination::bootstrap-5-clean') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="pane-export" role="tabpanel" aria-labelledby="tab-export">
                            <div class="alert alert-secondary">Anteprima CSV (separatore ";")</div>
                            <pre class="bg-light p-3 border rounded small mb-3">tipo;data_reg;arrivo;partenza;nominativo;soggetti;pernottamenti_imponibili;tariffa
0;2026-03-05;2026-03-05;2026-03-10;Mario Rossi;1;5;{{ $tassa->tassa_soggiorno }}
777;2026-03-05;2026-03-05;2026-03-10;Mario Rossi;1;{{ max(0, (int)($tassa->giorni_massimo ?? 6) ? 10 - (int)$tassa->giorni_massimo : 0) }};0
</pre>
                            <p class="text-muted mb-0">Le righe con tipo 777 indicano solo le notti oltre il limite massimo imponibile. Non sono esenzioni e non generano importo da pagare.</p>
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
        // Se arrivo da paginazione, apri automaticamente la tab Esenzioni
        const url = new URL(window.location.href);
        if (url.searchParams.has('page')) {
            const tabButton = document.getElementById('tab-esenzioni');
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
    });
</script>
@endsection
