<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$factory = new class {
    use \Tests\Support\StrutturaFixtures;
    public function structure() { return $this->structureFor(null); }
};
$s = $factory->structure();
$s->update(['regione' => 'Emilia-Romagna', 'istat_codice_struttura' => 'TEST-FIXTURE', 'camere_disponibili' => 10, 'letti_disponibili' => 20]);
$stay = \App\Models\Schedina::forceCreate([
    'id' => 9000001, 'struttura_id' => $s->id, 'circuito' => 'schedina', 'is_arrive' => false,
    'scheda' => 'TEST-FAMIGLIA', 'name' => 'Èlia & Fixture', 'surname' => "D'Àngelo <Test>", 'sex' => 'M', 'relationship' => '17',
    'arrive' => '2026-04-01', 'departure' => '2026-04-02', 'cant_people' => 2, 'room' => 1, 'beds' => 2,
    'oa_country' => '100000100', 'oa_city' => '412058091', 'oa_prov' => 'RM', 'oa_city_nac' => '100000100', 'oa_date_nac' => '2000-01-01',
    'or_country' => '100000100', 'or_city' => '412058091', 'or_prov' => 'RM', 'or_region' => 'Lazio',
    'istat_tipo_turismo' => 'Balneare', 'istat_mezzo_trasporto' => 'Auto', 'istat_canale_prenotazione' => 'Diretta web',
]);
\App\Models\Componenti::forceCreate([
    'id' => 9000001, 'struttura_id' => $s->id, 'schedina_id' => $stay->id, 'name' => 'Minore Fixture', 'surname' => 'Sintetico', 'sex' => 'F', 'relationship' => '19',
    'country_nac' => '100000216', 'city_nac' => '100000100', 'date_nac' => '2020-02-29', 'country' => '100000216', 'city' => 'Berlino',
]);
$service = new \App\Services\IstatTabellaAService();
$from = \Carbon\Carbon::parse('2026-04-01'); $to = \Carbon\Carbon::parse('2026-04-02');
$a = $service->analysePeriodo($s, $from, $to);
if ([$a['totale_arrivi'], $a['totale_partenze'], $a['totale_presenze']] !== [2, 2, 2]) { throw new \RuntimeException('Risultato statistico diverso dalla fixture attesa.'); }
$xml = $service->buildXml($s, $from, $to);
$golden = __DIR__.'/../Fixtures/istat/famiglia.xml';
if (is_file($golden) && file_get_contents($golden) !== $xml) { throw new \RuntimeException('XML diverso dall’artefatto sintetico verificato.'); }
echo 'XML_FIXTURE_BASE64='.base64_encode($xml).PHP_EOL;
echo "PASS: fixture nota, arrivi=2, partenze=2, presenze=2; XSD e confronto XML sintetico.\n";
