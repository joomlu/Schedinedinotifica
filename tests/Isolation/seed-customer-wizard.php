<?php
require __DIR__.'/seed-tipo-cliente.php';
\App\Models\GeoNazione::firstOrCreate(['nome' => 'Francia'], ['codice_iso2' => 'FR', 'cittadinanza' => 'Francese', 'is_italia' => false]);
$doc = \App\Models\TipoDocumento::firstOrCreate(['codice' => 'PASOR'], ['descrizione' => 'PASSAPORTO']);
$base = ['struttura_id' => $s->id, 'name' => 'Persona', 'surname' => 'Sintetica', 'sex' => 'F', 'country_reg' => 'Francia', 'ciudadania_reg' => 'Francese', 'nac_reg' => '1980-01-01'];
$guest = \App\Models\Customers::forceCreate($base + ['type_housed' => 'Ospite', 'type_doc_reg' => $doc->descrizione, 'num_doc_reg' => 'QA123', 'country_doc_reg' => 'Francia']);
$component = \App\Models\Customers::forceCreate($base + ['type_housed' => 'Componente']);
$legacy = \App\Models\Customers::forceCreate(array_replace($base, ['type_housed' => '', 'ciudadania_reg' => null, 'country_reg' => null]));
file_put_contents(public_path('wizard-fixture-ids.json'), json_encode(['ospite' => $guest->id, 'componente' => $component->id, 'legacy' => $legacy->id]));
