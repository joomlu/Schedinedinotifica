<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$fixture = new class {
    use \Tests\Support\StrutturaFixtures;
    public function prepara(): void {
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $s->update(['nome_struttura'=>'Hotel Questura sintetico', 'questura_profilo'=>'standard', 'questura_modalita'=>'txt']);
        $user = $this->actor('proprietario', $owner->id);
        $user->update(['username'=>'questura-ui', 'password'=>\Illuminate\Support\Facades\Hash::make('Password-Fixture-Questura-123!')]);
        file_put_contents(public_path('questura-ui-fixture.json'), json_encode(['struttura'=>$s->id]));
    }
};
$fixture->prepara();
echo "Fixture proprietario e struttura sintetici, nessun invio.\n";
