<?php
require __DIR__.'/seed-customer-wizard.php';
$admin = \Tests\Support\GeoLogoFixtures::user('admin');
$owner = \App\Models\Proprietario::create(['admin_id' => $admin->id, 'nome' => 'Proprietario stampa sintetico', 'attivo' => true]);
$s->update(['proprietario_id' => $owner->id]);
$user = \Tests\Support\GeoLogoFixtures::user('proprietario');
$user->update(['proprietario_id' => $owner->id, 'struttura_id' => $s->id]);
$sheet->update(['scheda' => 'S-26004', 'circuito' => 'schedina', 'name' => 'TitolareStampa', 'or_doc' => 'DOCUMENTO-SINTETICO']);
\App\Models\Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $sheet->id, 'name' => 'ComponenteStampa', 'surname' => 'Sintetico', 'comune_nac' => 'Roma', 'city_nac' => 'ITALIANA']);
$sheet->refresh();
file_put_contents(public_path('schedina-print-fixture.json'), json_encode(['codice' => $sheet->scheda, 'schedina' => $sheet->id, 'cliente' => $c->id, 'struttura' => $s->id]));
\Illuminate\Support\Facades\Artisan::call('route:cache');
