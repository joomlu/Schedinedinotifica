<?php

require __DIR__.'/tassa-bellaria-fixtures.php';

$anteprime = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function seed(): void
    {
        $s = $this->structureFor(null);
        $s->update(['nome_struttura' => 'Hotel anteprime sintetiche', 'citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
        $u = $this->actor('struttura_user', null, $s->id);
        $u->update(['username' => 'tassa-anteprima', 'password' => \Illuminate\Support\Facades\Hash::make('Password-tassa-fixture-123!')]);
        $ids = ['struttura' => $s->id];
        foreach ([
            'prima' => ['2026-05-10', '2026-05-13', '1980-01-01'],
            'dopo' => ['2026-10-01', '2026-10-04', '1980-01-01'],
            'esente' => ['2026-06-10', '2026-06-13', '2015-01-01'],
            'limite' => ['2026-06-10', '2026-06-20', '2008-06-16'],
            'positivo' => ['2026-06-10', '2026-06-13', '1980-01-01'],
            'parziale' => ['2026-05-31', '2026-06-03', '1980-01-01'],
            'checkout' => ['2026-12-31', '2027-01-01', '1980-01-01'],
            'crossyear' => ['2026-12-31', '2027-01-03', '1980-01-01'],
        ] as $caso => [$arrivo, $partenza, $nascita]) {
            $p = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Anteprima '.$caso, 'surname' => 'Sintetica', 'arrive' => $arrivo, 'departure' => $partenza, 'oa_date_nac' => $nascita, 'exent' => 'NO', 'is_arrive' => 0]);
            $ids[$caso] = $p->id;
        }
        file_put_contents(public_path('tassa-anteprima-ids.json'), json_encode($ids));
    }
};
$anteprime->seed();
echo "Fixture anteprime sintetiche create esclusivamente nel database effimero.\n";
