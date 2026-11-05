<?php

return [
    'enabled' => env('ISTAT_WS_ENABLED', false),
    'endpoint' => 'https://datiturismo.regione.emilia-romagna.it/ws/checkinV2',
    // Limite tecnico delle copie di lavoro; non termine legale di conservazione.
    'payload_days' => 30,
];
