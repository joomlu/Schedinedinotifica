<?php

require __DIR__.'/istat-cycle-fixtures.php';

\App\Models\User::factory()->create([
    'username' => 'sito-crm-ui', 'email' => 'sito-crm-ui@example.invalid',
    'ruolo' => 'super_admin', 'attivo' => true,
    'password' => bcrypt('Password-sito-fixture-123!'),
]);
echo "PASS: superadmin CRM sintetico nel solo runtime effimero.\n";
