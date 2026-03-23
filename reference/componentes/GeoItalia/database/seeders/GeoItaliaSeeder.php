<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GeoItaliaSeeder extends Seeder
{
    public function run(): void
    {
        $dataPath = $this->dataPath();
        $now = now();

        Schema::disableForeignKeyConstraints();
        DB::table('geo_comuni_cap')->truncate();
        DB::table('geo_cap')->truncate();
        DB::table('geo_comuni')->truncate();
        DB::table('geo_province')->truncate();
        DB::table('geo_regioni')->truncate();
        DB::table('geo_nazioni')->truncate();
        Schema::enableForeignKeyConstraints();

        // Nazioni
        $nazioneRows = collect($this->readJson($dataPath . '/nazioni.json'))
            ->map(function (array $row) use ($now) {
                $sigla = trim((string) ($row['sigla_nazione'] ?? $row['sigla'] ?? ''));
                $nome = trim((string) ($row['denominazione_nazione'] ?? $row['nome'] ?? ''));
                $citt = trim((string) ($row['denominazione_cittadinanza'] ?? $row['cittadinanza'] ?? ''));
                $iso2 = $this->iso2From($sigla, $nome);
                $isItalia = $iso2 === 'IT' || str_contains(Str::upper($nome), 'ITALIA');

                return [
                    'codice_iso2' => $iso2,
                    'nome' => $nome ?: ($sigla ?: 'N/A'),
                    'cittadinanza' => $citt ?: $nome,
                    'is_italia' => $isItalia,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        $nazioneRows->chunk(500)->each(fn ($chunk) => DB::table('geo_nazioni')->insert($chunk->all()));
        $italiaId = (int) DB::table('geo_nazioni')->where('is_italia', true)->value('id');
        if (!$italiaId) {
            $italiaId = (int) DB::table('geo_nazioni')->orderBy('id')->value('id');
        }

        // Regioni
        $regionMap = [];
        foreach ($this->readJson($dataPath . '/regioni.json') as $row) {
            $code = trim((string) ($row['codice_regione'] ?? ''));
            $nome = trim((string) ($row['denominazione_regione'] ?? $row['nome'] ?? ''));
            if ($code === '' || $nome === '' || !$italiaId) {
                continue;
            }
            $id = DB::table('geo_regioni')->insertGetId([
                'geo_nazione_id' => $italiaId,
                'codice_regione' => $code,
                'nome' => $nome,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $regionMap[$code] = $id;
        }

        // Province
        $provinceRows = collect($this->readJson($dataPath . '/province.json'))
            ->map(function (array $row) use ($regionMap, $now) {
                $regCode = trim((string) ($row['codice_regione'] ?? ''));
                $regId = $regionMap[$regCode] ?? null;
                if (!$regId) {
                    return null;
                }
                $sigla = trim((string) ($row['sigla_provincia'] ?? $row['sigla'] ?? ''));
                $nome = trim((string) ($row['denominazione_provincia'] ?? $row['nome'] ?? ''));

                return [
                    'geo_regione_id' => $regId,
                    'sigla' => $sigla,
                    'nome' => $nome ?: $sigla,
                    'codice_provincia' => $row['codice_sovracomunale'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter();

        $provinceRows->chunk(500)->each(fn ($chunk) => DB::table('geo_province')->insert($chunk->all()));
        $provMap = DB::table('geo_province')->pluck('id', 'sigla')->toArray();

        // Comuni
        $comuniRows = collect($this->readJson($dataPath . '/comuni.json'))
            ->map(function (array $row) use ($provMap, $now) {
                $siglaProv = trim((string) ($row['sigla_provincia'] ?? ''));
                $provId = $provMap[$siglaProv] ?? null;
                if (!$provId) {
                    return null;
                }
                $nome = trim((string) ($row['denominazione_ita'] ?? $row['denominazione_ita_altra'] ?? $row['denominazione_altra'] ?? ''));

                return [
                    'geo_provincia_id' => $provId,
                    'codice_istat' => trim((string) ($row['codice_istat'] ?? '')),
                    'nome' => $nome,
                    'lat' => $this->toDecimal($row['lat'] ?? null),
                    'lng' => $this->toDecimal($row['lon'] ?? $row['lng'] ?? null),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter();

        $comuniRows->chunk(500)->each(fn ($chunk) => DB::table('geo_comuni')->insert($chunk->all()));
        $comuneMap = DB::table('geo_comuni')->pluck('id', 'codice_istat')->toArray();

        // CAP
        $capRows = collect($this->readJson($dataPath . '/cap.json'))
            ->map(function (array $row) use ($now) {
                $cap = trim((string) ($row['cap'] ?? ''));
                return $cap === '' ? null : [
                    'cap' => $cap,
                    'lat' => $this->toDecimal($row['lat'] ?? null),
                    'lng' => $this->toDecimal($row['lon'] ?? $row['lng'] ?? null),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->unique('cap');

        $capRows->chunk(500)->each(fn ($chunk) => DB::table('geo_cap')->insert($chunk->all()));
        $capMap = DB::table('geo_cap')->pluck('id', 'cap')->toArray();

        // Pivot Comune-CAP
        $priorities = [];
        $pivotRows = [];
        foreach ($this->readJson($dataPath . '/comuni_cap.json') as $row) {
            $istat = trim((string) ($row['codice_istat'] ?? ''));
            $capVal = trim((string) ($row['cap'] ?? ''));
            $comuneId = $comuneMap[$istat] ?? null;
            $capId = $capMap[$capVal] ?? null;
            if (!$comuneId || !$capId) {
                continue;
            }
            $priorities[$comuneId] = ($priorities[$comuneId] ?? 0) + 1;
            $pivotRows[] = [
                'geo_comune_id' => $comuneId,
                'geo_cap_id' => $capId,
                'principale' => $priorities[$comuneId] === 1,
                'priorita' => $priorities[$comuneId],
                'localita' => null,
                'note' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        collect($pivotRows)
            ->chunk(500)
            ->each(fn ($chunk) => DB::table('geo_comuni_cap')->insert($chunk->all()));
    }

    protected function dataPath(): string
    {
        $local = database_path('data');
        if (file_exists($local . '/nazioni.json')) {
            return $local;
        }

        return __DIR__ . '/../data';
    }

    protected function readJson(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function iso2From(string $sigla, string $nome): string
    {
        $candidate = Str::upper($sigla);
        if (strlen($candidate) === 2) {
            return $candidate;
        }
        if ($candidate === 'ITALIA' || $nome === 'Italia') {
            return 'IT';
        }

        return Str::upper(substr(hash('crc32', $sigla ?: $nome ?: uniqid('nz', true)), 0, 2));
    }

    protected function toDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (float) str_replace(',', '.', (string) $value);
    }
}
