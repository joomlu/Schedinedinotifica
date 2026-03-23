@props([
    'tipologieGenerali' => collect(),
    'tipologieStruttura' => collect(),
    'classificazioni' => collect(),
    'entity' => null,
    'generaleName' => 'tipologia_generale_id',
    'strutturaName' => 'tipologia_struttura_id',
    'classificazioneName' => 'classificazione_id',
    'generaleId' => 'tipologia_generale_id',
    'strutturaId' => 'tipologia_struttura_id',
    'classificazioneId' => 'classificazione_id',
    'requiredClassificazione' => false,
])

<div
    data-ui="tipologie-filtro"
    data-tipologie-lock="strict-v1"
>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Tipologia generale *</label>
            <x-ui.select id="{{ $generaleId }}" name="{{ $generaleName }}" required data-role="tipologia-generale">
                <option value="">Seleziona tipologia generale</option>
                @foreach($tipologieGenerali as $gen)
                    <option
                        value="{{ $gen->id }}"
                        @selected(old($generaleName, optional($entity)->tipologia_generale_id) == $gen->id)
                    >
                        {{ $gen->nome }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Tipologia struttura *</label>
            <x-ui.select id="{{ $strutturaId }}" name="{{ $strutturaName }}" required data-role="tipologia-struttura">
                <option value="">Seleziona tipologia struttura</option>
                @foreach($tipologieStruttura as $ts)
                    <option
                        value="{{ $ts->id }}"
                        data-generale="{{ $ts->tipologia_generale_id }}"
                        @selected(old($strutturaName, optional($entity)->tipologia_struttura_id) == $ts->id)
                    >
                        {{ $ts->nome }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="col-md-4">
            <label class="form-label">
                Classificazione{{ $requiredClassificazione ? ' *' : '' }}
            </label>
            @if($requiredClassificazione)
                <x-ui.select id="{{ $classificazioneId }}" name="{{ $classificazioneName }}" required data-role="tipologia-classificazione">
                    <option value="">Seleziona classificazione</option>
                    @foreach($classificazioni as $cl)
                        <option
                            value="{{ $cl->id }}"
                            data-tipologie="{{ $cl->tipologieStruttura->pluck('id')->implode(',') }}"
                            @selected(old($classificazioneName, optional($entity)->classificazione_id) == $cl->id)
                        >
                            {{ $cl->nome }}
                        </option>
                    @endforeach
                </x-ui.select>
            @else
                <x-ui.select id="{{ $classificazioneId }}" name="{{ $classificazioneName }}" data-role="tipologia-classificazione">
                    <option value="">Seleziona classificazione</option>
                    @foreach($classificazioni as $cl)
                        <option
                            value="{{ $cl->id }}"
                            data-tipologie="{{ $cl->tipologieStruttura->pluck('id')->implode(',') }}"
                            @selected(old($classificazioneName, optional($entity)->classificazione_id) == $cl->id)
                        >
                            {{ $cl->nome }}
                        </option>
                    @endforeach
                </x-ui.select>
            @endif
        </div>
    </div>

    <p class="text-muted small mb-0">
        Selezione a cascata: la classificazione proposta dipende dalla tipologia struttura scelta.
    </p>
</div>
