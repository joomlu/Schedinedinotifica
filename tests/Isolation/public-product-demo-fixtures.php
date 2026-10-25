<?php
// Solo dati dimostrativi nel database effimero attestato dal runner ufficiale.
require __DIR__.'/tassa-anteprima-fixtures.php';
$ids = json_decode(file_get_contents(public_path('tassa-anteprima-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$s = \App\Models\Struttura::withoutGlobalScopes()->findOrFail($ids['struttura']);
$s->update(['nome_struttura' => 'Hotel Dimostrativo', 'email' => 'reception@example.invalid']);
$u = \App\Models\User::where('username', 'tassa-anteprima')->firstOrFail();
$u->update(['name' => 'Reception Demo']);
$arrivo = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'arrivi', 'name' => 'Anna', 'surname' => 'Ospite Demo', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(3)->toDateString(), 'cant_people' => 2]);
$parent = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'Marco', 'surname' => 'Ospite Demo', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(3)->toDateString(), 'cant_people' => 1]);
$r = \App\Models\WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $parent->id, 'codice' => 'DEMO-WEB', 'numero_prenotazione' => 'DEMO-2026', 'email' => 'ospite@example.invalid', 'nome_referente' => 'Marco Ospite Demo', 'arrivo' => now()->toDateString(), 'partenza' => now()->addDays(3)->toDateString(), 'quantita_persone' => 1, 'numero_adulti' => 1, 'numero_bambini' => 0, 'token' => \Illuminate\Support\Str::random(64), 'stato' => 'da_inviare']);
app(\App\Services\WebCheckinLink::class)->issue($r);
file_put_contents(public_path('public-product-demo-ids.json'), json_encode(['web' => $r->id, 'short' => $r->short_token, 'schedina' => $ids['positivo'], 'arrivo' => $arrivo->id]));
