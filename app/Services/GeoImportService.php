<?php

namespace App\Services;

use App\Models\GeoCap;
use App\Models\GeoComune;
use App\Models\GeoComuneCap;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Intl\Countries;

class GeoImportService
{
    public function import(bool $fresh = false): array
    {
        if ($fresh) {
            $this->freshImport();
        }

        $stats = [
            'nazioni' => 0,
            'regioni' => 0,
            'province' => 0,
            'comuni' => 0,
            'cap' => 0,
            'comuni_cap' => 0,
        ];

        $nazioni = $this->loadJson('gi_nazioni.json');
        $regioni = $this->loadJson('gi_regioni.json');
        $province = $this->loadJson('gi_province.json');
        $comuni = $this->loadJson('gi_comuni.json');
        $comuniCap = $this->loadJson('gi_comuni_cap.json');

        DB::transaction(function () use (&$stats, $nazioni, $regioni, $province, $comuni, $comuniCap) {
            $nationMap = $this->importNazioni($nazioni, $stats);
            $regionMap = $this->importRegion($regioni, $stats, $nationMap);
            $provinceMap = $this->importProvince($province, $stats, $regionMap);
            $comuneMap = $this->importComuni($comuni, $stats, $provinceMap);
            $this->importCapAndPivot($comuniCap, $stats, $comuneMap);
        });

        return $stats;
    }

