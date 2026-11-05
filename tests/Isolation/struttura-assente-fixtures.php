<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$fixture = new class {
    use \Tests\Support\StrutturaFixtures;
    public function prepara(): void
    {
        foreach (['admin', 'super_admin'] as $role) {
            $user = $this->actor($role);
            $user->forceFill(['username' => 'senza-struttura-'.$role, 'password' => \Illuminate\Support\Facades\Hash::make('Fixture-selezione-123!')])->save();
            $hotel = $this->structureFor($this->ownerFor($user));
            $hotel->forceFill(['nome_struttura' => 'Hotel sintetico '.$role])->save();
        }
    }
};
$fixture->prepara();
