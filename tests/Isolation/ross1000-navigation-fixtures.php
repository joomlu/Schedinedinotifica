<?php
require __DIR__.'/questura-checks.php';
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$seed = new class {
    use \Tests\Support\StrutturaFixtures;
    public function run(): void {
        foreach (['incompleta', 'completa', 'soggiorno-incompleto'] as $name) {
            $s = $this->structureFor(null);
            $s->update(['nome_struttura' => 'tanggo sintetica '.$name, 'regione' => 'Emilia-Romagna', 'scadenza_servizio' => '2099-12-31']);
            if ($name !== 'incompleta') $s->update(['camere_disponibili' => 10, 'letti_disponibili' => 20, 'istat_codice_struttura' => 'CODICE-SINTETICO']);
            $u = $this->actor('struttura_user', null, $s->id);
            $u->update(['ruolo_operativo' => 'proprietario', 'username' => 'ross-'.$name, 'password' => \Illuminate\Support\Facades\Hash::make('Password-ross-sintetica-123!')]);
            if ($name === 'soggiorno-incompleto') \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'schedina', 'is_arrive' => false, 'name' => 'Persona sintetica', 'arrive' => now()->startOfMonth()->toDateString(), 'departure' => now()->endOfMonth()->toDateString()]);
        }
    }
};
$seed->run();
