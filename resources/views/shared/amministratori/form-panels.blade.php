@php
    $activeTab = request('tab', old('ragione_sociale') || old('partita_iva') ? 'fiscale' : (old('username') || old('password') ? 'accesso' : 'profilo'));
    $serviziStorico = collect($serviziStorico ?? []);
    $fattureStorico = collect($fattureStorico ?? []);
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light-subtle border-0">
        <h5 class="card-title mb-1">Quadro amministratore</h5>
        <p class="text-muted mb-0">Sintesi amministrativa, commerciale e fiscale dell'amministratore.</p>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Amministratore</div>
                    <div class="fw-semibold mt-1">{{ $admin->name ?: 'Nuovo amministratore' }}</div>
                    <div class="small text-muted mt-2">{{ $admin->ragione_sociale ?: 'Ragione sociale non indicata' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Accesso</div>
                    <div class="fw-semibold mt-1">{{ $admin->username ?: 'Username non configurata' }}</div>
                    <div class="small text-muted mt-2">{{ $admin->email ?: 'Email non indicata' }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Proprietari</div>
                    <div class="fw-semibold mt-1">{{ $proprietariCount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Strutture</div>
                    <div class="fw-semibold mt-1">{{ $struttureCount ?? 0 }}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Servizi attivi</div>
                    <div class="fw-semibold mt-1">{{ $serviziCount ?? 0 }}</div>
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
                    <div class="text-muted small">Fatture / proforme</div>
                    <div class="fw-semibold mt-1">{{ $fattureStorico->count() }}</div>
                    <div class="small text-muted mt-2">{{ number_format((float) ($fatturatoTotale ?? 0), 2, ',', '.') }} € storico</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Contatti</div>
                    <div class="fw-semibold mt-1">{{ $admin->telefono ?: 'Telefono non indicato' }}</div>
                    <div class="small text-muted mt-2">{{ $admin->qualifica ?: 'Qualifica non indicata' }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="rounded-3 border p-3 h-100">
                    <div class="text-muted small">Dati fiscali</div>
                    <div class="fw-semibold mt-1">{{ $admin->partita_iva ?: 'P.IVA non indicata' }}</div>
                    <div class="small text-muted mt-2">{{ $admin->codice_fiscale ?: 'C.F. non indicato' }}</div>
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
                    <button class="nav-link {{ $activeTab === 'profilo' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#amministratore-pane-profilo" type="button" role="tab">Profilo</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'fiscale' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#amministratore-pane-fiscale" type="button" role="tab">Fiscale e GEO</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'accesso' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#amministratore-pane-accesso" type="button" role="tab">Accesso</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'storico' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#amministratore-pane-storico" type="button" role="tab">Proprietari e fatturazione</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade {{ $activeTab === 'profilo' ? 'show active' : '' }}" id="amministratore-pane-profilo" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Anagrafica amministratore</h5>
                        <p class="text-muted mb-0">Dati principali dell'amministratore e identità amministrativa.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">Nome</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Nome visualizzato</label>
                                <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $admin->display_name) }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Qualifica</label>
                                <input type="text" name="qualifica" class="form-control" value="{{ old('qualifica', $admin->qualifica) }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="admin-email-profilo" value="{{ old('email', $admin->email) }}">
                                <small class="text-muted">Si sincronizza con l'email di accesso.</small>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Telefono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $admin->telefono) }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Ragione sociale</label>
                                <input type="text" name="ragione_sociale" class="form-control" value="{{ old('ragione_sociale', $admin->ragione_sociale) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'fiscale' ? 'show active' : '' }}" id="amministratore-pane-fiscale" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Dati fiscali</h5>
                                <p class="text-muted mb-0">Dati fiscali da usare nelle proforme e nel circuito amministrativo del superadmin.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label class="form-label">Codice fiscale</label>
                                        <input type="text" name="codice_fiscale" class="form-control" value="{{ old('codice_fiscale', $admin->codice_fiscale) }}">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Partita IVA</label>
                                        <input type="text" name="partita_iva" class="form-control" value="{{ old('partita_iva', $admin->partita_iva) }}">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">Codice destinatario</label>
                                        <input type="text" name="codice_destinatario" class="form-control" value="{{ old('codice_destinatario', $admin->codice_destinatario) }}">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="form-label">PEC</label>
                                        <input type="email" name="pec" class="form-control" value="{{ old('pec', $admin->pec) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Domicilio e GEO</h5>
                                <p class="text-muted mb-0">Dati territoriali e indirizzo fiscale dell'amministratore.</p>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <x-geo.italia
                                            prefix="amministratore_geo"
                                            title="GEO amministratore"
                                            :required="false"
                                            :value="[
                                                'nazione_text' => old('nazione', $admin->nazione ?? 'Italia'),
                                                'regione_text' => old('regione', $admin->regione),
                                                'provincia_text' => old('provincia', $admin->provincia),
                                                'comune_text' => old('citta', $admin->citta),
                                                'cap' => old('cap', $admin->cap),
                                                'cap_text' => old('cap', $admin->cap),
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
                                        <input type="text" name="indirizzo" class="form-control" value="{{ old('indirizzo', $admin->indirizzo) }}">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Numero civico</label>
                                        <input type="text" name="numero_civico" class="form-control" value="{{ old('numero_civico', $admin->numero_civico) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'accesso' ? 'show active' : '' }}" id="amministratore-pane-accesso" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light-subtle border-0">
                        <h5 class="card-title mb-1">Accesso amministratore</h5>
                        <p class="text-muted mb-0">Credenziali dirette dell'amministratore nel sistema.</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $admin->username) }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Email accesso</label>
                                <input type="email" name="email" class="form-control" id="admin-email-accesso" value="{{ old('email', $admin->email) }}">
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Password @if($mode === 'edit')<small class="text-muted">(lascia vuota per non cambiarla)</small>@endif</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" id="admin-password-input" autocomplete="new-password">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-admin-password">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                </div>
                                @if(old('password'))
                                    <small class="text-muted d-block mt-2">Password inserita in questa modifica: al salvataggio verrà sostituita con quella nuova.</small>
                                @endif
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Stato accesso</label>
                                <x-ui.select name="attivo">
                                    <option value="1" {{ (string) old('attivo', $admin->attivo ?? true) === '1' ? 'selected' : '' }}>Attivo</option>
                                    <option value="0" {{ (string) old('attivo', $admin->attivo ?? true) === '0' ? 'selected' : '' }}>Disattivo</option>
                                </x-ui.select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'storico' ? 'show active' : '' }}" id="amministratore-pane-storico" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Proprietari collegati</h5>
                                <p class="text-muted mb-0">Proprietari attualmente gestiti da questo amministratore.</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Proprietario</th>
                                                <th>Strutture</th>
                                                <th class="text-end">Accesso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($proprietariGestiti as $proprietario)
                                                <tr>
                                                    <td>{{ $proprietario->nome }}</td>
                                                    <td>{{ $proprietario->strutture_count }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('superadmin.proprietari.edit', ['id' => $proprietario->id, 'tab' => 'storico']) }}" class="btn btn-sm btn-outline-secondary">Accesso</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-muted">Nessun proprietario collegato.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Strutture collegate</h5>
                                <p class="text-muted mb-0">Strutture gestite tramite i proprietari associati.</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Struttura</th>
                                                <th>Proprietario</th>
                                                <th>Scadenza</th>
                                                <th class="text-end">Accesso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($struttureGestite as $struttura)
                                                <tr>
                                                    <td>{{ $struttura->nome_struttura }}</td>
                                                    <td>{{ $struttura->proprietario?->nome ?: '—' }}</td>
                                                    <td>{{ optional($struttura->scadenza_servizio)->format('d/m/Y') ?: '—' }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('superadmin.strutture.edit', ['id' => $struttura->id, 'tab' => 'storico']) }}" class="btn btn-sm btn-outline-secondary">Accesso</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-muted">Nessuna struttura collegata.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0">
                                <h5 class="card-title mb-1">Servizi dell'amministratore</h5>
                                <p class="text-muted mb-0">Catalogo dei servizi che il superadmin fattura all'amministratore.</p>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0" id="admin-servizi-table">
                                        <thead>
                                            <tr>
                                                <th>Servizio</th>
                                                <th>Tipo costo</th>
                                                <th>Importo</th>
                                                <th>Quantità</th>
                                                <th>Attivo</th>
                                                <th>Note</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $serviziRows = old('servizi', $serviziStorico->map(fn ($servizio) => ['id' => $servizio->id, 'nome' => $servizio->nome, 'tipo_costo' => $servizio->tipo_costo, 'importo' => $servizio->importo, 'quantita_default' => $servizio->quantita_default, 'attivo' => $servizio->attivo, 'note' => $servizio->note])->values()->all()); @endphp
                                            @foreach($serviziRows as $index => $servizio)
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="servizi[{{ $index }}][id]" value="{{ $servizio['id'] ?? '' }}">
                                                        <input type="text" name="servizi[{{ $index }}][nome]" class="form-control form-control-sm" value="{{ $servizio['nome'] ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <x-ui.select name="servizi[{{ $index }}][tipo_costo]">
                                                            <option value="per_struttura" {{ ($servizio['tipo_costo'] ?? 'per_struttura') === 'per_struttura' ? 'selected' : '' }}>Per struttura</option>
                                                            <option value="una_tantum" {{ ($servizio['tipo_costo'] ?? '') === 'una_tantum' ? 'selected' : '' }}>Una tantum</option>
                                                            <option value="assistenza" {{ ($servizio['tipo_costo'] ?? '') === 'assistenza' ? 'selected' : '' }}>Assistenza</option>
                                                        </x-ui.select>
                                                    </td>
                                                    <td><input type="number" step="0.01" min="0" name="servizi[{{ $index }}][importo]" class="form-control form-control-sm" value="{{ $servizio['importo'] ?? '' }}"></td>
                                                    <td><input type="number" min="1" name="servizi[{{ $index }}][quantita_default]" class="form-control form-control-sm" value="{{ $servizio['quantita_default'] ?? 1 }}"></td>
                                                    <td>
                                                        <input type="hidden" name="servizi[{{ $index }}][attivo]" value="0">
                                                        <input type="checkbox" class="form-check-input" name="servizi[{{ $index }}][attivo]" value="1" {{ !empty($servizio['attivo']) ? 'checked' : '' }}>
                                                    </td>
                                                    <td><input type="text" name="servizi[{{ $index }}][note]" class="form-control form-control-sm" value="{{ $servizio['note'] ?? '' }}"></td>
                                                </tr>
                                            @endforeach
                                            @for($i = count($serviziRows); $i < count($serviziRows) + 2; $i++)
                                                <tr>
                                                    <td><input type="text" name="servizi[{{ $i }}][nome]" class="form-control form-control-sm"></td>
                                                    <td>
                                                        <x-ui.select name="servizi[{{ $i }}][tipo_costo]">
                                                            <option value="per_struttura">Per struttura</option>
                                                            <option value="una_tantum">Una tantum</option>
                                                            <option value="assistenza">Assistenza</option>
                                                        </x-ui.select>
                                                    </td>
                                                    <td><input type="number" step="0.01" min="0" name="servizi[{{ $i }}][importo]" class="form-control form-control-sm"></td>
                                                    <td><input type="number" min="1" name="servizi[{{ $i }}][quantita_default]" class="form-control form-control-sm" value="1"></td>
                                                    <td>
                                                        <input type="hidden" name="servizi[{{ $i }}][attivo]" value="0">
                                                        <input type="checkbox" class="form-check-input" name="servizi[{{ $i }}][attivo]" value="1" checked>
                                                    </td>
                                                    <td><input type="text" name="servizi[{{ $i }}][note]" class="form-control form-control-sm"></td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light-subtle border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">Storico fatturazione</h5>
                                    <p class="text-muted mb-0">Proforme e documenti amministrativi dell'amministratore.</p>
                                </div>
                                @if($admin->exists)
                                    <a href="{{ route('superadmin.amministratori.proforme.create', $admin->id) }}" class="btn btn-primary btn-sm">Nuova proforma</a>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
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
                                            @forelse($fattureStorico as $fattura)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $fattura->numero }}</div>
                                                        @if($fattura->numero_fattura)
                                                            <div class="small text-muted">Fatt. {{ $fattura->numero_fattura }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>{{ optional($fattura->data_documento)->format('d/m/Y') ?: '—' }}</div>
                                                        @if($fattura->data_pagamento)
                                                            <div class="small text-muted">Pagata {{ optional($fattura->data_pagamento)->format('d/m/Y') }}</div>
                                                        @endif
                                                    </td>
                                                    <td>{{ in_array($fattura->stato, ['pagata', 'fatturata'], true) ? 'Pagata' : 'Proforma' }}</td>
                                                    <td>{{ $fattura->intestazione ?: ($admin->ragione_sociale ?: $admin->name) }}</td>
                                                    <td>{{ number_format((float) $fattura->totale, 2, ',', '.') }} €</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $fattura->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                            {{ in_array($fattura->stato, ['pagata', 'fatturata'], true) ? 'Pagata' : 'Proforma' }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-muted">Nessun documento emesso.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profilo = document.getElementById('admin-email-profilo');
            const accesso = document.getElementById('admin-email-accesso');

            if (!profilo || !accesso) return;

            const sync = (source, target) => {
                target.value = source.value;
            };

            profilo.addEventListener('input', function () {
                sync(profilo, accesso);
            });

            accesso.addEventListener('input', function () {
                sync(accesso, profilo);
            });
        });
    </script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggle-admin-password');
    const passwordInput = document.getElementById('admin-password-input');
    toggleButton?.addEventListener('click', function () {
        if (!passwordInput) return;
        passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endpush
