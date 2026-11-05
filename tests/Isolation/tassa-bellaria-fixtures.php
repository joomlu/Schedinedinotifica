<?php

require __DIR__.'/questura-checks.php';
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fixtures = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function seed(): void
    {
        $ids = [];
        foreach (['senza', 'con', 'gruppo', 'legacy'] as $mode) {
            $s = $this->structureFor(null);
            $s->update(['nome_struttura' => 'Hotel sintetico di prova', 'citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle', 'scadenza_servizio' => '2099-12-31']);
            $u = $this->actor('struttura_user', null, $s->id);
            if ($mode === 'legacy') {
                $u->update(['ruolo_operativo' => 'proprietario']);
            }
            $u->update(['username' => 'tassa-'.$mode, 'password' => \Illuminate\Support\Facades\Hash::make('Password-tassa-fixture-123!')]);
            $cfg = \App\Models\TassaDiSoggiorno::create(['struttura_id' => $s->id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6,
                'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);
            if ($mode === 'legacy') {
                $cfg->update(['inizio' => '2026-03-01', 'fine' => '2026-10-01']);
            }
            foreach (['400', '405', '410', '415', '420', '425', '430', '440'] as $code) {
                \App\Models\TassaEsenzione::create(['struttura_id' => $s->id, 'codice' => $code, 'descrizione' => 'Motivo sintetico '.$code, 'attivo' => true]);
            }
            $p = \App\Models\Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'Persona sintetica', 'surname' => 'Verifica', 'scheda' => 101,
                'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => '2008-06-13', 'exent' => 'NO', 'is_arrive' => 0]);
            \App\Models\Componenti::forceCreate(['schedina_id' => $p->id, 'struttura_id' => $s->id, 'name' => 'Componente sintetico', 'date_nac' => '1980-01-01', 'exent' => 'NO']);
            if ($mode === 'gruppo') {
                for ($i = 1; $i <= 34; $i++) {
                    \App\Models\Componenti::forceCreate(['schedina_id' => $p->id, 'struttura_id' => $s->id, 'name' => 'Componente sintetico '.$i, 'date_nac' => '1980-01-01', 'exent' => 'NO']);
                }
            }
            if ($mode === 'con') {
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="200"><rect width="1200" height="200" fill="#d9efee"/><path d="M0 100 Q200 50 400 100 T800 100 T1200 100 V200 H0Z" fill="#287c85"/><circle cx="1050" cy="45" r="28" fill="#efc66f"/></svg>';
                file_put_contents(public_path('tassa-sintetica.svg'), $svg);
                $cfg->update(['ricevuta_immagine' => 'tassa-sintetica.svg']);
                $s->update(['logo' => 'tassa-sintetica.svg', 'logo_citta' => 'tassa-sintetica.svg']);
            }
            if ($mode === 'con') {
                $admin = $this->actor('admin', null, $s->id);
                $s->update(['proprietario_id' => $this->ownerFor($admin)->id]);
                $admin->update(['username' => 'tassa-admin', 'password' => \Illuminate\Support\Facades\Hash::make('Password-tassa-fixture-123!')]);
            }
            $ids[$mode] = $p->id;
            $ids['struttura_'.$mode] = $s->id;
        }
        file_put_contents(public_path('tassa-fixture-ids.json'), json_encode($ids));
    }
};
$fixtures->seed();
echo "Fixture Tassa sintetiche create esclusivamente nel database effimero.\n";
