<?php

require __DIR__.'/p1-baseline-fixtures.php';
$ids = json_decode(file_get_contents(public_path('p1-baseline-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$r = \App\Models\WebCheckinRichiesta::findOrFail($ids['web']);
\App\Models\TassaDiSoggiorno::create(['struttura_id' => $r->struttura_id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);
$parent = \App\Models\Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id)->replicate();
$parent->fill(['circuito' => 'web', 'scheda' => 'W-SINTETICA', 'name' => 'Web sintetico pending'])->save();
$pending = $r->replicate();
$pending->fill(['schedina_id' => $parent->id, 'codice' => 'SHORTPENDING', 'token' => str_repeat('c', 64), 'stato' => 'da_inviare'])->save();
$other = \App\Models\Struttura::where('id', '!=', $r->struttura_id)->firstOrFail();
$foreignParent = \App\Models\Schedina::forceCreate(['struttura_id' => $r->struttura_id, 'name' => 'RISERVATO-ALTRO-TENANT', 'surname' => 'Sintetico']);
$foreign = $r->replicate();
$foreign->fill(['struttura_id' => $other->id, 'schedina_id' => $foreignParent->id, 'codice' => 'SHORTFOREIGN', 'token' => str_repeat('d', 64)])->save();
file_put_contents(public_path('web-short-ids.json'), json_encode(['web' => $r->id, 'full' => $r->token, 'short' => $r->codice.'-'.substr($r->token, 0, 8), 'pending' => $pending->codice.'-'.substr($pending->token, 0, 8), 'pendingFull' => $pending->token, 'foreign' => $foreign->codice.'-'.substr($foreign->token, 0, 8)]));
