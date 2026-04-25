    @php
        $effectiveMode = $formModeOverride
            ?? ((($mode ?? 'create') === 'edit' && isset($customer) && $customer) ? 'edit' : 'create');
        $isEdit = in_array($effectiveMode, ['edit', 'import'], true) && isset($customer) && $customer;
        $entity = $isEdit ? $customer : null;
        $cardTitle = $cardTitleOverride ?? ($isEdit ? 'Cliente - modifica' : 'Cliente - aggiungere');
        $formAction = $formActionOverride ?? ($isEdit ? route('customer.update', $entity->id) : route('customer.store'));
        $formMethod = strtoupper($formMethodOverride ?? ($isEdit ? 'PUT' : 'POST'));
        $initialTab = old('active_tab', request('tab', 'steparrow-gen-info'));
        $draftKey = $draftKeyOverride ?? ($isEdit ? ('clienti.edit.' . $entity->id . '.draft.v1') : 'clienti.nuovo.draft.v1');
        $fieldValue = function (string $field, $default = null) use ($entity) {
            return old($field, data_get($entity, $field, $default));
        };
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
    @endphp

    <div class="row">

    
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $cardTitle }}</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <form
                        method="POST"
                        action="{{ $formAction }}"
                        class="form-steps"
                        autocomplete="off"
                        data-draft-key="{{ $draftKey }}"
                        data-mode="{{ $effectiveMode }}"
                    >
                    @csrf 
                        @if($formMethod !== 'POST')
                            @method($formMethod)
                        @endif
                        <input type="hidden" name="save_mode_intent" id="customer_save_mode_intent" value="{{ old('save_mode_intent', '') }}">
                        <input type="hidden" name="country_reg_fallback" id="country_reg_fallback" value="{{ $fieldValue('country_reg_fallback', data_get($entity, 'country_reg')) }}">
                        <input type="hidden" name="active_tab" id="customer_active_tab" value="{{ $initialTab }}">
                        <div class="text-center pt-3 pb-4 mb-1 d-flex justify-content-center">
                        </div>
                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-gen-info" type="button" role="tab" aria-controls="steparrow-gen-info" aria-selected="true">
                                        Cliente Residenza
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button" role="tab" aria-controls="steparrow-description-info" aria-selected="false">
                                        Anagrafica
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-azienda-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-azienda-info" type="button" role="tab" aria-controls="steparrow-azienda-info" aria-selected="false">
                                        Azienda
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-contact-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-contact-info" type="button" role="tab" aria-controls="steparrow-contact-info" aria-selected="false">
                                        Contatti
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content">
                            <!-- Cliente Residenza -->
                            <div class="tab-pane fade show active" id="steparrow-gen-info" role="tabpanel" aria-labelledby="steparrow-gen-info-tab">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="card mb-0 border-0 shadow-sm">
                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                <i class="ri-user-3-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Dati cliente</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3 align-items-end" data-ui="customer-group-cascade">
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Tipo Cliente</label>
                                                        <x-ui.select name="type_cliente" id="customer-type-cliente" required>
                                                            <option value="">Seleziona tipo cliente</option>
                                                            @foreach($tipiClienti as $tipoCliente)
                                                                <option value="{{ $tipoCliente->descrizione }}" @selected($fieldValue('type_cliente', $fieldValue('type_housed')) === $tipoCliente->descrizione)>
                                                                    {{ $tipoCliente->descrizione }}
                                                                </option>
                                                            @endforeach
                                                        </x-ui.select>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Gruppo I</label>
                                                        <x-ui.select name="group" id="customer-group" data-role="customer-group-l1" data-no-select2="1">
                                                            <option value="">Seleziona gruppo</option>
                                                            @foreach($gruppiLivello1 as $gruppo)
                                                                <option value="{{ $gruppo->nome }}"
                                                                    data-group-id="{{ $gruppo->id }}"
                                                                    @selected($fieldValue('group') === $gruppo->nome)>
                                                                    {{ $gruppo->nome }}
                                                                </option>
                                                            @endforeach
                                                        </x-ui.select>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Gruppo II</label>
                                                        <x-ui.select name="subgroup" id="customer-subgroup" data-role="customer-group-l2" data-no-select2="1">
                                                            <option value="">Seleziona sottogruppo</option>
                                                            @foreach($gruppiLivello2 as $gruppo)
                                                                <option value="{{ $gruppo->nome }}"
                                                                    data-group-id="{{ $gruppo->id }}"
                                                                    data-parent-id="{{ $gruppo->parent_id }}"
                                                                    @selected($fieldValue('subgroup') === $gruppo->nome)>
                                                                    {{ $gruppo->nome }}
                                                                </option>
                                                            @endforeach
                                                        </x-ui.select>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Gruppo III</label>
                                                        <x-ui.select name="subgroup1" id="customer-subgroup1" data-role="customer-group-l3" data-no-select2="1">
                                                            <option value="">Seleziona sottogruppo</option>
                                                            @foreach($gruppiLivello3 as $gruppo)
                                                                <option value="{{ $gruppo->nome }}"
                                                                    data-parent-id="{{ $gruppo->parent_id }}"
                                                                    @selected($fieldValue('subgroup1') === $gruppo->nome)>
                                                                    {{ $gruppo->nome }}
                                                                </option>
                                                            @endforeach
                                                        </x-ui.select>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mt-1 align-items-end">
                                                    <div class="col-lg-2">
                                                        <label class="form-label">Titolo</label>
                                                        <x-ui.select name="type">
                                                            <option value="">Seleziona titolo</option>
                                                            @foreach($titoli as $titolo)
                                                                <option value="{{ $titolo->nome }}" @selected($fieldValue('type') === $titolo->nome)>
                                                                    {{ $titolo->nome }}
                                                                </option>
                                                            @endforeach
                                                        </x-ui.select>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <label class="form-label">Nome</label>
                                                        <input type="text" class="form-control customer-text-only" name="name" value="{{ $fieldValue('name') }}" inputmode="text" autocomplete="given-name">
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <label class="form-label">Cognome</label>
                                                        <input type="text" class="form-control customer-text-only" name="surname" value="{{ $fieldValue('surname') }}" inputmode="text" autocomplete="family-name">
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <label class="form-label">Sesso</label>
                                                        <x-ui.select name="sex">
                                                            <option value="">Seleziona sesso</option>
                                                            <option value="M" @selected($fieldValue('sex') === 'M')>M</option>
                                                            <option value="F" @selected($fieldValue('sex') === 'F')>F</option>
                                                        </x-ui.select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <x-geo.italia
                                            title="Geo residenza"
                                            :value="[
                                                'nazione_text' => $fieldValue('country'),
                                                'regione_text' => $fieldValue('region'),
                                                'provincia_text' => $fieldValue('province'),
                                                'comune_text' => $fieldValue('city'),
                                                'cap' => $fieldValue('cap'),
                                                'cap_text' => $fieldValue('cap'),
                                                'manual' => (bool) $fieldValue('geo_manual', false),
                                            ]"
                                            :names="[
                                                'nazione_id' => 'country',
                                                'regione_id' => 'region',
                                                'provincia_id' => 'province',
                                                'comune_id' => 'city',
                                                'cap' => 'cap',
                                                'regione_text' => 'region',
                                                'provincia_text' => 'province',
                                                'citta_text' => 'city',
                                                'cap_text' => 'cap',
                                                'manual_flag' => 'geo_manual',
                                            ]"
                                        />
                                    </div>

                                    <div class="col-12">
                                        <div class="card mb-0 border-0 shadow-sm">
                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                <i class="ri-road-map-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Indirizzo residenza</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">Tipo via</label>
                                                            <x-ui.select name="typeaway">
                                                                @foreach($tipiVia as $typestreet)
                                                                    <option value="{{ $typestreet->name }}" @selected($fieldValue('typeaway') === $typestreet->name)>{{ $typestreet->name }}</option>
                                                                @endforeach
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Indirizzo</label>
                                                            <input type="text" class="form-control" name="address" value="{{ $fieldValue('address') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">Numero civico</label>
                                                            <input type="text" class="form-control" name="number" value="{{ $fieldValue('number') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start justify-content-end gap-3 mt-4">
                                    @if(($submitLayout ?? 'customer') === 'import')
                                        <button type="submit" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            {{ $primarySubmitLabel ?? 'Aggiorna riga importazione' }}
                                        </button>
                                    @else
                                        <button type="submit" name="save_mode" value="draft" formnovalidate class="btn btn-outline-primary btn-label">
                                            <i class="ri-save-line label-icon align-middle fs-16 me-2"></i>
                                            Salva a bozza
                                        </button>
                                        <button type="submit" name="save_mode" value="final" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a cliente
                                        </button>
                                        <button type="submit" name="save_mode" value="to_schedina" class="btn btn-info btn-label right">
                                            <i class="ri-file-transfer-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a schedina
                                        </button>
                                    @endif
                                    @unless($isEdit)
                                    <button type="button" class="btn btn-soft-success btn-label right nexttab" data-nexttab="steparrow-description-info">
                                        <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                        Anagrafica
                                    </button>
                                    @endunless
                                </div>
                            </div>

                            <!-- Anagrafica -->
                            <div class="tab-pane fade" id="steparrow-description-info" role="tabpanel" aria-labelledby="steparrow-description-info-tab">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="card mb-0 border-0 shadow-sm">
                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                <i class="ri-id-card-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Dati anagrafici</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-4">
                                                    <div class="col-12">
                                                        <x-geo.italia
                                                            title="Geo anagrafica"
                                                            icon="ri-map-pin-user-line"
                                                            prefix="birth"
                                                            :names="[
                                                                'nazione_id' => 'country_reg',
                                                                'regione_id' => 'region_reg',
                                                                'provincia_id' => 'prov_reg',
                                                                'comune_id' => 'city_reg',
                                                                'cap' => 'cap_reg',
                                                                'regione_text' => 'region_reg',
                                                                'provincia_text' => 'prov_reg',
                                                                'citta_text' => 'city_reg',
                                                                'cap_text' => 'cap_reg',
                                                                'manual_flag' => 'geo_manual_reg',
                                                            ]"
                                                            :value="[
                                                                'nazione_text' => $fieldValue('country_reg'),
                                                                'regione_text' => $fieldValue('region_reg'),
                                                                'provincia_text' => $fieldValue('prov_reg'),
                                                                'comune_text' => $fieldValue('city_reg'),
                                                                'cap' => $fieldValue('cap_reg'),
                                                                'cap_text' => $fieldValue('cap_reg'),
                                                                'manual' => (bool) $fieldValue('geo_manual_reg', false),
                                                            ]"
                                                        />
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="card mb-0 border-0 shadow-sm">
                                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                                <i class="ri-user-star-line me-2 text-primary"></i>
                                                                <h6 class="card-title mb-0">Cittadinanza e nascita</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row g-3 align-items-end">
                                                                    <div class="col-lg-4">
                                                                        <label class="form-label">Cittadinanza</label>
                                                                        <x-ui.select
                                                                            id="customer-ciudadania-reg"
                                                                            name="ciudadania_reg"
                                                                            data-allow-manual="1"
                                                                            placeholder="Seleziona o digita cittadinanza"
                                                                        >
                                                                            <option value="">Seleziona cittadinanza</option>
                                                                            @foreach($cittadinanze as $cittadinanza)
                                                                                <option value="{{ $cittadinanza }}" @selected($fieldValue('ciudadania_reg') === $cittadinanza)>{{ $cittadinanza }}</option>
                                                                            @endforeach
                                                                            @if($fieldValue('ciudadania_reg') && !$cittadinanze->contains($fieldValue('ciudadania_reg')))
                                                                                <option value="{{ $fieldValue('ciudadania_reg') }}" selected>{{ $fieldValue('ciudadania_reg') }}</option>
                                                                            @endif
                                                                        </x-ui.select>
                                                                    </div>
                                                                    <div class="col-lg-4">
                                                                        <label class="form-label">Data di nascita</label>
                                                                        <x-calendario
                                                                            name="nac_reg"
                                                                            variant="birth"
                                                                            :value="$toDateInput($fieldValue('nac_reg'))"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="card mb-0 border-0 shadow-sm">
                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                <i class="ri-passport-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Documento identità</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Tipo documento</label>
                                                            <x-ui.select name="type_doc_reg">
                                                                <option value="">Seleziona tipo documento</option>
                                                                @foreach($tipiDocumento as $tipoDocumento)
                                                                    <option value="{{ $tipoDocumento->name }}" @selected($fieldValue('type_doc_reg') === $tipoDocumento->name)>{{ $tipoDocumento->name }}</option>
                                                                @endforeach
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Documento</label>
                                                            <input type="text" class="form-control" name="num_doc_reg" value="{{ $fieldValue('num_doc_reg') }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Data rilascio</label>
                                                            <x-calendario
                                                                name="date_pub_reg"
                                                                variant="period-start"
                                                                group="doc-date-reg"
                                                                :readonly="true"
                                                                :value="$toDateInput($fieldValue('date_pub_reg'))"
                                                                placeholder="gg/mm/aaaa"
                                                                autocomplete="off"
                                                                autocorrect="off"
                                                                autocapitalize="off"
                                                                spellcheck="false"
                                                                inputmode="none"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Scadenza</label>
                                                            <x-calendario
                                                                name="expire_reg"
                                                                variant="period-end"
                                                                group="doc-date-reg"
                                                                :readonly="true"
                                                                :value="$toDateInput($fieldValue('expire_reg'))"
                                                                placeholder="gg/mm/aaaa"
                                                                autocomplete="off"
                                                                autocorrect="off"
                                                                autocapitalize="off"
                                                                spellcheck="false"
                                                                inputmode="none"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rilasciato da</label>
                                                            <x-ui.select name="rilasciato_reg" placeholder="Seleziona rilasciato da">
                                                                <option value="">Seleziona rilasciato da</option>
                                                                @foreach($rilasciatoDa as $rilascio)
                                                                    <option value="{{ $rilascio->name }}" @selected($fieldValue('rilasciato_reg') === $rilascio->name)>
                                                                        {{ $rilascio->name }}
                                                                    </option>
                                                                @endforeach
                                                                @if($fieldValue('rilasciato_reg') && !$rilasciatoDa->pluck('name')->contains($fieldValue('rilasciato_reg')))
                                                                    <option value="{{ $fieldValue('rilasciato_reg') }}" selected>{{ $fieldValue('rilasciato_reg') }}</option>
                                                                @endif
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Paese rilascio</label>
                                                            <x-ui.select id="customer-published-country" name="country_doc_reg">
                                                                <option value="">Seleziona paese rilascio</option>
                                                                @foreach($geoNazioni as $nation)
                                                                    @php $value = $nation->nome ?? ''; @endphp
                                                                    @if($value !== '')
                                                                        <option value="{{ $value }}" @selected($fieldValue('country_doc_reg') === $value)>{{ $value }}</option>
                                                                    @endif
                                                                @endforeach
                                                                @if($fieldValue('country_doc_reg') && !$geoNazioni->pluck('nome')->contains($fieldValue('country_doc_reg')))
                                                                    <option value="{{ $fieldValue('country_doc_reg') }}" selected>{{ $fieldValue('country_doc_reg') }}</option>
                                                                @endif
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3" id="customer-published-city-wrap">
                                                        <div class="mb-3">
                                                            <label class="form-label">Città rilascio</label>
                                                            <x-ui.select id="customer-published-city" name="city_doc_reg" placeholder="Seleziona città">
                                                                <option value="">Seleziona città</option>
                                                                @foreach($ciudades as $ciudad)
                                                                    @php $cityValue = is_array($ciudad) ? ($ciudad['nome'] ?? '') : ($ciudad->nome ?? ''); @endphp
                                                                    @if($cityValue !== '')
                                                                        <option value="{{ $cityValue }}" @selected($fieldValue('city_doc_reg') === $cityValue)>{{ $cityValue }}</option>
                                                                    @endif
                                                                @endforeach
                                                                @if($fieldValue('city_doc_reg') && !$ciudades->pluck('nome')->contains($fieldValue('city_doc_reg')))
                                                                    <option value="{{ $fieldValue('city_doc_reg') }}" selected>{{ $fieldValue('city_doc_reg') }}</option>
                                                                @endif
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="card mb-0 border-0 shadow-sm">
                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                <i class="ri-sticky-note-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Osservazione anagrafica</h5>
                                            </div>
                                            <div class="card-body">
                                                <textarea class="form-control" name="observation_reg" rows="2">{{ $fieldValue('observation_reg') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start justify-content-end gap-3 mt-4">
                                    @if(($submitLayout ?? 'customer') === 'import')
                                        <button type="submit" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            {{ $primarySubmitLabel ?? 'Aggiorna riga importazione' }}
                                        </button>
                                    @else
                                        <button type="submit" name="save_mode" value="draft" formnovalidate class="btn btn-outline-primary btn-label">
                                            <i class="ri-save-line label-icon align-middle fs-16 me-2"></i>
                                            Salva a bozza
                                        </button>
                                        <button type="submit" name="save_mode" value="final" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a cliente
                                        </button>
                                        <button type="submit" name="save_mode" value="to_schedina" class="btn btn-info btn-label right">
                                            <i class="ri-file-transfer-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a schedina
                                        </button>
                                    @endif
                                    @unless($isEdit)
                                    <button type="button" class="btn btn-soft-success btn-label right nexttab" data-nexttab="steparrow-azienda-info">
                                        <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                        Azienda
                                    </button>
                                    @endunless
                                </div>
                            </div>

                            <!-- Azienda -->
                            <div class="tab-pane fade" id="steparrow-azienda-info" role="tabpanel" aria-labelledby="steparrow-azienda-info-tab">
                                @php
                                    $hasAziendaDefault = ($fieldValue('azienda') || $fieldValue('cf_az') || $fieldValue('pi_az') || $fieldValue('email_az')) ? '1' : '0';
                                    $hasAzienda = old('has_azienda', $hasAziendaDefault);
                                @endphp
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="card mb-0 border-0 shadow-sm">
                                            <div class="card-header border-0 bg-light-subtle d-flex align-items-center">
                                                <i class="ri-building-4-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Dati aziendali</h5>
                                            </div>
                                            <div class="card-body" data-azienda-scope>
                                                <div class="row g-3 mb-2">
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Ha azienda?</label>
                                                        <input type="hidden" name="has_azienda" id="has_azienda_hidden" value="0">
                                                        <div class="form-check form-switch form-switch-md mt-1">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                role="switch"
                                                                id="has_azienda_switch"
                                                                name="has_azienda"
                                                                value="1"
                                                                @checked($hasAzienda === '1')
                                                            >
                                                            <label class="form-check-label ms-2" for="has_azienda_switch">Sì, ha azienda</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Azienda</label>
                                                        <input type="text" class="form-control" name="azienda" value="{{ $fieldValue('azienda') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">C.F.</label>
                                                        <input type="text" class="form-control" name="cf_az" value="{{ $fieldValue('cf_az') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">P.I.</label>
                                                        <input type="text" class="form-control" name="pi_az" value="{{ $fieldValue('pi_az') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">SDI</label>
                                                        <input type="text" class="form-control" name="sdi_az" value="{{ $fieldValue('sdi_az') }}" data-azienda-field maxlength="7">
                                                    </div>
                                                </div>
                                                <div class="row g-3 mt-2">
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Telefono</label>
                                                        <input type="text" class="form-control phone" name="phone_az" value="{{ $fieldValue('phone_az') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Celular</label>
                                                        <input type="text" class="form-control phone" name="cellphone_az" value="{{ $fieldValue('cellphone_az') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="text" class="form-control email" name="email_az" value="{{ $fieldValue('email_az') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Fax</label>
                                                        <input type="text" class="form-control phone" name="fax_az" value="{{ $fieldValue('fax_az') }}" data-azienda-field>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mt-2">
                                                    <div class="col-lg-6">
                                                        <label class="form-label">Sito web</label>
                                                        <input type="url" class="form-control" name="website" value="{{ $fieldValue('website') }}" data-azienda-field>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <label class="form-label">Descrizione</label>
                                                        <textarea class="form-control" name="desc_az" rows="2" data-azienda-field>{{ $fieldValue('desc_az') }}</textarea>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Nota</label>
                                                        <textarea class="form-control" name="nota" rows="2" data-azienda-field>{{ $fieldValue('nota') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12" data-azienda-scope>
                                        <x-geo.italia
                                            title="Geo azienda"
                                            prefix="az"
                                            :value="[
                                                'nazione_text' => $fieldValue('country_az'),
                                                'regione_text' => $fieldValue('region_az'),
                                                'provincia_text' => $fieldValue('province_az'),
                                                'comune_text' => $fieldValue('city_az'),
                                                'cap' => $fieldValue('cap_az'),
                                                'cap_text' => $fieldValue('cap_az'),
                                                'manual' => (bool) $fieldValue('geo_manual_az', false),
                                            ]"
                                            :names="[
                                                'nazione_id' => 'country_az',
                                                'regione_id' => 'region_az',
                                                'provincia_id' => 'province_az',
                                                'comune_id' => 'city_az',
                                                'cap' => 'cap_az',
                                                'regione_text' => 'region_az',
                                                'provincia_text' => 'province_az',
                                                'citta_text' => 'city_az',
                                                'cap_text' => 'cap_az',
                                                'manual_flag' => 'geo_manual_az',
                                            ]"
                                        />
                                    </div>
                                </div>
                                <div class="d-flex align-items-start justify-content-end gap-3 mt-4">
                                    @if(($submitLayout ?? 'customer') === 'import')
                                        <button type="submit" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            {{ $primarySubmitLabel ?? 'Aggiorna riga importazione' }}
                                        </button>
                                    @else
                                        <button type="submit" name="save_mode" value="draft" formnovalidate class="btn btn-outline-primary btn-label">
                                            <i class="ri-save-line label-icon align-middle fs-16 me-2"></i>
                                            Salva a bozza
                                        </button>
                                        <button type="submit" name="save_mode" value="final" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a cliente
                                        </button>
                                        <button type="submit" name="save_mode" value="to_schedina" class="btn btn-info btn-label right">
                                            <i class="ri-file-transfer-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a schedina
                                        </button>
                                    @endif
                                    @unless($isEdit)
                                    <button type="button" class="btn btn-soft-success btn-label right nexttab" data-nexttab="steparrow-contact-info">
                                        <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                        Contatti
                                    </button>
                                    @endunless
                                </div>
                            </div>

                            <!-- Contatti -->
                            <div class="tab-pane fade" id="steparrow-contact-info" role="tabpanel" aria-labelledby="steparrow-contact-info-tab">
                                <div class="row g-4">
                                    <div class="col-lg-12">
                                        <div class="card border-0 shadow-sm mb-0">
                                            <div class="card-header bg-light-subtle border-0 d-flex align-items-center">
                                                <i class="ri-contacts-book-2-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Contatti cliente</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Telefono</label>
                                                        <input type="text" class="form-control phone" name="phone" value="{{ $fieldValue('phone') }}">
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Celular</label>
                                                        <input type="text" class="form-control phone" name="cellphone" value="{{ $fieldValue('cellphone') }}">
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="text" class="form-control email" name="email" value="{{ $fieldValue('email') }}" required>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Fax</label>
                                                        <input type="text" class="form-control phone" name="fax" value="{{ $fieldValue('fax') }}">
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <label class="form-label">Osservazione</label>
                                                        <textarea class="form-control" name="observation" rows="2">{{ $fieldValue('observation') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="card border-0 shadow-sm mb-0">
                                            <div class="card-header bg-light-subtle border-0 d-flex align-items-center">
                                                <i class="ri-shield-check-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Consensi e privacy</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-lg-4">
                                                        <input type="hidden" name="privacy_consent" value="0">
                                                        <input type="hidden" name="privacy_consent_at" value="{{ old('privacy_consent_at', optional($fieldValue('privacy_consent_at') ? \Carbon\Carbon::parse($fieldValue('privacy_consent_at')) : null)->toDateTimeString()) }}">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                                <div>
                                                                    <div class="fw-semibold">Privacy</div>
                                                                    <div class="text-muted small">Obbligatorio per registrare correttamente il cliente.</div>
                                                                </div>
                                                                <div class="form-check form-switch form-switch-md m-0">
                                                                    <input class="form-check-input js-consent-switch" type="checkbox" role="switch" id="privacy_consent_switch" name="privacy_consent" value="1" @checked((bool) $fieldValue('privacy_consent')) data-timestamp-target="privacy_consent_at">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <input type="hidden" name="marketing_consent" value="0">
                                                        <input type="hidden" name="marketing_consent_at" value="{{ old('marketing_consent_at', optional($fieldValue('marketing_consent_at') ? \Carbon\Carbon::parse($fieldValue('marketing_consent_at')) : null)->toDateTimeString()) }}">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                                <div>
                                                                    <div class="fw-semibold">Marketing</div>
                                                                    <div class="text-muted small">Consente export e contatti promozionali.</div>
                                                                </div>
                                                                <div class="form-check form-switch form-switch-md m-0">
                                                                    <input class="form-check-input js-consent-switch" type="checkbox" role="switch" id="marketing_consent_switch" name="marketing_consent" value="1" @checked((bool) $fieldValue('marketing_consent')) data-timestamp-target="marketing_consent_at">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <input type="hidden" name="communication_consent" value="0">
                                                        <input type="hidden" name="communication_consent_at" value="{{ old('communication_consent_at', optional($fieldValue('communication_consent_at') ? \Carbon\Carbon::parse($fieldValue('communication_consent_at')) : null)->toDateTimeString()) }}">
                                                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                                <div>
                                                                    <div class="fw-semibold">Comunicazioni</div>
                                                                    <div class="text-muted small">Consente contatti informativi via email o WhatsApp.</div>
                                                                </div>
                                                                <div class="form-check form-switch form-switch-md m-0">
                                                                    <input class="form-check-input js-consent-switch" type="checkbox" role="switch" id="communication_consent_switch" name="communication_consent" value="1" @checked((bool) $fieldValue('communication_consent')) data-timestamp-target="communication_consent_at">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="card border-0 shadow-sm mb-0" data-azienda-scope>
                                            <div class="card-header bg-light-subtle border-0 d-flex align-items-center">
                                                <i class="ri-building-line me-2 text-primary"></i>
                                                <h5 class="card-title mb-0">Contatti azienda</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="text" class="form-control email" name="email_az" value="{{ $fieldValue('email_az') }}">
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Telefono</label>
                                                        <input type="text" class="form-control phone" name="phone_az" value="{{ $fieldValue('phone_az') }}">
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Fax</label>
                                                        <input type="text" class="form-control phone" name="fax_az" value="{{ $fieldValue('fax_az') }}">
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <label class="form-label">Celular</label>
                                                        <input type="text" class="form-control phone" name="cellphone_az" value="{{ $fieldValue('cellphone_az') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start justify-content-end gap-3 mt-4">
                                    @if(($submitLayout ?? 'customer') === 'import')
                                        <button type="submit" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            {{ $primarySubmitLabel ?? 'Aggiorna riga importazione' }}
                                        </button>
                                    @else
                                        <button type="submit" name="save_mode" value="draft" formnovalidate class="btn btn-outline-primary btn-label">
                                            <i class="ri-save-line label-icon align-middle fs-16 me-2"></i>
                                            Salva a bozza
                                        </button>
                                        <button type="submit" name="save_mode" value="final" class="btn btn-success btn-label right">
                                            <i class="ri-check-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a cliente
                                        </button>
                                        <button type="submit" name="save_mode" value="to_schedina" class="btn btn-info btn-label right">
                                            <i class="ri-file-transfer-line label-icon align-middle fs-16 ms-2"></i>
                                            Salva a schedina
                                        </button>
                                    @endif
                                </div>
                            </div>
                            </div>
                            <!-- end tab content -->
                        </div>
                        <!-- end step arrow nav -->
                    </form>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
    </div><!-- end row -->
