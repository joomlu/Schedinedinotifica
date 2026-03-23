@extends('layouts.master')
@section('title', 'Web Check-in')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Schedine
    @endslot
    @slot('title')
        Web Check-in
    @endslot
@endcomponent

@php
    $totali = $totaliWebCheckin ?? [];
    $countDaInviare = $totali['da_inviare'] ?? 0;
    $countCompilazione = $totali['in_compilazione'] ?? 0;
    $countCompilato = $totali['compilato'] ?? 0;
    $countConvertito = $totali['convertito'] ?? 0;
    $formatShortDate = function ($value) {
        if (!$value) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('j/n/y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };
    $statiDisponibili = [
        '' => 'Tutti gli stati',
        'da_inviare' => 'Da inviare',
        'in_compilazione' => 'In compilazione',
        'compilato' => 'Compilato',
        'convertito' => 'Convertito',
    ];
@endphp

<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3">
                <div class="text-muted small">Da inviare</div>
                <div class="fs-4">{{ $countDaInviare }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3">
                <div class="text-muted small">In compilazione</div>
                <div class="fs-4">{{ $countCompilazione }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3">
                <div class="text-muted small">Compilati</div>
                <div class="fs-4">{{ $countCompilato }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3">
                <div class="text-muted small">Convertiti nel circuito operativo</div>
                <div class="fs-4">{{ $countConvertito }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('schedina.web') }}" class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-5">
                        <label class="form-label mb-1">Filtro stato</label>
                        <x-ui.select name="stato">
                            @foreach($statiDisponibili as $value => $label)
                                <option value="{{ $value }}" @selected(($statoFiltro ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="col-xl-4 col-md-7">
                        <label class="form-label mb-1">Ricerca rapida</label>
                        <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Codice, prenotazione, referente, email...">
                    </div>
                    <div class="col-xl-5">
                        <div class="d-flex justify-content-xl-end gap-2">
                            <a href="{{ route('schedina.web') }}" class="btn btn-light">Reset</a>
                            <button type="submit" class="btn btn-primary">Applica filtri</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <x-crud-table
            title="Web Check-in"
            subtitle="Richieste cliente, compilazione online e importazione nel circuito arrivi"
            searchPlaceholder="Cerca per codice, referente, arrivo, partenza, persone o stato..."
            searchId="webCheckinSearch"
            createText="Nuovo Web Check-in"
            :createHref="route('web_checkin.create')"
            createTarget="#"
            tableId="webCheckinTable"
            :paginator="$richieste"
        >
            <x-slot name="columns">
                <tr>
                    <th style="width: 10%;">Codice</th>
                    <th style="width: 13%;">Ref.</th>
                    <th style="width: 12%;">Pren.</th>
                    <th style="width: 8%;">Arr.</th>
                    <th style="width: 8%;">Part.</th>
                    <th style="width: 6%;">Per.</th>
                    <th style="width: 12%;">Contatto</th>
                    <th style="width: 8%;">Stato</th>
                    <th style="width: 8%;">Sched.</th>
                    <th class="text-end" style="width: 15%; min-width: 210px;">Azioni</th>
                </tr>
            </x-slot>

            <x-slot name="rows">
                @foreach($richieste as $richiesta)
                    @php
                        $statoClass = match($richiesta->stato) {
                            'compilato' => 'bg-success-subtle text-success',
                            'convertito' => 'bg-primary-subtle text-primary',
                            'in_compilazione' => 'bg-info-subtle text-info',
                            default => 'bg-warning-subtle text-warning',
                        };
                        $contatto = $richiesta->email;
                        if ($richiesta->whatsapp) {
                            $contatto .= ' · ' . $richiesta->whatsapp;
                        }
                        $publicUrl = route('web_checkin.public.short.show', ['access' => $richiesta->codice . '-' . substr((string) $richiesta->token, 0, 8)]);
                        $mailSubject = 'Benvenuto in ' . ($richiesta->struttura->nome_struttura ?? 'Struttura') . ' | Completa il tuo Web Check-in';
                        $mailBody = "Gentile {$richiesta->nome_referente},\n\n"
                            . 'abbiamo preparato il tuo invito al Web Check-in per ' . ($richiesta->struttura->nome_struttura ?? 'la struttura') . ".\n"
                            . "Apri questa pagina personale per iniziare in modo semplice e guidato:\n\n"
                            . "{$publicUrl}\n\n"
                            . "Troverai il logo della struttura, il riepilogo del soggiorno e il pulsante per aprire il tuo Web Check-in.\n\n"
                            . "Grazie per la collaborazione.\n"
                            . "A presto,\n"
                            . 'Reception ' . ($richiesta->struttura->nome_struttura ?? 'Struttura');
                        $mailToUrl = 'mailto:' . rawurlencode((string) $richiesta->email)
                            . '?subject=' . rawurlencode($mailSubject)
                            . '&body=' . rawurlencode($mailBody);
                        $hasEmail = filled($richiesta->email);
                        $whatsAppPhone = preg_replace('/\D+/', '', (string) ($richiesta->whatsapp ?? ''));
                        $whatsAppBody = "Gentile {$richiesta->nome_referente},\n\n"
                            . 'abbiamo preparato il tuo invito al Web Check-in per ' . ($richiesta->struttura->nome_struttura ?? 'la struttura') . ".\n"
                            . "Apri questa pagina personale per iniziare:\n\n"
                            . $publicUrl
                            . "\n\nGrazie.\n"
                            . 'Reception ' . ($richiesta->struttura->nome_struttura ?? 'Struttura');
                        $whatsAppUrl = $whatsAppPhone !== ''
                            ? 'https://wa.me/' . $whatsAppPhone . '?text=' . rawurlencode($whatsAppBody)
                            : null;
                    @endphp
                    <tr
                        class="data-row"
                        data-codice="{{ $richiesta->codice }}"
                        data-referente="{{ $richiesta->nome_referente }}"
                        data-prenotazione="{{ $richiesta->numero_prenotazione }}"
                        data-contatto="{{ $contatto }}"
                        data-arrivo="{{ $formatShortDate(optional($richiesta->arrivo)->format('Y-m-d')) }}"
                        data-partenza="{{ $formatShortDate(optional($richiesta->partenza)->format('Y-m-d')) }}"
                        data-persone="{{ $richiesta->quantita_persone }}"
                        data-stato="{{ $richiesta->stato }}"
                    >
                        <td class="align-middle text-nowrap"><span class="badge bg-dark-subtle text-dark">{{ $richiesta->codice }}</span></td>
                        <td class="align-middle text-nowrap">{{ $richiesta->nome_referente }}</td>
                        <td class="align-middle text-nowrap">{{ $richiesta->numero_prenotazione }}</td>
                        <td class="align-middle text-nowrap">{{ $formatShortDate(optional($richiesta->arrivo)->format('Y-m-d')) }}</td>
                        <td class="align-middle text-nowrap">{{ $formatShortDate(optional($richiesta->partenza)->format('Y-m-d')) }}</td>
                        <td class="align-middle text-center text-nowrap">{{ $richiesta->quantita_persone }}</td>
                        <td class="align-middle text-nowrap">{{ $contatto }}</td>
                        <td class="align-middle text-nowrap">
                            <div class="d-flex flex-column gap-1">
                                <span class="badge {{ $statoClass }}">{{ str_replace('_', ' ', $richiesta->stato) }}</span>
                                @if($richiesta->schedina && $richiesta->stato !== 'convertito')
                                    <span class="badge bg-success-subtle text-success">Compilata nel web</span>
                                @endif
                            </div>
                        </td>
                        <td class="align-middle text-nowrap">{{ $richiesta->schedina->scheda ?? '-' }}</td>
                        <td class="text-end align-middle">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('web_checkin.edit', ['id' => $richiesta->id]) }}" class="btn btn-soft-info btn-sm" title="Gestione richiesta">
                                    <i class="ri-settings-3-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ $publicUrl }}" class="btn btn-soft-primary btn-sm" title="Apri pagina cliente" target="_blank">
                                    <i class="ri-links-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ $hasEmail ? $mailToUrl : '#' }}" class="btn btn-soft-success btn-sm js-webcheckin-email" title="Apri email nel programma di posta" data-has-email="{{ $hasEmail ? '1' : '0' }}">
                                    <i class="ri-mail-send-line fs-16 align-middle"></i>
                                </a>
                                <a href="{{ $whatsAppUrl ?: '#' }}" class="btn btn-soft-success btn-sm js-webcheckin-whatsapp" title="Apri WhatsApp esterno" target="_blank" rel="noopener" data-has-whatsapp="{{ $whatsAppUrl ? '1' : '0' }}">
                                    <i class="ri-whatsapp-line fs-16 align-middle"></i>
                                </a>
                                <button type="button" class="btn btn-soft-dark btn-sm js-copy-webcheckin-link" data-link="{{ $publicUrl }}" title="Copia link breve">
                                    <i class="ri-file-copy-line fs-16 align-middle"></i>
                                </button>
                                @if($richiesta->schedina)
                                    <a href="{{ route('schedina.edit', ['id' => $richiesta->schedina->id]) }}" class="btn btn-soft-secondary btn-sm" title="Apri schedina web">
                                        <i class="ri-file-list-3-line fs-16 align-middle"></i>
                                    </a>
                                @endif
                                <form action="{{ route('web_checkin.destroy', ['id' => $richiesta->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-sm" title="Elimina">
                                        <i class="ri-delete-bin-line fs-16 align-middle"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot>
        </x-crud-table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const showMissingDataAlert = function (text) {
            if (window.Swal) {
                window.Swal.fire({
                    title: 'Dato mancante',
                    text: text,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            window.alert(text);
        };

        document.querySelectorAll('.js-webcheckin-email').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (button.dataset.hasEmail === '1') return;
                event.preventDefault();
                showMissingDataAlert('Questo indirizzo email non risulta caricato nel sistema.');
            });
        });

        document.querySelectorAll('.js-webcheckin-whatsapp').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (button.dataset.hasWhatsapp === '1') return;
                event.preventDefault();
                showMissingDataAlert('Questo numero WhatsApp non risulta caricato nel sistema.');
            });
        });

        document.querySelectorAll('.js-copy-webcheckin-link').forEach(function (button) {
            button.addEventListener('click', async function () {
                const link = button.dataset.link || '';
                if (!link) return;
                try {
                    await navigator.clipboard.writeText(link);
                    button.classList.remove('btn-soft-dark');
                    button.classList.add('btn-soft-success');
                    setTimeout(function () {
                        button.classList.remove('btn-soft-success');
                        button.classList.add('btn-soft-dark');
                    }, 1600);
                } catch (error) {
                    window.prompt('Copia il link:', link);
                }
            });
        });
    });
</script>
@endpush
