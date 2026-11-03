<?php
require __DIR__.'/seed-tipologie-appartamento.php';
$ids=json_decode(file_get_contents(public_path('anagrafiche-fixture.json')), true);
\App\Models\Struttura::findOrFail($ids['struttura'])->update(['regione'=>'Emilia-Romagna']);
