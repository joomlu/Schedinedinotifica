@extends('layouts.master')

@section('title', 'Centro assistenza generale')

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Aiuto @endslot
    @slot('title') Centro assistenza generale @endslot
@endcomponent

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-0 bg-light-subtle">
        <div class="row g-3 align-items-end">
            <div class="col-xl-7">
                <h4 class="card-title mb-1">Centro assistenza generale</h4>
                <p class="text-muted mb-0">Questa e la guida operativa normale del sistema. Qui trovi esattamente il centro assistenza generale, separato dalla guida amministrativa di admin e superadmin.</p>
            </div>
            <div class="col-xl-5">
                <label class="form-label">Cerca un argomento</label>
                <div class="search-box">
                    <input type="text" id="helpSearch" class="form-control search" placeholder="Cerca: clienti, schedine, Questura, Tabella A, notifiche, supporto...">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isAdmin())
                        <a href="{{ route('help.index') }}" class="btn btn-light">Torna alla scelta aiuto</a>
                    @endif
                    <a href="{{ route('supporto.index') }}" class="btn btn-warning text-dark">Apri supporto online</a>
                    <a href="{{ route('notifiche.index') }}" class="btn btn-light">Apri notifiche</a>
                    <a href="{{ route('help.print', ['section' => 'general']) }}" target="_blank" class="btn btn-light">Stampa centro assistenza</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="step-arrow-nav mb-4">
            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#help-pane-guida" type="button" role="tab">Guida rapida</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#help-pane-ruoli" type="button" role="tab">Impostazioni e gestione</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#help-pane-moduli" type="button" role="tab">Moduli</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#help-pane-faq" type="button" role="tab">Domande frequenti</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#help-pane-problemi" type="button" role="tab">Problemi comuni</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="help-pane-guida" role="tabpanel">
                @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isAdmin())
                    <div class="card border mb-4">
                        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <h6 class="card-title mb-1">Guida amministrativa separata</h6>
                                <p class="text-muted mb-0">Se vuoi la guida dedicata di admin e superadmin, la trovi separata senza alterare questo centro assistenza generale.</p>
                            </div>
                            <a href="{{ route('help.admin') }}" class="btn btn-primary">Apri guida admin e superadmin</a>
                        </div>
                    </div>
                @endif
                <div class="row g-3">
                    @foreach($guide as $item)
                        <div class="col-xl-6 help-item" data-help-search="{{ strtolower($item['title'].' '.$item['keywords'].' '.$item['summary'].' '.$item['result'].' '.implode(' ', $item['steps'])) }}">
                            <div class="card border h-100 shadow-sm mb-0">
                                <div class="card-header border-0 bg-light-subtle d-flex align-items-center gap-2">
                                    <i class="{{ $item['icon'] }} text-primary fs-20"></i>
                                    <h5 class="card-title mb-0 fs-6">{{ $item['title'] }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">{{ $item['summary'] }}</p>
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-1">Passi da seguire</div>
                                        <ol class="mb-0 ps-3">
                                            @foreach($item['steps'] as $step)
                                                <li class="mb-2">{{ $step }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Risultato</div>
                                        <p class="mb-0">{{ $item['result'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane fade" id="help-pane-ruoli" role="tabpanel">
                <div class="card border mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Indice di impostazioni e gestione</h6>
                        <p class="text-muted mb-3">Questa sezione riguarda solo l uso operativo interno della struttura: proprietario, reception, utenti, profilo, consegne, entrate / uscite e attivita.</p>
                        <div class="d-flex flex-nowrap overflow-auto gap-2 pb-1">
                            @foreach($managementTopics as $topic)
                                <a href="{{ route('help.management', ['slug' => $topic['slug']]) }}" class="btn btn-light">
                                    {{ $topic['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    @foreach($personas as $item)
                        <div class="col-xl-6 help-item" data-help-search="{{ strtolower($item['title'].' '.$item['keywords'].' '.$item['summary'].' '.$item['result'].' '.implode(' ', $item['steps'])) }}">
                            <div class="card border h-100 shadow-sm mb-0">
                                <div class="card-header border-0 bg-light-subtle d-flex align-items-center gap-2">
                                    <i class="{{ $item['icon'] }} text-primary fs-20"></i>
                                    <h5 class="card-title mb-0 fs-6">{{ $item['title'] }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">{{ $item['summary'] }}</p>
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-1">Cosa fai normalmente</div>
                                        <ol class="mb-0 ps-3">
                                            @foreach($item['steps'] as $step)
                                                <li class="mb-2">{{ $step }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Risultato</div>
                                        <p class="mb-0">{{ $item['result'] }}</p>
                                    </div>
                                    @if(!empty($item['details']))
                                        <div class="mt-3">
                                            <div class="fw-semibold mb-1">Punti importanti</div>
                                            <ul class="mb-0 ps-3">
                                                @foreach($item['details'] as $detail)
                                                    <li class="mb-2">{{ $detail }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(!empty($item['field_groups']))
                                        <div class="mt-3">
                                            <div class="fw-semibold mb-2">Argomenti da approfondire</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($item['field_groups'] as $group)
                                                    @php
                                                        $slug = match ($group['title']) {
                                                            'Utenti' => 'utenti',
                                                            'Password e profili', 'Profilo personale' => 'profilo',
                                                            'Consegne', 'Consegne di turno' => 'consegne',
                                                            'Entrate / uscite e attivita' => 'entrate-uscite',
                                                            default => strtolower($item['title']) === 'per proprietario' ? 'proprietario' : 'reception',
                                                        };
                                                    @endphp
                                                    <a href="{{ route('help.management', ['slug' => $slug]) }}" class="btn btn-light btn-sm">{{ $group['title'] }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane fade" id="help-pane-moduli" role="tabpanel">
                <div class="card border mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Indice dei moduli</h6>
                        <p class="text-muted mb-3">Qui trovi i moduli principali del programma nell ordine corretto di lavoro. Apri il modulo che ti serve per leggere la guida completa e stampare solo quel settore.</p>
                        <div class="d-flex flex-nowrap overflow-auto gap-2 pb-1">
                            @foreach($modules as $module)
                                <a href="{{ route('help.module', ['slug' => $module['slug']]) }}" class="btn btn-light">
                                    {{ $module['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    @foreach($modules as $module)
                        <div class="col-xxl-6 help-item" data-help-search="{{ strtolower($module['title'].' '.$module['keywords'].' '.$module['summary'].' '.$module['when'].' '.$module['result'].' '.implode(' ', $module['items'])) }}">
                            <div class="card border h-100 shadow-sm mb-0">
                                <div class="card-header border-0 bg-light-subtle d-flex align-items-center gap-2">
                                    <i class="{{ $module['icon'] }} text-primary fs-20"></i>
                                    <h5 class="card-title mb-0 fs-6">{{ $module['title'] }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">{{ $module['summary'] }}</p>
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-1">Quando lo usi</div>
                                        <p class="mb-0">{{ $module['when'] }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <div class="fw-semibold mb-1">Cosa fai qui</div>
                                        <ul class="mb-0 ps-3">
                                            @foreach($module['items'] as $point)
                                                <li class="mb-2">{{ $point }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">Risultato</div>
                                        <p class="mb-0">{{ $module['result'] }}</p>
                                    </div>
                                    @if(!empty($module['details']))
                                        <div class="mt-3">
                                            <div class="fw-semibold mb-1">Campi e punti importanti</div>
                                            <ul class="mb-0 ps-3">
                                                @foreach($module['details'] as $detail)
                                                    <li class="mb-2">{{ $detail }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(!empty($module['field_groups']))
                                        <div class="mt-3">
                                            <div class="fw-semibold mb-1">Guida dettagliata disponibile</div>
                                            <p class="mb-0 text-muted">Apri il modulo dedicato per leggere tutte le spiegazioni complete di questo argomento.</p>
                                        </div>
                                    @endif
                                    <div class="mt-3 d-flex flex-wrap gap-2">
                                        <a href="{{ route('help.module', ['slug' => $module['slug']]) }}" class="btn btn-primary">Apri guida del modulo</a>
                                        <a href="{{ route('help.print', ['section' => 'modules', 'module' => $module['slug'] ?? null]) }}" target="_blank" class="btn btn-light">Stampa questo modulo</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane fade" id="help-pane-faq" role="tabpanel">
                <div class="card border mb-4">
                    <div class="card-body">
                        <label class="form-label">Cerca nelle domande frequenti</label>
                        <div class="search-box">
                            <input type="text" id="faqSearch" class="form-control search" placeholder="Cerca: schedine, clienti, login, Questura...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box" id="faqAccordion">
                    @foreach($faqs as $index => $faq)
                        <div class="accordion-item help-item faq-item" data-help-search="{{ strtolower($faq['question'].' '.$faq['answer'].' '.$faq['keywords']) }}">
                            <h2 class="accordion-header" id="faqHeading{{ $index }}">
                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $index }}">
                                    {{ $faq['question'] }}
                                </button>
                            </h2>
                            <div id="faqCollapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="faqHeading{{ $index }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane fade" id="help-pane-problemi" role="tabpanel">
                <div class="card border mb-4">
                    <div class="card-body">
                        <label class="form-label">Cerca nei problemi comuni</label>
                        <div class="search-box">
                            <input type="text" id="troubleshootingSearch" class="form-control search" placeholder="Cerca: utente, schedina, XML, Questura...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    @foreach($troubleshooting as $item)
                        <div class="col-xl-6 help-item troubleshooting-item" data-help-search="{{ strtolower($item['title'].' '.$item['problem'].' '.$item['keywords'].' '.implode(' ', $item['solution'])) }}">
                            <div class="card border h-100 shadow-sm mb-0">
                                <div class="card-header border-0 bg-light-subtle">
                                    <h5 class="card-title mb-1 fs-6">{{ $item['title'] }}</h5>
                                    <p class="text-muted mb-0">{{ $item['problem'] }}</p>
                                </div>
                                <div class="card-body">
                                    <ol class="mb-0 ps-3">
                                        @foreach($item['solution'] as $step)
                                            <li class="mb-2">{{ $step }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="helpNoResults" class="alert alert-warning mt-4 d-none">
            Nessun argomento trovato. Prova con parole diverse, per esempio: clienti, schedine, Questura, Tabella A, notifiche, supporto.
        </div>

        <div class="card border mt-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h6 class="card-title mb-1">Stampa del manuale</h6>
                    <p class="text-muted mb-0">Usa la stampa completa quando vuoi consegnare tutto il manuale. Per i moduli singoli usa invece il pulsante dentro al modulo.</p>
                </div>
                <a href="{{ route('help.print', ['section' => 'general']) }}" target="_blank" class="btn btn-primary">Stampa manuale completo</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('helpSearch');
    const faqSearch = document.getElementById('faqSearch');
    const troubleshootingSearch = document.getElementById('troubleshootingSearch');
    const items = Array.from(document.querySelectorAll('.help-item'));
    const faqItems = Array.from(document.querySelectorAll('.faq-item'));
    const troubleshootingItems = Array.from(document.querySelectorAll('.troubleshooting-item'));
    const noResults = document.getElementById('helpNoResults');

    const applyFilter = function (value) {
        const term = (value || '').trim().toLowerCase();
        let visibleCount = 0;

        items.forEach(function (item) {
            const haystack = item.dataset.helpSearch || '';
            const visible = !term || haystack.includes(term);
            item.classList.toggle('d-none', !visible);
            if (visible) {
                visibleCount += 1;
            }
        });

        noResults.classList.toggle('d-none', visibleCount > 0);
    };

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            applyFilter(searchInput.value);
        });
    }

    const applyScopedFilter = function (input, scopedItems) {
        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            const term = input.value.trim().toLowerCase();
            scopedItems.forEach(function (item) {
                const haystack = item.dataset.helpSearch || '';
                const visible = !term || haystack.includes(term);
                item.classList.toggle('d-none', !visible);
            });
        });
    };

    applyScopedFilter(faqSearch, faqItems);
    applyScopedFilter(troubleshootingSearch, troubleshootingItems);
});
</script>
@endpush
