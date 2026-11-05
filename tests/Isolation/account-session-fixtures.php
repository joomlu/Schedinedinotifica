<?php

require __DIR__.'/baseline-acceptance-fixtures.php';
$ids = json_decode(file_get_contents(public_path('baseline-acceptance-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$manager = \App\Models\User::findOrFail($ids['manager']['id']);
$recovery = \App\Models\User::factory()->create(['username' => 'recovery-sintetico', 'ruolo' => 'struttura_user', 'ruolo_operativo' => 'reception', 'struttura_id' => $manager->struttura_id, 'attivo' => true, 'password' => \Illuminate\Support\Facades\Hash::make('Password-audit-123!')]);
foreach (['ricordami-disattivo', 'ricordami-reset', 'recupero-valido'] as $name) {
    $user = \App\Models\User::factory()->create(['username' => $name, 'ruolo' => 'struttura_user', 'ruolo_operativo' => 'reception', 'struttura_id' => $manager->struttura_id, 'attivo' => true, 'password' => \Illuminate\Support\Facades\Hash::make('Password-audit-123!')]);
    $ids[$name] = ['id' => $user->id, 'email' => $user->email];
    if ($name === 'recupero-valido') {
        $ids[$name]['token'] = \Illuminate\Support\Facades\Password::broker()->createToken($user);
    }
}
$ids['recupero'] = ['id' => $recovery->id, 'email' => $recovery->email, 'token' => \Illuminate\Support\Facades\Password::broker()->createToken($recovery)];
$foreign = \App\Models\Customers::forceCreate(['struttura_id' => $source->id, 'name' => 'Cliente estraneo sintetico', 'surname' => 'Audit']);
$own = \App\Models\Customers::forceCreate(['struttura_id' => $manager->struttura_id, 'name' => 'Cliente proprio sintetico', 'surname' => 'Audit']);
$ids['clienti'] = ['estraneo' => $foreign->id, 'proprio' => $own->id];
file_put_contents(public_path('account-session-ids.json'), json_encode($ids));
