<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disabilita FK, pulizia tabelle nell'ordine corretto, poi reimporta
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('geo_caps')->delete();
        DB::table('geo_cities')->delete();
        DB::table('geo_provinces')->delete();
        DB::table('geo_regions')->delete();
        DB::table('stati')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Importa in ordine: regions -> provinces -> cities -> caps -> nations
        $this->seedRegions();
        $this->seedProvinces();
        $this->seedCities();
        $this->seedCaps();
        $this->seedNations();
    }

    protected function read(string $file): array
    {
        $path = storage_path('app/'.ltrim($file, '/'));
        if (! file_exists($path)) {
            return [];
        }
        $json = file_get_contents($path);
        $arr = json_decode($json, true);

        return is_array($arr) ? $arr : [];
    }

    protected function chunkInsert(string $table, array $rows, int $size = 1000): void
    {
        foreach (array_chunk($rows, $size) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    protected function seedNations(): void
    {
        $items = $this->read('gi_nazioni.json');
        if (! $items) {
            return;
        }
        $rows = [];
        foreach ($items as $n) {
            $rows[] = [
                'sigla_nazione' => (string) ($n['sigla_nazione'] ?? ''),
                'denominazione_nazione' => (string) ($n['denominazione_nazione'] ?? ($n['denominazione_ita'] ?? '')),
                'denominazione_cittadinanza' => (string) ($n['denominazione_cittadinanza'] ?? ''),
            ];
        }
        $this->chunkInsert('stati', $rows);
    }

    protected function seedRegions(): void
    {
        $items = $this->read('gi_regioni.json');
        if (! $items) {
            return;
        }
        // già pulito in run()
        $rows = [];
        foreach ($items as $r) {
            $rows[] = [
                'codice_regione' => (string) ($r['codice_regione'] ?? ''),
                'denominazione_regione' => (string) ($r['denominazione_regione'] ?? ''),
                'tipologia_regione' => (string) ($r['tipologia_regione'] ?? ''),
                'ripartizione_geografica' => (string) ($r['ripartizione_geografica'] ?? ''),
            ];
        }
        $this->chunkInsert('geo_regions', $rows);
    }

    protected function seedProvinces(): void
    {
        $items = $this->read('gi_province.json');
        if (! $items) {
            return;
        }
        // già pulito in run()
        $rows = [];
        foreach ($items as $p) {
            $rows[] = [
                'sigla_provincia' => (string) ($p['sigla_provincia'] ?? ''),
                'denominazione_provincia' => (string) ($p['denominazione_provincia'] ?? ''),
                'codice_regione' => (string) ($p['codice_regione'] ?? ''),
                'tipologia_provincia' => (string) ($p['tipologia_provincia'] ?? ''),
            ];
        }
        $this->chunkInsert('geo_provinces', $rows);
    }

    protected function seedCities(): void
    {
        $items = $this->read('gi_comuni.json');
        if (! $items) {
            return;
        }
        // già pulito in run()
        $rows = [];
        // Mappa sigla_provincia -> codice_regione dalle province già caricate
        $provToReg = DB::table('geo_provinces')->pluck('codice_regione', 'sigla_provincia');
        foreach ($items as $c) {
            $siglaProvincia = (string) ($c['sigla_provincia'] ?? '');
            $codiceRegione = $provToReg[$siglaProvincia] ?? null; // deve esistere per rispettare i vincoli FK
            $rows[] = [
                'codice_istat' => (string) ($c['codice_istat'] ?? ''),
                'denominazione_ita' => (string) ($c['denominazione_ita'] ?? ''),
                'denominazione_ita_altra' => (string) ($c['denominazione_ita_altra'] ?? ''),
                'sigla_provincia' => $siglaProvincia,
                // Ricavato dalle province (il JSON dei comuni non contiene il codice_regione)
                'codice_regione' => (string) $codiceRegione,
                'flag_capoluogo' => (string) ($c['flag_capoluogo'] ?? ''),
                'codice_belfiore' => (string) ($c['codice_belfiore'] ?? ''),
                'lat' => (string) ($c['lat'] ?? ''),
                'lon' => (string) ($c['lon'] ?? ''),
                'superficie_kmq' => (string) ($c['superficie_kmq'] ?? ''),
                'codice_sovracomunale' => (string) ($c['codice_sovracomunale'] ?? ''),
            ];
        }
        $this->chunkInsert('geo_cities', $rows);
    }

    protected function seedCaps(): void
    {
        $items = $this->read('gi_comuni_cap.json');
        if (! $items) {
            return;
        }
        // già pulito in run()
        $rows = [];
        foreach ($items as $x) {
            $rows[] = [
                'codice_istat' => (string) ($x['codice_istat'] ?? ''),
                'cap' => (string) ($x['cap'] ?? ($x['CAP'] ?? '')),
                'sigla_provincia' => (string) ($x['sigla_provincia'] ?? ''),
                'denominazione_provincia' => (string) ($x['denominazione_provincia'] ?? ''),
                'codice_regione' => (string) ($x['codice_regione'] ?? ''),
                'denominazione_regione' => (string) ($x['denominazione_regione'] ?? ''),
            ];
        }
        $this->chunkInsert('geo_caps', $rows);
    }
}
