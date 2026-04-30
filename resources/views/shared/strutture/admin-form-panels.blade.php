@php
    $statoPagamento = old('stato_pagamento', $struttura->stato_pagamento ?? 'pagato');
    $avviso = old('avviso', $struttura->avviso ?? 'attivo');
    $statoOnline = (string) old('attiva', $struttura->attiva ?? true);
    $accessoNome = old('accesso_nome', $accessoPrincipale->name ?? '');
    $accessoUsername = old('accesso_username', $accessoPrincipale->username ?? '');
    $accessoEmail = old('accesso_email', $accessoPrincipale->email ?? '');
    $zoneOptions = collect($zoneOptions ?? []);
    $localitaOptions = collect($localitaOptions ?? []);
    $articoliCatalogo = collect($articoliCatalogo ?? []);
    $servizioCorrenteId = (int) old('articolo_id', optional($licenzeStorico->firstWhere('articolo.parent_id', null))->articolo_id ?? 0);
    $statoPagamentoLabel = in_array($statoPagamento, ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $statoPagamento));
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light-subtle border-0">
        <h5 class="card-title mb-1">Quadro struttura</h5>
        <p class="text-muted mb-0">Sintesi amministrativa immediata.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Proprietario</div>
                    <div class="fw-semibold mt-1">{{ optional($struttura->proprietario)->nome ?: 'Non assegnato' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Servizio</div>
                    <div class="fw-semibold mt-1">Schedine di Notifica</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Piano</div>
                    <div class="fw-semibold mt-1">{{ $struttura->piano ?: 'Non definito' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Servizio pagato</div>
                    <div class="fw-semibold mt-1">{{ $statoPagamentoLabel }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Numero ricevuta</div>
                    <div class="fw-semibold mt-1">{{ $struttura->numero_ricevuta_pagamento ?: 'Non indicato' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Prossimo vencimento</div>
                    <div class="fw-semibold mt-1">{{ optional($struttura->scadenza_servizio)->format('d/m/Y') ?: 'Non impostato' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Stato</div>
                    <div class="mt-1">
                        <span class="badge {{ ($struttura->attiva ?? true) ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            {{ ($struttura->attiva ?? true) ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Avviso</div>
                    <div class="fw-semibold mt-1">{{ ucfirst($struttura->avviso ?? 'attivo') }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Licenze</div>
                    <div class="fw-semibold mt-1">{{ $licenzeStorico->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0">
                <h5 class="card-title mb-1">Dati struttura</h5>
                <p class="text-muted mb-0">Anagrafica e riferimento geografico amministrativo della struttura.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome struttura</label>
                        <input type="text" name="nome_struttura" class="form-control" value="{{ old('nome_struttura', $struttura->nome_struttura) }}" required>
                    </div>
                    <div class="col-12">
                        <x-geo.italia
                            prefix="admin_struttura_geo"
                            title="GEO struttura"
                            :required="false"
                            :value="[
                                'nazione_text' => old('nazione', $struttura->nazione ?? 'Italia'),
                                'regione_text' => old('regione', $struttura->regione),
                                'provincia_text' => old('provincia', $struttura->provincia),
                                'comune_text' => old('citta', $struttura->citta),
                                'cap' => old('cap', $struttura->cap),
                                'cap_text' => old('cap', $struttura->cap),
                            ]"
                            :names="[
                                'nazione_id' => 'nazione',
                                'regione_id' => 'regione',
                                'provincia_id' => 'provincia',
                                'comune_id' => 'citta',
                                'cap' => 'cap',
                                'regione_text' => 'regione',
                                'provincia_text' => 'provincia',
                                'citta_text' => 'citta',
                                'cap_text' => 'cap',
                                'manual_flag' => 'geo_manual',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0">
                <h5 class="card-title mb-1">Dettaglio indirizzo</h5>
                <p class="text-muted mb-0">Zona, località e dati di contesto visivo della struttura.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-12">
                        <div class="border rounded-3 p-3">
                            <div class="text-center mb-3">
                                @if($struttura->logo_citta)
                                    <img src="{{ asset($struttura->logo_citta) }}" alt="Logo città" class="img-fluid mb-2" style="max-height: 78px;">
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Zona</label>
                                    <x-ui.select name="zona" data-allow-manual="1">
                                        <option value=""></option>
                                        @foreach($zoneOptions as $zonaOption)
                                            <option value="{{ $zonaOption }}" @selected(old('zona', $struttura->zona) === $zonaOption)>{{ $zonaOption }}</option>
                                        @endforeach
                                        @php $zonaCorrente = old('zona', $struttura->zona); @endphp
                                        @if($zonaCorrente && !$zoneOptions->contains($zonaCorrente))
                                            <option value="{{ $zonaCorrente }}" selected>{{ $zonaCorrente }}</option>
                                        @endif
                                    </x-ui.select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quartiere / Località</label>
                                    <x-ui.select name="localita" data-allow-manual="1">
                                        <option value=""></option>
                                        @foreach($localitaOptions as $localitaOption)
                                            <option value="{{ $localitaOption }}" @selected(old('localita', $struttura->localita) === $localitaOption)>{{ $localitaOption }}</option>
                                        @endforeach
                                        @php $localitaCorrente = old('localita', $struttura->localita); @endphp
                                        @if($localitaCorrente && !$localitaOptions->contains($localitaCorrente))
                                            <option value="{{ $localitaCorrente }}" selected>{{ $localitaCorrente }}</option>
                                        @endif
                                    </x-ui.select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Numero civico</label>
                                    <input type="text" name="numero_civico" class="form-control" value="{{ old('numero_civico', $struttura->numero_civico) }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Indirizzo</label>
                                    <input type="text" name="indirizzo" class="form-control" value="{{ old('indirizzo', $struttura->indirizzo) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Latitudine</label>
                                    <input type="text" name="latitudine" class="form-control" value="{{ old('latitudine', $struttura->latitudine) }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Longitudine</label>
                                    <input type="text" name="longitudine" class="form-control" value="{{ old('longitudine', $struttura->longitudine) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0">
                <h5 class="card-title mb-1">Proprietà e servizio</h5>
                <p class="text-muted mb-0">Assetto amministrativo della struttura, stato del servizio e controllo pagamenti.</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Proprietario</label>
                        <x-ui.select name="proprietario_id">
                            <option value="">-- Seleziona --</option>
                            @foreach($proprietari as $proprietario)
                                <option value="{{ $proprietario->id }}" {{ (string) old('proprietario_id', $struttura->proprietario_id) === (string) $proprietario->id ? 'selected' : '' }}>
                                    {{ $proprietario->nome }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Servizio</label>
                        <x-ui.select name="articolo_id">
                            <option value="">-- Seleziona servizio --</option>
                            @foreach($articoliCatalogo as $articoloCatalogo)
                                <option value="{{ $articoloCatalogo->id }}" @selected($servizioCorrenteId === (int) $articoloCatalogo->id)>
                                    {{ $articoloCatalogo->nome }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Piano</label>
                        <input type="text" name="piano" class="form-control" value="{{ old('piano', $struttura->piano) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prossimo vencimento</label>
                        <x-calendario name="scadenza_servizio" variant="single" :value="old('scadenza_servizio', $struttura->scadenza_servizio)" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stato pagamento</label>
                        <x-ui.select name="stato_pagamento">
                            <option value="pagato" {{ in_array($statoPagamento, ['pagato', 'ok'], true) ? 'selected' : '' }}>Pagato</option>
                            <option value="da_pagare" {{ $statoPagamento === 'da_pagare' ? 'selected' : '' }}>Da pagare</option>
                            <option value="sospeso" {{ $statoPagamento === 'sospeso' ? 'selected' : '' }}>Sospeso</option>
                        </x-ui.select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Numero ricevuta pagamento</label>
                        <input type="text" name="numero_ricevuta_pagamento" class="form-control" value="{{ old('numero_ricevuta_pagamento', $struttura->numero_ricevuta_pagamento) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stato</label>
                        <x-ui.select name="attiva">
                            <option value="1" {{ $statoOnline === '1' ? 'selected' : '' }}>Online</option>
                            <option value="0" {{ $statoOnline === '0' ? 'selected' : '' }}>Offline</option>
                        </x-ui.select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Avviso</label>
                        <x-ui.select name="avviso">
                            <option value="attivo" {{ $avviso === 'attivo' ? 'selected' : '' }}>Attivo</option>
                            <option value="sospeso" {{ $avviso === 'sospeso' ? 'selected' : '' }}>Sospeso</option>
                            <option value="inattivo" {{ $avviso === 'inattivo' ? 'selected' : '' }}>Inattivo</option>
                        </x-ui.select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Messaggio stato Offline</label>
                        <textarea name="messaggio_offline" class="form-control" rows="4" placeholder="Il servizio non è disponibile. Contatta l'amministratore.">{{ old('messaggio_offline', $struttura->messaggio_offline) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Messaggio Avviso</label>
                        <textarea name="messaggio_avviso" class="form-control" rows="4" placeholder="Messaggio da mostrare se lo stato è sospeso o inattivo.">{{ old('messaggio_avviso', $struttura->messaggio_avviso) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light-subtle border-0">
                <h5 class="card-title mb-1">Accesso struttura</h5>
                <p class="text-muted mb-0">Credenziali principali di accesso al software della struttura, separate dalle credenziali operative interne.</p>
            </div>
            <div class="card-body">
                @if($accessoPrincipale)
                    <div class="alert alert-success">
                        Accesso principale collegato:
                        <strong>{{ $accessoPrincipale->username }}</strong>
                        @if($accessoPrincipale->email)
                            · {{ $accessoPrincipale->email }}
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning">
                        Nessun accesso principale configurato. Inserisci almeno username e password per crearne uno.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nome accesso</label>
                        <input type="text" name="accesso_nome" class="form-control" value="{{ $accessoNome }}" placeholder="Nome accesso struttura">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="accesso_username" class="form-control" value="{{ $accessoUsername }}" placeholder="username-struttura">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email accesso</label>
                        <input type="email" name="accesso_email" class="form-control" value="{{ $accessoEmail }}" placeholder="struttura@example.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <div class="position-relative">
                            <input type="password" name="accesso_password" class="form-control pe-5" id="accesso_password" placeholder="{{ $mode === 'edit' ? 'Lascia vuoto per mantenerla' : 'Minimo 8 caratteri' }}">
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted" type="button" data-password-toggle="accesso_password" style="height: 100%;">
                                <i class="ri-eye-fill align-middle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-light-subtle border-0">
        <h5 class="card-title mb-1">Storico licenze e pagamenti</h5>
        <p class="text-muted mb-0">Storico amministrativo completo della struttura, con piano, scadenze e stato pagamento.</p>
    </div>
    <div class="card-body">
        @if($licenzeStorico->isEmpty())
            <div class="text-muted">Nessuna licenza storica disponibile per questa struttura.</div>
        @else
            <div class="row g-3">
                @foreach($licenzeStorico as $licenza)
                    <div class="col-md-6 col-xl-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $licenza->numero_licenza ?: '—' }}</div>
                                    <div class="text-muted small">{{ $licenza->articolo->nome ?? $licenza->articolo->codice ?? 'Piano non definito' }}</div>
                                </div>
                                <span class="badge {{ $licenza->attiva ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $licenza->attiva ? 'Attiva' : 'Non attiva' }}
                                </span>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <div class="text-muted small">Dal</div>
                                    <div class="fw-medium">{{ optional($licenza->data_inizio)->format('d/m/Y') ?: '—' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Al</div>
                                    <div class="fw-medium">{{ optional($licenza->data_scadenza)->format('d/m/Y') ?: '—' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Pagamento</div>
                                    <div class="fw-medium">{{ in_array(($licenza->stato_pagamento ?? 'pagato'), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $licenza->stato_pagamento ?? 'pagato')) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Prezzo</div>
                                    <div class="fw-medium">{{ number_format((float) $licenza->prezzo, 2, ',', '.') }} €</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        var targetId = button.getAttribute('data-password-toggle');
                        var input = document.getElementById(targetId);
                        if (!input) return;
                        input.type = input.type === 'password' ? 'text' : 'password';
                    });
                });
            });
        </script>
    @endpush
@endonce
