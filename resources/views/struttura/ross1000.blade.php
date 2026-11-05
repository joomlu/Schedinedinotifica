@php
    $credStatus = $credStatus ?? app(\App\Services\IstatWebService::class)->credentialsStatus($struttura);
    $regionSupported = $regionSupported ?? (\Illuminate\Support\Str::of((string) $struttura->regione)->ascii()->upper()->replaceMatches('/[^A-Z0-9]+/', ' ')->trim()->value() === 'EMILIA ROMAGNA');
@endphp
<div id="configurazione-ross1000">
        <div class="card border-0 bg-light-subtle mb-3">
            <div class="card-header border-0 py-2 d-flex align-items-center">
                <i class="ri-shield-check-line me-2 text-primary"></i>
                <h5 class="card-title mb-0 fs-6">Verifica configurazione Tabella A</h5>
            </div>
            <div class="card-body pt-2">
                <div class="row g-3 small">
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Regione struttura</div>
                        <div class="{{ $regionSupported ? 'text-success' : 'text-danger' }}">{{ $struttura->regione ?: 'Non indicata' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Username ISTAT</div>
                        <div class="{{ !in_array('username', $credStatus['missing']) && !$credStatus['blocked'] ? 'text-success' : 'text-danger' }}">{{ !in_array('username', $credStatus['missing']) && !$credStatus['blocked'] ? 'Presente' : 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Password ISTAT</div>
                        <div class="{{ !in_array('password', $credStatus['missing']) && !$credStatus['blocked'] ? 'text-success' : 'text-danger' }}">{{ !in_array('password', $credStatus['missing']) && !$credStatus['blocked'] ? 'Presente' : 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-2 col-md-4">
                        <div class="text-muted">Codice Ross1000</div>
                        <div class="{{ filled($struttura->istat_codice_struttura) ? 'text-success' : 'text-danger' }}">{{ $struttura->istat_codice_struttura ?: 'Mancante' }}</div>
                    </div>
                    <div class="col-xl-4 col-md-8">
                        <div class="text-muted">Trasporto Ross1000</div>
                        <div>{{ config('istat.enabled', false) === true ? 'Abilitato' : 'Disabilitato' }} — endpoint regionale fisso</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 bg-light-subtle mb-3">
            <div class="card-body row g-3">
                <div class="col-12"><h5>Configurazione Ross1000</h5><p class="small text-muted">Lascia vuote le credenziali per conservare quelle salvate. Per credenziali non decifrabili reinserisci entrambi i valori forniti dall’ente. Il salvataggio non verifica l’accesso remoto.</p></div>
                <div class="col-md-4"><label class="form-label" for="istat-username">Username WebService</label><input id="istat-username" form="ross1000-config-form" name="username" type="password" value="" autocomplete="off" class="form-control"></div>
                <div class="col-md-4"><label class="form-label" for="istat-password">Password WebService</label><input id="istat-password" form="ross1000-config-form" name="password" type="password" value="" autocomplete="new-password" class="form-control"></div>
                <div class="col-md-4"><label class="form-label" for="istat-codice">Codice struttura Ross1000</label><input id="istat-codice" form="ross1000-config-form" name="codice" value="{{ $struttura->istat_codice_struttura }}" class="form-control" required></div>
                <div class="col-12 text-end"><button type="submit" form="ross1000-config-form" class="btn btn-primary">Salva configurazione</button></div>
            </div>
        </div>
</div>
