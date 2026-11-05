@props([
    'prefix' => 'geo',
    'value' => [],
    'title' => null,
    'required' => false,
    'disabled' => false,
    'endpointBase' => '/geo',
    'endpointFallback' => '/api/geo',
    'names' => [],
    'showToggleItalia' => true,
    'showNazione' => true,
    'showRegione' => true,
    'showProvincia' => true,
    'showComune' => true,
    'showCap' => true,
    'showIndirizzo' => false,
    'showCittadinanza' => false,
    'labels' => [
        'title' => 'Localizzazione',
        'nazione' => 'Nazione',
        'regione' => 'Regione',
        'provincia' => 'Provincia',
        'comune' => 'Città / Comune',
        'cap' => 'CAP',
        'indirizzo' => 'Indirizzo',
        'cittadinanza' => 'Cittadinanza',
    ],
    'placeholders' => [
        'nazione' => 'Seleziona una nazione',
        'regione' => 'Seleziona una regione',
        'provincia' => 'Seleziona una provincia',
        'comune' => 'Seleziona un comune',
        'cap' => 'Seleziona o digita un CAP',
        'regione_manual' => 'Regione (estero)',
        'provincia_manual' => 'Provincia (estero)',
        'comune_manual' => 'Città (estero)',
        'cap_manual' => 'CAP (estero)',
        'indirizzo' => 'Inserisci indirizzo',
        'cittadinanza' => 'Cittadinanza',
    ],
])
@php
    $defaultNames = [
        'nazione_id' => $prefix.'[nazione_id]',
        'regione_id' => $prefix.'[regione_id]',
        'provincia_id' => $prefix.'[provincia_id]',
        'comune_id' => $prefix.'[comune_id]',
        'cap' => $prefix.'[cap]',
        'regione_text' => $prefix.'[regione_text]',
        'provincia_text' => $prefix.'[provincia_text]',
        'citta_text' => $prefix.'[citta_text]',
        'cap_text' => $prefix.'[cap_text]',
        'manual_flag' => $prefix.'[manual]',
        'indirizzo' => $prefix.'[indirizzo]',
        'cittadinanza' => $prefix.'[cittadinanza]',
    ];
    $names = array_merge($defaultNames, $names ?? []);

    $initial = [
        'nazione_id' => $value['nazione_id'] ?? ($value['nazione'] ?? null),
        'nazione_text' => $value['nazione'] ?? null,
        'regione_id' => $value['regione_id'] ?? null,
        'regione_text' => $value['regione_text'] ?? ($value['regione'] ?? null),
        'provincia_id' => $value['provincia_id'] ?? null,
        'provincia_text' => $value['provincia_text'] ?? ($value['provincia'] ?? null),
        'comune_id' => $value['comune_id'] ?? null,
        'comune_text' => $value['comune_text'] ?? ($value['comune'] ?? ($value['citta'] ?? null)),
        'cap' => $value['cap'] ?? null,
        'cap_text' => $value['cap_text'] ?? ($value['cap'] ?? null),
        'indirizzo' => $value['indirizzo'] ?? null,
        'cittadinanza' => $value['cittadinanza'] ?? null,
        'manual' => (bool) ($value['manual'] ?? false),
    ];

    $cardTitle = $title ?? ($labels['title'] ?? 'Localizzazione');
@endphp

<div
    class="card mb-0 border-0 shadow-sm"
    data-ui="geo-italia"
    data-prefix="{{ $prefix }}"
    data-endpoint-base="{{ $endpointBase }}"
    data-endpoint-fallback="{{ $endpointFallback }}"
    data-initial='@json($initial)'
