<?php

require __DIR__.'/web-checkin-short-fixtures.php';
$ids = json_decode(file_get_contents(public_path('web-short-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$r = \App\Models\WebCheckinRichiesta::where('token', $ids['pendingFull'])->firstOrFail();
$parent = \App\Models\Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id);
$parent->update(['cant_people' => 2]);
\App\Models\Componenti::forceCreate(['struttura_id' => $r->struttura_id, 'schedina_id' => $parent->id, 'name' => 'ACCOMPAGNATORE-BROWSER-SINTETICO', 'surname' => 'Audit', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']);
$r->update(['quantita_persone' => 2]);
$staff = \App\Models\User::factory()->create(['name' => 'Reception sintetica', 'username' => 'audit-reception', 'ruolo' => 'struttura_user', 'ruolo_operativo' => 'reception', 'struttura_id' => $r->struttura_id, 'attivo' => true, 'password' => \Illuminate\Support\Facades\Hash::make('Password-audit-123!')]);
$target = \App\Models\User::factory()->create(['ruolo' => 'struttura_user', 'ruolo_operativo' => 'proprietario', 'struttura_id' => $r->struttura_id, 'attivo' => true]);
file_put_contents(public_path('baseline-final-ids.json'), json_encode(['full' => $r->token, 'short' => $ids['pending'], 'target' => $target->id]));
