<?php

require __DIR__.'/struttura-selezione-fixtures.php';

$super = \App\Models\User::factory()->create(['ruolo' => 'super_admin', 'username' => 'p1-super', 'name' => 'Amministratore sintetico P1', 'attivo' => true, 'password' => \Illuminate\Support\Facades\Hash::make('Password-tassa-fixture-123!')]);
$target = \App\Models\User::where('username', 'tassa-anteprima')->firstOrFail();
$ids = json_decode(file_get_contents(public_path('tassa-anteprima-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$web = \App\Models\WebCheckinRichiesta::create(['struttura_id' => $target->struttura_id, 'schedina_id' => $ids['positivo'], 'codice' => 'P1WEB', 'numero_prenotazione' => 'SINTETICA-P1', 'email' => 'p1@example.invalid', 'nome_referente' => 'Persona sintetica', 'arrivo' => '2026-06-10', 'partenza' => '2026-06-13', 'quantita_persone' => 1, 'token' => str_repeat('b', 64), 'stato' => 'convertito']);
file_put_contents(public_path('p1-baseline-ids.json'), json_encode(['super' => $super->id, 'target' => $target->id, 'web' => $web->id, 'token' => $web->token, 'positivo' => $ids['positivo']]));
