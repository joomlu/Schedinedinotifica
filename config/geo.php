<?php

return [
    // Prefijo oficial para los endpoints GeoItalia.
    'endpoint_prefix' => '/geo',

    // Prefijo opcional de fallback con el mismo contrato. Mantener sincronizado o null para desactivar.
    'fallback_prefix' => '/api/geo',

    // Tablas obligatorias que deben estar pobladas para un funcionamiento consistente.
    'required_tables' => [
        'geo_nazioni',
        'geo_regioni',
        'geo_province',
        'geo_comuni',
        'geo_cap',
        'geo_comuni_cap',
    ],

    // No permitir endpoints alternativos ni reconfiguración desde vistas.
    'allow_custom_endpoints' => false,
];
