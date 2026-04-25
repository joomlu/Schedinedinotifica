<?php

namespace App\Services;

use App\Models\GeoCap;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientiCsvGeoEnricher
{
    /** @var array<string,array{comune:string,provincia:string,nazione:string}|null> */
    private array $capCache = [];

    /** @var array<string,string|null> */
    private array $comuneProvCache = [];

    /** @var array<string,bool> */
    private array $dbAvailable = [];

    private bool $dbEnabled = true;

    /**
     * @return array{totale:int, aggiornati:int, irrisolti:int, output:string, report:string}
     */
    public function enrich(string $inputPath, string $outputPath, string $reportPath): array
    {
        $this->dbEnabled = $this->checkDbAvailable();
        if (!$this->dbEnabled) {
            \Log::warning('ClientiCsvGeoEnricher: tabelle geo DB non disponibili, arricchimento DB disabilitato.');
        }

        if (!is_readable($inputPath)) {
            throw new \RuntimeException("File input non leggibile: {$inputPath}");
        }

        [$statiSet, $statiByKey] = $this->loadStati();
        $comuniByName = $this->loadComuni();

        $in = fopen($inputPath, 'r');
        if ($in === false) {
            throw new \RuntimeException("Impossibile aprire il file input: {$inputPath}");
        }

        $header = fgetcsv($in, 0, ';');
        if (!is_array($header)) {
            fclose($in);
            throw new \RuntimeException('Header CSV non valido o assente.');
        }

        $header = $this->normalizeHeader($header);
        $baseCols = count($header);

        $rows = [];
        $maxCols = $baseCols;
        while (($row = fgetcsv($in, 0, ';')) !== false) {
            $rows[] = $row;
            $maxCols = max($maxCols, count($row));
        }
        fclose($in);

        if ($maxCols > $baseCols) {
            for ($i = $baseCols; $i < $maxCols; $i++) {
                $header[] = 'Extra_' . ($i - $baseCols + 1);
            }
        }

        $out = fopen($outputPath, 'w');
        if ($out === false) {
            throw new \RuntimeException("Impossibile creare output CSV: {$outputPath}");
        }

        $report = fopen($reportPath, 'w');
        if ($report === false) {
            fclose($out);
            throw new \RuntimeException("Impossibile creare report CSV: {$reportPath}");
        }

        fputcsv($out, $header, ';');
        fputcsv($report, [
            'riga_csv',
            'Nro Clienti',
            'missing_fields',
            'Nazione-residenza',
            'Provincia-residenza',
            'Citta-residenza',
            'Cap',
            'Nazione-Anagrafica',
            'Provincia-Anagrafica',
            'Citta-Anagrafica',
        ], ';');

        $totale = 0;
        $aggiornati = 0;
        $irrisolti = 0;

        foreach ($rows as $idx => $rawRow) {
            $totale++;
            $row = $this->rowToAssoc($header, $rawRow);
            $original = $row;

            $this->normalizeGeoValues($row);

            $this->fillFromTwin($row, 'Nazione-residenza', 'Nazione-Anagrafica');
            $this->fillFromTwin($row, 'Provincia-residenza', 'Provincia-Anagrafica');
            $this->fillFromTwin($row, 'Citta-residenza', 'Citta-Anagrafica');

            $this->inferGeoBlock($row, 'Nazione-residenza', 'Provincia-residenza', 'Citta-residenza', $statiSet, $statiByKey, $comuniByName);
            $this->inferGeoBlock($row, 'Nazione-Anagrafica', 'Provincia-Anagrafica', 'Citta-Anagrafica', $statiSet, $statiByKey, $comuniByName);

            if ($this->isUpdated($original, $row)) {
                $aggiornati++;
            }

            $missing = $this->missingGeoFields($row);
            if (!empty($missing)) {
                $irrisolti++;
                fputcsv($report, [
                    $idx + 2,
                    (string) ($row['Nro Clienti'] ?? ''),
                    implode('|', $missing),
                    (string) ($row['Nazione-residenza'] ?? ''),
                    (string) ($row['Provincia-residenza'] ?? ''),
                    (string) ($row['Citta-residenza'] ?? ''),
                    (string) ($row['Cap'] ?? ''),
                    (string) ($row['Nazione-Anagrafica'] ?? ''),
                    (string) ($row['Provincia-Anagrafica'] ?? ''),
                    (string) ($row['Citta-Anagrafica'] ?? ''),
                ], ';');
            }

            fputcsv($out, $this->assocToRow($header, $row), ';');
        }

        fclose($out);
        fclose($report);

        return [
            'totale' => $totale,
            'aggiornati' => $aggiornati,
            'irrisolti' => $irrisolti,
            'output' => $outputPath,
            'report' => $reportPath,
        ];
    }

    /**
     * @return array{0:array<string,bool>,1:array<string,string>}
     */
    private function loadStati(): array
    {
        $path = base_path('reference/libreria/stati.csv');
        if (!is_readable($path)) {
            return [[], []];
        }

        $fh = fopen($path, 'r');
        if ($fh === false) {
            return [[], []];
        }

        $header = fgetcsv($fh, 0, "\t");
        $set = [];
        $byKey = [];

        while (($row = fgetcsv($fh, 0, "\t")) !== false) {
            if (!is_array($header) || count($row) !== count($header)) {
                continue;
            }
            $assoc = array_combine($header, $row) ?: [];
            $nome = trim((string) ($assoc['Stato'] ?? ''));
            if ($nome === '') {
                continue;
            }

            $set[mb_strtoupper($nome)] = true;
            $byKey[$this->normalizeKey($nome)] = $nome;
        }

        fclose($fh);
        return [$set, $byKey];
    }

    /**
     * @return array<string,array<int,string>>
     */
    private function loadComuni(): array
    {
        $path = base_path('reference/libreria/comuni.csv');
        if (!is_readable($path)) {
            return [];
        }

        $fh = fopen($path, 'r');
        if ($fh === false) {
            return [];
        }

        $header = fgetcsv($fh, 0, "\t");
        $map = [];

        while (($row = fgetcsv($fh, 0, "\t")) !== false) {
            if (!is_array($header) || count($row) !== count($header)) {
                continue;
            }
            $assoc = array_combine($header, $row) ?: [];
            $city = trim((string) ($assoc['Citta'] ?? ''));
            $prov = trim((string) ($assoc['Provincia'] ?? ''));
            if ($city === '' || $prov === '') {
                continue;
            }

            $key = $this->normalizeKey($city);
            if (!isset($map[$key])) {
                $map[$key] = [];
            }
            if (!in_array($prov, $map[$key], true)) {
                $map[$key][] = $prov;
            }
        }

        fclose($fh);
        return $map;
    }

    /**
     * @param array<int,string> $header
     * @param array<int,string> $row
     * @return array<string,string>
     */
    private function rowToAssoc(array $header, array $row): array
    {
        $n = count($header);
        $m = count($row);
        if ($m < $n) {
            $row = array_pad($row, $n, '');
        } elseif ($m > $n) {
            $row = array_slice($row, 0, $n);
        }

        $assoc = array_combine($header, $row);
        return $assoc === false ? [] : $assoc;
    }

    /**
     * @param array<int,string> $header
     * @param array<string,string> $row
     * @return array<int,string>
     */
    private function assocToRow(array $header, array $row): array
    {
        $out = [];
        foreach ($header as $col) {
            $out[] = (string) ($row[$col] ?? '');
        }
        return $out;
    }

    /**
     * @param array<int,string> $header
     * @return array<int,string>
     */
    private function normalizeHeader(array $header): array
    {
        foreach ($header as $idx => $name) {
            $clean = trim((string) $name);
            if ($idx === 0) {
                $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean) ?? $clean;
            }
            $header[$idx] = $clean;
        }

        return $header;
    }

    /**
     * @param array<string,string> $row
     */
    private function normalizeGeoValues(array &$row): void
    {
        foreach ([
            'Nazione-residenza',
            'Provincia-residenza',
            'Citta-residenza',
            'Cap',
            'Nazione-Anagrafica',
            'Provincia-Anagrafica',
            'Citta-Anagrafica',
        ] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value === '' || $value === '-' || $value === '0' || $value === '11') {
                $row[$field] = '';
                continue;
            }

            if ($field === 'Cap') {
                $digits = preg_replace('/\D+/', '', $value) ?? '';
                $row[$field] = strlen($digits) === 5 ? $digits : '';
                continue;
            }

            $value = preg_replace('/\s+/', ' ', $value) ?? $value;
            $row[$field] = trim($value);
        }
    }

    /**
     * @param array<string,string> $row
     */
    private function fillFromTwin(array &$row, string $a, string $b): void
    {
        $va = trim((string) ($row[$a] ?? ''));
        $vb = trim((string) ($row[$b] ?? ''));

        if ($va === '' && $vb !== '') {
            $row[$a] = $vb;
            return;
        }

        if ($vb === '' && $va !== '') {
            $row[$b] = $va;
        }
    }

    /**
     * @param array<string,string> $row
     * @param array<string,bool> $statiSet
     * @param array<string,string> $statiByKey
     * @param array<string,array<int,string>> $comuniByName
     */
    private function inferGeoBlock(
        array &$row,
        string $countryField,
        string $provinceField,
        string $cityField,
        array $statiSet,
        array $statiByKey,
        array $comuniByName
    ): void {
        $country = trim((string) ($row[$countryField] ?? ''));
        $province = trim((string) ($row[$provinceField] ?? ''));
        $city = trim((string) ($row[$cityField] ?? ''));

        if ($country !== '') {
            $canonicalCountry = $statiByKey[$this->normalizeKey($country)] ?? null;
            if ($canonicalCountry !== null) {
                $country = $canonicalCountry;
                $row[$countryField] = $canonicalCountry;
            }
        }

        if ($country === '' && ($province !== '' || $city !== '')) {
            $country = 'ITALIA';
            $row[$countryField] = 'ITALIA';
        }

        $isItalian = $this->looksItalianCountry($country, $statiSet);

        // Arricchimento tramite CAP (solo Italia): ricava comune e provincia.
        $cap = trim((string) ($row['Cap'] ?? ''));
        if ($cap !== '' && $isItalian && ($city === '' || $province === '')) {
            $capData = $this->resolveCapFromDb($cap);
            if ($capData !== null) {
                if ($city === '') {
                    $city = $capData['comune'];
                    $row[$cityField] = $city;
                }
                if ($province === '') {
                    $province = $capData['provincia'];
                    $row[$provinceField] = $province;
                }
                if ($country === '') {
                    $country = 'ITALIA';
                    $row[$countryField] = 'ITALIA';
                }
            }
        }

        // Arricchimento via CSV comuni (unica provincia)
        if ($city !== '' && $province === '' && $isItalian) {
            $provCandidates = $comuniByName[$this->normalizeKey($city)] ?? [];
            if (count($provCandidates) === 1) {
                $province = $provCandidates[0];
                $row[$provinceField] = $province;
            }
        }

        // Arricchimento tramite DB comune → provincia
        if ($city !== '' && $province === '' && $isItalian) {
            $provFromDb = $this->resolveComuneProvFromDb($city);
            if ($provFromDb !== null) {
                $province = $provFromDb;
                $row[$provinceField] = $province;
            }
        }

        if ($province !== '' && $city === '' && $isItalian) {
            // Non inventiamo la citta: resta vuota e finisce nel report manuale.
        }
    }

    /**
     * @param array<string,string> $row
     * @return array<int,string>
     */
    private function missingGeoFields(array $row): array
    {
        $missing = [];

        foreach ([
            'Nazione-residenza',
            'Provincia-residenza',
            'Citta-residenza',
            'Nazione-Anagrafica',
            'Provincia-Anagrafica',
            'Citta-Anagrafica',
        ] as $field) {
            if (trim((string) ($row[$field] ?? '')) === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * @param array<string,string> $before
     * @param array<string,string> $after
     */
    private function isUpdated(array $before, array $after): bool
    {
        foreach ([
            'Nazione-residenza',
            'Provincia-residenza',
            'Citta-residenza',
            'Cap',
            'Nazione-Anagrafica',
            'Provincia-Anagrafica',
            'Citta-Anagrafica',
        ] as $field) {
            if ((string) ($before[$field] ?? '') !== (string) ($after[$field] ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string,bool> $statiSet
     */
    private function looksItalianCountry(string $country, array $statiSet): bool
    {
        $c = trim($country);
        if ($c === '') {
            return true;
        }

        $up = mb_strtoupper($c);
        if ($up === 'ITALIA') {
            return true;
        }

        return !isset($statiSet[$up]);
    }

    private function normalizeKey(string $value): string
    {
        $ascii = Str::of($value)->ascii()->upper()->value();
        $ascii = preg_replace('/[^A-Z0-9]+/', ' ', $ascii) ?? $ascii;
        return trim(preg_replace('/\s+/', ' ', $ascii) ?? $ascii);
    }

    /**
     * @return array{comune:string,provincia:string,nazione:string}|null
     */
    private function resolveCapFromDb(string $cap): ?array
    {
        if (!$this->dbEnabled) {
            return null;
        }

        if (array_key_exists($cap, $this->capCache)) {
            return $this->capCache[$cap];
        }

        try {
            $result = DB::table('geo_cap')
                ->join('geo_comuni_cap', 'geo_cap.id', '=', 'geo_comuni_cap.geo_cap_id')
                ->join('geo_comuni', 'geo_comuni_cap.geo_comune_id', '=', 'geo_comuni.id')
                ->join('geo_province', 'geo_comuni.geo_provincia_id', '=', 'geo_province.id')
                ->where('geo_cap.cap', $cap)
                ->orderByDesc('geo_comuni_cap.principale')
                ->orderBy('geo_comuni_cap.priorita')
                ->select(['geo_comuni.nome as comune', 'geo_province.sigla as sigla'])
                ->first();

            if ($result === null) {
                $this->capCache[$cap] = null;
                return null;
            }

            $data = [
                'comune' => mb_strtoupper((string) $result->comune),
                'provincia' => (string) $result->sigla,
                'nazione' => 'ITALIA',
            ];
            $this->capCache[$cap] = $data;
            return $data;
        } catch (\Throwable) {
            $this->capCache[$cap] = null;
            return null;
        }
    }

    private function resolveComuneProvFromDb(string $cityName): ?string
    {
        if (!$this->dbEnabled) {
            return null;
        }

        $key = $this->normalizeKey($cityName);
        if (array_key_exists($key, $this->comuneProvCache)) {
            return $this->comuneProvCache[$key];
        }

        try {
            $upper = mb_strtoupper(trim($cityName));
            $results = DB::table('geo_comuni')
                ->join('geo_province', 'geo_comuni.geo_provincia_id', '=', 'geo_province.id')
                ->where(DB::raw('UPPER(geo_comuni.nome)'), $upper)
                ->pluck('geo_province.sigla')
                ->all();

            if (count($results) === 1) {
                $sigla = (string) $results[0];
                $this->comuneProvCache[$key] = $sigla;
                return $sigla;
            }

            $this->comuneProvCache[$key] = null;
            return null;
        } catch (\Throwable) {
            $this->comuneProvCache[$key] = null;
            return null;
        }
    }

    private function checkDbAvailable(): bool
    {
        try {
            DB::table('geo_cap')->limit(1)->count();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
