@props([
    'tipologieGenerali' => collect(),
    'tipologieStruttura' => collect(),
    'classificazioni' => collect(),
    'struttura' => null,
])

<x-xx.struttura-tipologia
    :tipologie-generali="$tipologieGenerali"
    :tipologie-struttura="$tipologieStruttura"
    :classificazioni="$classificazioni"
    :entity="$struttura"
/>
