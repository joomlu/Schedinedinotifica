<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$f = new class {
    use \Tests\Support\StrutturaFixtures;
    public function prepara(): void
    {
        foreach ([1440, 768, 390] as $width) {
            $s = $this->structureFor(null);
            $u = $this->actor('struttura_user', null, $s->id);
            $u->forceFill(['username' => 'cestino-ui-'.$width, 'password' => \Illuminate\Support\Facades\Hash::make('Fixture-cestino-123!')])->save();
            \Illuminate\Support\Facades\Auth::login($u);
            for ($i = 0; $i < 13; $i++) {
                $customer = \App\Models\Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Ospite sintetico '.$i, 'surname' => 'Cestino']);
                app(\App\Services\CestinoService::class)->archiveModel($customer);
                $customer->delete();
            }
            foreach (['urgente', 'alta', 'normale'] as $priority) {
                \App\Models\CalendarioEvento::forceCreate(['struttura_id' => $s->id, 'ambito' => 'struttura', 'tipo' => 'nota', 'titolo' => 'Avviso '.$priority, 'data_evento' => '2026-10-30', 'priorita' => $priority, 'stato' => 'da_fare', 'created_by' => $u->id]);
            }
            \App\Models\CalendarioEvento::forceCreate(['struttura_id' => $s->id, 'ambito' => 'struttura', 'tipo' => 'scadenza', 'titolo' => 'Scadenza sintetica', 'data_evento' => '2026-10-30', 'priorita' => 'normale', 'stato' => 'da_fare', 'created_by' => $u->id]);
        }
    }
};
$f->prepara();
echo "Fixture sintetiche cestino e avvisi pronte\n";
