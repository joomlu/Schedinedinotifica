<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\App\Models\User::factory()->create([
    'username' => 'contatti-crm-ui', 'email' => 'contatti-crm@example.invalid',
    'ruolo' => 'super_admin', 'attivo' => true,
    'password' => bcrypt('Password-contatti-fixture-123!'),
]);
echo "PASS: utente CRM sintetico nel solo runtime effimero.\n";
