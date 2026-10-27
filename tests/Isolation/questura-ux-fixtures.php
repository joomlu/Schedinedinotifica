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
        foreach (['vuote-desktop', 'vuote-tablet', 'vuote-mobile', 'errore', 'ciclo-desktop', 'ciclo-tablet', 'ciclo-mobile'] as $kind) {
            $s = $this->structureFor(null);
            $u = $this->actor('struttura_user', null, $s->id);
            $u->update(['ruolo_operativo' => 'proprietario', 'username' => 'ux-'.$kind, 'password' => \Illuminate\Support\Facades\Hash::make('Password-ux-fixture-123!')]);
            if (str_starts_with($kind, 'ciclo-')) {
                $s->update(['questura_username' => 'UTENTE-SINTETICO', 'questura_password' => 'PASSWORD-SINTETICA', 'questura_wskey' => 'WSKEY-SINTETICA']);
                $fr = \App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'FR'], ['nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
                $stay = \App\Models\Schedina::create(['struttura_id' => $s->id, 'scheda' => 'QA-SINTETICA', 'circuito' => 'schedina', 'relationship' => $kind === 'ciclo-desktop' ? '16' : ($kind === 'ciclo-tablet' ? '17' : '18'), 'cant_people' => $kind === 'ciclo-desktop' ? 1 : 2,
                    'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(), 'surname' => 'Żółć', 'name' => 'Élodie', 'sex' => 'F',
                    'oa_date_nac' => '1980-02-29', 'oa_country' => (string) $fr->id, 'oa_city_nac' => $fr->cittadinanza ?: $fr->nome, 'or_doctype' => 'IDENT', 'or_doc' => 'TEST123', 'or_published_country' => (string) $fr->id]);
                if ($kind !== 'ciclo-desktop') {
                    \App\Models\Componenti::create(['struttura_id' => $s->id, 'schedina_id' => $stay->id, 'relationship' => $kind === 'ciclo-tablet' ? '19' : '20', 'surname' => 'Esempio', 'name' => 'Componente', 'sex' => 'M', 'date_nac' => '2000-01-01', 'country_nac' => (string) $fr->id, 'city_nac' => $fr->cittadinanza ?: $fr->nome]);
                }
            }
            if ($kind === 'errore') {
                \Illuminate\Support\Facades\DB::table('struttura')->where('id', $s->id)->update(['questura_password' => 'CIPHERTEXT-NON-VALIDO-UX']);
            }
        }
    }
};
$fixtures->seed();
echo "Fixture UX sintetiche create nel solo database effimero.\n";

@mkdir(storage_path('framework/testing'), 0700, true);
file_put_contents(storage_path('framework/testing/questura-browser'), 'Solo SOAP double nel runtime isolato');
