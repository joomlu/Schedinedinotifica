<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedina;
use App\Models\Titolo;
use App\Models\TipoVia;
use App\Models\TipoDocumento;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Customers;
use App\Models\Componenti;
use App\Models\Struttura;
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ArrivalsController extends Controller
{
    public function index(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        $q = trim((string) $request->query('q', ''));
        $this->normalizeOperationalArriviDates($struttura->id);
        $this->resequenceCircuitCodes($struttura->id, 'arrivi');

        $baseQuery = Schedina::query()
            ->where('struttura_id', $struttura->id)
            ->where('is_arrive', 1)
            ->with('componenti')
            ->when($q !== '', function ($inner) use ($q) {
                $like = '%' . $q . '%';
                $inner->where(function ($where) use ($like) {
                    $where->where('scheda', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('id_prenotazione_esterna', 'like', $like)
                        ->orWhere('oa_city', 'like', $like)
                        ->orWhere('oa_country', 'like', $like);
                });
            });

        $totaliCollection = (clone $baseQuery)->get(['id', 'arrive', 'cant_people', 'room']);

        $query = $baseQuery
            ->orderByDesc('scheda')
            ->orderByDesc('id');

        $arrivals = $query->paginate(10)->withQueryString();
        $arrivals->getCollection()->transform(function (Schedina $arrival) {
            return $this->decorateSchedina($arrival);
        });

        return view('arrivals.list', [
            'arrivals' => $arrivals,
            'struttura' => $struttura,
            'q' => $q,
            'arriviStats' => [
                'in_arrivo_oggi' => $totaliCollection->where('arrive', now()->toDateString())->count(),
                'tot_persone' => (int) $totaliCollection->sum(fn ($item) => (int) ($item->cant_people ?? 0)),
                'tot_camere' => (int) $totaliCollection->sum(fn ($item) => (int) ($item->room ?? 0)),
            ],
        ]);
    }

    public function new()
    {
        $schedina     = new Schedina();
        $schedina->arrive = now()->toDateString();
        $schedina->departure = now()->addDay()->toDateString();
        $titoli = Titolo::query()->orderBy('nome')->get(['id', 'nome as name']);
        $tipiVia = TipoVia::query()->orderBy('nome')->get(['id', 'nome as name']);
        $tipiDocumento = TipoDocumento::query()->orderBy('descrizione')->get(['id', 'descrizione as name']);
        $nations     = GeoNazione::orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2']);
        $regions     = GeoRegione::query()->orderBy('nome')->get(['id', 'nome']);
        $provinces   = GeoProvincia::query()->orderBy('nome')->get(['id', 'nome', 'sigla']);
        $ciudades    = GeoComune::orderBy('nome')->get(['id', 'nome']);
        $tassaConfig = null;
        $esenzioni   = [];
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        $strutturaInfo = $currentId ? Struttura::find($currentId) : null;
        $geoEndpoints = [];

        return view('arrivals.new', [
            'schedina'    => $schedina,
            'titoli'      => $titoli,
            'tipiVia' => $tipiVia,
            'tipiDocumento'    => $tipiDocumento,
            'nations'     => $nations,
            'regions'     => $regions,
            'provinces'   => $provinces,
            'ciudades'    => $ciudades,
            'tassaConfig' => $tassaConfig,
            'esenzioni'   => $esenzioni,
            'strutturaInfo' => $strutturaInfo,
            'geoEndpoints' => $geoEndpoints,
        ]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->input('query', ''));
        if ($query === '') {
            return response()->json([]);
        }

        $like = '%' . $query . '%';
        $results = Customers::query()
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('surname', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('numero_cliente', 'like', $like);
            })
            ->orderBy('surname')
            ->orderBy('name')
            ->limit(10)
            ->get([
                'id',
                'numero_cliente',
                'type',
                'name',
                'surname',
                'sex',
                'type_housed',
                'group',
                'subgroup',
                'subgroup1',
                'email',
                'phone',
                'fax',
                'cellphone',
                'observation',
                'country',
                'city',
                'region',
                'province',
                'cap',
                'typeaway',
                'address',
                'number',
                'country_reg',
                'region_reg',
                'city_reg',
                'prov_reg',
                'cap_reg',
                'geo_manual_reg',
                'ciudadania_reg',
                'nac_reg',
                'type_doc_reg',
                'num_doc_reg',
                'date_pub_reg',
                'expire_reg',
                'rilasciato_reg',
                'country_doc_reg',
                'city_doc_reg',
                'observation_reg',
                'privacy_consent',
                'privacy_consent_at',
                'marketing_consent',
                'marketing_consent_at',
                'communication_consent',
                'communication_consent_at',
            ]);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $struttura = $this->resolveStruttura($request);
        if (!$struttura) {
            return back()->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.'])->withInput();
        }

        $saveMode = $this->resolveSaveMode($request);

        if ($saveMode === 'full') {
            $this->validateSchedinaModeRequest($request);
            $this->validateComponentiRows($request);
            $this->validatePeopleConsistency($request);
        } elseif ($saveMode === 'to_arrivi') {
            $this->validateArriviModeRequest($request);
        }

        $payload = $this->buildSchedinaPayload($request);
        $payload['struttura_id'] = $struttura->id;
        $this->applySaveModeState($payload, $struttura->id, $saveMode);

        $schedina = Schedina::query()->create($payload);
        $this->syncCamere($schedina, $request);
        $this->syncComponenti($schedina, $request);
        $this->syncCircuitNumbering($struttura->id, null, (string) ($payload['circuito'] ?? 'arrivi'));

        return $this->redirectAfterSave($schedina, $saveMode);
    }

    public function update(Request $request)
    {
        $schedina = Schedina::find($request->id);
        $schedina->name = $request->name;
        $schedina->save();
        return redirect()->back();
    }

    public function a_schedina($id)
    {
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        $schedina = Schedina::query()
            ->where('struttura_id', $currentId)
            ->where('is_arrive', 1)
            ->findOrFail($id);

        $missing = $this->missingSchedinaFields($schedina);
        if (!empty($missing)) {
            return redirect()
                ->route('schedina.edit', ['id' => $schedina->id, 'active_tab' => $this->suggestTabForMissingFields($missing)])
                ->with('warning', 'Completa prima i dati obbligatori della schedina per convertire correttamente l\'arrivo.');
        }

        $previousCircuit = $this->normalizeSchedaCircuit($schedina);
        $schedina->is_arrive = 0;
        $schedina->circuito = 'schedina';
        $schedina->scheda = $this->nextSchedaCode((int) $schedina->struttura_id, 'schedina');
        $schedina->save();
        $this->syncCircuitNumbering((int) $schedina->struttura_id, $previousCircuit, 'schedina');

        return redirect()
            ->route('schedina.edit', ['id' => $schedina->id])
            ->with('success', 'Arrivo convertito in schedina.');
    }

    public function destroy($id)
    {
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        $schedina = Schedina::query()
            ->where('struttura_id', $currentId)
            ->where('is_arrive', 1)
            ->findOrFail($id);
        app(CestinoService::class)->archiveModel($schedina, [
            'source' => 'Arrivi',
            'entity_type' => 'Arrivo',
            'circuito' => 'arrivi',
        ]);
        $schedina->delete();
        return redirect()->back()->with('success', 'Arrivo spostato nel cestino.');
    }

    private function resolveStruttura(Request $request): Struttura
    {
        $id = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        abort_unless($id, 403, 'Struttura non selezionata.');

        return Struttura::query()->findOrFail($id);
    }

    private function nextSchedaCode(int $strutturaId, string $circuito = 'schedina'): string
    {
        $yy = now()->format('y');
        $prefix = $this->circuitCodePrefix($circuito) . '-' . $yy;
        $pattern = $prefix . '%';

        $last = Schedina::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->where('scheda', 'like', $pattern)
            ->orderByDesc('scheda')
            ->value('scheda');

        $serial = 1;
        if (is_string($last) && preg_match('/^[A-Z]-\d{2}(\d{3})$/', $last, $m)) {
            $serial = ((int) $m[1]) + 1;
        }

        return sprintf('%s-%s%03d', $this->circuitCodePrefix($circuito), $yy, $serial);
    }

    private function normalizeDateForDb($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, (string) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function missingSchedinaFields(Schedina $schedina): array
    {
        $required = [
            'customer_privacy_consent' => 'Privacy',
            'name' => 'Nome',
            'surname' => 'Cognome',
            'sex' => 'Sesso',
            'arrive' => 'Arrivo',
            'departure' => 'Partenza',
            'cant_people' => 'Quantità persone',
            'room' => 'Camera',
            'beds' => 'Letti',
            'oa_country' => 'Nazione anagrafica',
            'oa_city' => 'Città anagrafica',
            'oa_region' => 'Regione anagrafica',
            'oa_prov' => 'Provincia anagrafica',
            'oa_city_nac' => 'Cittadinanza',
            'oa_date_nac' => 'Data di nascita',
            'or_country' => 'Nazione residenza',
            'or_city' => 'Città residenza',
            'or_region' => 'Regione residenza',
            'or_prov' => 'Provincia residenza',
            'or_typeaway' => 'Tipo via',
            'or_address' => 'Indirizzo',
            'or_num' => 'Numero civico',
            'or_doc' => 'Documento',
            'or_doctype' => 'Tipo documento',
            'or_published_date' => 'Data rilascio',
            'or_expire' => 'Scadenza',
            'or_published' => 'Rilasciato da',
            'or_published_country' => 'Paese rilascio',
        ];

        $missing = [];
        foreach ($required as $field => $label) {
            $value = $schedina->{$field};
            if ($field === 'customer_privacy_consent') {
                if (!(bool) $value) {
                    $missing[$field] = $label;
                }
                continue;
            }

            if ($value === null || $value === '') {
                $missing[$field] = $label;
            }
        }

        $country = str((string) ($schedina->or_published_country ?? ''))->ascii()->lower()->value();
        if (($country === 'italia' || str_contains($country, 'italia')) && empty($schedina->or_published_city)) {
            $missing['or_published_city'] = 'Città rilascio';
        }

        $componentiCount = $schedina->componenti()->count();
        $expectedComponenti = max(0, ((int) ($schedina->cant_people ?? 0)) - 1);
        if ((int) ($schedina->cant_people ?? 0) > 0 && $componentiCount !== $expectedComponenti) {
            $missing['componenti_count'] = 'Numero componenti coerente con quantità persone';
        }

        return $missing;
    }

    private function suggestTabForMissingFields(array $missing): string
    {
        $anagFields = ['oa_country', 'oa_city', 'oa_region', 'oa_prov', 'oa_city_nac', 'oa_date_nac'];
        $resFields = ['or_country', 'or_city', 'or_region', 'or_prov', 'or_typeaway', 'or_address', 'or_num', 'or_doc', 'or_doctype', 'or_published_date', 'or_expire', 'or_published', 'or_published_country', 'or_published_city'];
        $contactFields = ['customer_privacy_consent'];

        foreach (array_keys($missing) as $field) {
            if (in_array($field, $anagFields, true)) {
                return 'schedina-step-anag';
            }
            if (in_array($field, $resFields, true)) {
                return 'schedina-step-res';
            }
            if (in_array($field, $contactFields, true)) {
                return 'schedina-step-contact';
            }
            if ($field === 'componenti_count') {
                return 'schedina-step-comp';
            }
        }

        return 'schedina-step-base';
    }

    private function resolveSaveMode(Request $request): string
    {
        return (string) $request->input('save_mode', $request->input('save_mode_intent', 'to_arrivi'));
    }

    private function validateArriviModeRequest(Request $request): void
    {
        $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'arrive' => ['nullable', 'date'],
            'departure' => ['nullable', 'date'],
        ], [], $this->schedinaValidationAttributes());
    }

    private function validateSchedinaModeRequest(Request $request): void
    {
        $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'customer_privacy_consent' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'sex' => ['required', 'in:M,F'],
            'arrive' => ['required', 'date'],
            'departure' => ['required', 'date', 'after_or_equal:arrive'],
            'cant_people' => ['required', 'integer', 'min:1', 'max:999'],
            'room' => ['required', 'integer', 'min:1', 'max:999'],
            'beds' => ['required', 'integer', 'min:1', 'max:999'],
            'oa_country' => ['required', 'string', 'max:100'],
            'oa_city' => ['required', 'string', 'max:150'],
            'oa_region' => ['required', 'string', 'max:150'],
            'oa_prov' => ['required', 'string', 'max:150'],
            'oa_city_nac' => ['required', 'string', 'max:150'],
            'oa_date_nac' => ['required', 'date'],
            'or_country' => ['required', 'string', 'max:100'],
            'or_city' => ['required', 'string', 'max:150'],
            'or_region' => ['required', 'string', 'max:150'],
            'or_prov' => ['required', 'string', 'max:150'],
            'or_typeaway' => ['required', 'string', 'max:100'],
            'or_address' => ['required', 'string', 'max:150'],
            'or_num' => ['required', 'string', 'max:20'],
            'or_doc' => ['required', 'string', 'max:150'],
            'or_doctype' => ['required', 'string', 'max:100'],
            'or_published_date' => ['required', 'date'],
            'or_expire' => ['required', 'date'],
            'or_published' => ['required', 'string', 'max:150'],
            'or_published_country' => ['required', 'string', 'max:150'],
            'or_published_city' => [
                \Illuminate\Validation\Rule::requiredIf(function () use ($request) {
                    $country = str((string) $request->input('or_published_country', ''))->ascii()->lower()->value();
                    return $country === 'italia' || str_contains($country, 'italia');
                }),
                'nullable',
                'string',
                'max:150',
            ],
        ], [], $this->schedinaValidationAttributes());
    }

    private function buildSchedinaPayload(Request $request): array
    {
        return $this->resolveGeoLabelsFromInput([
            'customer_id' => $request->input('customer_id'),
            'customer_type_housed' => $request->input('customer_type_housed'),
            'customer_group' => $request->input('customer_group'),
            'customer_subgroup' => $request->input('customer_subgroup'),
            'customer_subgroup1' => $request->input('customer_subgroup1'),
            'customer_email' => $request->input('customer_email'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_cellphone' => $request->input('customer_cellphone'),
            'customer_fax' => $request->input('customer_fax'),
            'customer_observation' => $request->input('customer_observation'),
            'customer_anag_observation' => $request->input('customer_anag_observation'),
            'customer_privacy_consent' => (bool) $request->boolean('customer_privacy_consent'),
            'customer_privacy_consent_at' => $request->boolean('customer_privacy_consent')
                ? ($request->input('customer_privacy_consent_at') ?: now())
                : null,
            'customer_marketing_consent' => (bool) $request->boolean('customer_marketing_consent'),
            'customer_marketing_consent_at' => $request->boolean('customer_marketing_consent')
                ? ($request->input('customer_marketing_consent_at') ?: now())
                : null,
            'customer_communication_consent' => (bool) $request->boolean('customer_communication_consent'),
            'customer_communication_consent_at' => $request->boolean('customer_communication_consent')
                ? ($request->input('customer_communication_consent_at') ?: now())
                : null,
            'type' => $request->input('type'),
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'sex' => $request->input('sex'),
            'relationship' => $request->input('relationship', 'OSPITE SINGOLO'),
            'exent' => $request->input('exent', 'NO'),
            'arrive' => $this->normalizeDateForDb($request->input('arrive')),
            'departure' => $this->normalizeDateForDb($request->input('departure')),
            'cant_people' => $request->input('cant_people'),
            'room' => $request->input('room'),
            'beds' => $request->input('beds'),
            'observation' => $request->input('observation'),
            'oa_country' => $request->input('oa_country'),
            'oa_city' => $request->input('oa_city'),
            'oa_region' => $request->input('oa_region'),
            'oa_prov' => $request->input('oa_prov'),
            'oa_cap' => $request->input('oa_cap'),
            'oa_city_nac' => $request->input('oa_city_nac'),
            'oa_date_nac' => $this->normalizeDateForDb($request->input('oa_date_nac')),
            'or_country' => $request->input('or_country'),
            'or_city' => $request->input('or_city'),
            'or_region' => $request->input('or_region'),
            'or_prov' => $request->input('or_prov'),
            'or_cap' => $request->input('or_cap'),
            'or_typeaway' => $request->input('or_typeaway'),
            'or_address' => $request->input('or_address'),
            'or_num' => $request->input('or_num'),
            'or_doc' => $request->input('or_doc'),
            'or_doctype' => $request->input('or_doctype'),
            'or_published_date' => $this->normalizeDateForDb($request->input('or_published_date')),
            'or_expire' => $this->normalizeDateForDb($request->input('or_expire')),
            'or_published' => $request->input('or_published'),
            'or_published_country' => $request->input('or_published_country'),
            'or_published_city' => $request->input('or_published_city'),
            'fonte_prenotazione' => $request->input('fonte_prenotazione'),
            'id_prenotazione_esterna' => $request->input('id_prenotazione_esterna'),
            'istat_tipo_turismo' => $request->input('istat_tipo_turismo'),
            'istat_mezzo_trasporto' => $request->input('istat_mezzo_trasporto'),
            'istat_canale_prenotazione' => $request->input('istat_canale_prenotazione'),
            'istat_titolo_studio' => $request->input('istat_titolo_studio'),
            'istat_professione' => $request->input('istat_professione'),
            'istat_non_turista' => $request->boolean('istat_non_turista'),
            'agganciata_il' => $request->input('agganciata_il'),
            'agganciata_da' => $request->input('agganciata_da'),
        ]);
    }

    private function applySaveModeState(array &$payload, int $strutturaId, string $saveMode): void
    {
        if ($saveMode === 'draft') {
            $payload['circuito'] = 'bozza';
            $payload['is_arrive'] = 0;
            $payload['scheda'] = null;
            return;
        }

        if ($saveMode === 'to_arrivi') {
            $payload['circuito'] = 'arrivi';
            $payload['is_arrive'] = 1;
            $payload['scheda'] = $this->nextSchedaCode($strutturaId, 'arrivi');
            $this->applyOperationalArriviDates($payload);
            return;
        }

        $payload['circuito'] = 'schedina';
        $payload['is_arrive'] = 0;
        $payload['scheda'] = $this->nextSchedaCode($strutturaId, 'schedina');
    }

    private function redirectAfterSave(Schedina $schedina, string $saveMode)
    {
        if ($saveMode === 'draft') {
            return redirect()
                ->route('schedina.edit', ['id' => $schedina->id])
                ->with('warning', 'Bozza schedina salvata: dati non completi.');
        }

        if ($saveMode === 'to_arrivi') {
            return redirect()
                ->route('arrivals')
                ->with('success', 'Arrivo registrato.');
        }

        return redirect()
            ->route('schedina.edit', ['id' => $schedina->id])
            ->with('success', 'Schedina creata con successo.');
    }

    private function applyOperationalArriviDates(array &$payload): void
    {
        $today = now()->startOfDay();

        $submittedArrival = $this->safeParseDate($payload['arrive'] ?? null);
        $submittedDeparture = $this->safeParseDate($payload['departure'] ?? null);

        $nights = 1;
        if ($submittedArrival && $submittedDeparture && $submittedDeparture->greaterThan($submittedArrival)) {
            $nights = max(1, $submittedArrival->diffInDays($submittedDeparture));
        }

        $payload['arrive'] = $today->toDateString();
        $payload['departure'] = $today->copy()->addDays($nights)->toDateString();
    }

    private function normalizeOperationalArriviDates(int $strutturaId): void
    {
        $today = now()->startOfDay();

        $rows = Schedina::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->where(function ($query) {
                $query->where('circuito', 'arrivi')
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('circuito')->where('is_arrive', 1);
                    });
            })
            ->get(['id', 'arrive', 'departure']);

        foreach ($rows as $row) {
            $currentArrival = $this->safeParseDate($row->arrive);
            $currentDeparture = $this->safeParseDate($row->departure);

            $nights = 1;
            if ($currentArrival && $currentDeparture && $currentDeparture->greaterThan($currentArrival)) {
                $nights = max(1, $currentArrival->diffInDays($currentDeparture));
            }

            $newArrival = $today->toDateString();
            $newDeparture = $today->copy()->addDays($nights)->toDateString();

            if ((string) $row->arrive !== $newArrival || (string) $row->departure !== $newDeparture) {
                Schedina::query()
                    ->withoutGlobalScopes()
                    ->whereKey($row->id)
                    ->update([
                        'arrive' => $newArrival,
                        'departure' => $newDeparture,
                    ]);
            }
        }
    }

    private function safeParseDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function syncCircuitNumbering(int $strutturaId, ?string $previousCircuit, string $currentCircuit): void
    {
        $circuits = collect([$previousCircuit, $currentCircuit])
            ->filter()
            ->map(fn ($c) => $this->normalizeCircuitName((string) $c))
            ->filter(fn ($c) => in_array($c, ['schedina', 'arrivi', 'web'], true))
            ->unique()
            ->values();

        foreach ($circuits as $circuito) {
            $this->resequenceCircuitCodes($strutturaId, $circuito);
        }
    }

    private function resequenceCircuitCodes(int $strutturaId, string $circuito): void
    {
        $rows = Schedina::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->where(function ($query) use ($circuito) {
                $normalized = $this->normalizeCircuitName($circuito);

                if ($normalized === 'arrivi') {
                    $query->where('circuito', 'arrivi')
                        ->orWhere(function ($legacy) {
                            $legacy->whereNull('circuito')
                                ->where('is_arrive', 1);
                        });
                    return;
                }

                if ($normalized === 'web') {
                    $query->where('circuito', 'web');
                    return;
                }

                $query->where('circuito', 'schedina')
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('circuito')
                            ->where('is_arrive', 0)
                            ->whereNotNull('scheda')
                            ->where('scheda', '!=', '');
                    });
            })
            ->orderByRaw('arrive IS NULL')
            ->orderBy('arrive')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'scheda', 'arrive', 'created_at']);

        if ($rows->isEmpty()) {
            return;
        }

        $prefix = $this->circuitCodePrefix($circuito);
        $grouped = $rows->groupBy(fn ($row) => $this->codeYearForRecord($row));

        foreach ($grouped as $yy => $records) {
            foreach ($records->values() as $index => $record) {
                $newCode = sprintf('%s-%s%03d', $prefix, $yy, $index + 1);
                if ((string) $record->scheda !== $newCode) {
                    Schedina::query()->withoutGlobalScopes()->whereKey($record->id)->update(['scheda' => $newCode]);
                }
            }
        }
    }

    private function circuitCodePrefix(string $circuito): string
    {
        return match ($this->normalizeCircuitName($circuito)) {
            'arrivi' => 'A',
            'web' => 'W',
            default => 'S',
        };
    }

    private function normalizeCircuitName(string $circuito): string
    {
        $value = strtolower(trim($circuito));

        return match ($value) {
            'arrivo', 'arrivi', 'to_arrivi' => 'arrivi',
            'web', 'web-checkin', 'web_checkin' => 'web',
            default => 'schedina',
        };
    }

    private function normalizeSchedaCircuit(?Schedina $schedina): ?string
    {
        if (!$schedina) {
            return null;
        }

        $circuito = trim((string) ($schedina->circuito ?? ''));
        if ($circuito !== '') {
            return $this->normalizeCircuitName($circuito);
        }

        return (bool) ($schedina->is_arrive ?? false) ? 'arrivi' : 'schedina';
    }

    private function codeYearForRecord($row): string
    {
        try {
            return ($row->arrive ? Carbon::parse($row->arrive) : Carbon::parse($row->created_at))->format('y');
        } catch (\Throwable $e) {
            return now()->format('y');
        }
    }

    private function validatePeopleConsistency(Request $request): void
    {
        $totalPeople = (int) $request->input('cant_people', 0);
        if ($totalPeople <= 0) {
            return;
        }

        $componentiCount = count($this->normalizedComponentiRows($request));
        $expectedComponenti = max(0, $totalPeople - 1);

        if ($componentiCount !== $expectedComponenti) {
            $message = $expectedComponenti === 0
                ? 'Con quantità persone pari a 1 non devono essere presenti componenti.'
                : "Con quantità persone pari a {$totalPeople} devono essere presenti {$expectedComponenti} componenti oltre all ospite principale.";

            throw ValidationException::withMessages([
                'cant_people' => $message,
            ]);
        }
    }

    private function schedinaValidationAttributes(): array
    {
        return [
            'customer_privacy_consent' => 'privacy',
            'name' => 'nome',
            'surname' => 'cognome',
            'sex' => 'sesso',
            'arrive' => 'arrivo',
            'departure' => 'partenza',
            'cant_people' => 'quantità persone',
            'room' => 'camera',
            'beds' => 'letti',
            'oa_country' => 'nazione anagrafica',
            'oa_city' => 'città anagrafica',
            'oa_region' => 'regione anagrafica',
            'oa_prov' => 'provincia anagrafica',
            'oa_city_nac' => 'cittadinanza',
            'oa_date_nac' => 'data di nascita',
            'or_country' => 'nazione residenza',
            'or_city' => 'città residenza',
            'or_region' => 'regione residenza',
            'or_prov' => 'provincia residenza',
            'or_typeaway' => 'tipo via',
            'or_address' => 'indirizzo',
            'or_num' => 'numero civico',
            'or_doc' => 'documento',
            'or_doctype' => 'tipo documento',
            'or_published_date' => 'data rilascio',
            'or_expire' => 'scadenza',
            'or_published' => 'rilasciato da',
            'or_published_country' => 'paese rilascio',
            'or_published_city' => 'città rilascio',
        ];
    }

    private function normalizedComponentiRows(Request $request): array
    {
        return collect($request->input('componenti', []))
            ->map(function ($row) {
                $normalized = [];
                foreach ((array) $row as $key => $value) {
                    $normalized[$key] = is_string($value) ? trim($value) : $value;
                }
                return $normalized;
            })
            ->filter(function ($row) {
                return trim((string) ($row['name'] ?? '')) !== ''
                    || trim((string) ($row['surname'] ?? '')) !== ''
                    || trim((string) ($row['sex'] ?? '')) !== '';
            })
            ->values()
            ->all();
    }

    private function validateComponentiRows(Request $request): void
    {
        $rows = $this->normalizedComponentiRows($request);
        if (empty($rows)) {
            return;
        }

        $requiredFields = [
            'name' => 'Nome',
            'surname' => 'Cognome',
            'sex' => 'Sesso',
            'relationship' => 'Tipo alloggiato',
            'exent' => 'Esente',
            'city_nac' => 'Cittadinanza',
            'country_nac' => 'Nazione nascita',
            'regione_nac' => 'Regione nascita',
            'province_nac' => 'Provincia nascita',
            'comune_nac' => 'Città nascita',
            'city' => 'Città residenza',
            'date_nac' => 'Data di nascita',
            'country' => 'Nazione',
            'regione' => 'Regione',
            'province' => 'Provincia',
            'typeaway' => 'Tipo via',
            'address' => 'Strada',
            'number' => 'Num',
            'cap' => 'CAP',
        ];

        $errors = [];
        foreach ($rows as $index => $row) {
            foreach ($requiredFields as $key => $label) {
                if (($row[$key] ?? null) === null || $row[$key] === '') {
                    $errors["componenti.$index.$key"] = 'Componente #' . ($index + 1) . ": campo obbligatorio ($label).";
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function syncCamere(Schedina $schedina, Request $request): void
    {
        $camerePayload = collect($request->input('camere', []))
            ->map(function ($row) use ($schedina) {
                return [
                    'struttura_id' => $schedina->struttura_id,
                    'numero_camera' => $row['numero_camera'] ?? null,
                    'posti_letto' => $row['posti_letto'] ?? null,
                    'note' => $row['note'] ?? null,
                    'fonte_camera' => $row['fonte_camera'] ?? null,
                    'camera_esterna_id' => $row['camera_esterna_id'] ?? null,
                ];
            })
            ->filter(function ($row) {
                return !empty($row['numero_camera']) || !empty($row['note']) || (!is_null($row['posti_letto']));
            })
            ->values()
            ->all();

        $schedina->camere()->delete();
        if (!empty($camerePayload)) {
            $schedina->camere()->createMany($camerePayload);
        }
    }

    private function syncComponenti(Schedina $schedina, Request $request): void
    {
        $rows = $this->normalizedComponentiRows($request);

        Componenti::query()
            ->where('schedina_id', $schedina->id)
            ->delete();

        if (empty($rows)) {
            return;
        }

        $payload = collect($rows)->map(function ($row) use ($schedina) {
            return $this->resolveComponenteGeoLabels([
                'struttura_id' => $schedina->struttura_id,
                'schedina_id' => $schedina->id,
                'customer_id' => $schedina->customer_id,
                'name' => $row['name'] ?? null,
                'surname' => $row['surname'] ?? null,
                'sex' => $row['sex'] ?? null,
                'relationship' => $row['relationship'] ?? null,
                'exent' => $row['exent'] ?? null,
                'city_nac' => $row['city_nac'] ?? null,
                'province_nac' => $row['province_nac'] ?? null,
                'country_nac' => $row['country_nac'] ?? null,
                'regione_nac' => $row['regione_nac'] ?? null,
                'comune_nac' => $row['comune_nac'] ?? null,
                'cap_nac' => $row['cap_nac'] ?? null,
                'date_nac' => $this->normalizeDateForDb($row['date_nac'] ?? null),
                'country' => $row['country'] ?? null,
                'regione' => $row['regione'] ?? null,
                'province' => $row['province'] ?? null,
                'city' => $row['city'] ?? null,
                'typeaway' => $row['typeaway'] ?? null,
                'address' => $row['address'] ?? null,
                'number' => $row['number'] ?? null,
                'cap' => $row['cap'] ?? null,
            ]);
        })->all();

        Componenti::query()->insert($payload);
    }

    private function resolveGeoLabelsFromInput(array $data): array
    {
        foreach (['oa_country', 'or_country', 'or_published_country'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $nazione = GeoNazione::query()->find((int) $data[$field], ['nome']);
            if ($nazione) {
                $data[$field] = $nazione->nome;
            }
        }

        foreach (['oa_region', 'or_region'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $regione = GeoRegione::query()->find((int) $data[$field], ['nome']);
            if ($regione) {
                $data[$field] = $regione->nome;
            }
        }

        foreach (['oa_prov', 'or_prov'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $provincia = GeoProvincia::query()->find((int) $data[$field], ['nome', 'sigla']);
            if ($provincia) {
                $data[$field] = $provincia->sigla ?: $provincia->nome;
            }
        }

        foreach (['oa_city', 'or_city', 'or_published_city'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $comune = GeoComune::query()->find((int) $data[$field], ['nome']);
            if ($comune) {
                $data[$field] = $comune->nome;
            }
        }

        return $data;
    }

    private function resolveComponenteGeoLabels(array $data): array
    {
        foreach (['country_nac', 'country'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $nazione = GeoNazione::query()->find((int) $data[$field], ['nome']);
            if ($nazione) {
                $data[$field] = $nazione->nome;
            }
        }

        foreach (['regione_nac', 'regione'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $regione = GeoRegione::query()->find((int) $data[$field], ['nome']);
            if ($regione) {
                $data[$field] = $regione->nome;
            }
        }

        foreach (['province_nac', 'province'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $provincia = GeoProvincia::query()->find((int) $data[$field], ['nome', 'sigla']);
            if ($provincia) {
                $data[$field] = $provincia->sigla ?: $provincia->nome;
            }
        }

        foreach (['comune_nac', 'city'] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $comune = GeoComune::query()->find((int) $data[$field], ['nome']);
            if ($comune) {
                $data[$field] = $comune->nome;
            }
        }

        return $data;
    }

    private function decorateSchedina(Schedina $schedina): Schedina
    {
        $schedina->oa_country = $this->resolveGeoLabelValue($schedina->oa_country, GeoNazione::query(), 'nome');
        $schedina->oa_city = $this->resolveGeoLabelValue($schedina->oa_city, GeoComune::query(), 'nome');
        $schedina->or_country = $this->resolveGeoLabelValue($schedina->or_country, GeoNazione::query(), 'nome');
        $schedina->or_city = $this->resolveGeoLabelValue($schedina->or_city, GeoComune::query(), 'nome');

        return $schedina;
    }

    private function resolveGeoLabelValue($raw, $query, string $labelColumn): string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (!ctype_digit($value)) {
            return $value;
        }

        return (string) ((clone $query)->whereKey((int) $value)->value($labelColumn) ?? $value);
    }
}
