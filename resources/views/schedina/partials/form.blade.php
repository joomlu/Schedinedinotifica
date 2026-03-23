@php
    $prefilledCustomer = $prefilledCustomer ?? null;
    $usePutMethod = $usePutMethod ?? null;
    $isEdit = $usePutMethod !== null ? (bool) $usePutMethod : (!empty($schedina) && $schedina->id);
    $formAction = $formAction ?? ($isEdit ? route('schedina.update', ['id' => $schedina->id]) : route('schedina.store'));
    $formTitle = $formTitle ?? ($isEdit ? 'Modifica schedina' : 'Nuova schedina');
    $nextSchedaCode = $nextSchedaCode ?? null;
    $showIstatFields = $showIstatFields ?? true;
    $istatTipoTurismoOptions = \App\Services\IstatTabellaAService::TIPO_TURISMO;
    $istatMezzoTrasportoOptions = \App\Services\IstatTabellaAService::MEZZO_TRASPORTO;
    $istatCanaleOptions = \App\Services\IstatTabellaAService::CANALE_PRENOTAZIONE;
    $istatTitoloStudioOptions = \App\Services\IstatTabellaAService::TITOLO_STUDIO;
    $professioniIstat = [
        'Impiegato',
        'Insegnante',
        'Pensionato',
        'Studente',
        'Libero professionista',
        'Commerciante',
        'Artigiano',
        'Operaio',
        'Dirigente',
        'Medico',
        'Infermiere',
        'Avvocato',
        'Architetto',
        'Ingegnere',
        'Consulente',
        'Imprenditore',
        'Agente di commercio',
        'Autista',
        'Forze dell’ordine',
        'Militare',
        'Casalinga',
        'Disoccupato',
    ];

    $toDateInput = function ($value) {
        if (empty($value)) {
            return '';
        }
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, (string) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                continue;
            }
        }
        try {
            return \Carbon\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return '';
        }
    };

    $prefMap = [
        'type' => 'type',
        'name' => 'name',
        'surname' => 'surname',
        'sex' => 'sex',
        'oa_country' => 'country_reg',
        'oa_city' => 'city_reg',
        'oa_region' => 'region_reg',
        'oa_prov' => 'prov_reg',
        'oa_cap' => 'cap_reg',
        'oa_city_nac' => 'ciudadania_reg',
        'oa_date_nac' => 'nac_reg',
        'or_country' => 'country',
        'or_city' => 'city',
        'or_region' => 'region',
        'or_prov' => 'province',
        'or_cap' => 'cap',
        'or_typeaway' => 'typeaway',
        'or_address' => 'address',
        'or_num' => 'number',
        'or_doc' => 'num_doc_reg',
        'or_doctype' => 'type_doc_reg',
        'or_published_date' => 'date_pub_reg',
        'or_published' => 'rilasciato_reg',
        'or_expire' => 'expire_reg',
        'or_published_country' => 'country_doc_reg',
        'or_published_city' => 'city_doc_reg',
    ];

    $valueOf = function (string $field, $fallback = null) use ($prefMap, $schedina, $prefilledCustomer) {
        $prefKey = $prefMap[$field] ?? $field;
        return old($field, data_get($schedina, $field, data_get($prefilledCustomer, $prefKey, $fallback)));
    };

    $selectedCustomerId = old('customer_id', $schedina->customer_id ?? $prefilledCustomer?->id);
    $componentiRows = collect(old('componenti', isset($componenti) ? $componenti->toArray() : []));
    if ($componentiRows->isEmpty()) {
        $componentiRows = collect([[]]);
    }
    $hasComponentiData = $componentiRows->contains(function ($row) {
        $row = is_array($row) ? $row : (array) $row;
        $keys = ['name', 'surname', 'sex', 'relationship', 'country', 'city', 'date_nac'];
        foreach ($keys as $key) {
            if (!empty($row[$key])) {
                return true;
            }
        }
        return false;
    });
    $showOnlyAddComponente = !$hasComponentiData && $componentiRows->count() === 1;
    $comuniLabelMap = collect($ciudades ?? [])->mapWithKeys(function ($c) {
        $id = is_array($c) ? ($c['id'] ?? null) : ($c->id ?? null);
        $nome = is_array($c) ? ($c['nome'] ?? null) : ($c->nome ?? null);
        return [$id => $nome];
    });
@endphp

