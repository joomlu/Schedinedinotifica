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
        $s = $this->structureFor(null);
        $s->forceFill(['nome_struttura' => 'Hotel Calendario sintetico'])->save();
        $u = $this->actor('struttura_user', null, $s->id);
        $u->forceFill(['username' => 'calendario-ui', 'password' => \Illuminate\Support\Facades\Hash::make('Fixture-calendario-123!')])->save();
        foreach (['manuale' => 'Verificare documenti Rossi', 'nota' => 'Reception aperta fino alle 22', 'evento' => 'Riunione reception'] as $tipo => $titolo) {
            \App\Models\CalendarioEvento::forceCreate(['struttura_id' => $s->id, 'ambito' => 'struttura', 'tipo' => $tipo, 'titolo' => $titolo, 'descrizione' => 'Descrizione sintetica del calendario.', 'data_evento' => '2026-10-30', 'priorita' => 'normale', 'stato' => 'da_fare', 'created_by' => $u->id]);
        }
        \App\Models\Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Anna', 'surname' => 'Compleanno sintetico', 'nac_reg' => '1984-10-30']);
        $h = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Mario', 'surname' => 'Rossi sintetico', 'arrive' => '2026-10-30', 'departure' => '2026-11-01', 'cant_people' => 2, 'is_arrive' => false, 'circuito' => 'schedina']);
        \App\Models\Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $h->id, 'name' => 'Laura', 'surname' => 'Componente sintetico', 'date_nac' => '1990-05-01']);
    }
};
$f->prepara();
echo "Fixture calendario sintetiche pronte\n";
