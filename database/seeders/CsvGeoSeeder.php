<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CsvGeoSeeder extends Seeder
{
    public function run(): void
    {
        // Percorsi sorgenti
        $base = base_path('reference/libreria');
        $anprPath = $base . DIRECTORY_SEPARATOR . 'Comuni-subentrati_ANPR-16aprile';
        $comuniPath = $base . DIRECTORY_SEPARATOR . 'comuni.csv';

        if (! file_exists($anprPath) || ! file_exists($comuniPath)) {
            $this->command?->warn('CSV non trovati in reference/libreria. Salto CsvGeoSeeder.');
            return;
        }

        // Mappa Citta -> sigla provincia da comuni.csv (solo attuali, DataFineVal vuoto)
        $cityToSigla = [];
        $fh = fopen($comuniPath, 'r');
        if ($fh !== false) {
            $header = fgetcsv($fh, 0, "\t"); // tab-separated
            while (($row = fgetcsv($fh, 0, "\t")) !== false) {
                if (count($row) < 4) { continue; }
                [$codice, $citta, $provSigla, $dataFine] = $row;
                $cittaKey = self::norm((string) $citta);
                $provSigla = trim((string) $provSigla);
                $dataFine = trim((string) $dataFine);
                if ($cittaKey === '' || $provSigla === '') { continue; }
                // Preferisci voci senza DataFineVal (attive)
                if (!isset($cityToSigla[$cittaKey]) || $dataFine === '') {
                    $cityToSigla[$cittaKey] = $provSigla;
                }
            }
            fclose($fh);
        }

        // Prima passata: raccogli regioni e province (nome->sigla) da ANPR incrociando cityToSigla
        $regionSet = [];
        $provNameToSiglaVotes = [];
        $provNameToRegionVotes = [];
        $records = [];

        $fh = fopen($anprPath, 'r');
        if ($fh === false) {
            $this->command?->error('Impossibile aprire ANPR CSV');
            return;
        }
        $header = fgetcsv($fh, 0, ',');
        // Attesi: Comune,Numero abitanti,Data subentro,Provincia,Regione,Prefettura,Codice Istat,Codice  catastale
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            if (count($row) < 8) { continue; }
            $comune = trim($row[0] ?? '');
            $provName = trim($row[3] ?? '');
            $regName = trim($row[4] ?? '');
            $istat = trim($row[6] ?? '');
            $belfiore = trim($row[7] ?? '');
            if ($comune === '' || $provName === '' || $regName === '' || $istat === '') { continue; }

            $regionSet[$regName] = true;

            $cityKey = self::norm($comune);
            $sigla = $cityToSigla[$cityKey] ?? null;
            if ($sigla) {
                $provKey = self::norm($provName);
                $provNameToSiglaVotes[$provKey][$sigla] = ($provNameToSiglaVotes[$provKey][$sigla] ?? 0) + 1;
            }
            $provNameToRegionVotes[self::norm($provName)][$regName] = ($provNameToRegionVotes[self::norm($provName)][$regName] ?? 0) + 1;

            $records[] = [
                'comune' => $comune,
                'prov_name' => $provName,
                'reg_name' => $regName,
                'istat' => $istat,
                'belfiore' => $belfiore,
                'sigla_provincia' => $sigla, // può essere null qui
            ];
        }
        fclose($fh);

        // Codici regione stabilizzati (01..)
        $regionNames = array_keys($regionSet);
        sort($regionNames, SORT_NATURAL);
        $regionCodeMap = [];
        $i = 1;
        foreach ($regionNames as $name) {
            $regionCodeMap[$name] = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $i++;
        }

        // Risolvi sigla provincia per nome provincia: prendi la sigla più votata
        $provNameToSigla = [];
        foreach ($provNameToSiglaVotes as $provKey => $votes) {
            arsort($votes);
            $siglaTop = array_key_first($votes);
            $provNameToSigla[$provKey] = $siglaTop;
        }

        // Correzioni manuali per casi noti (ambiguità nei dataset ANPR/comuni)
        // Le chiavi sono già normalizzate con self::norm (lowercase, spazi singoli)
        $manualSiglaOverrides = [
            'sud sardegna' => 'SU', // evitare conflitto con Cagliari (CA)
        ];
        foreach ($manualSiglaOverrides as $nameKey => $sigla) {
            $provNameToSigla[$nameKey] = $sigla;
        }

        // Risolvi regione per provincia (più votata)
        $provNameToRegion = [];
        foreach ($provNameToRegionVotes as $provKey => $votes) {
            arsort($votes);
            $regNameTop = array_key_first($votes);
            $provNameToRegion[$provKey] = $regNameTop;
        }

        // Prepara righe
        $regionRows = [];
        foreach ($regionNames as $regName) {
            $regionRows[] = [
                'codice_regione' => $regionCodeMap[$regName],
                'denominazione_regione' => $regName,
                'tipologia_regione' => null,
                'ripartizione_geografica' => null,
            ];
        }

        // Province
        $provSeen = [];
        $provRows = [];
        foreach ($records as $r) {
            $provKey = self::norm($r['prov_name']);
            if (isset($provSeen[$provKey])) { continue; }
            $sigla = $provNameToSigla[$provKey] ?? null;
            $regName = $provNameToRegion[$provKey] ?? $r['reg_name'];
            $codReg = $regionCodeMap[$regName] ?? null;
            if (! $sigla || ! $codReg) { continue; }
            $provRows[] = [
                'sigla_provincia' => $sigla,
                'denominazione_provincia' => $r['prov_name'],
                'codice_regione' => $codReg,
                'tipologia_provincia' => null,
            ];
            $provSeen[$provKey] = true;
        }

        // Comuni
        $cityRows = [];
        foreach ($records as $r) {
            $sigla = $r['sigla_provincia'] ?? ($provNameToSigla[self::norm($r['prov_name'])] ?? null);
            $codReg = $regionCodeMap[$r['reg_name']] ?? null;
            if (! $sigla || ! $codReg) { continue; }
            $cityRows[] = [
                'codice_istat' => $r['istat'],
                'denominazione_ita' => $r['comune'],
                'denominazione_ita_altra' => '',
                'sigla_provincia' => $sigla,
                'codice_regione' => $codReg,
                'flag_capoluogo' => '',
                'codice_belfiore' => $r['belfiore'],
                'lat' => '',
                'lon' => '',
                'superficie_kmq' => '',
                'codice_sovracomunale' => '',
            ];
        }

        // Scrivi nel DB
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('geo_caps')->delete();
        DB::table('geo_cities')->delete();
        DB::table('geo_provinces')->delete();
        DB::table('geo_regions')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->chunkInsert('geo_regions', $regionRows);
        $this->chunkInsert('geo_provinces', $provRows);
        $this->chunkInsert('geo_cities', $cityRows);

        $this->command?->info(sprintf('CsvGeoSeeder: importate %d regioni, %d province, %d comuni', count($regionRows), count($provRows), count($cityRows)));
    }

    protected static function norm(string $s): string
    {
        $s = trim(mb_strtolower($s));
        // normalizzazione semplice: sostituisci doppi spazi
        $s = preg_replace('/\s+/', ' ', $s);
        return (string) $s;
    }

    protected function chunkInsert(string $table, array $rows, int $size = 1000): void
    {
        foreach (array_chunk($rows, $size) as $chunk) {
            if (!empty($chunk)) {
                DB::table($table)->insert($chunk);
            }
        }
    }
}
