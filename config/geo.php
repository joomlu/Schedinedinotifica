<?php

return [
    // 'json' or 'db' - set GEO_SOURCE=db in .env to enable DB-backed geo repository
    'source' => env('GEO_SOURCE', 'json'),

    // Table and column mapping for DB source
    'tables' => [
        'stati' => [
            'table' => 'stati',
            'code' => 'sigla',  // IT, FR, ES, etc.
            'name' => 'denominazione',
            'codice_questura' => 'codice_questura',
            'codice_istat' => 'codice_istat',
            'cittadinanza' => 'cittadinanza',
        ],
        'regions' => [
            'table' => 'regioni',
            'code' => 'codice_regione',
            'name' => 'denominazione',
            'stato_fk' => 'stato_id',  // Changed from country_id
            'stato_code' => 'sigla',   // Changed from sigla_nazione
        ],
        'provinces' => [
            'table' => 'province',
            'code' => 'sigla_provincia',
            'name' => 'denominazione',
            'region_fk' => 'region_id',
            'region_code' => 'codice_regione',
        ],
        'comuni' => [
            'table' => 'comuni',
            'code' => 'codice_questura',  // Codice Questura (primary)
            'name' => 'denominazione',
            'province_fk' => 'province_id',
            'province_code' => 'sigla_provincia',
            'region_fk' => 'region_id',
            'stato_fk' => 'stato_id',
        ],
        'cap' => [
            'table' => 'comune_cap',
            'cap' => 'cap',
            'comune_fk' => 'comune_id',
            'comune_code' => 'code',  // Changed from codice_istat to match comuni table
            'province_code' => 'sigla_provincia',
        ],
    ],
];
