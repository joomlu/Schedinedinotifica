<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fixture = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function seed(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $s->update(['nome_struttura' => 'Hotel Anagrafica Sintetica', 'ragione_sociale' => 'Società struttura sintetica', 'camere_disponibili' => 17]);
        $c = \App\Models\Classificazione::create(['nome' => '3 stelle']);
        $c->tipologieStruttura()->attach($s->tipologia_struttura_id);
        $s->update(['classificazione_id' => $c->id, 'classificazione' => $c->nome]);
        $users = ['admin' => $admin, 'super' => $this->actor('super_admin'), 'owner' => $this->actor('proprietario', $owner->id),
            'hotel' => $this->actor('struttura_user', $owner->id, $s->id), 'reception' => $this->actor('struttura_user', $owner->id, $s->id)];
        foreach ($users as $role => $user) {
            $user->update(['username' => 'anagrafica-'.$role, 'password' => \Illuminate\Support\Facades\Hash::make('Password-anagrafica-sintetica-123!'),
                'ruolo_operativo' => $role === 'hotel' ? 'proprietario' : ($role === 'reception' ? 'reception' : null)]);
        }
        file_put_contents(public_path('anagrafiche-fixture.json'), json_encode(['struttura' => $s->id, 'proprietario' => $owner->id, 'classificazione' => $c->id, 'generale' => $s->tipologia_generale_id, 'tipo' => $s->tipologia_struttura_id]));
    }
};
$fixture->seed();