>
    <div class="card-header border-0 bg-light-subtle d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="ri-map-pin-line me-2 text-primary"></i>
            <h5 class="card-title mb-0">{{ $cardTitle }}</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($showToggleItalia)
                <div class="btn-group" role="group" aria-label="Toggle Italia / Estero">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-action="italia">
                        Italia
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-action="estero">
                        Estero
                    </button>
                </div>
            @endif
            <button type="button" class="btn btn-outline-danger btn-sm" data-action="geo-reset">
                Reset GEO
            </button>
        </div>
    </div>
    <div class="card-body">
        <input type="hidden" name="{{ $names['manual_flag'] }}" value="{{ $initial['manual'] ? 1 : 0 }}" data-role="manual-flag">
        <div class="row g-3 align-items-end">
            <div class="col-md-3{{ $showNazione ? '' : ' d-none' }}">
                <label class="form-label">{{ $labels['nazione'] }}</label>
                <x-ui.select
                    id="{{ $prefix }}_nazione_id"
                    name="{{ $names['nazione_id'] }}"
                    data-role="nazione"
                    data-geo="1"
                    placeholder="{{ $placeholders['nazione'] }}"
                    :required="$required"
                    :disabled="$disabled"
                >
                    <option value=""></option>
                    @if($initial['nazione_id'] || $initial['nazione_text'])
                        <option value="{{ $initial['nazione_id'] ?? $initial['nazione_text'] }}" selected>{{ $initial['nazione_text'] ?? $initial['nazione_id'] }}</option>
                    @else
                        <option value="">{{ $placeholders['nazione'] }}</option>
                    @endif
                </x-ui.select>
                @error($names['nazione_id'])
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3{{ $showRegione ? '' : ' d-none' }}" data-role="italia-block">
                <label class="form-label">{{ $labels['regione'] }}</label>
                <x-ui.select
                    id="{{ $prefix }}_regione_id"
                    name="{{ $names['regione_id'] }}"
                    data-role="regione"
                    data-geo="1"
                    placeholder="{{ $placeholders['regione'] }}"
                    :disabled="$disabled"
                >
                    @if($initial['regione_id'] || $initial['regione_text'])
                        <option value="{{ $initial['regione_id'] ?? $initial['regione_text'] }}" selected>{{ $initial['regione_text'] ?? $initial['regione_id'] }}</option>
                    @else
                        <option value="">{{ $placeholders['regione'] }}</option>
                    @endif
                </x-ui.select>
            </div>

            <div class="col-md-3{{ $showProvincia ? '' : ' d-none' }}" data-role="italia-block">
                <label class="form-label">{{ $labels['provincia'] }}</label>
                <x-ui.select
                    id="{{ $prefix }}_provincia_id"
                    name="{{ $names['provincia_id'] }}"
                    data-role="provincia"
                    data-geo="1"
                    placeholder="{{ $placeholders['provincia'] }}"
                    :disabled="$disabled"
                >
                    @if($initial['provincia_id'] || $initial['provincia_text'])
                        <option value="{{ $initial['provincia_id'] ?? $initial['provincia_text'] }}" selected>{{ $initial['provincia_text'] ?? $initial['provincia_id'] }}</option>
                    @else
                        <option value="">{{ $placeholders['provincia'] }}</option>
                    @endif
                </x-ui.select>
            </div>

            <div class="col-md-3{{ $showComune ? '' : ' d-none' }}" data-role="italia-block">
                <label class="form-label">{{ $labels['comune'] }}</label>
                <x-ui.select
                    id="{{ $prefix }}_comune_id"
                    name="{{ $names['comune_id'] }}"
                    data-role="comune"
                    data-geo="1"
                    placeholder="{{ $placeholders['comune'] }}"
                    :disabled="$disabled"
                >
                    @if($initial['comune_id'] || $initial['comune_text'])
                        <option value="{{ $initial['comune_id'] ?? $initial['comune_text'] }}" selected>{{ $initial['comune_text'] ?? $initial['comune_id'] }}</option>
                    @else
                        <option value="">{{ $placeholders['comune'] }}</option>
                    @endif
                </x-ui.select>
            </div>

            <div class="col-md-3{{ $showCap ? '' : ' d-none' }}" data-role="italia-block">
                <label class="form-label">{{ $labels['cap'] }}</label>
                <x-ui.select
                    id="{{ $prefix }}_cap"
                    name="{{ $names['cap'] }}"
                    data-role="cap"
                    data-geo="1"
                    placeholder="{{ $placeholders['cap'] }}"
                    data-allow-manual="1"
                    :disabled="$disabled"
                >
                    @if($initial['cap'])
                        <option value="{{ $initial['cap'] }}" selected>{{ $initial['cap'] }}</option>
                    @else
                        <option value="">{{ $placeholders['cap'] }}</option>
                    @endif
                </x-ui.select>
            </div>

            @if($showRegione)
            <div class="col-md-3 d-none" data-role="estero-block">
                <label class="form-label">{{ $placeholders['regione_manual'] }}</label>
                <input
                    type="text"
                    class="form-control"
                    name="{{ $names['regione_text'] }}"
                    value="{{ $initial['regione_text'] }}"
                    placeholder="{{ $placeholders['regione_manual'] }}"
                    data-role="manual-regione"
                    @if($disabled) disabled @endif
                >
            </div>
            @endif

            @if($showProvincia)
            <div class="col-md-3 d-none" data-role="estero-block">
                <label class="form-label">{{ $placeholders['provincia_manual'] }}</label>
                <input
                    type="text"
                    class="form-control"
                    name="{{ $names['provincia_text'] }}"
                    value="{{ $initial['provincia_text'] }}"
                    placeholder="{{ $placeholders['provincia_manual'] }}"
                    data-role="manual-provincia"
                    @if($disabled) disabled @endif
                >
            </div>
            @endif

            @if($showComune)
            <div class="col-md-3 d-none" data-role="estero-block">
                <label class="form-label">{{ $placeholders['comune_manual'] }}</label>
                <input
                    type="text"
                    class="form-control"
                    name="{{ $names['citta_text'] }}"
                    value="{{ $initial['comune_text'] }}"
                    placeholder="{{ $placeholders['comune_manual'] }}"
                    data-role="manual-comune"
                    @if($disabled) disabled @endif
                >
            </div>
            @endif

            @if($showCap)
            <div class="col-md-3 d-none" data-role="estero-block">
                <label class="form-label">{{ $placeholders['cap_manual'] }}</label>
                <input
                    type="text"
                    class="form-control"
                    name="{{ $names['cap_text'] }}"
                    value="{{ $initial['cap_text'] }}"
                    placeholder="{{ $placeholders['cap_manual'] }}"
                    data-role="manual-cap"
                    @if($disabled) disabled @endif
                >
            </div>
            @endif

            @if($showIndirizzo)
                <div class="col-md-6">
                    <label class="form-label">{{ $labels['indirizzo'] }}</label>
                    <input
                        type="text"
                        class="form-control"
                        name="{{ $names['indirizzo'] }}"
                        value="{{ old($names['indirizzo'], $initial['indirizzo']) }}"
                        placeholder="{{ $placeholders['indirizzo'] }}"
                        data-role="indirizzo"
                        @if($disabled) disabled @endif
                    >
                </div>
            @else
                <input type="hidden" name="{{ $names['indirizzo'] }}" value="{{ old($names['indirizzo'], $initial['indirizzo']) }}" data-role="indirizzo">
            @endif

            @if($showCittadinanza)
                <div class="col-md-3">
                    <label class="form-label">{{ $labels['cittadinanza'] }}</label>
                    <input
                        type="text"
                        class="form-control"
                        name="{{ $names['cittadinanza'] }}"
                        value="{{ old($names['cittadinanza'], $initial['cittadinanza']) }}"
                        placeholder="{{ $placeholders['cittadinanza'] }}"
                        data-role="cittadinanza"
                        @if($disabled) disabled @endif
                        readonly
                    >
                </div>
            @else
                <input type="hidden" name="{{ $names['cittadinanza'] }}" value="{{ old($names['cittadinanza'], $initial['cittadinanza']) }}" data-role="cittadinanza">
            @endif
        </div>
    </div>
</div>
