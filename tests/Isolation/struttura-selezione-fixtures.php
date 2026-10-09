<?php

require __DIR__.'/tassa-anteprima-fixtures.php';
$selection = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function seed(): void
    {
        $ids = [];
        foreach (['una' => 1, 'multi' => 2, 'vuota' => 0] as $mode => $count) {
            $owner = $this->ownerFor($this->actor('admin'));
            $user = $this->actor('proprietario', $owner->id);
            $user->update(['username' => 'selezione-'.$mode, 'password' => \Illuminate\Support\Facades\Hash::make('Password-tassa-fixture-123!')]);
            for ($i = 0; $i < $count; $i++) {
                $s = $this->structureFor($owner);
                $s->update(['nome_struttura' => 'Hotel selezione '.$mode.' '.$i, 'citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
                $ids[$mode][] = $s->id;
                if ($mode === 'multi' && $i === 0) {
                    foreach (['positivo' => '2026-06-10', 'zero' => '2026-05-10'] as $case => $arrive) {
                        $p = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Persona sintetica '.$case, 'surname' => 'Selezione', 'scheda' => 101, 'arrive' => $arrive, 'departure' => date('Y-m-d', strtotime($arrive.' +3 days')), 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
                        $ids[$case] = $p->id;
                    }
                }
            }
        }
        file_put_contents(public_path('struttura-selezione-ids.json'), json_encode($ids));
    }
};
$selection->seed();
\App\Models\User::where('username', 'tassa-anteprima')->firstOrFail()->update(['ruolo_operativo' => 'reception']);
