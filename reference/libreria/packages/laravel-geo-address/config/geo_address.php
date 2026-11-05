<?php

return [
    // Se true, registra le rotte di esempio sotto /geo-sample/* per test local
    'enable_sample_routes' => false,

    // Se vuoi cambiare i path degli endpoint usati dal JS (default quelli dell'app host)
    'endpoints' => [
        'nations' => '/nations',
        'regions' => '/regions',
        'provinces_all' => '/provinces-all',
        'cities_by_province' => '/cities-by-province',
        'cap_by_province' => '/cap-by-province',
    ],
];
