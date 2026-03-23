@extends('layouts.master')

@section('title', 'Storico cliente')

@section('content')
@component('components.breadcrumb')
    @slot('li_1')
        Clienti
    @endslot
    @slot('title')
        Storico cliente
    @endslot
@endcomponent

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light-subtle border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h4 class="card-title mb-1">{{ trim(($customer->surname ?? '') . ' ' . ($customer->name ?? '')) ?: 'Cliente' }}</h4>
                    <div class="text-muted small">
                        {{ $customer->numero_cliente ?: 'Codice non disponibile' }}
                        @if($customer->type_housed)
                            <span class="mx-1">|</span>{{ $customer->type_housed }}
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customer.print', $customer->id) }}" class="btn btn-soft-secondary btn-sm js-customer-print" data-popup-title="Scheda cliente">
                        <i class="ri-printer-line fs-16 align-middle"></i>
                        Stampa scheda
                    </a>
                    <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-soft-primary btn-sm">
                        <i class="ri-edit-line fs-16 align-middle"></i>
                        Modifica cliente
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Soggiorni registrati</div>
                            <div class="fw-semibold fs-5">{{ $storicoSummary['totale_soggiorni'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Componenti storici</div>
                            <div class="fw-semibold fs-5">{{ $storicoSummary['totale_componenti'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Primo arrivo</div>
                            <div class="fw-semibold fs-5">{{ $storicoSummary['primo_arrivo']?->format('d/m/Y') ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Ultimo arrivo</div>
                            <div class="fw-semibold fs-5">{{ $storicoSummary['ultimo_arrivo']?->format('d/m/Y') ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">Storico soggiorni</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Schedina</th>
                                <th>Arrivo</th>
                                <th>Partenza</th>
                                <th>Giorni</th>
                                <th>Componenti</th>
                                <th>Registrata il</th>
                                <th>Circuito</th>
                                <th>Osservazione</th>
                                <th class="text-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storicoSchedine as $schedina)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark-subtle text-dark">{{ $schedina->storico_label }}</span>
                                    </td>
                                    <td>{{ $schedina->arrivo_date?->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $schedina->partenza_date?->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $schedina->giorni_soggiorno ?? '—' }}</td>
                                    <td>{{ $schedina->componenti_totali }}</td>
                                    <td>{{ $schedina->created_at ? $schedina->created_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td>{{ $schedina->circuito ?: '—' }}</td>
                                    <td>{{ $schedina->storico_note !== '' ? $schedina->storico_note : '—' }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('schedina.edit', ['id' => $schedina->id]) }}" class="btn btn-soft-info btn-sm" title="Apri schedina">
                                                <i class="ri-edit-line fs-16 align-middle"></i>
                                            </a>
                                            <a href="{{ route('schedina.tassa.print', ['id' => $schedina->id]) }}" class="btn btn-soft-secondary btn-sm" title="Stampa tassa" target="_blank">
                                                <i class="ri-printer-line fs-16 align-middle"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Nessuna schedina storica collegata a questo cliente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-customer-print').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const href = link.getAttribute('href');
                if (!href) return;

                const width = 980;
                const height = 860;
                const left = Math.max(0, Math.round((window.screen.width - width) / 2));
                const top = Math.max(0, Math.round((window.screen.height - height) / 2));
                const popup = window.open(
                    href,
                    'customer-print-popup',
                    `popup=yes,width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
                );

                if (popup) {
                    popup.focus();
                }
            });
        });
    });
</script>
@endpush
