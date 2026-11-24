<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$fixture = new class {
    use \Tests\Support\StrutturaFixtures;

    public function seed(): array
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->forceFill(['ruolo_operativo' => 'proprietario', 'password' => \Illuminate\Support\Facades\Hash::make('Password-fixture-123!')])->save();

        return ['email' => $u->email, 'password' => 'Password-fixture-123!'];
    }
};
file_put_contents(public_path('operative-session-fixture.json'), json_encode($fixture->seed(), JSON_THROW_ON_ERROR));

// Anche l'app fresca del comando deve conservare tutte le guardie del runtime.
$command = new class(new \Illuminate\Filesystem\Filesystem) extends \Illuminate\Foundation\Console\RouteCacheCommand {
    protected function getFreshApplication()
    {
        $fresh = require $this->laravel->bootstrapPath('app.php');
        \Tests\Support\TestingEnvironment::configureApplication($fresh);
        $fresh->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $fresh;
    }
};
$kernel->registerCommand($command);
if ($kernel->call('route:cache', ['--no-interaction' => true]) !== 0 || !is_file($app->getCachedRoutesPath())) {
    throw new RuntimeException('Cache delle route sintetica non generata.');
}
echo "Cache delle route generata nella copia effimera con guardie attive.\n";
