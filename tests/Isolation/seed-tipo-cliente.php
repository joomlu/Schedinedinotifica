<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$s = \App\Models\Struttura::forceCreate(['nome_struttura' => 'Hotel Browser', 'tipologia_struttura' => 'Hotel', 'tipo_apertura' => 'Annuale', 'nazione' => 'Italia', 'regione' => 'Lazio', 'provincia' => 'RM', 'citta' => 'Roma', 'indirizzo' => 'Via Test', 'cap' => '00100', 'telefono' => '061111111', 'email' => 'browser@fixture.invalid', 'attiva' => true, 'scadenza_servizio' => now()->addYear()]);
$user = \Tests\Support\GeoLogoFixtures::user('super_admin');
$user->update(['struttura_id' => $s->id]);
$data = $s->getAttributes();
unset($data['id'], $data['created_at'], $data['updated_at'], $data['gruppi_inizializzati_at'], $data['titoli_inizializzati_at']);
$data['nome_struttura'] = 'Hotel Browser B';
$b = \App\Models\Struttura::forceCreate($data);
$other = \Tests\Support\GeoLogoFixtures::user('struttura_user');
$other->update(['struttura_id' => $b->id]);
$c = \App\Models\Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Cliente Browser', 'surname' => 'Sintetico', 'group' => 'Business', 'type' => 'Sig.', 'type_housed' => 'Richiesta', 'privacy_consent' => true]);
$sheet = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'customer_id' => $c->id, 'customer_group' => 'Business', 'type' => 'Sig.', 'name' => 'Capogruppo Browser', 'surname' => 'Sintetico', 'sex' => 'M', 'relationship' => 'CAPO GRUPPO', 'circuito' => 'schedina', 'scheda' => 'S-26001', 'arrive' => '2026-11-10', 'departure' => '2026-11-12', 'cant_people' => 2, 'room' => 1, 'beds' => 2]);
\App\Models\Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $sheet->id, 'name' => 'Componente Browser', 'surname' => 'Sintetico', 'relationship' => 'MEMBRO GRUPPO', 'sex' => 'F', 'date_nac' => '1980-01-01']);
file_put_contents(public_path('tipo-cliente-fixture-ids.json'), json_encode(['cliente' => $c->id, 'schedina' => $sheet->id]));

\Illuminate\Support\Facades\DB::table('tipo_cliente')->insert(['codice' => 'STORICO', 'descrizione' => 'Extra sintetico da conservare', 'attivo' => true]);
