@props([
    'mode' => 'create',
    'formModeOverride' => null,
    'customer' => null,
    'formActionOverride' => null,
    'formMethodOverride' => null,
    'cardTitleOverride' => null,
    'draftKeyOverride' => null,
    'submitLayout' => 'customer',
    'primarySubmitLabel' => 'Aggiorna riga importazione',
    'tipiClienti' => collect(),
    'gruppiLivello1' => collect(),
    'gruppiLivello2' => collect(),
    'gruppiLivello3' => collect(),
    'titoli' => collect(),
    'tipiVia' => collect(),
    'tipiDocumento' => collect(),
    'nations' => collect(),
    'regions' => collect(),
    'provinces' => collect(),
    'ciudades' => collect(),
    'rilasciatoDa' => collect(),
    'cittadinanze' => collect(),
    'geoNazioni' => collect(),
])

@include('customers.partials.scheda-cliente-form')

@push('scripts')
    @include('customers.partials.scheda-cliente-scripts')
@endpush
