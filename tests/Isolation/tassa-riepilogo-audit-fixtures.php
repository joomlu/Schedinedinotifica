<?php

require __DIR__.'/tassa-anteprima-fixtures.php';

$fixture = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function seed(): void
    {
        $s = $this->structureFor(null);
        $s->update(['nome_struttura' => 'Hotel sintetico — audit riepilogo', 'citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['username' => 'tassa-audit', 'password' => \Illuminate\Support\Facades\Hash::make('Password-tassa-fixture-123!')]);
        \App\Models\TassaDiSoggiorno::create(['struttura_id' => $s->id, 'tassa_soggiorno' => '1.50', 'giorni_massimo' => 6,
            'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);
        $p = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'scheda' => 1, 'name' => str_repeat('Ospite sintetico àèìòù ', 4), 'surname' => str_repeat('Cognome sintetico ', 4),
            'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => '2008-06-13', 'exent' => 'NO', 'is_arrive' => 0]);
        \App\Models\Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'name' => 'Minore sintetico', 'surname' => 'Audit', 'date_nac' => '2010-01-01', 'exent' => 'NO']);
        file_put_contents(public_path('tassa-audit-ids.json'), json_encode(['schedina' => $p->id]));
    }
};
$fixture->seed();