    protected function freshImport(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        GeoComuneCap::truncate();
        GeoCap::truncate();
        GeoComune::truncate();
        GeoProvincia::truncate();
        GeoRegione::truncate();
        GeoNazione::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function loadJson(string $filename): array
    {
        $paths = [
            storage_path("app/{$filename}"),
            base_path("reference/libreria/geo/{$filename}"),
            base_path($filename),
        ];

        foreach ($paths as $path) {
            if (is_readable($path)) {
                $content = file_get_contents($path);
                $json = json_decode($content, true);
                return $json ?? [];
            }
        }

        throw new \RuntimeException("File JSON non trovato: {$filename}");
    }

    protected function importNazioni(array $rows, array &$stats): array
    {
        $namesIt = Countries::getNames('it');
        $upperToIso = [];
        foreach ($namesIt as $iso => $name) {
            $upperToIso[Str::upper($name)] = $iso;
        }

        $map = [
            'byIso' => [],
            'italiaId' => null,
        ];

        foreach ($rows as $row) {
            $name = trim($row['denominazione_nazione'] ?? $row['denominazione'] ?? '');
            if ($name === '') {
                continue;
            }
            $cit = trim($row['denominazione_cittadinanza'] ?? $row['cittadinanza'] ?? '') ?: null;
            $iso2 = trim($row['sigla_nazione'] ?? '');
            $iso2 = strlen($iso2) === 2 ? Str::upper($iso2) : null;
            $upperName = Str::upper($name);
            if (!$iso2 && isset($upperToIso[$upperName])) {
                $iso2 = $upperToIso[$upperName];
            }
            if (!$iso2) {
                // fallback deterministico ai primi 2 caratteri del nome (per rispettare il vincolo unique)
                $iso2 = Str::upper(Str::substr($upperName, 0, 2));
            }
            $isItalia = $iso2 === 'IT' || Str::contains($upperName, 'ITALIA');

            $nazione = GeoNazione::updateOrCreate(
                ['codice_iso2' => $iso2],
                [
                    'nome' => $name,
                    'cittadinanza' => $cit,
                    'is_italia' => $isItalia,
                ]
            );

            $map['byIso'][$iso2] = $nazione->id;
            if ($isItalia) {
                $map['italiaId'] = $nazione->id;
            }
            $stats['nazioni']++;
        }

        return $map;
    }

    protected function importRegion(array $rows, array &$stats, array $nationMap): array
    {
        $italiaId = $nationMap['italiaId'] ?? null;
        $map = [];

        foreach ($rows as $row) {
            $code = trim($row['codice_regione'] ?? '');
            $name = trim($row['denominazione_regione'] ?? $row['nome'] ?? '');
            if ($code === '' || $name === '') {
                continue;
            }
            $regione = GeoRegione::updateOrCreate(
                ['geo_nazione_id' => $italiaId, 'codice_regione' => $code],
                ['nome' => $name]
            );
            $map[$code] = $regione->id;
            $stats['regioni']++;
        }

        return $map;
    }

    protected function importProvince(array $rows, array &$stats, array $regionMap): array
    {
        $map = [];
        foreach ($rows as $row) {
            $sigla = Str::upper(trim($row['sigla_provincia'] ?? $row['sigla'] ?? ''));
            $name = trim($row['denominazione_provincia'] ?? $row['nome'] ?? '');
            $codiceRegione = trim($row['codice_regione'] ?? '');
            if ($sigla === '' || $name === '' || !$codiceRegione) {
                continue;
            }
            $regioneId = $regionMap[$codiceRegione] ?? null;
            if (!$regioneId) {
                continue;
            }
            $provincia = GeoProvincia::updateOrCreate(
                ['sigla' => $sigla],
                [
                    'geo_regione_id' => $regioneId,
                    'nome' => $name,
                    'codice_provincia' => $row['codice_sovracomunale'] ?? ($row['codice_provincia'] ?? null),
                ]
            );
            $map[$sigla] = $provincia->id;
            $stats['province']++;
        }
        return $map;
    }

    protected function importComuni(array $rows, array &$stats, array $provinceMap): array
    {
        $map = [];
        foreach ($rows as $row) {
            $siglaProvincia = Str::upper(trim($row['sigla_provincia'] ?? ''));
            $codiceIstat = trim($row['codice_istat'] ?? '');
            $name = trim($row['denominazione_ita'] ?? $row['denominazione'] ?? '');
            if ($siglaProvincia === '' || $codiceIstat === '' || $name === '') {
                continue;
            }
            $provinciaId = $provinceMap[$siglaProvincia] ?? null;
            if (!$provinciaId) {
                continue;
            }
            $lat = $row['lat'] ?? $row['latitudine'] ?? null;
            $lng = $row['lon'] ?? $row['lng'] ?? $row['longitudine'] ?? null;
            $comune = GeoComune::updateOrCreate(
                ['codice_istat' => $codiceIstat],
                [
                    'geo_provincia_id' => $provinciaId,
                    'nome' => $name,
                    'lat' => $lat !== null ? (float) $lat : null,
                    'lng' => $lng !== null ? (float) $lng : null,
                ]
            );
            $map[$codiceIstat] = $comune->id;
            $stats['comuni']++;
        }
        return $map;
    }

    protected function importCapAndPivot(array $rows, array &$stats, array $comuneMap): void
    {
        $capsByValue = [];
        $pivotPerComune = [];

        foreach ($rows as $row) {
            $capValue = trim($row['cap'] ?? '');
            $codiceIstat = trim($row['codice_istat'] ?? '');
            if ($capValue === '' || $codiceIstat === '') {
                continue;
            }
            $comuneId = $comuneMap[$codiceIstat] ?? null;
            if (!$comuneId) {
                continue;
            }

            $cap = $capsByValue[$capValue] ?? GeoCap::firstOrCreate(
                ['cap' => $capValue],
                [
                    'lat' => isset($row['lat']) ? (float) $row['lat'] : null,
                    'lng' => isset($row['lon']) ? (float) $row['lon'] : (isset($row['lng']) ? (float) $row['lng'] : null),
                ]
            );
            $capsByValue[$capValue] = $cap;

            $localita = $row['localita'] ?? '';
            $localita = $localita === null ? '' : trim((string) $localita);
            $pivot = GeoComuneCap::updateOrCreate(
                [
                    'geo_comune_id' => $comuneId,
                    'geo_cap_id' => $cap->id,
                    'localita' => $localita,
                ],
                [
                    'principale' => (bool) ($row['principale'] ?? false),
                    'priorita' => (int) ($row['priorita'] ?? 100),
                    'note' => $row['note'] ?? null,
                ]
            );

            $pivotPerComune[$comuneId][] = $pivot;
            $stats['cap'] = count($capsByValue);
            $stats['comuni_cap']++;
        }

        foreach ($pivotPerComune as $comuneId => $pivots) {
            $hasPrincipale = collect($pivots)->contains(fn ($p) => (bool) $p->principale);
            if ($hasPrincipale) {
                continue;
            }
            $best = collect($pivots)
                ->sortBy([
                    ['priorita', 'asc'],
                    fn ($p) => (int) $this->numericCap($p->cap?->cap),
                ])
                ->first();
            if ($best) {
                $best->principale = true;
                $best->save();
            }
        }
    }

    protected function numericCap(?string $cap): int
    {
        return (int) ($cap ? preg_replace('/[^0-9]/', '', $cap) : 0);
    }
}
