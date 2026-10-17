<?php
require __DIR__.'/seed-tre-configurazioni.php';
\App\Models\GeoComune::create(['geo_provincia_id' => \App\Models\GeoComune::firstOrFail()->geo_provincia_id, 'nome' => 'Rimini', 'codice_istat' => '099014']);
