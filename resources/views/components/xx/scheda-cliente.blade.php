@props([
    'mode' => 'create',
    'customer' => null,
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
