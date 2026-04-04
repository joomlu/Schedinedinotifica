@php
    $accessoNome = old('accesso_nome', $accessoPrincipale->name ?? '');
    $accessoUsername = old('accesso_username', $accessoPrincipale->username ?? '');
    $accessoEmail = old('accesso_email', $accessoPrincipale->email ?? '');
    $activeTab = request('tab', old('ragione_sociale') || old('partita_iva') || old('codice_fiscale') ? 'fiscale' : (old('accesso_username') || old('accesso_password') ? 'accesso' : 'profilo'));
    $serviziAttiviCount = $serviziAttiviCount ?? 0;
    $prossimaScadenza = $prossimaScadenza ?? null;
    $fatturatoTotale = $fatturatoTotale ?? 0;
    $proformeByStruttura = collect($fattureStorico ?? [])->reduce(function ($carry, $fattura) {
        foreach ($fattura->righe->pluck('struttura_id')->filter()->unique() as $strutturaId) {
            if (!$carry->has($strutturaId)) {
                $carry->put($strutturaId, $fattura);
            }
        }

        return $carry;
    }, collect());
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light-subtle border-0">
        <h5 class="card-title mb-1">Quadro proprietario</h5>
        <p class="text-muted mb-0">Sintesi amministrativa, economica e anagrafica del proprietario.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Proprietario</div>
                    <div class="fw-semibold mt-1">{{ $proprietario->nome ?: 'Nuovo proprietario' }}</div>
                    <div class="small text-muted mt-2">{{ $proprietario->ragione_sociale ?: 'Ragione sociale non indicata' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Contatti</div>
                    <div class="fw-semibold mt-1">{{ $proprietario->email ?: 'Email non indicata' }}</div>
                    <div class="small text-muted mt-2">
                        {{ $proprietario->telefono ?: 'Telefono assente' }}
                        @if($proprietario->cellulare)
                            · {{ $proprietario->cellulare }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Strutture</div>
                    <div class="fw-semibold mt-1">{{ $struttureCount }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Servizi attivi</div>
                    <div class="fw-semibold mt-1">{{ $serviziAttiviCount }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Fatture</div>
                    <div class="fw-semibold mt-1">{{ $fattureStorico->count() }}</div>
                    <div class="small text-muted mt-2">{{ number_format((float) $fatturatoTotale, 2, ',', '.') }} € storico</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Prossima scadenza</div>
                    <div class="fw-semibold mt-1">{{ optional($prossimaScadenza)->format('d/m/Y') ?: 'Nessuna scadenza' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Accesso proprietario</div>
                    <div class="fw-semibold mt-1">{{ $accessoPrincipale->username ?? 'Non configurato' }}</div>
                    <div class="small text-muted mt-2">{{ $accessoPrincipale->email ?? 'Nessuna email accesso' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Dati fiscali</div>
                    <div class="fw-semibold mt-1">{{ $proprietario->partita_iva ?: 'P.IVA non indicata' }}</div>
                    <div class="small text-muted mt-2">{{ $proprietario->codice_fiscale ?: 'C.F. non indicato' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Residenza</div>
                    <div class="fw-semibold mt-1">{{ $proprietario->citta ?: 'Città non indicata' }}</div>
                    <div class="small text-muted mt-2">
                        {{ collect([$proprietario->provincia, $proprietario->regione, $proprietario->nazione])->filter()->implode(' · ') ?: 'GEO non completato' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'profilo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#proprietario-pane-profilo" type="button" role="tab" aria-selected="{{ $activeTab === 'profilo' ? 'true' : 'false' }}">Profilo</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'fiscale' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#proprietario-pane-fiscale" type="button" role="tab" aria-selected="{{ $activeTab === 'fiscale' ? 'true' : 'false' }}">Fiscale e GEO</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'accesso' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#proprietario-pane-accesso" type="button" role="tab" aria-selected="{{ $activeTab === 'accesso' ? 'true' : 'false' }}">Accesso</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'storico' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#proprietario-pane-storico" type="button" role="tab" aria-selected="{{ $activeTab === 'storico' ? 'true' : 'false' }}">Strutture e fatturazione</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'profilo' ? 'show active' : '' }}" id="proprietario-pane-profilo" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Anagrafica proprietario</h5>
                                <p class="text-muted mb-0">Dati principali, riferimenti amministrativi e contatti diretti del proprietario.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label">Nome proprietario</label>
                                        <input type="text" name="nome" class="form-control" value="{{ old('nome', $proprietario->nome) }}">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Ragione sociale</label>
                                        <input type="text" name="ragione_sociale" class="form-control" value="{{ old('ragione_sociale', $proprietario->ragione_sociale) }}">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $proprietario->email) }}">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Telefono</label>
                                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $proprietario->telefono) }}">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Cellulare</label>
                                        <input type="text" name="cellulare" class="form-control" value="{{ old('cellulare', $proprietario->cellulare) }}">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">PEC</label>
                                        <input type="email" name="pec" class="form-control" value="{{ old('pec', $proprietario->pec) }}">
                                    </div>
                                    @isset($admins)
                                        <div class="col-lg-4">
                                            <label class="form-label">Admin associato</label>
                                            <x-ui.select name="admin_id">
                                                <option value="">-- Nessuno --</option>
                                                @foreach($admins as $adminOption)
                                                    <option value="{{ $adminOption->id }}" {{ (string) old('admin_id', $proprietario->admin_id) === (string) $adminOption->id ? 'selected' : '' }}>
                                                        {{ $adminOption->name }} ({{ $adminOption->email }})
                                                    </option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>
                                    @endisset
                                    <div class="col-lg-8">
                                        <label class="form-label">Note amministrative</label>
                                        <textarea name="note_amministrative" class="form-control" rows="3">{{ old('note_amministrative', $proprietario->note_amministrative) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Note</label>
                                        <textarea name="note" class="form-control" rows="3">{{ old('note', $proprietario->note) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'fiscale' ? 'show active' : '' }}" id="proprietario-pane-fiscale" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Dati fiscali e fatturazione</h5>
                                <p class="text-muted mb-0">Intestazione fiscale e dati da usare per proforme, fatture e gestione amministrativa.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label class="form-label">Codice fiscale</label>
                                        <input type="text" name="codice_fiscale" class="form-control" value="{{ old('codice_fiscale', $proprietario->codice_fiscale) }}">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Partita IVA</label>
                                        <input type="text" name="partita_iva" class="form-control" value="{{ old('partita_iva', $proprietario->partita_iva) }}">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Codice destinatario</label>
                                        <input type="text" name="codice_destinatario" class="form-control" value="{{ old('codice_destinatario', $proprietario->codice_destinatario) }}">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Codice unico</label>
                                        <input type="text" name="codice_unico" class="form-control" value="{{ old('codice_unico', $proprietario->codice_unico) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Domicilio fiscale e GEO</h5>
                                <p class="text-muted mb-0">Residenza, coordinate e riferimento territoriale del proprietario.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <x-geo.italia
                                            prefix="proprietario_geo"
                                            title="GEO proprietario"
                                            :required="false"
                                            :value="[
                                                'nazione_text' => old('nazione', $proprietario->nazione ?? 'Italia'),
                                                'regione_text' => old('regione', $proprietario->regione),
                                                'provincia_text' => old('provincia', $proprietario->provincia),
                                                'comune_text' => old('citta', $proprietario->citta),
                                                'cap' => old('cap', $proprietario->cap),
                                                'cap_text' => old('cap', $proprietario->cap),
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
                                    <div class="col-lg-6">
                                        <label class="form-label">Indirizzo</label>
                                        <input type="text" name="indirizzo" class="form-control" value="{{ old('indirizzo', $proprietario->indirizzo) }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Numero civico</label>
                                        <input type="text" name="numero_civico" class="form-control" value="{{ old('numero_civico', $proprietario->numero_civico) }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Lat.</label>
                                        <input type="text" name="latitudine" class="form-control" value="{{ old('latitudine', $proprietario->latitudine) }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Lng.</label>
                                        <input type="text" name="longitudine" class="form-control" value="{{ old('longitudine', $proprietario->longitudine) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'accesso' ? 'show active' : '' }}" id="proprietario-pane-accesso" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Accesso proprietario</h5>
                                <p class="text-muted mb-0">Credenziali per vedere strutture, servizi e situazione amministrativa del proprietario.</p>
                            </div>
                            <div class="card-body">
                                @if($accessoPrincipale)
                                    <div class="alert alert-success">
                                        Accesso proprietario collegato:
                                        <strong>{{ $accessoPrincipale->username }}</strong>
                                        @if($accessoPrincipale->email)
                                            · {{ $accessoPrincipale->email }}
                                        @endif
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        Nessun accesso proprietario configurato. Inserisci username e password per crearlo.
                                    </div>
                                @endif

                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label">Nome accesso</label>
                                        <input type="text" name="accesso_nome" class="form-control" value="{{ $accessoNome }}" placeholder="Mario Rossi">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="accesso_username" class="form-control" value="{{ $accessoUsername }}" placeholder="mrossi">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Email accesso</label>
                                        <input type="email" name="accesso_email" class="form-control" value="{{ $accessoEmail }}" placeholder="proprietario@example.com">
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Password</label>
                                        <div class="position-relative">
                                            <input type="password" name="accesso_password" class="form-control pe-5" id="proprietario_accesso_password" placeholder="{{ $mode === 'edit' ? 'Lascia vuoto per mantenerla' : 'Minimo 8 caratteri' }}">
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted" type="button" data-password-toggle="proprietario_accesso_password" style="height: 100%;">
                                                <i class="ri-eye-fill align-middle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'storico' ? 'show active' : '' }}" id="proprietario-pane-storico" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Strutture collegate</h5>
                                <p class="text-muted mb-0">Quadro sintetico delle strutture del proprietario con servizio, piano e scadenza.</p>
                            </div>
                            <div class="card-body">
                                @if($struttureStorico->isEmpty())
                                    <div class="text-muted">Nessuna struttura collegata.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Struttura</th>
                                                    <th>Servizio</th>
                                                    <th>Piano</th>
                                                    <th>Pagamento</th>
                                                    <th>Scadenza</th>
                                                    <th class="text-end">Azioni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($struttureStorico as $strutturaItem)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $strutturaItem->nome_struttura }}</div>
                                                            <div class="small text-muted">{{ collect([$strutturaItem->citta, $strutturaItem->provincia])->filter()->implode(' · ') ?: 'Località non indicata' }}</div>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $serviziStruttura = $licenzeStorico
                                                                    ->where('struttura_id', $strutturaItem->id)
                                                                    ->pluck('articolo.nome')
                                                                    ->filter()
                                                                    ->unique()
                                                                    ->values();
                                                            @endphp
                                                            @if($serviziStruttura->isNotEmpty())
                                                                <div class="fw-medium">{{ $serviziStruttura->join(', ') }}</div>
                                                            @else
                                                                <span class="text-muted">Nessun servizio attivo</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $strutturaItem->piano ?: '—' }}</td>
                                                        <td>{{ in_array(($strutturaItem->stato_pagamento ?? 'pagato'), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $strutturaItem->stato_pagamento ?? 'pagato')) }}</td>
                                                        <td>{{ optional($strutturaItem->scadenza_servizio)->format('d/m/Y') ?: '—' }}</td>
                                                        <td class="text-end">
                                                            <a href="{{ request()->routeIs('superadmin.*') ? route('superadmin.strutture.edit', $strutturaItem->id) : route('admin.strutture.edit', $strutturaItem->id) }}" class="btn btn-sm btn-outline-secondary">
                                                                Accesso
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Licenze, prodotti e servizi</h5>
                                <p class="text-muted mb-0">Servizi attivi o storici per struttura, con piano economico e periodo coperto.</p>
                            </div>
                            <div class="card-body">
                                @if($licenzeStorico->isEmpty())
                                    <div class="text-muted">Nessuna licenza disponibile.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Struttura</th>
                                                    <th>Prodotto / Servizio</th>
                                                    <th>Licenza</th>
                                                    <th>Pagamento</th>
                                                    <th>Dal</th>
                                                    <th>Al</th>
                                                    <th class="text-end">Documenti</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($licenzeStorico as $licenza)
                                                    @php
                                                        $proformaLicenza = $licenza->struttura_id ? $proformeByStruttura->get($licenza->struttura_id) : null;
                                                        $proformaLicenzaPaid = in_array(($proformaLicenza->stato ?? ''), ['pagata', 'fatturata', 'ok'], true);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $licenza->struttura->nome_struttura ?? '—' }}</td>
                                                        <td>{{ $licenza->articolo->nome ?? $licenza->articolo->codice ?? '—' }}</td>
                                                        <td>{{ $licenza->numero_licenza ?: '—' }}</td>
                                                        <td>
                                                            <div>{{ in_array(($licenza->stato_pagamento ?? 'pagato'), ['ok', 'pagato'], true) ? 'Pagato' : ucfirst(str_replace('_', ' ', $licenza->stato_pagamento ?? 'pagato')) }}</div>
                                                            @if($proformaLicenzaPaid && $proformaLicenza)
                                                                <div class="small text-muted">
                                                                    {{ optional($proformaLicenza->data_pagamento)->format('d/m/Y') ?: 'Data pagamento da indicare' }}
                                                                    @if($proformaLicenza->numero_fattura)
                                                                        · Fatt. {{ $proformaLicenza->numero_fattura }}
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>{{ optional($licenza->data_inizio)->format('d/m/Y') ?: '—' }}</td>
                                                        <td>{{ optional($licenza->data_scadenza)->format('d/m/Y') ?: '—' }}</td>
                                                        <td class="text-end">
                                                            <a href="{{ request()->routeIs('superadmin.*') ? route('superadmin.pagamenti.licenze.print', $licenza->id) : route('admin.pagamenti.licenze.print', $licenza->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                                Apri licenza
                                                            </a>
                                                            @if($proformaLicenza)
                                                                <a href="{{ request()->routeIs('superadmin.*') ? route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proformaLicenza->id]) : route('admin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proformaLicenza->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                                    {{ $proformaLicenzaPaid ? 'Pagata' : 'Proforma' }}
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <div>
                                        <h5 class="card-title mb-1">Storico fatturazione</h5>
                                        <p class="text-muted mb-0">Documenti emessi, intestazione fiscale e quadro economico del proprietario.</p>
                                    </div>
                                    @if($proprietario->exists)
                                        <a href="{{ request()->routeIs('superadmin.*') ? route('superadmin.proprietari.proforme.create', ['id' => $proprietario->id]) : route('admin.proprietari.proforme.create', ['id' => $proprietario->id]) }}" class="btn btn-sm btn-primary">
                                            Nuova proforma
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if($fattureStorico->isEmpty())
                                    <div class="text-muted">Nessuna fattura disponibile.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>N. fattura</th>
                                                    <th>Data fattura</th>
                                                    <th>Stato</th>
                                                    <th>Intestazione</th>
                                                    <th>Totale</th>
                                                    <th class="text-end">Documento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($fattureStorico as $fattura)
                                                    @php
                                                        $fatturaLabel = in_array(($fattura->stato ?? ''), ['pagata', 'fatturata', 'ok'], true) ? 'Pagata' : 'Proforma';
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $fattura->numero ?: '—' }}</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ optional($fattura->data_documento)->format('d/m/Y') ?: '—' }}</div>
                                                            <div class="small text-muted">
                                                                {{ $fatturaLabel === 'Pagata' ? 'Documento pagato' : 'Documento proforma' }}
                                                                @if($fatturaLabel === 'Pagata' && ($fattura->data_pagamento || $fattura->numero_fattura))
                                                                    · {{ optional($fattura->data_pagamento)->format('d/m/Y') ?: 'Data pagamento da indicare' }}
                                                                    @if($fattura->numero_fattura)
                                                                        · Fatt. {{ $fattura->numero_fattura }}
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>{{ $fatturaLabel }}</td>
                                                        <td>{{ $fattura->intestazione ?: ($proprietario->ragione_sociale ?: $proprietario->nome) }}</td>
                                                        <td>{{ number_format((float) $fattura->totale, 2, ',', '.') }} €</td>
                                                        <td class="text-end">
                                                            <a href="{{ request()->routeIs('superadmin.*') ? route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $fattura->id]) : route('admin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $fattura->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                                {{ $fatturaLabel }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
