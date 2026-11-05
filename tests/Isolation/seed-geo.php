<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['super_admin', 'admin', 'proprietario'] as $role) {
    \Tests\Support\GeoLogoFixtures::user($role);
}
$comune = \Tests\Support\GeoLogoFixtures::comune();
\Tests\Support\GeoLogoFixtures::existingLogo($comune);
for ($i = 2; $i <= 15; $i++) {
    \App\Models\GeoComune::create(['geo_provincia_id' => $comune->geo_provincia_id, 'nome' => 'Zeta Fixture '.$i, 'codice_istat' => '99'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)]);
}
echo "Fixture sintetiche browser create nel solo database temporaneo.\n";
