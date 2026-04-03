<?php

namespace App\Http\Controllers;

use App\Models\Struttura;
use App\Http\Requests\StrutturaRequest;
use App\Models\TipologiaGenerale;
use App\Models\TipologiaStruttura;
use App\Models\Classificazione;
use App\Models\GeoNazione;
use App\Models\GeoRegione;
use App\Models\GeoProvincia;
use App\Models\GeoComune;
use App\Models\GeoComuneCap;
use App\Models\StrutturaZona;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class StrutturaController extends Controller
{
    public function edit()
    {
        $currentId = \App\Support\StrutturaCorrente::getId();
        $strutturaQuery = \App\Models\Struttura::query();
        $struttura = $currentId
            ? $strutturaQuery->findOrFail($currentId)
            : $strutturaQuery->firstOrFail();
        $tipologieGenerali = TipologiaGenerale::all();
        $tipologieStruttura = TipologiaStruttura::with('generale')->get();
        $classificazioni = Classificazione::with('tipologieStruttura')->get();
        $geoComuneId = $this->resolveGeoComuneId($struttura->citta);
        $zoneOptions = $this->buildZoneOptions($struttura, $geoComuneId, 'zona');
        $localitaOptions = $this->buildZoneOptions($struttura, $geoComuneId, 'localita');

        return view('struttura.edit', [
            'struttura' => $struttura,
            'tipologieGenerali' => $tipologieGenerali,
            'tipologieStruttura' => $tipologieStruttura,
            'classificazioni' => $classificazioni,
            'zoneOptions' => $zoneOptions,
            'localitaOptions' => $localitaOptions,
        ]);
    }

    public function update(\App\Http\Requests\StrutturaRequest $request)
    {
        $currentId = \App\Support\StrutturaCorrente::getId();
        $strutturaQuery = \App\Models\Struttura::query();
        $struttura = $currentId
            ? $strutturaQuery->findOrFail($currentId)
            : $strutturaQuery->firstOrFail();
        $data = $request->validated();

        // Allinea le colonne legacy string con le scelte FK
        if (!empty($data['tipologia_generale_id'])) {
            $generale = TipologiaGenerale::find($data['tipologia_generale_id']);
            $data['tipologia_generale'] = $generale?->nome;
        }
        if (!empty($data['tipologia_struttura_id'])) {
            $tipStr = TipologiaStruttura::find($data['tipologia_struttura_id']);
            $data['tipologia_struttura'] = $tipStr?->nome;
        }
        if (array_key_exists('classificazione_id', $data)) {
            $class = $data['classificazione_id'] ? Classificazione::find($data['classificazione_id']) : null;
            $data['classificazione'] = $class?->nome;
        }

        $data = $this->normalizeGeoLabels($data);


        // Gestione upload logo struttura
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('uploads/loghi', 'public');
            $data['logo'] = 'storage/' . $path;
        } else {
            unset($data['logo']); // Non sovrascrivere se non caricato
        }

        // Gestione upload logo città (campo: logo_citta)
        if ($request->hasFile('logo_citta')) {
            $file = $request->file('logo_citta');
            $path = $file->store('uploads/loghi_citta', 'public');
            $data['logo_citta'] = 'storage/' . $path;
        } else {
            unset($data['logo_citta']);
        }

        // Gestione switch tipo_apertura
        if ($request->filled('tipo_apertura')) {
            $data['tipo_apertura'] = $request->input('tipo_apertura') === 'Annuale' ? 'Annuale' : 'Stagionale';
        } else {
            $data['tipo_apertura'] = 'Stagionale';
        }

        // Flag gestione camere reali dal gestionale
        $data['camere_reali_enabled'] = $request->boolean('camere_reali_enabled');
        $data['istat_ws_simulazione'] = $request->boolean('istat_ws_simulazione');
        $data['questura_ws_simulazione'] = $request->boolean('questura_ws_simulazione');

        // Compatibilità schema legacy: citta/localita/logo_citta vs città/località/logo_città
        $data = $this->normalizeLegacyColumnNames($data);
        $data = $this->fillMissingCoordinates($data, $request);

        $struttura->update($data);
        $geoComuneId = $this->resolveGeoComuneId($request->input('citta') ?: ($data['citta'] ?? null));
        $this->persistZoneSuggestion($struttura, $geoComuneId, 'zona', $data['zona'] ?? null);
        $this->persistZoneSuggestion($struttura, $geoComuneId, 'localita', $data['localita'] ?? null);

        return redirect()->back()->with('success', 'Struttura aggiornata con successo.');
    }

    public function zoneSuggestions(Request $request)
    {
        $currentId = \App\Support\StrutturaCorrente::getId();
        $strutturaQuery = \App\Models\Struttura::query();
        $struttura = $currentId
            ? $strutturaQuery->findOrFail($currentId)
            : $strutturaQuery->firstOrFail();

        $geoComuneId = $request->filled('geo_comune_id')
            ? (int) $request->input('geo_comune_id')
            : null;

        return response()->json([
            'zona' => $this->buildZoneOptions($struttura, $geoComuneId, 'zona'),
            'localita' => $this->buildZoneOptions($struttura, $geoComuneId, 'localita'),
        ]);
    }

    private function normalizeGeoLabels(array $data): array
    {
        if (!array_key_exists('nazione', $data) || !array_key_exists('regione', $data) || !array_key_exists('provincia', $data) || !array_key_exists('citta', $data)) {
            return $data;
        }

        if (is_numeric($data['nazione'])) {
            $nazione = GeoNazione::find((int) $data['nazione']);
            if ($nazione) {
                $data['nazione'] = $nazione->nome;
            }
        }

        if (is_numeric($data['regione'])) {
            $regione = GeoRegione::find((int) $data['regione']);
            if ($regione) {
                $data['regione'] = $regione->nome;
            }
        }

        if (is_numeric($data['provincia'])) {
            $provincia = GeoProvincia::find((int) $data['provincia']);
            if ($provincia) {
                $data['provincia'] = $provincia->sigla ?: $provincia->nome;
            }
        }

        if (is_numeric($data['citta'])) {
            $comune = GeoComune::find((int) $data['citta']);
            if ($comune) {
                $data['citta'] = $comune->nome;
            }
        }

        return $data;
    }

    private function normalizeLegacyColumnNames(array $data): array
    {
        $aliases = [
            'citta' => ['citta', 'città'],
            'localita' => ['localita', 'località'],
            'logo_citta' => ['logo_citta', 'logo_città'],
        ];

        foreach ($aliases as $logical => $candidates) {
            [$plain, $accent] = $candidates;
            $value = null;
            $hasValue = false;

            if (array_key_exists($plain, $data)) {
                $value = $data[$plain];
                $hasValue = true;
            } elseif (array_key_exists($accent, $data)) {
                $value = $data[$accent];
                $hasValue = true;
            }

            unset($data[$plain], $data[$accent]);

            if (!$hasValue) {
                continue;
            }

            if (Schema::hasColumn('struttura', $plain)) {
                $data[$plain] = $value;
                continue;
            }

            if (Schema::hasColumn('struttura', $accent)) {
                $data[$accent] = $value;
            }
        }

        return $data;
    }

    private function resolveGeoComuneId($value): ?int
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return GeoComune::query()->whereKey((int) $value)->value('id');
        }

        return GeoComune::query()
            ->where('nome', (string) $value)
            ->value('id');
    }

    private function fillMissingCoordinates(array $data, Request $request): array
    {
        $latMissing = blank($data['latitudine'] ?? null);
        $lngMissing = blank($data['longitudine'] ?? null);

        if (!$latMissing && !$lngMissing) {
            return $data;
        }

        $geoComuneId = $request->input('citta');

        if (!is_numeric($geoComuneId)) {
            $geoComuneId = $this->resolveGeoComuneId($data['citta'] ?? $request->input('citta'));
        }

        if (!$geoComuneId) {
            return $data;
        }

        $comune = GeoComune::query()->find($geoComuneId, ['lat', 'lng']);

        if (!$comune) {
            return $data;
        }

        if ($latMissing && filled($comune->lat)) {
            $data['latitudine'] = $comune->lat;
        }

        if ($lngMissing && filled($comune->lng)) {
            $data['longitudine'] = $comune->lng;
        }

        return $data;
    }

    private function buildZoneOptions(Struttura $struttura, ?int $geoComuneId, string $tipo): array
    {
        $values = collect();

        if ($tipo === 'zona') {
            $values = $values->merge([
                'Mare',
                'Monte',
                'Centro',
                'Collina',
                'Lago',
                'Stazione',
                'Fiera',
                'Porto',
                'Campagna',
                'Terme',
            ]);
        }

        $catalogo = StrutturaZona::query()
            ->where('struttura_id', $struttura->id)
            ->where('tipo', $tipo)
            ->where('attiva', true)
            ->when($geoComuneId, function ($query) use ($geoComuneId) {
                $query->where(function ($sub) use ($geoComuneId) {
                    $sub->whereNull('geo_comune_id')
                        ->orWhere('geo_comune_id', $geoComuneId);
                });
            })
            ->orderBy('ordine')
            ->orderBy('nome')
            ->pluck('nome');

        $values = $values->merge($catalogo);

        if ($tipo === 'localita' && $geoComuneId) {
            $localitaGeo = GeoComuneCap::query()
                ->where('geo_comune_id', $geoComuneId)
                ->whereNotNull('localita')
                ->where('localita', '<>', '')
                ->orderByDesc('principale')
                ->orderBy('priorita')
                ->pluck('localita');

            $values = $values->merge($localitaGeo);
        }

        $cityName = $geoComuneId
            ? GeoComune::query()->whereKey($geoComuneId)->value('nome')
            : $struttura->citta;

        if ($tipo === 'localita' && $cityName) {
            $values->push($cityName);
        }

        if ($cityName) {
            $values = $values->merge($this->collectSiblingZoneValues($struttura, $cityName, $tipo));
        }

        return $values
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item))
            ->values()
            ->all();
    }

    private function persistZoneSuggestion(Struttura $struttura, ?int $geoComuneId, string $tipo, ?string $value): void
    {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        StrutturaZona::firstOrCreate([
            'struttura_id' => $struttura->id,
            'geo_comune_id' => $geoComuneId,
            'tipo' => $tipo,
            'nome' => $value,
        ], [
            'attiva' => true,
            'ordine' => 0,
        ]);
    }

    private function resolveStrutturaCityColumn(): string
    {
        if (Schema::hasColumn('struttura', 'citta')) {
            return 'citta';
        }

        if (Schema::hasColumn('struttura', 'città')) {
            return 'città';
        }

        return 'citta';
    }

    private function collectSiblingZoneValues(Struttura $struttura, string $cityName, string $tipo)
    {
        foreach (array_unique([$this->resolveStrutturaCityColumn(), 'citta', 'città']) as $cityColumn) {
            try {
                return Struttura::query()
                    ->where('id', '<>', $struttura->id)
                    ->where($cityColumn, $cityName)
                    ->whereNotNull($tipo)
                    ->where($tipo, '<>', '')
                    ->pluck($tipo);
            } catch (QueryException $e) {
                if (!$this->isMissingColumnException($e)) {
                    throw $e;
                }
            }
        }

        return collect();
    }

    private function isMissingColumnException(QueryException $e): bool
    {
        $message = mb_strtolower($e->getMessage());

        return str_contains($message, 'unknown column')
            || str_contains($message, 'column not found');
    }
}