@php
    $camereEnabledFlag = (bool) config('app.camere_reali_enabled', env('CAMERE_REALI_ENABLED', false));
    $allowedStrutture = collect(explode(',', (string) env('CAMERE_REALI_STRUTTURE', '')))
        ->map(fn($v) => trim($v))
        ->filter();
    $currentStrutturaId = $strutturaInfo->id ?? null;
    $camereEnabledByStruttura = $strutturaInfo->camere_reali_enabled ?? null;

    if ($camereEnabledFlag) {
        if (!is_null($camereEnabledByStruttura)) {
            $camereEnabledFlag = (bool) $camereEnabledByStruttura;
        } elseif ($allowedStrutture->isNotEmpty()) {
            $camereEnabledFlag = $currentStrutturaId && $allowedStrutture->contains((string) $currentStrutturaId);
        }
    }
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">{{ $formTitle }}</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ $formAction }}" class="form-steps" autocomplete="off">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="save_mode" id="save-mode" value="{{ old('save_mode', 'full') }}">
            <input type="hidden" name="save_mode_intent" id="save-mode-intent" value="{{ old('save_mode_intent', '') }}">
            <input type="hidden" name="active_tab" id="active-tab" value="{{ old('active_tab', request()->query('active_tab', session('active_tab', 'schedina-step-base'))) }}">

            <div class="step-arrow-nav mb-4">
                <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="schedina-step-base" data-bs-toggle="pill" data-bs-target="#schedina-step-base-pane" type="button" role="tab" aria-controls="schedina-step-base-pane" aria-selected="true">Schedina</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schedina-step-anag" data-bs-toggle="pill" data-bs-target="#schedina-step-anag-pane" type="button" role="tab" aria-controls="schedina-step-anag-pane" aria-selected="false">Anagrafica</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schedina-step-res" data-bs-toggle="pill" data-bs-target="#schedina-step-res-pane" type="button" role="tab" aria-controls="schedina-step-res-pane" aria-selected="false">Residenza / Doc.</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schedina-step-contact" data-bs-toggle="pill" data-bs-target="#schedina-step-contact-pane" type="button" role="tab" aria-controls="schedina-step-contact-pane" aria-selected="false">Contatti</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schedina-step-comp" data-bs-toggle="pill" data-bs-target="#schedina-step-comp-pane" type="button" role="tab" aria-controls="schedina-step-comp-pane" aria-selected="false">Componenti</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="schedina-step-tassa" data-bs-toggle="pill" data-bs-target="#schedina-step-tassa-pane" type="button" role="tab" aria-controls="schedina-step-tassa-pane" aria-selected="false">Tassa di soggiorno</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="schedina-step-base-pane" role="tabpanel" aria-labelledby="schedina-step-base">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-hotel-bed-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Dati soggiorno</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Arrivo <span class="text-danger">*</span></label>
                                        <x-calendario
                                            name="arrive"
                                            variant="period-start"
                                            group="soggiorno"
                                            :value="$toDateInput($valueOf('arrive'))"
                                            placeholder="gg/mm/aaaa"
                                        />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Partenza <span class="text-danger">*</span></label>
                                        <x-calendario
                                            name="departure"
                                            variant="period-end"
                                            group="soggiorno"
                                            :value="$toDateInput($valueOf('departure'))"
                                            placeholder="gg/mm/aaaa"
                                        />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo alloggiato <span class="text-danger">*</span></label>
                                        @php $relationshipValue = old('relationship', $schedina->relationship ?? 'OSPITE SINGOLO'); @endphp
                                        <x-ui.select name="relationship">
                                            <option value="CAPO FAMIGLIA" {{ $relationshipValue === 'CAPO FAMIGLIA' ? 'selected' : '' }}>CAPO FAMIGLIA</option>
                                            <option value="CAPO GRUPPO" {{ $relationshipValue === 'CAPO GRUPPO' ? 'selected' : '' }}>CAPO GRUPPO</option>
                                            <option value="OSPITE SINGOLO" {{ $relationshipValue === 'OSPITE SINGOLO' ? 'selected' : '' }}>OSPITE SINGOLO</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label form-label-help">Esente <span class="text-danger">*</span>
                                            <x-ui.help
                                                title="Esente"
                                                text="Serve per indicare se l ospite paga o non paga la tassa di soggiorno. Il formato e una scelta da elenco. Usa NO quando non c e nessuna esenzione. Se scegli una voce di esenzione, il sistema la usa anche nel calcolo della tassa."
                                            />
                                        </label>
                                        @php $exentValue = old('exent', $schedina->exent ?? 'NO'); @endphp
                                        <x-ui.select name="exent">
                                            <option value="NO" {{ $exentValue === 'NO' ? 'selected' : '' }}>No</option>
                                            <option value="Personale" {{ $exentValue === 'Personale' ? 'selected' : '' }}>Personale</option>
                                            <option value="Acompagnatore Turistico" {{ $exentValue === 'Acompagnatore Turistico' ? 'selected' : '' }}>Acompagnatore Turistico</option>
                                            <option value="Autista" {{ $exentValue === 'Autista' ? 'selected' : '' }}>Autista</option>
                                            <option value="Forze armate in seervizio" {{ $exentValue === 'Forze armate in seervizio' ? 'selected' : '' }}>Forze armate in seervizio</option>
                                            <option value="Accompagnatori per pazienti" {{ $exentValue === 'Accompagnatori per pazienti' ? 'selected' : '' }}>Accompagnatori per pazienti</option>
                                            <option value="Residente in hotel" {{ $exentValue === 'Residente in hotel' ? 'selected' : '' }}>Residente in hotel</option>
                                            <option value="Residente nel comune" {{ $exentValue === 'Residente nel comune' ? 'selected' : '' }}>Residente nel comune</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-user-3-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Dati cliente</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @php $tipoClienteValue = old('customer_type_housed', $schedina->customer_type_housed ?? $prefilledCustomer->type_housed ?? 'Ospite'); @endphp
                                <input type="hidden" id="customer-type-housed" name="customer_type_housed" value="{{ $tipoClienteValue }}">
                                <div class="col-lg-3">
                                    <label class="form-label form-label-help">Quantità persone <span class="text-danger">*</span>
                                        <x-ui.help
                                            title="Quantità persone"
                                            text="Serve per indicare quante persone appartengono a questa schedina. Il formato e un numero intero. Rappresenta il totale delle persone registrate nel soggiorno e aiuta i conteggi operativi e statistici."
                                        />
                                    </label>
                                    <input type="number" class="form-control" name="cant_people" min="1" required value="{{ old('cant_people', $schedina->cant_people ?? 1) }}">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label form-label-help">Camera <span class="text-danger">*</span>
                                        <x-ui.help
                                            title="Camera"
                                            text="Serve per indicare quante camere occupa questa schedina. Il formato e un numero intero. Inserisci 1 se il soggiorno occupa una sola camera, 2 se ne occupa due, e cosi via."
                                        />
                                    </label>
                                    <input type="number" class="form-control" name="room" min="1" required value="{{ old('room', $schedina->room) }}">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label form-label-help">Letti <span class="text-danger">*</span>
                                        <x-ui.help
                                            title="Letti"
                                            text="Serve per indicare quanti letti occupa la schedina. Il formato e un numero intero. Si ragiona in letti singoli equivalenti, non in letti matrimoniali o doppi. Normalmente il numero dei letti coincide con il numero delle persone ospitate."
                                        />
                                    </label>
                                    <input type="number" class="form-control" name="beds" min="1" required value="{{ old('beds', $schedina->beds) }}">
                                </div>
                                <input type="hidden" id="customer_id" name="customer_id" value="{{ $selectedCustomerId }}">
                            </div>
                            <div class="form-text mt-2">Quantità persone, Camera e Letti sono obbligatori per i conteggi ISTAT Tavola A e per il controllo della tassa di soggiorno.</div>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <div class="row g-3 align-items-end" data-ui="customer-group-cascade" data-scope="schedina">
                                        <div class="col-lg-3">
                                            <label class="form-label">Gruppo I</label>
                                            @php $customerGroupValue = old('customer_group', $schedina->customer_group ?? $prefilledCustomer?->group); @endphp
                                            <x-ui.select name="customer_group" id="schedina-customer-group" data-role="customer-group-l1" data-no-select2="1">
                                                <option value="">Seleziona gruppo</option>
                                                @foreach(($gruppiLivello1 ?? collect()) as $group)
                                                    <option value="{{ $group->nome }}" data-group-id="{{ $group->id }}" @selected($customerGroupValue === $group->nome)>{{ $group->nome }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Gruppo II</label>
                                            @php $customerSubgroupValue = old('customer_subgroup', $schedina->customer_subgroup ?? $prefilledCustomer?->subgroup); @endphp
                                            <x-ui.select name="customer_subgroup" id="schedina-customer-subgroup" data-role="customer-group-l2" data-no-select2="1">
                                                <option value="">Seleziona gruppo</option>
                                                @foreach(($gruppiLivello2 ?? collect()) as $group)
                                                    <option value="{{ $group->nome }}" data-group-id="{{ $group->id }}" data-parent-id="{{ $group->parent_id }}" @selected($customerSubgroupValue === $group->nome)>{{ $group->nome }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label class="form-label">Gruppo III</label>
                                            @php $customerSubgroup1Value = old('customer_subgroup1', $schedina->customer_subgroup1 ?? $prefilledCustomer?->subgroup1); @endphp
                                            <x-ui.select name="customer_subgroup1" id="schedina-customer-subgroup1" data-role="customer-group-l3" data-no-select2="1">
                                                <option value="">Seleziona sottogruppo</option>
                                                @foreach(($gruppiLivello3 ?? collect()) as $group)
                                                    <option value="{{ $group->nome }}" data-parent-id="{{ $group->parent_id }}" @selected($customerSubgroup1Value === $group->nome)>{{ $group->nome }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Titolo</label>
                                    <x-ui.select name="type">
                                        @foreach($titoli as $title)
                                            <option value="{{ $title->name }}" {{ $valueOf('type') === $title->name ? 'selected' : '' }}>{{ $title->name }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="{{ $valueOf('name') }}">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Cognome <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="surname" name="surname" value="{{ $valueOf('surname') }}">
                                </div>
                                <div class="col-lg-2">
                                    <label class="form-label">Sesso <span class="text-danger">*</span></label>
                                    <x-ui.select name="sex">
                                        @foreach(['M','F'] as $sex)
                                            <option value="{{ $sex }}" {{ ($valueOf('sex', 'M')) === $sex ? 'selected' : '' }}>{{ $sex }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-file-list-3-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Osservazioni</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="mb-0">
                                        <label class="form-label">Osservazione</label>
                                        <textarea class="form-control" name="observation" rows="3">{{ old('observation', $schedina->observation) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($showIstatFields)
                        <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                <i class="ri-bar-chart-grouped-line me-2 text-primary"></i>
                                <h5 class="card-title mb-0">
                                    <span class="section-title-help">Dati statistici Tavola A
                                        <x-ui.help
                                            title="Dati statistici Tavola A"
                                            text="Questa sezione serve per i dati statistici turistici ISTAT Tavola A. I campi sono in formato elenco o testo breve, secondo il caso. Non sempre sono obbligatori per salvare la schedina, ma sono utili per i riepiloghi statistici della struttura."
                                        />
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label">Tipo turismo</label>
                                        <x-ui.select name="istat_tipo_turismo">
                                            <option value="">Non disponibile</option>
                                            @foreach($istatTipoTurismoOptions as $code => $label)
                                                <option value="{{ $code }}" @selected(old('istat_tipo_turismo', $schedina->istat_tipo_turismo) === $code)>{{ $label }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Mezzo di trasporto</label>
                                        <x-ui.select name="istat_mezzo_trasporto">
                                            <option value="">Non disponibile</option>
                                            @foreach($istatMezzoTrasportoOptions as $code => $label)
                                                <option value="{{ $code }}" @selected(old('istat_mezzo_trasporto', $schedina->istat_mezzo_trasporto) === $code)>{{ $label }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Canale prenotazione</label>
                                        <x-ui.select name="istat_canale_prenotazione">
                                            <option value="">Non disponibile</option>
                                            @foreach($istatCanaleOptions as $code => $label)
                                                <option value="{{ $code }}" @selected(old('istat_canale_prenotazione', $schedina->istat_canale_prenotazione) === $code)>{{ $label }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Titolo di studio</label>
                                        <x-ui.select name="istat_titolo_studio">
                                            <option value="">Non disponibile</option>
                                            @foreach($istatTitoloStudioOptions as $code => $label)
                                                <option value="{{ $code }}" @selected(old('istat_titolo_studio', $schedina->istat_titolo_studio) === $code)>{{ $label }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Professione</label>
                                        <x-ui.select name="istat_professione" data-allow-manual="1">
                                            <option value="">Non disponibile</option>
                                            @foreach($professioniIstat as $professione)
                                                <option value="{{ $professione }}" @selected(old('istat_professione', $schedina->istat_professione) === $professione)>{{ $professione }}</option>
                                            @endforeach
                                            @php $professioneValue = old('istat_professione', $schedina->istat_professione); @endphp
                                            @if($professioneValue && !collect($professioniIstat)->contains($professioneValue))
                                                <option value="{{ $professioneValue }}" selected>{{ $professioneValue }}</option>
                                            @endif
                                        </x-ui.select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Codice prenotazione</label>
                                        <input type="text" class="form-control" name="id_prenotazione_esterna" value="{{ old('id_prenotazione_esterna', $schedina->id_prenotazione_esterna) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label form-label-help" for="istatNonTurista">Ospite non turista
                                            <x-ui.help
                                                title="Ospite non turista"
                                                text="Serve per indicare che questo soggiorno non deve essere contato nel movimento turistico ISTAT. Il formato e una scelta attiva o non attiva. Se lo attivi, la schedina resta valida ma viene esclusa dal conteggio turistico della Tavola A."
                                            />
                                        </label>
                                        <div class="form-check form-switch form-switch-md">
                                            <input class="form-check-input" type="checkbox" role="switch" name="istat_non_turista" id="istatNonTurista" value="1" @checked(old('istat_non_turista', $schedina->istat_non_turista))>
                                            <label class="form-check-label" for="istatNonTurista">Attiva opzione</label>
                                        </div>
                                        <div class="form-text">Se attivo, questa schedina non viene conteggiata nella Tavola A.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($camereEnabledFlag)
                        @php $camereOld = old('camere', $schedina->camere ?? []); $hasCamereOld = !empty($camereOld); @endphp
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h5 class="mt-2 mb-0">Camere reali (facoltativo)</h5>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="camere-toggle" aria-expanded="{{ $hasCamereOld ? 'true' : 'false' }}">
                                        {{ $hasCamereOld ? 'Disconnetti' : 'Connetti gestionale' }}
                                    </button>
                                </div>
                                <div id="camere-section" class="border rounded p-3 {{ $hasCamereOld ? '' : 'd-none' }}" data-active="{{ $hasCamereOld ? 'true' : 'false' }}">
                                    <div id="camere-container">
                                        @forelse($camereOld as $index => $camera)
                                            <div class="row g-2 align-items-end mb-2 camera-row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Numero camera</label>
                                                    <input type="text" class="form-control" name="camere[{{ $index }}][numero_camera]" value="{{ $camera['numero_camera'] ?? $camera->numero_camera ?? '' }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Posti letto</label>
                                                    <input type="number" class="form-control" name="camere[{{ $index }}][posti_letto]" value="{{ $camera['posti_letto'] ?? $camera->posti_letto ?? '' }}" min="0" max="20">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Note</label>
                                                    <input type="text" class="form-control" name="camere[{{ $index }}][note]" value="{{ $camera['note'] ?? $camera->note ?? '' }}" maxlength="255">
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger w-100 remove-camera">Rimuovi</button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="row g-2 align-items-end mb-2 camera-row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Numero camera</label>
                                                    <input type="text" class="form-control" name="camere[0][numero_camera]">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Posti letto</label>
                                                    <input type="number" class="form-control" name="camere[0][posti_letto]" min="0" max="20">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Note</label>
                                                    <input type="text" class="form-control" name="camere[0][note]" maxlength="255">
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger w-100 remove-camera">Rimuovi</button>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" id="add-camera-row">Aggiungi camera</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @include('schedina.partials.save-actions', ['next' => 'schedina-step-anag', 'isEdit' => $isEdit])
                </div>

                <div class="tab-pane fade" id="schedina-step-anag-pane" role="tabpanel" aria-labelledby="schedina-step-anag">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-user-location-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Anagrafica ospite</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <x-geo.italia
                                        title="Geo anagrafica"
                                        icon="ri-map-pin-user-line"
                                        prefix="birth"
                                        :showCap="true"
                                        :names="[
                                            'nazione_id' => 'oa_country',
                                            'regione_id' => 'oa_region',
                                            'provincia_id' => 'oa_prov',
                                            'comune_id' => 'oa_city',
                                            'cap' => 'oa_cap',
                                            'regione_text' => 'oa_region',
                                            'provincia_text' => 'oa_prov',
                                            'citta_text' => 'oa_city',
                                            'cap_text' => 'oa_cap',
                                            'manual_flag' => 'oa_geo_manual',
                                        ]"
                                        :value="[
                                            'nazione_text' => $valueOf('oa_country'),
                                            'regione_text' => $valueOf('oa_region'),
                                            'provincia_text' => $valueOf('oa_prov'),
                                            'comune_text' => $valueOf('oa_city'),
                                            'cap' => $valueOf('oa_cap'),
                                            'cap_text' => $valueOf('oa_cap'),
                                            'manual' => (bool) old('oa_geo_manual', false),
                                        ]"
                                    />
                                    <div class="form-text mt-2">Per salvare la schedina sono obbligatori: nazione, regione, provincia e città dell anagrafica.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-id-card-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Dati anagrafici</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label">Cittadinanza <span class="text-danger">*</span></label>
                                    <x-ui.select
                                        id="schedina-oa-city-nac"
                                        name="oa_city_nac"
                                        data-allow-manual="1"
                                        placeholder="Seleziona o digita cittadinanza"
                                    >
                                        <option value="">Seleziona cittadinanza</option>
                                        @foreach(($cittadinanze ?? collect()) as $cittadinanza)
                                            <option value="{{ $cittadinanza }}" @selected($valueOf('oa_city_nac') === $cittadinanza)>{{ $cittadinanza }}</option>
                                        @endforeach
                                        @if($valueOf('oa_city_nac') && !collect($cittadinanze ?? [])->contains($valueOf('oa_city_nac')))
                                            <option value="{{ $valueOf('oa_city_nac') }}" selected>{{ $valueOf('oa_city_nac') }}</option>
                                        @endif
                                    </x-ui.select>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Data di nascita <span class="text-danger">*</span></label>
                                    <x-calendario
                                        name="oa_date_nac"
                                        variant="birth"
                                        :value="$toDateInput($valueOf('oa_date_nac'))"
                                        placeholder="gg/mm/aaaa"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('schedina.partials.save-actions', ['previous' => 'schedina-step-base', 'next' => 'schedina-step-res', 'isEdit' => $isEdit])
                </div>

                <div class="tab-pane fade" id="schedina-step-res-pane" role="tabpanel" aria-labelledby="schedina-step-res">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-map-pin-2-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Residenza</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <x-geo.italia
                                        title="Geo residenza"
                                        :value="[
                                            'nazione_text' => $valueOf('or_country'),
                                            'regione_text' => $valueOf('or_region'),
                                            'provincia_text' => $valueOf('or_prov'),
                                            'comune_text' => $valueOf('or_city'),
                                            'cap' => $valueOf('or_cap'),
                                            'cap_text' => $valueOf('or_cap'),
                                            'manual' => (bool) old('or_geo_manual', $schedina->or_geo_manual ?? false),
                                        ]"
                                        :names="[
                                            'nazione_id' => 'or_country',
                                            'regione_id' => 'or_region',
                                            'provincia_id' => 'or_prov',
                                            'comune_id' => 'or_city',
                                            'cap' => 'or_cap',
                                            'regione_text' => 'or_region',
                                            'provincia_text' => 'or_prov',
                                            'citta_text' => 'or_city',
                                            'cap_text' => 'or_cap',
                                            'manual_flag' => 'or_geo_manual',
                                        ]"
                                    />
                                    <div class="form-text mt-2">Per salvare la schedina sono obbligatori: nazione, regione, provincia e città della residenza.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-road-map-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Indirizzo residenza</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-2">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo via <span class="text-danger">*</span></label>
                                        <x-ui.select name="or_typeaway">
                                            @foreach($tipiVia as $typestreet)
                                                <option value="{{ $typestreet->name }}" {{ $valueOf('or_typeaway') === $typestreet->name ? 'selected' : '' }}>{{ $typestreet->name }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label">Indirizzo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="or_address" value="{{ $valueOf('or_address') }}">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="mb-3">
                                        <label class="form-label">Numero civico <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="or_num" value="{{ $valueOf('or_num') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-passport-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Documento identità</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo documento <span class="text-danger">*</span></label>
                                        <x-ui.select name="or_doctype">
                                            @foreach($tipiDocumento as $type)
                                                <option value="{{ $type->name }}" {{ $valueOf('or_doctype') === $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Documento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="or_doc" value="{{ $valueOf('or_doc') }}">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Data rilascio <span class="text-danger">*</span></label>
                                        <x-calendario
                                            name="or_published_date"
                                            :value="$toDateInput($valueOf('or_published_date'))"
                                            placeholder="gg/mm/aaaa"
                                        />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Scadenza <span class="text-danger">*</span></label>
                                        <x-calendario
                                            name="or_expire"
                                            variant="period-end"
                                            group="doc-schedina"
                                            :value="$toDateInput($valueOf('or_expire'))"
                                            placeholder="gg/mm/aaaa"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Rilasciato da <span class="text-danger">*</span></label>
                                        <x-ui.select name="or_published" placeholder="Seleziona rilasciato da">
                                            <option value="">Seleziona rilasciato da</option>
                                            @foreach(($rilasciatoDa ?? collect()) as $rilasciato)
                                                <option value="{{ $rilasciato->name }}" @selected($valueOf('or_published') === $rilasciato->name)>{{ $rilasciato->name }}</option>
                                            @endforeach
                                            @if($valueOf('or_published') && !(collect($rilasciatoDa ?? [])->pluck('name')->contains($valueOf('or_published'))))
                                                <option value="{{ $valueOf('or_published') }}" selected>{{ $valueOf('or_published') }}</option>
                                            @endif
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">Paese rilascio <span class="text-danger">*</span></label>
                                        <x-ui.select id="or-published-country" name="or_published_country">
                                            <option value="">Seleziona paese</option>
                                            @foreach($nations as $nation)
                                                @php $value = is_array($nation) ? ($nation['nome'] ?? '') : ($nation->nome ?? ''); @endphp
                                                @if($value !== '')
                                                    <option value="{{ $value }}" {{ $valueOf('or_published_country') === $value ? 'selected' : '' }}>{{ $value }}</option>
                                                @endif
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="col-lg-3" id="or-published-city-wrap">
                                    <div class="mb-3">
                                        <label class="form-label">Città rilascio <span class="text-danger">*</span> <span class="text-muted">(se Italia)</span></label>
                                        <x-ui.select id="or-published-city" name="or_published_city" placeholder="Seleziona città">
                                            <option value="">Seleziona città</option>
                                            @foreach($ciudades as $ciudad)
                                                @php $cityValue = is_array($ciudad) ? ($ciudad['nome'] ?? '') : ($ciudad->nome ?? ''); @endphp
                                                @if($cityValue !== '')
                                                    <option value="{{ $cityValue }}" {{ $valueOf('or_published_city') === $cityValue ? 'selected' : '' }}>{{ $cityValue }}</option>
                                                @endif
                                            @endforeach
                                        </x-ui.select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('schedina.partials.save-actions', ['previous' => 'schedina-step-anag', 'next' => 'schedina-step-contact', 'isEdit' => $isEdit])
                </div>

                <div class="tab-pane fade" id="schedina-step-contact-pane" role="tabpanel" aria-labelledby="schedina-step-contact">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-contacts-book-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Contatti cliente</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <label class="form-label">Telefono</label>
                                    <input type="text" class="form-control" name="customer_phone" value="{{ old('customer_phone', $schedina->customer_phone ?? $prefilledCustomer?->phone) }}">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Cellulare</label>
                                    <input type="text" class="form-control" name="customer_cellphone" value="{{ old('customer_cellphone', $schedina->customer_cellphone ?? $prefilledCustomer?->cellphone) }}">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" class="form-control" name="customer_email" value="{{ old('customer_email', $schedina->customer_email ?? $prefilledCustomer?->email) }}">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Fax</label>
                                    <input type="text" class="form-control" name="customer_fax" value="{{ old('customer_fax', $schedina->customer_fax ?? $prefilledCustomer?->fax) }}">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Osservazione cliente</label>
                                    <textarea class="form-control" name="customer_observation" rows="2">{{ old('customer_observation', $schedina->customer_observation ?? $prefilledCustomer?->observation) }}</textarea>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Osservazione anagrafica</label>
                                    <textarea class="form-control" name="customer_anag_observation" rows="2">{{ old('customer_anag_observation', $schedina->customer_anag_observation ?? $prefilledCustomer?->observation_reg) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <i class="ri-shield-check-line me-2 text-primary"></i>
                            <h5 class="card-title mb-0">
                                <span class="section-title-help">Consensi cliente
                                    <x-ui.help
                                        title="Consensi cliente"
                                        text="Privacy e il consenso obbligatorio per trattare i dati nella schedina. Marketing permette comunicazioni promozionali future. Comunicazioni permette contatti operativi o commerciali come email o WhatsApp. I consensi seguono il cliente e vanno gestiti in modo coerente."
                                    />
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <input type="hidden" name="customer_privacy_consent" value="0">
                                    <input type="hidden" name="customer_privacy_consent_at" value="{{ old('customer_privacy_consent_at', optional($schedina->customer_privacy_consent_at ?? $prefilledCustomer?->privacy_consent_at)->toDateTimeString()) }}">
                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold">Privacy <span class="text-danger">*</span></div>
                                                <div class="text-muted small">Obbligatorio per la registrazione della schedina.</div>
                                            </div>
                                            <div class="form-check form-switch form-switch-md m-0">
                                                <input class="form-check-input js-schedina-consent-switch" type="checkbox" role="switch" name="customer_privacy_consent" value="1" @checked(old('customer_privacy_consent', $schedina->customer_privacy_consent ?? $prefilledCustomer?->privacy_consent)) data-timestamp-target="customer_privacy_consent_at">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <input type="hidden" name="customer_marketing_consent" value="0">
                                    <input type="hidden" name="customer_marketing_consent_at" value="{{ old('customer_marketing_consent_at', optional($schedina->customer_marketing_consent_at ?? $prefilledCustomer?->marketing_consent_at)->toDateTimeString()) }}">
                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold">Marketing</div>
                                                <div class="text-muted small">Permette comunicazioni promozionali future.</div>
                                            </div>
                                            <div class="form-check form-switch form-switch-md m-0">
                                                <input class="form-check-input js-schedina-consent-switch" type="checkbox" role="switch" name="customer_marketing_consent" value="1" @checked(old('customer_marketing_consent', $schedina->customer_marketing_consent ?? $prefilledCustomer?->marketing_consent)) data-timestamp-target="customer_marketing_consent_at">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <input type="hidden" name="customer_communication_consent" value="0">
                                    <input type="hidden" name="customer_communication_consent_at" value="{{ old('customer_communication_consent_at', optional($schedina->customer_communication_consent_at ?? $prefilledCustomer?->communication_consent_at)->toDateTimeString()) }}">
                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold">Comunicazioni</div>
                                                <div class="text-muted small">Permette contatti via email o WhatsApp.</div>
                                            </div>
                                            <div class="form-check form-switch form-switch-md m-0">
                                                <input class="form-check-input js-schedina-consent-switch" type="checkbox" role="switch" name="customer_communication_consent" value="1" @checked(old('customer_communication_consent', $schedina->customer_communication_consent ?? $prefilledCustomer?->communication_consent)) data-timestamp-target="customer_communication_consent_at">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('schedina.partials.save-actions', ['previous' => 'schedina-step-res', 'next' => 'schedina-step-comp', 'isEdit' => $isEdit])
                </div>

                <div class="tab-pane fade" id="schedina-step-comp-pane" role="tabpanel" aria-labelledby="schedina-step-comp">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="ri-team-line me-2 text-primary"></i>
                                <h5 class="card-title mb-0">Componenti scheda</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="componenti-section">
                                <div class="alert alert-info py-2 mb-3">
                                    Campi obbligatori componente: Nome, Cognome, Sesso, Tipo alloggiato, Esente, Cittadinanza, Provincia nascita, Data di nascita, Nazione, Regione, Provincia, Città, Tipo via, Strada, Num, CAP.
                                </div>

                                <div id="componenti-container">
                                @foreach($componentiRows as $index => $row)
                                    @php
                                        $row = is_array($row) ? $row : (array) $row;
                                        $rowVal = fn($k, $d = '') => $row[$k] ?? $d;
                                        $isFilledRow = collect(['name', 'surname', 'sex', 'relationship', 'country', 'city', 'date_nac'])
                                            ->contains(fn($k) => !empty($rowVal($k)));
                                        $summaryName = trim(($rowVal('surname') ? $rowVal('surname').' ' : '').$rowVal('name'));
                                        $eta = null;
                                        if (!empty($rowVal('date_nac'))) {
                                            try {
                                                $eta = max(1, \Carbon\Carbon::parse($rowVal('date_nac'))->age);
                                            } catch (\Throwable $e) {
                                                $eta = null;
                                            }
                                        }
                                        $citySummary = $rowVal('city');
                                        if ($citySummary !== '' && is_numeric($citySummary) && $comuniLabelMap->has((int) $citySummary)) {
                                            $citySummary = (string) $comuniLabelMap->get((int) $citySummary);
                                        }
                                        if ($citySummary !== '' && is_numeric($citySummary)) {
                                            try {
                                                $resolvedCity = \App\Models\GeoComune::query()
                                                    ->where('id', (int) $citySummary)
                                                    ->orWhere('codice_istat', (string) $citySummary)
                                                    ->value('nome');
                                                if (!empty($resolvedCity)) {
                                                    $citySummary = (string) $resolvedCity;
                                                }
                                            } catch (\Throwable $e) {
                                                // no-op
                                            }
                                        }
                                        $cityGeoLabel = $citySummary !== '' && !is_numeric($citySummary) ? $citySummary : '';
                                    @endphp
                                    <div class="card border shadow-sm mb-3 componente-row {{ $showOnlyAddComponente && !$isFilledRow ? 'd-none componente-empty-row' : '' }}" data-index="{{ $index }}" data-city-label="{{ $cityGeoLabel }}">
                                        <div class="card-header d-flex align-items-center justify-content-between bg-light-subtle">
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-0 d-none"></h6>
                                                <div class="text-body componente-summary-line fs-6">
                                                    {{ ($index + 1) . '. ' }}{{ $summaryName !== '' ? $summaryName : 'Nuovo componente' }}
                                                    @if($citySummary) · Città: {{ $citySummary }} @endif
                                                    @if(!is_null($eta)) · Età: {{ $eta }} @endif
                                                    @if($rowVal('exent')) · Esente: {{ $rowVal('exent') }} @endif
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-soft-info btn-sm toggle-componente-details">
                                                    <i class="ri-eye-line align-bottom me-1"></i>Dettagli
                                                </button>
                                            <button type="button" class="btn btn-soft-danger btn-sm remove-componente">
                                                <i class="ri-delete-bin-line align-bottom me-1"></i>Rimuovi
                                            </button>
                                            </div>
                                        </div>
                                        <div class="card-body componente-details {{ $isFilledRow ? 'd-none' : '' }}">
                                            <div class="card border-0 bg-light-subtle mb-3">
                                                <div class="card-header border-0 d-flex align-items-center py-2">
                                                    <i class="ri-user-line me-2 text-primary"></i>
                                                    <h6 class="mb-0">Dati principali</h6>
                                                </div>
                                                <div class="card-body pt-2">
                                                    <div class="row g-3">
                                                        <div class="col-lg-3">
                                                            <label class="form-label">Tipo alloggiato</label>
                                                            <x-ui.select name="componenti[{{ $index }}][relationship]">
                                                                <option value="">Seleziona</option>
                                                                <option value="CAPO FAMIGLIA" {{ $rowVal('relationship') === 'CAPO FAMIGLIA' ? 'selected' : '' }}>CAPO FAMIGLIA</option>
                                                                <option value="CAPO GRUPPO" {{ $rowVal('relationship') === 'CAPO GRUPPO' ? 'selected' : '' }}>CAPO GRUPPO</option>
                                                                <option value="OSPITE SINGOLO" {{ $rowVal('relationship') === 'OSPITE SINGOLO' ? 'selected' : '' }}>OSPITE SINGOLO</option>
                                                                <option value="FAMILIARE" {{ $rowVal('relationship') === 'FAMILIARE' ? 'selected' : '' }}>FAMILIARE</option>
                                                                <option value="MEMBRO GRUPPO" {{ $rowVal('relationship') === 'MEMBRO GRUPPO' ? 'selected' : '' }}>MEMBRO GRUPPO</option>
                                                            </x-ui.select>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mt-1">
                                                        <div class="col-lg-3">
                                                            <label class="form-label">Nome</label>
                                                            <input type="text" class="form-control" name="componenti[{{ $index }}][name]" value="{{ $rowVal('name') }}">
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <label class="form-label">Cognome</label>
                                                            <input type="text" class="form-control" name="componenti[{{ $index }}][surname]" value="{{ $rowVal('surname') }}">
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <label class="form-label">Sesso</label>
                                                            <x-ui.select name="componenti[{{ $index }}][sex]">
                                                                <option value="">Seleziona</option>
                                                                <option value="M" {{ $rowVal('sex') === 'M' ? 'selected' : '' }}>M</option>
                                                                <option value="F" {{ $rowVal('sex') === 'F' ? 'selected' : '' }}>F</option>
                                                            </x-ui.select>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <label class="form-label">Esente</label>
                                                            <x-ui.select name="componenti[{{ $index }}][exent]">
                                                                <option value="NO" {{ strtoupper((string) $rowVal('exent', 'NO')) === 'NO' ? 'selected' : '' }}>NO</option>
                                                                @foreach(($esenzioni ?? collect()) as $esenzione)
                                                                    @php
                                                                        $codice = (string) ($esenzione->codice ?? '');
                                                                        $descrizione = (string) ($esenzione->descrizione ?? '');
                                                                        $label = trim($codice . ' - ' . $descrizione, ' -');
                                                                        $current = (string) $rowVal('exent');
                                                                    @endphp
                                                                    @if($codice !== '' || $descrizione !== '')
                                                                        <option value="{{ $codice !== '' ? $codice : $descrizione }}" {{ strcasecmp($current, $codice) === 0 || strcasecmp($current, $descrizione) === 0 ? 'selected' : '' }}>
                                                                            {{ $label }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light-subtle mb-3">
                                                <div class="card-header border-0 d-flex align-items-center py-2">
                                                    <i class="ri-id-card-line me-2 text-primary"></i>
                                                    <h6 class="mb-0">Anagrafica</h6>
                                                </div>
                                                <div class="card-body pt-2">
                                                    <x-geo.italia
                                                        prefix="componente_anag_{{ $index }}"
                                                        title="Geo anagrafica componente"
                                                        :showCap="true"
                                                        :showToggleItalia="true"
                                                        :showIndirizzo="false"
                                                        :showCittadinanza="false"
                                                        :names="[
                                                            'nazione_id' => 'componenti['.$index.'][country_nac]',
                                                            'regione_id' => 'componenti['.$index.'][regione_nac]',
                                                            'provincia_id' => 'componenti['.$index.'][province_nac]',
                                                            'comune_id' => 'componenti['.$index.'][comune_nac]',
                                                            'cap' => 'componenti['.$index.'][cap_nac]',
                                                            'regione_text' => 'componenti['.$index.'][regione_nac]',
                                                            'provincia_text' => 'componenti['.$index.'][province_nac]',
                                                            'citta_text' => 'componenti['.$index.'][comune_nac]',
                                                            'cap_text' => 'componenti['.$index.'][cap_nac]',
                                                            'manual_flag' => 'componenti['.$index.'][geo_anag_manual]',
                                                        ]"
                                                        :value="[
                                                            'nazione_text' => $rowVal('country_nac'),
                                                            'regione_text' => $rowVal('regione_nac'),
                                                            'provincia_text' => $rowVal('province_nac'),
                                                            'comune_text' => $rowVal('comune_nac'),
                                                            'cap' => $rowVal('cap_nac'),
                                                            'cap_text' => $rowVal('cap_nac'),
                                                            'manual' => (bool) $rowVal('geo_anag_manual', false),
                                                        ]"
                                                    />

                                                    <div class="card border-0 bg-body mt-3 mb-0 shadow-sm">
                                                        <div class="card-header border-0 bg-light-subtle py-2">
                                                            <h6 class="mb-0">Dati anagrafici aggiuntivi</h6>
                                                        </div>
                                                        <div class="card-body pt-2">
                                                            <div class="row g-3">
                                                                <div class="col-lg-4">
                                                                    <label class="form-label">Cittadinanza</label>
                                                                    <x-ui.select name="componenti[{{ $index }}][city_nac]" data-allow-manual="1">
                                                                        <option value="">Seleziona cittadinanza</option>
                                                                        @foreach(($cittadinanze ?? collect()) as $cittadinanza)
                                                                            <option value="{{ $cittadinanza }}" {{ $rowVal('city_nac') === $cittadinanza ? 'selected' : '' }}>{{ $cittadinanza }}</option>
                                                                        @endforeach
                                                                        @if($rowVal('city_nac') && !collect($cittadinanze ?? [])->contains($rowVal('city_nac')))
                                                                            <option value="{{ $rowVal('city_nac') }}" selected>{{ $rowVal('city_nac') }}</option>
                                                                        @endif
                                                                    </x-ui.select>
                                                                </div>
                                                                <div class="col-lg-4">
                                                                    <label class="form-label">Data di nascita</label>
                                                                    <x-calendario
                                                                        name="componenti[{{ $index }}][date_nac]"
                                                                        variant="birth"
                                                                        :value="$toDateInput($rowVal('date_nac'))"
                                                                        placeholder="gg/mm/aaaa"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border-0 bg-light-subtle mb-0">
                                                <div class="card-header border-0 d-flex align-items-center py-2">
                                                    <i class="ri-map-pin-2-line me-2 text-primary"></i>
                                                    <h6 class="mb-0">Residenza</h6>
                                                </div>
                                                <div class="card-body pt-2">
                                                    <x-geo.italia
                                                        prefix="componente_res_{{ $index }}"
                                                        title="Geo residenza componente"
                                                        :showCap="true"
                                                        :showToggleItalia="true"
                                                        :showIndirizzo="false"
                                                        :showCittadinanza="false"
                                                        :names="[
                                                            'nazione_id' => 'componenti['.$index.'][country]',
                                                            'regione_id' => 'componenti['.$index.'][regione]',
                                                            'provincia_id' => 'componenti['.$index.'][province]',
                                                            'comune_id' => 'componenti['.$index.'][city]',
                                                            'cap' => 'componenti['.$index.'][cap]',
                                                            'regione_text' => 'componenti['.$index.'][regione]',
                                                            'provincia_text' => 'componenti['.$index.'][province]',
                                                            'citta_text' => 'componenti['.$index.'][city]',
                                                            'cap_text' => 'componenti['.$index.'][cap]',
                                                            'manual_flag' => 'componenti['.$index.'][geo_res_manual]',
                                                        ]"
                                                        :value="[
                                                            'nazione_text' => $rowVal('country'),
                                                            'regione_text' => $rowVal('regione'),
                                                            'provincia_text' => $rowVal('province'),
                                                            'comune_text' => $cityGeoLabel !== '' ? $cityGeoLabel : $rowVal('city'),
                                                            'cap' => $rowVal('cap'),
                                                            'cap_text' => $rowVal('cap'),
                                                            'manual' => (bool) $rowVal('geo_res_manual', false),
                                                        ]"
                                                    />

                                                    <div class="card border-0 bg-body mt-3 mb-0 shadow-sm">
                                                        <div class="card-header border-0 bg-light-subtle py-2">
                                                            <h6 class="mb-0">Indirizzo residenza</h6>
                                                        </div>
                                                        <div class="card-body pt-2">
                                                            <div class="row g-3">
                                                                <div class="col-lg-3">
                                                                    <label class="form-label">Tipo Via</label>
                                                                    <x-ui.select name="componenti[{{ $index }}][typeaway]">
                                                                        <option value="">Seleziona tipo via</option>
                                                                        @foreach($tipiVia as $typestreet)
                                                                            <option value="{{ $typestreet->name }}" {{ $rowVal('typeaway') === $typestreet->name ? 'selected' : '' }}>{{ $typestreet->name }}</option>
                                                                        @endforeach
                                                                    </x-ui.select>
                                                                </div>
                                                                <div class="col-lg-5">
                                                                    <label class="form-label">Strada</label>
                                                                    <input type="text" class="form-control" name="componenti[{{ $index }}][address]" value="{{ $rowVal('address') }}">
                                                                </div>
                                                                <div class="col-lg-2">
                                                                    <label class="form-label">Num</label>
                                                                    <input type="text" class="form-control" name="componenti[{{ $index }}][number]" value="{{ $rowVal('number') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-componente-row">
                                    <i class="ri-add-line align-bottom me-1"></i>Aggiungi componente
                                </button>
                            </div>
                        </div>
                    </div>
                    @include('schedina.partials.save-actions', ['previous' => 'schedina-step-contact', 'next' => 'schedina-step-tassa', 'showComponentiSave' => true, 'isEdit' => $isEdit])
                </div>

                <div class="tab-pane fade" id="schedina-step-tassa-pane" role="tabpanel" aria-labelledby="schedina-step-tassa">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Tassa di soggiorno</h5>
                                @if(!empty($schedina->id))
                                    <a href="{{ route('schedina.tassa.print', ['id' => $schedina->id]) }}" class="btn btn-outline-secondary btn-sm" target="_blank">Stampa ricevuta costo tassa</a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if(!empty($tassaConfig))
                                <div class="card border-0 bg-light-subtle shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                @if(!empty($strutturaInfo?->logo_citta))
                                                    <img src="{{ asset($strutturaInfo->logo_citta) }}" alt="Logo città" style="max-height:60px;" class="rounded shadow-sm">
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $strutturaInfo->nome_struttura ?? 'Struttura' }}</div>
                                                    <div class="text-muted small">{{ $strutturaInfo->citta ?? '' }}{{ !empty($strutturaInfo->provincia) ? ' (' . $strutturaInfo->provincia . ')' : '' }}</div>
                                                    <div class="text-muted small">{{ $strutturaInfo->localita ?? $strutturaInfo->zona ?? '' }}</div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small">Aliquota</div>
                                                <div class="fw-semibold">{{ number_format((float) str_replace(',', '.', $tassaConfig->tassa_soggiorno ?? 0), 2, ',', '.') }} €</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small">Giorni massimo</div>
                                                <div class="fw-semibold">{{ $tassaConfig->giorni_massimo ?? '—' }}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-muted small">Totale da pagare</div>
                                                <div class="fw-semibold">{{ number_format($tassaDettaglio['totale'] ?? 0, 2, ',', '.') }} €</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted">Aliquota: <strong>{{ $tassaConfig->tassa_soggiorno }} €</strong> — Giorni massimo: <strong>{{ $tassaConfig->giorni_massimo }}</strong></p>
                            @else
                                <div class="alert alert-warning">Configura prima la tassa di soggiorno nella sezione dedicata.</div>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Età</th>
                                            <th>Esente</th>
                                            <th>Motivo</th>
                                            <th>Notti</th>
                                            <th>Notti nel periodo</th>
                                            <th>Notti imponibili</th>
                                            <th>Oltre giorni max</th>
                                            <th>Tassa (€)</th>
                                            <th>Subtotale (€)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tassaDettaglio['righe'] ?? [] as $riga)
                                            <tr>
                                                <td>{{ $riga['nome'] }}</td>
                                                <td>{{ $riga['eta'] ?? '—' }}</td>
                                                <td>{{ $riga['esente'] ? 'Sì' : 'No' }}</td>
                                                <td>{{ $riga['motivo'] ?? '—' }}</td>
                                                <td>{{ $riga['notti_totali'] }}</td>
                                                <td>{{ $riga['notti_periodo'] ?? $riga['notti_totali'] }}</td>
                                                <td>{{ $riga['notti_imponibili'] }}</td>
                                                <td>{{ $riga['notti_oltre_max'] }}</td>
                                                <td>{{ number_format($riga['aliquota'], 2, ',', '.') }}</td>
                                                <td>{{ number_format($riga['subtotale'], 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="9" class="text-end">Totale tassa da pagare</th>
                                            <th>{{ number_format($tassaDettaglio['totale'] ?? 0, 2, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @include('schedina.partials.save-actions', ['previous' => 'schedina-step-comp', 'isEdit' => $isEdit])
                </div>
            </div>
        </form>
    </div>
</div>
