<?php

require __DIR__.'/account-session-fixtures.php';
$legacyReception = \App\Models\User::factory()->create(['name' => 'Reception legacy sintetica', 'ruolo' => 'struttura_user', 'ruolo_operativo' => 'reception', 'struttura_id' => $manager->struttura_id, 'attivo' => true, 'password' => \Illuminate\Support\Facades\Hash::make('Password-audit-123!')]);
file_put_contents(public_path('legacy-user-ids.json'), json_encode(['manager' => ['email' => $manager->email], 'reception' => ['email' => $legacyReception->email], 'struttura' => $manager->struttura_id, 'estranea' => $source->id]));
