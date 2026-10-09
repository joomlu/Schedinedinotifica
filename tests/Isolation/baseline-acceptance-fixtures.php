<?php

require __DIR__.'/web-checkin-camera-fixtures.php';
$base = json_decode(file_get_contents(public_path('baseline-final-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$r = \App\Models\WebCheckinRichiesta::where('token', $base['full'])->firstOrFail();
$source = \App\Models\Struttura::withoutGlobalScopes()->findOrFail($r->struttura_id);
$sessionStructure = $source->replicate();
$sessionStructure->nome_struttura = 'Fixture sessioni sintetiche';
$sessionStructure->cir = null;
$sessionStructure->save();
$users = [];
foreach (['manager' => 'proprietario', 'disattivazione' => 'reception', 'reset' => 'reception', 'cambio' => 'reception'] as $name => $role) {
    $user = \App\Models\User::factory()->create(['username' => 'finale-'.$name, 'name' => 'Persona sintetica '.$name, 'ruolo' => 'struttura_user', 'ruolo_operativo' => $role, 'struttura_id' => $sessionStructure->id, 'attivo' => true, 'password' => \Illuminate\Support\Facades\Hash::make('Password-audit-123!')]);
    $users[$name] = ['id' => $user->id, 'email' => $user->email];
}
file_put_contents(public_path('baseline-acceptance-ids.json'), json_encode($users));
