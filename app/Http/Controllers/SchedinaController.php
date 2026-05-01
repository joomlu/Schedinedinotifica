<?php

namespace App\Http\Controllers;

use App\Models\Componenti;
use App\Models\Customers;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Gruppo;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\TipoDocumento;
use App\Models\TipoCliente;
use App\Models\TipoVia;
use App\Models\Titolo;
use App\Models\RilasciatoDa;
use App\Services\TassaDiSoggiornoService;
use App\Services\CestinoService;
use App\Support\StrutturaCorrente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchedinaController extends Controller
{
    protected TassaDiSoggiornoService $tassaService;

    public function __construct(TassaDiSoggiornoService $tassaService)
    {
        $this->tassaService = $tassaService;
    }

    public function index()
    {
        $q = trim((string) request('q', ''));
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        $strutturaInfo = $currentId ? Struttura::find($currentId) : null;
        if ($currentId) {
            $this->resequenceCircuitCodes($currentId, 'schedina');
        }
        [$tassaConfig, $esenzioni] = $this->loadTassaContext($strutturaInfo);

        $schedinas = Schedina::query()
            ->where('is_arrive', 0)
            ->where(function ($query) {
                $query->where('circuito', 'schedina')
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('circuito')
                            ->where('is_arrive', 0);
                    });
            })
            ->with('componenti')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('id', 'like', $like)
                        ->orWhere('scheda', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('arrive', 'like', $like)
                        ->orWhere('departure', 'like', $like)
                        ->orWhere('oa_country', 'like', $like)
                        ->orWhere('oa_city', 'like', $like)
                        ->orWhere('room', 'like', $like)
                        ->orWhere('beds', 'like', $like)
                        ->orWhere('relationship', 'like', $like);
                });
            })
            ->orderByDesc('scheda')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $schedinas->getCollection()->transform(function (Schedina $schedina) use ($tassaConfig, $esenzioni, $strutturaInfo) {
            $schedina = $this->decorateSchedina($schedina);
            $dettaglio = $this->tassaService->dettaglioSchedina(
                $schedina,
                $schedina->componenti ?? collect(),
                $tassaConfig,
                $esenzioni,
                $strutturaInfo
            );
            $schedina->tassa_totale = (float) ($dettaglio['totale'] ?? 0);
            $schedina->tassa_righe = (int) count($dettaglio['righe'] ?? []);
            $schedina->tassa_configurata = !empty($tassaConfig);
            $schedina->tassa_warning = collect($dettaglio['righe'] ?? [])
                ->pluck('motivo')
                ->filter()
                ->unique()
                ->implode(' · ');
            return $schedina;
        });

        return view('schedina.list', ['schedinas' => $schedinas]);
    }

    public function bozze()
    {
        $q = trim((string) request('q', ''));
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        $strutturaInfo = $currentId ? Struttura::find($currentId) : null;
        if ($currentId) {
            $this->resequenceCircuitCodes($currentId, 'bozza');
        }
        [$tassaConfig, $esenzioni] = $this->loadTassaContext($strutturaInfo);

        $schedinas = Schedina::query()
            ->where('is_arrive', 0)
            ->where('circuito', 'bozza')
            ->with('componenti')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('id', 'like', $like)
                        ->orWhere('scheda', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('arrive', 'like', $like)
                        ->orWhere('departure', 'like', $like)
                        ->orWhere('oa_country', 'like', $like)
                        ->orWhere('oa_city', 'like', $like)
                        ->orWhere('room', 'like', $like)
                        ->orWhere('beds', 'like', $like)
                        ->orWhere('relationship', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $schedinas->getCollection()->transform(function (Schedina $schedina) use ($tassaConfig, $esenzioni, $strutturaInfo) {
            $schedina = $this->decorateSchedina($schedina);
            $dettaglio = $this->tassaService->dettaglioSchedina(
                $schedina,
                $schedina->componenti ?? collect(),
                $tassaConfig,
                $esenzioni,
                $strutturaInfo
            );
            $schedina->tassa_totale = (float) ($dettaglio['totale'] ?? 0);
            $schedina->tassa_righe = (int) count($dettaglio['righe'] ?? []);
            $schedina->tassa_configurata = !empty($tassaConfig);
            $schedina->tassa_warning = collect($dettaglio['righe'] ?? [])
                ->pluck('motivo')
                ->filter()
                ->unique()
                ->implode(' · ');
            return $schedina;
        });

        return view('schedina.list', [
            'schedinas' => $schedinas,
            'pageTitle' => 'Schedine Bozze',
            'pageSubtitle' => 'Elenco delle schedine in compilazione non ancora complete',
            'createText' => 'Nuova bozza schedina',
        ]);
    }

    public function new(Request $request)
    {
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (!$currentId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $strutturaInfo = Struttura::find($currentId);
        if (!$strutturaInfo) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Struttura non valida.']);
        }

        $schedina = new Schedina();
        $componenti = collect();

        $customerId = (int) $request->query('customer_id', 0);
        $prefilledCustomer = $customerId > 0 ? Customers::query()->find($customerId) : null;
        [$schedina, $componenti] = $this->previewFromOldInput($request, $schedina, $componenti, $prefilledCustomer);

        [$tassaConfig, $esenzioni] = $this->loadTassaContext($strutturaInfo);
        $tassaDettaglio = $this->tassaService->dettaglioSchedina($schedina, $componenti, $tassaConfig, $esenzioni, $strutturaInfo);

        return view('schedina.new', array_merge(
            $this->commonFormData(),
            [
                'schedina' => $schedina,
                'strutturaInfo' => $strutturaInfo,
                'tassaConfig' => $tassaConfig,
                'esenzioni' => $esenzioni,
                'geoEndpoints' => [],
                'tassaDettaglio' => $tassaDettaglio,
                'componenti' => $componenti,
                'prefilledCustomer' => $prefilledCustomer,
                'nextSchedaCode' => $this->nextSchedaCode($currentId),
            ]
        ));
    }

    public function store(Request $request)
    {
        $currentId = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        if (!$currentId) {
            return back()->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.'])->withInput();
        }

        $saveMode = $this->resolveSaveMode($request);
        if ($saveMode === 'componenti') {
            return back()
                ->withInput()
                ->with('warning', 'Per salvare i componenti, salva prima la schedina principale.');
        }

        if ($saveMode === 'full') {
            $this->validateSchedinaRequest($request);
            $this->validateComponentiRows($request);
            $this->validatePeopleConsistency($request);
        } elseif ($saveMode === 'to_arrivi') {
            $this->validateArriviModeRequest($request);
        }

        $customer = $this->resolveCustomerFromRequest($request);
        $defaults = $customer ? $this->customerToSchedinaDefaults($customer) : [];

        $payload = $this->buildSchedinaPayload($request, $defaults);
        $payload['struttura_id'] = $currentId;
        $payload['customer_id'] = $customer?->id ?: $request->input('customer_id');
        $this->applySaveModeState($payload, $currentId, $saveMode, null);

        $schedina = Schedina::query()->create($payload);
        $this->syncCamere($schedina, $request);
        $this->syncComponenti($schedina, $request);
        $this->syncCircuitNumbering($currentId, null, (string) ($payload['circuito'] ?? 'schedina'));

        return $this->redirectAfterSave($schedina, $request, $saveMode, false);
    }

    public function edit(int $id)
    {
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (!$currentId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $strutturaInfo = Struttura::find($currentId);
        if (!$strutturaInfo) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Struttura non valida.']);
        }

        $schedina = Schedina::query()->findOrFail($id);
        $componenti = Componenti::query()->where('schedina_id', $id)->get();
        [$schedina, $componenti] = $this->previewFromOldInput($request = request(), $schedina, $componenti, $schedina->customer_id ? Customers::query()->find($schedina->customer_id) : null);
        [$tassaConfig, $esenzioni] = $this->loadTassaContext($strutturaInfo);
        $tassaDettaglio = $this->tassaService->dettaglioSchedina($schedina, $componenti, $tassaConfig, $esenzioni, $strutturaInfo);
        $prefilledCustomer = $schedina->customer_id ? Customers::query()->find($schedina->customer_id) : null;

        return view('schedina.edit', array_merge(
            $this->commonFormData(),
            [
                'schedina' => $schedina,
                'strutturaInfo' => $strutturaInfo,
                'tassaConfig' => $tassaConfig,
                'esenzioni' => $esenzioni,
                'geoEndpoints' => [],
                'tassaDettaglio' => $tassaDettaglio,
                'componenti' => $componenti,
                'prefilledCustomer' => $prefilledCustomer,
                'nextSchedaCode' => null,
            ]
        ));
    }

    public function copy(int $id)
    {
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        if (!$currentId) {
            return redirect()->route('strutture.seleziona.index')->withErrors(['struttura_id' => 'Seleziona una struttura per continuare.']);
        }

        $schedina = Schedina::query()
            ->where('struttura_id', $currentId)
            ->with(['camere', 'componenti'])
            ->findOrFail($id);

        $payload = $this->schedinaCopyPayload($schedina);

        return redirect()
            ->route('newschedina')
            ->withInput($payload)
            ->with('success', 'Schedina storica copiata in una nuova schedina modificabile.');
    }

    public function update(Request $request, int $id)
    {
        $schedina = Schedina::query()->findOrFail($id);
        $previousCircuit = $this->normalizeSchedaCircuit($schedina);
        $saveMode = $this->resolveSaveMode($request);

        if ($saveMode === 'componenti') {
            $this->validateComponentiRows($request);
            $this->validatePeopleConsistencyForComponentSave($request);
            $this->syncComponenti($schedina, $request);

            return redirect()
                ->route('schedina.edit', ['id' => $schedina->id, 'active_tab' => 'schedina-step-comp'])
                ->withInput($request->except(['componenti']))
                ->with('success', 'Componente salvato con successo.')
                ->with('active_tab', 'schedina-step-comp');
        }

        if ($saveMode === 'full') {
            $this->validateSchedinaRequest($request);
            $this->validateComponentiRows($request);
            $this->validatePeopleConsistency($request);
        } elseif ($saveMode === 'to_arrivi') {
            $this->validateArriviModeRequest($request);
        }

        $customer = $this->resolveCustomerFromRequest($request);
        $defaults = $customer ? $this->customerToSchedinaDefaults($customer) : [];

        $payload = $this->buildSchedinaPayload($request, $defaults);
        $payload['customer_id'] = $customer?->id ?: $request->input('customer_id');
        $this->applySaveModeState($payload, (int) $schedina->struttura_id, $saveMode, $schedina);

        $schedina->fill($payload)->save();
        $this->syncCamere($schedina, $request);
        $this->syncComponenti($schedina, $request);
        $this->syncCircuitNumbering((int) $schedina->struttura_id, $previousCircuit, (string) ($payload['circuito'] ?? $previousCircuit));

        return $this->redirectAfterSave($schedina, $request, $saveMode, true);
    }

    public function destroy(int $id)
    {
        $schedina = Schedina::query()->findOrFail($id);
        app(CestinoService::class)->archiveModel($schedina, [
            'source' => 'Schedine',
            'circuito' => 'schedina',
        ]);
        $schedina->delete();

        return redirect()->back()->with('success', 'Schedina spostata nel cestino.');
    }

    private function schedinaCopyPayload(Schedina $schedina): array
    {
        $excluded = [
            'id',
            'scheda',
            'circuito',
            'is_arrive',
            'struttura_id',
            'created_at',
            'updated_at',
            'questura_exported_at',
            'questura_export_count',
            'last_questura_export_id',
            'questura_sent_at',
            'questura_send_count',
            'last_questura_transmission_id',
            'istat_exported_at',
            'istat_export_count',
            'last_istat_export_id',
            'istat_sent_at',
            'istat_send_count',
            'last_istat_transmission_id',
            'agganciata_il',
            'agganciata_da',
        ];

        $payload = Arr::except($schedina->only($schedina->getFillable()), $excluded);

        $payload['save_mode'] = 'full';
        $payload['save_mode_intent'] = '';

        $payload['camere'] = $schedina->camere->map(function ($camera) {
            return [
                'numero_camera' => $camera->numero_camera,
                'posti_letto' => $camera->posti_letto,
                'note' => $camera->note,
                'fonte_camera' => $camera->fonte_camera,
                'camera_esterna_id' => $camera->camera_esterna_id,
            ];
        })->values()->all();

        $payload['componenti'] = $schedina->componenti->map(function ($componente) {
            return [
                'name' => $componente->name,
                'surname' => $componente->surname,
                'sex' => $componente->sex,
                'relationship' => $componente->relationship,
                'exent' => $componente->exent,
                'city_nac' => $componente->city_nac,
                'province_nac' => $componente->province_nac,
                'country_nac' => $componente->country_nac,
                'regione_nac' => $componente->regione_nac,
                'comune_nac' => $componente->comune_nac,
                'cap_nac' => $componente->cap_nac,
                'date_nac' => $componente->date_nac,
                'country' => $componente->country,
                'regione' => $componente->regione,
                'province' => $componente->province,
                'city' => $componente->city,
                'typeaway' => $componente->typeaway,
                'address' => $componente->address,
                'number' => $componente->number,
                'cap' => $componente->cap,
            ];
        })->values()->all();

        return $payload;
    }

    public function printTassa(int $id)
    {
        $schedina = Schedina::query()->findOrFail($id);
        $componenti = Componenti::query()->where('schedina_id', $id)->get();
        $currentId = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id ?? $schedina->struttura_id;
        $struttura = $currentId ? Struttura::find($currentId) : null;
        [$tassaConfig, $esenzioni] = $this->loadTassaContext($struttura);
        $dettaglio = $this->tassaService->dettaglioSchedina($schedina, $componenti, $tassaConfig, $esenzioni, $struttura);
        $arrivo = $this->tassaService->parseDate($schedina->arrive);
        $partenza = $this->tassaService->parseDate($schedina->departure);
        $logoComune = $this->resolveLogoComune($struttura);

        return view('schedina.print-tassa', [
            'schedina' => $schedina,
            'componenti' => $componenti,
            'struttura' => $struttura,
            'tassaConfig' => $tassaConfig,
            'dettaglio' => $dettaglio,
            'arrivo' => $arrivo,
            'partenza' => $partenza,
            'logoComune' => $logoComune,
        ]);
    }

    protected function commonFormData(): array
    {
        $cittadinanze = GeoNazione::query()
            ->whereNotNull('cittadinanza')
            ->where('cittadinanza', '<>', '')
            ->orderBy('cittadinanza')
            ->pluck('cittadinanza')
            ->unique()
            ->values();

        $rilasciatoDa = Schema::hasTable('rilasciato_da')
            ? RilasciatoDa::query()
                ->when(Schema::hasColumn('rilasciato_da', 'attivo'), fn($query) => $query->where('attivo', true))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return [
            'titoli' => Titolo::query()->orderBy('nome')->get(['id', 'nome as name']),
            'tipiCliente' => TipoCliente::query()
                ->when(Schema::hasColumn('tipo_cliente', 'attivo'), fn($query) => $query->where('attivo', true))
                ->orderBy('descrizione')
                ->get(['id', 'codice', 'descrizione as nome']),
            'groups' => Schema::hasTable('gruppi')
                ? Gruppo::query()->orderBy('livello')->orderBy('nome')->get(['id', 'nome', 'livello', 'parent_id'])
                : collect(),
            'gruppiLivello1' => Schema::hasTable('gruppi')
                ? Gruppo::query()->where('livello', 1)->orderBy('nome')->get(['id', 'nome', 'parent_id'])
                : collect(),
            'gruppiLivello2' => Schema::hasTable('gruppi')
                ? Gruppo::query()->where('livello', 2)->orderBy('nome')->get(['id', 'nome', 'parent_id'])
                : collect(),
            'gruppiLivello3' => Schema::hasTable('gruppi')
                ? Gruppo::query()->where('livello', 3)->orderBy('nome')->get(['id', 'nome', 'parent_id'])
                : collect(),
            'tipiVia' => TipoVia::query()->orderBy('nome')->get(['id', 'nome as name']),
            'tipiDocumento' => TipoDocumento::query()->orderBy('descrizione')->get(['id', 'descrizione as name']),
            'nations' => GeoNazione::query()->orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2']),
            'regions' => GeoRegione::query()->orderBy('nome')->get(['id', 'nome']),
            'provinces' => GeoProvincia::query()->orderBy('nome')->get(['id', 'nome', 'sigla']),
            'ciudades' => GeoComune::query()->orderBy('nome')->get(['id', 'nome']),
            'cittadinanze' => $cittadinanze,
            'rilasciatoDa' => $rilasciatoDa,
        ];
    }

    protected function loadTassaContext(?Struttura $struttura): array
    {
        $tassaConfig = $struttura ? TassaDiSoggiorno::query()->where('struttura_id', $struttura->id)->first() : null;
        $esenzioni = collect();

        if ($struttura && Schema::hasTable('tassa_esenzioni')) {
            $esenzioni = TassaEsenzione::query()
                ->where('struttura_id', $struttura->id)
                ->where('attivo', true)
                ->orderBy('ordine')
                ->orderBy('codice')
                ->get();
        }

        return [$tassaConfig, $esenzioni];
    }

    protected function validateSchedinaRequest(Request $request): void
    {
        $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'customer_type_housed' => ['nullable', 'string', 'max:100'],
            'customer_group' => ['nullable', 'string', 'max:191'],
            'customer_subgroup' => ['nullable', 'string', 'max:191'],
            'customer_subgroup1' => ['nullable', 'string', 'max:191'],
            'customer_email' => ['nullable', 'email', 'max:191'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_cellphone' => ['nullable', 'string', 'max:50'],
            'customer_fax' => ['nullable', 'string', 'max:50'],
            'customer_observation' => ['nullable', 'string'],
            'customer_anag_observation' => ['nullable', 'string'],
            'customer_privacy_consent' => ['required', 'boolean'],
            'customer_marketing_consent' => ['nullable', 'boolean'],
            'customer_communication_consent' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'sex' => ['required', 'in:M,F'],
            'relationship' => ['nullable', 'string', 'max:50'],
            'exent' => ['nullable', 'string', 'max:191'],
            'arrive' => ['required', 'date'],
            'departure' => ['required', 'date', 'after_or_equal:arrive'],
            'cant_people' => ['required', 'integer', 'min:1', 'max:999'],
            'room' => ['required', 'integer', 'min:1', 'max:999'],
            'beds' => ['required', 'integer', 'min:1', 'max:999'],
            'observation' => ['nullable', 'string'],
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
            'or_cap' => ['nullable', 'string', 'max:20'],
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
                Rule::requiredIf(function () use ($request) {
                    $country = Str::of((string) $request->input('or_published_country', ''))->ascii()->lower()->value();
                    return $country === 'italia' || str_contains($country, 'italia');
                }),
                'nullable',
                'string',
                'max:150',
            ],
            'componenti' => ['nullable', 'array'],
            'componenti.*.name' => ['nullable', 'string', 'max:100'],
            'componenti.*.surname' => ['nullable', 'string', 'max:100'],
            'componenti.*.sex' => ['nullable', 'in:M,F'],
            'componenti.*.relationship' => ['nullable', 'string', 'max:50'],
            'componenti.*.exent' => ['nullable', 'string', 'max:50'],
            'componenti.*.city_nac' => ['nullable', 'string', 'max:150'],
            'componenti.*.province_nac' => ['nullable', 'string', 'max:150'],
            'componenti.*.country_nac' => ['nullable', 'string', 'max:150'],
            'componenti.*.regione_nac' => ['nullable', 'string', 'max:150'],
            'componenti.*.comune_nac' => ['nullable', 'string', 'max:150'],
            'componenti.*.cap_nac' => ['nullable', 'string', 'max:20'],
            'componenti.*.date_nac' => ['nullable', 'date'],
            'componenti.*.country' => ['nullable', 'string', 'max:150'],
            'componenti.*.regione' => ['nullable', 'string', 'max:150'],
            'componenti.*.province' => ['nullable', 'string', 'max:150'],
            'componenti.*.city' => ['nullable', 'string', 'max:150'],
            'componenti.*.typeaway' => ['nullable', 'string', 'max:100'],
            'componenti.*.address' => ['nullable', 'string', 'max:150'],
            'componenti.*.number' => ['nullable', 'string', 'max:20'],
            'componenti.*.cap' => ['nullable', 'string', 'max:20'],
            'camere.*.numero_camera' => ['nullable', 'string', 'max:30'],
            'camere.*.posti_letto' => ['nullable', 'integer', 'min:0', 'max:20'],
            'camere.*.note' => ['nullable', 'string', 'max:255'],
            'camere.*.fonte_camera' => ['nullable', 'string', 'max:30'],
            'camere.*.camera_esterna_id' => ['nullable', 'string', 'max:100'],
            'fonte_prenotazione' => ['nullable', 'string', 'max:30'],
            'id_prenotazione_esterna' => ['nullable', 'string', 'max:100'],
            'istat_tipo_turismo' => ['nullable', 'string', 'max:30'],
            'istat_mezzo_trasporto' => ['nullable', 'string', 'max:30'],
            'istat_canale_prenotazione' => ['nullable', 'string', 'max:30'],
            'istat_titolo_studio' => ['nullable', 'string', 'max:30'],
            'istat_professione' => ['nullable', 'string', 'max:120'],
            'istat_non_turista' => ['nullable', 'boolean'],
        ], [], $this->schedinaValidationAttributes());
    }

    private function validateArriviModeRequest(Request $request): void
    {
        $request->validate([
            'customer_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'arrive' => ['required', 'date'],
            'departure' => ['nullable', 'date', 'after_or_equal:arrive'],
        ], [], $this->schedinaValidationAttributes());
    }

    private function resolveCustomerFromRequest(Request $request): ?Customers
    {
        $currentStrutturaId = StrutturaCorrente::getId() ?? $request->user()?->struttura_id;
        $customerId = (int) $request->input('customer_id', 0);
        if ($customerId <= 0 || !$currentStrutturaId) {
            return null;
        }

        $allowedStrutturaIds = $this->customerSearchStructureIds((int) $currentStrutturaId);
        $customer = Customers::query()
            ->withoutGlobalScopes()
            ->whereIn('struttura_id', $allowedStrutturaIds)
            ->find($customerId);

        if (!$customer) {
            return null;
        }

        if ((int) $customer->struttura_id === (int) $currentStrutturaId) {
            return $customer;
        }

        return $this->localizeChainCustomerForCurrentStruttura($customer, (int) $currentStrutturaId);
    }

    private function customerToSchedinaDefaults(Customers $customer): array
    {
        return [
            'customer_type_housed' => $customer->type_housed,
            'customer_group' => $customer->group,
            'customer_subgroup' => $customer->subgroup,
            'customer_subgroup1' => $customer->subgroup1,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_cellphone' => $customer->cellphone,
            'customer_fax' => $customer->fax,
            'customer_observation' => $customer->observation,
            'customer_anag_observation' => $customer->observation_reg,
            'customer_privacy_consent' => $customer->privacy_consent,
            'customer_privacy_consent_at' => $customer->privacy_consent_at,
            'customer_marketing_consent' => $customer->marketing_consent,
            'customer_marketing_consent_at' => $customer->marketing_consent_at,
            'customer_communication_consent' => $customer->communication_consent,
            'customer_communication_consent_at' => $customer->communication_consent_at,
            'type' => $customer->type,
            'name' => $customer->name,
            'surname' => $customer->surname,
            'sex' => $customer->sex,
            'oa_country' => $customer->country_reg,
            'oa_city' => $customer->city_reg,
            'oa_region' => $customer->region_reg ?: $customer->region,
            'oa_prov' => $customer->prov_reg,
            'oa_cap' => $customer->cap_reg,
            'oa_city_nac' => $customer->ciudadania_reg,
            'oa_date_nac' => $customer->nac_reg,
            'or_country' => $customer->country,
            'or_city' => $customer->city,
            'or_region' => $customer->region,
            'or_prov' => $customer->province,
            'or_cap' => $customer->cap,
            'or_typeaway' => $customer->typeaway,
            'or_address' => $customer->address,
            'or_num' => $customer->number,
            'or_doc' => $customer->num_doc_reg,
            'or_doctype' => $customer->type_doc_reg,
            'or_published_date' => $customer->date_pub_reg,
            'or_expire' => $customer->expire_reg,
            'or_published' => $customer->rilasciato_reg,
            'or_published_country' => $customer->country_doc_reg ?: $customer->country_reg,
            'or_published_city' => $customer->city_doc_reg ?: $customer->city_reg,
        ];
    }

    protected function buildSchedinaPayload(Request $request, array $defaults = []): array
    {
        $inputOr = function (string $key, $default = null) use ($request) {
            $value = $request->input($key);
            if ($value === null || $value === '') {
                return $default;
            }
            return $value;
        };

        return $this->resolveGeoLabelsFromInput([
            'customer_type_housed' => $inputOr('customer_type_housed', $defaults['customer_type_housed'] ?? null),
            'customer_group' => $inputOr('customer_group', $defaults['customer_group'] ?? null),
            'customer_subgroup' => $inputOr('customer_subgroup', $defaults['customer_subgroup'] ?? null),
            'customer_subgroup1' => $inputOr('customer_subgroup1', $defaults['customer_subgroup1'] ?? null),
            'customer_email' => $inputOr('customer_email', $defaults['customer_email'] ?? null),
            'customer_phone' => $inputOr('customer_phone', $defaults['customer_phone'] ?? null),
            'customer_cellphone' => $inputOr('customer_cellphone', $defaults['customer_cellphone'] ?? null),
            'customer_fax' => $inputOr('customer_fax', $defaults['customer_fax'] ?? null),
            'customer_observation' => $inputOr('customer_observation', $defaults['customer_observation'] ?? null),
            'customer_anag_observation' => $inputOr('customer_anag_observation', $defaults['customer_anag_observation'] ?? null),
            'customer_privacy_consent' => (bool) $request->boolean('customer_privacy_consent', (bool) ($defaults['customer_privacy_consent'] ?? false)),
            'customer_privacy_consent_at' => $request->boolean('customer_privacy_consent', (bool) ($defaults['customer_privacy_consent'] ?? false))
                ? ($inputOr('customer_privacy_consent_at', $defaults['customer_privacy_consent_at'] ?? now()))
                : null,
            'customer_marketing_consent' => (bool) $request->boolean('customer_marketing_consent', (bool) ($defaults['customer_marketing_consent'] ?? false)),
            'customer_marketing_consent_at' => $request->boolean('customer_marketing_consent', (bool) ($defaults['customer_marketing_consent'] ?? false))
                ? ($inputOr('customer_marketing_consent_at', $defaults['customer_marketing_consent_at'] ?? now()))
                : null,
            'customer_communication_consent' => (bool) $request->boolean('customer_communication_consent', (bool) ($defaults['customer_communication_consent'] ?? false)),
            'customer_communication_consent_at' => $request->boolean('customer_communication_consent', (bool) ($defaults['customer_communication_consent'] ?? false))
                ? ($inputOr('customer_communication_consent_at', $defaults['customer_communication_consent_at'] ?? now()))
                : null,
            'type' => $inputOr('type', $defaults['type'] ?? null),
            'name' => $inputOr('name', $defaults['name'] ?? null),
            'surname' => $inputOr('surname', $defaults['surname'] ?? null),
            'sex' => $inputOr('sex', $defaults['sex'] ?? null),
            'relationship' => $inputOr('relationship', 'OSPITE SINGOLO'),
            'exent' => $inputOr('exent', 'NO'),
            'arrive' => $this->normalizeDateForDb($request->input('arrive')),
            'departure' => $this->normalizeDateForDb($request->input('departure')),
            'cant_people' => $inputOr('cant_people'),
            'room' => $inputOr('room'),
            'beds' => $inputOr('beds'),
            'observation' => $inputOr('observation'),
            'oa_country' => $inputOr('oa_country', $defaults['oa_country'] ?? null),
            'oa_city' => $inputOr('oa_city', $defaults['oa_city'] ?? null),
            'oa_region' => $inputOr('oa_region', $defaults['oa_region'] ?? null),
            'oa_prov' => $inputOr('oa_prov', $defaults['oa_prov'] ?? null),
            'oa_cap' => $inputOr('oa_cap', $defaults['oa_cap'] ?? null),
            'oa_city_nac' => $inputOr('oa_city_nac', $defaults['oa_city_nac'] ?? null),
            'oa_date_nac' => $this->normalizeDateForDb($inputOr('oa_date_nac', $defaults['oa_date_nac'] ?? null)),
            'or_country' => $inputOr('or_country', $defaults['or_country'] ?? null),
            'or_city' => $inputOr('or_city', $defaults['or_city'] ?? null),
            'or_region' => $inputOr('or_region', $defaults['or_region'] ?? null),
            'or_prov' => $inputOr('or_prov', $defaults['or_prov'] ?? null),
            'or_cap' => $inputOr('or_cap', $defaults['or_cap'] ?? null),
            'or_typeaway' => $inputOr('or_typeaway', $defaults['or_typeaway'] ?? null),
            'or_address' => $inputOr('or_address', $defaults['or_address'] ?? null),
            'or_num' => $inputOr('or_num', $defaults['or_num'] ?? null),
            'or_doc' => $inputOr('or_doc', $defaults['or_doc'] ?? null),
            'or_doctype' => $inputOr('or_doctype', $defaults['or_doctype'] ?? null),
            'or_published_date' => $this->normalizeDateForDb($inputOr('or_published_date', $defaults['or_published_date'] ?? null)),
            'or_expire' => $this->normalizeDateForDb($inputOr('or_expire', $defaults['or_expire'] ?? null)),
            'or_published' => $inputOr('or_published', $defaults['or_published'] ?? null),
            'or_published_country' => $inputOr('or_published_country', $defaults['or_published_country'] ?? null),
            'or_published_city' => $inputOr('or_published_city', $defaults['or_published_city'] ?? null),
            'fonte_prenotazione' => $inputOr('fonte_prenotazione'),
            'id_prenotazione_esterna' => $inputOr('id_prenotazione_esterna'),
            'istat_tipo_turismo' => $inputOr('istat_tipo_turismo'),
            'istat_mezzo_trasporto' => $inputOr('istat_mezzo_trasporto'),
            'istat_canale_prenotazione' => $inputOr('istat_canale_prenotazione'),
            'istat_titolo_studio' => $inputOr('istat_titolo_studio'),
            'istat_professione' => $inputOr('istat_professione'),
            'istat_non_turista' => $request->boolean('istat_non_turista'),
            'agganciata_il' => $inputOr('agganciata_il'),
            'agganciata_da' => $inputOr('agganciata_da'),
        ]);
    }

    private function resolveSaveMode(Request $request): string
    {
        return (string) $request->input('save_mode', $request->input('save_mode_intent', 'full'));
    }

    private function applySaveModeState(array &$payload, int $strutturaId, string $saveMode, ?Schedina $schedina = null): void
    {
        if ($saveMode === 'draft') {
            $payload['circuito'] = 'bozza';
            $payload['is_arrive'] = 0;
            $payload['scheda'] = $schedina?->scheda ?: null;
            return;
        }

        if ($saveMode === 'to_arrivi') {
            $payload['circuito'] = 'arrivi';
            $payload['is_arrive'] = 1;
            $payload['scheda'] = $this->shouldKeepCircuitCode($schedina, 'arrivi')
                ? ($schedina?->scheda ?: $this->nextSchedaCode($strutturaId, 'arrivi'))
                : $this->nextSchedaCode($strutturaId, 'arrivi');
            $this->applyOperationalArriviDates($payload);
            return;
        }

        $payload['circuito'] = 'schedina';
        $payload['is_arrive'] = 0;
        $payload['scheda'] = $this->shouldKeepCircuitCode($schedina, 'schedina')
            ? ($schedina?->scheda ?: $this->nextSchedaCode($strutturaId, 'schedina'))
            : $this->nextSchedaCode($strutturaId, 'schedina');
    }

    private function customerSearchStructureIds(int $currentStrutturaId): array
    {
        $struttura = Struttura::query()->find($currentStrutturaId);
        if (!$struttura) {
            return [$currentStrutturaId];
        }

        if (empty($struttura->proprietario_id)) {
            return [$currentStrutturaId];
        }

        $ids = Struttura::query()
            ->where('proprietario_id', $struttura->proprietario_id)
            ->pluck('id')
            ->all();

        return !empty($ids) ? $ids : [$currentStrutturaId];
    }

    private function localizeChainCustomerForCurrentStruttura(Customers $sourceCustomer, int $currentStrutturaId): Customers
    {
        $existing = $this->findEquivalentCustomerInStruttura($sourceCustomer, $currentStrutturaId);
        if ($existing) {
            return $existing;
        }

        $copy = $sourceCustomer->replicate();
        $copy->struttura_id = $currentStrutturaId;
        $copy->numero_cliente = null;
        $copy->created_at = now();
        $copy->updated_at = now();
        $copy->save();

        $this->ensureNumeroClienteForLocalizedCustomer($copy);
        $copy->save();

        return $copy;
    }

    private function findEquivalentCustomerInStruttura(Customers $sourceCustomer, int $currentStrutturaId): ?Customers
    {
        $query = Customers::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $currentStrutturaId)
            ->where('name', $sourceCustomer->name)
            ->where('surname', $sourceCustomer->surname);

        if (!empty($sourceCustomer->num_doc_reg)) {
            return $query
                ->where('num_doc_reg', $sourceCustomer->num_doc_reg)
                ->when(!empty($sourceCustomer->nac_reg), fn ($inner) => $inner->where('nac_reg', $sourceCustomer->nac_reg))
                ->latest('id')
                ->first();
        }

        if (!empty($sourceCustomer->email)) {
            return (clone $query)
                ->where('email', $sourceCustomer->email)
                ->latest('id')
                ->first();
        }

        if (!empty($sourceCustomer->cellphone)) {
            return (clone $query)
                ->where('cellphone', $sourceCustomer->cellphone)
                ->latest('id')
                ->first();
        }

        if (!empty($sourceCustomer->phone)) {
            return (clone $query)
                ->where('phone', $sourceCustomer->phone)
                ->latest('id')
                ->first();
        }

        return null;
    }

    private function ensureNumeroClienteForLocalizedCustomer(Customers $customer): void
    {
        $prefix = $this->prefixByTipoCliente($customer->type_housed);
        $yearTwoDigits = $customer->created_at
            ? $customer->created_at->format('y')
            : now()->format('y');
        $expectedStart = "{$prefix}-{$yearTwoDigits}-";
        $currentCode = (string) ($customer->numero_cliente ?? '');

        if ($currentCode !== '' && str_starts_with($currentCode, $expectedStart)) {
            return;
        }

        $nextSerial = $this->nextNumeroClienteSerialForStruttura((int) $customer->struttura_id, $prefix, $yearTwoDigits);
        $customer->numero_cliente = sprintf('%s-%s-%04d', $prefix, $yearTwoDigits, $nextSerial);
    }

    private function prefixByTipoCliente(?string $tipoCliente): string
    {
        return match (trim((string) $tipoCliente)) {
            'Richiesta' => 'R',
            'Componente' => 'C',
            default => 'O',
        };
    }

    private function nextNumeroClienteSerialForStruttura(int $strutturaId, string $prefix, string $yearTwoDigits): int
    {
        $pattern = "{$prefix}-{$yearTwoDigits}-%";

        $lastCode = Customers::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->where('numero_cliente', 'like', $pattern)
            ->orderByDesc('numero_cliente')
            ->value('numero_cliente');

        if (!$lastCode || !preg_match('/-(\d{4})$/', $lastCode, $matches)) {
            return 1;
        }

        return ((int) $matches[1]) + 1;
    }

    private function redirectAfterSave(Schedina $schedina, Request $request, string $saveMode, bool $isUpdate)
    {
        if ($saveMode === 'draft') {
            return redirect()
                ->route('schedina.bozze')
                ->with('warning', $isUpdate ? 'Bozza schedina aggiornata: dati non completi.' : 'Bozza schedina salvata: dati non completi.');
        }

        if ($saveMode === 'to_arrivi') {
            return redirect()
                ->route('arrivals')
                ->with('success', $isUpdate ? 'Schedina salvata nel circuito Arrivi.' : 'Schedina registrata nel circuito Arrivi.');
        }

        return redirect()
            ->route('schedina')
            ->with('success', $isUpdate ? 'Schedina aggiornata con successo.' : 'Schedina creata con successo.');
    }

    private function applyOperationalArriviDates(array &$payload): void
    {
        $today = now()->startOfDay();

        $submittedArrival = $this->safeParseDateValue($payload['arrive'] ?? null);
        $submittedDeparture = $this->safeParseDateValue($payload['departure'] ?? null);

        $nights = 1;
        if ($submittedArrival && $submittedDeparture && $submittedDeparture->greaterThan($submittedArrival)) {
            $nights = max(1, $submittedArrival->diffInDays($submittedDeparture));
        }

        $payload['arrive'] = $today->toDateString();
        $payload['departure'] = $today->copy()->addDays($nights)->toDateString();
    }

    private function safeParseDateValue($value): ?Carbon
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

    protected function syncCamere(Schedina $schedina, Request $request): void
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

    protected function validateComponentiRows(Request $request): void
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
                    $errors["componenti.$index.$key"] = "Componente #".($index + 1).": campo obbligatorio ($label).";
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function validatePeopleConsistency(Request $request): void
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

    private function validatePeopleConsistencyForComponentSave(Request $request): void
    {
        $totalPeople = (int) $request->input('cant_people', 0);
        if ($totalPeople <= 0) {
            return;
        }

        $componentiCount = count($this->normalizedComponentiRows($request));
        $expectedComponenti = max(0, $totalPeople - 1);

        if ($expectedComponenti === 0 && $componentiCount > 0) {
            throw ValidationException::withMessages([
                'cant_people' => 'Con quantità persone pari a 1 non devono essere presenti componenti.',
            ]);
        }

        if ($componentiCount > $expectedComponenti) {
            throw ValidationException::withMessages([
                'cant_people' => "Con quantità persone pari a {$totalPeople} non puoi salvare più di {$expectedComponenti} componenti oltre all ospite principale.",
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

    private function previewFromOldInput(Request $request, Schedina $schedina, $componenti, ?Customers $prefilledCustomer = null): array
    {
        $old = $request->session()->getOldInput();
        if (empty($old)) {
            return [$schedina, $componenti];
        }

        $previewRequest = Request::create('/', 'POST', $old);
        $defaults = $prefilledCustomer ? $this->customerToSchedinaDefaults($prefilledCustomer) : [];
        $schedina->fill($this->buildSchedinaPayload($previewRequest, $defaults));

        if (!array_key_exists('componenti', $old)) {
            return [$schedina, $componenti];
        }

        $componentiPreview = collect($this->normalizedComponentiRows($previewRequest))
            ->map(fn (array $row) => new Componenti($this->resolveComponenteGeoLabels($row)));

        return [$schedina, $componentiPreview];
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

    protected function syncComponenti(Schedina $schedina, Request $request): void
    {
        $rows = $this->normalizedComponentiRows($request);
        $rawRows = collect($request->input('componenti', []));
        $existingCount = Componenti::query()
            ->where('schedina_id', $schedina->id)
            ->count();

        // Protegge da submit anomali del tab componenti:
        // se esistono già componenti salvati e il browser rimanda solo
        // una riga vuota/placeholder, non si cancella l elenco esistente.
        if ($existingCount > 0 && $rawRows->isNotEmpty() && empty($rows)) {
            return;
        }

        if (empty($rows)) {
            Componenti::query()
                ->where('schedina_id', $schedina->id)
                ->delete();
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

        DB::transaction(function () use ($schedina, $payload) {
            Componenti::query()
                ->where('schedina_id', $schedina->id)
                ->delete();

            Componenti::query()->insert($payload);
        });
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

    protected function nextSchedaCode(int $strutturaId, string $circuito = 'schedina'): string
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

    protected function syncCircuitNumbering(int $strutturaId, ?string $previousCircuit, string $currentCircuit): void
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

    protected function resequenceCircuitCodes(int $strutturaId, string $circuito): void
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

    protected function circuitCodePrefix(string $circuito): string
    {
        return match ($this->normalizeCircuitName($circuito)) {
            'arrivi' => 'A',
            'web' => 'W',
            default => 'S',
        };
    }

    protected function normalizeCircuitName(string $circuito): string
    {
        $value = Str::of($circuito)->trim()->lower()->value();

        return match ($value) {
            'arrivo', 'arrivi', 'to_arrivi' => 'arrivi',
            'web', 'web-checkin', 'web_checkin' => 'web',
            default => 'schedina',
        };
    }

    protected function normalizeSchedaCircuit(?Schedina $schedina): ?string
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

    protected function shouldKeepCircuitCode(?Schedina $schedina, string $targetCircuit): bool
    {
        return $schedina && $this->normalizeSchedaCircuit($schedina) === $this->normalizeCircuitName($targetCircuit);
    }

    protected function codeYearForRecord($row): string
    {
        try {
            return ($row->arrive ? Carbon::parse($row->arrive) : Carbon::parse($row->created_at))->format('y');
        } catch (\Throwable $e) {
            return now()->format('y');
        }
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
                // try next format
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveLogoComune(?Struttura $struttura): ?string
    {
        if (!$struttura) {
            return null;
        }

        $comune = GeoComune::query()->where('nome', $struttura->citta)->first();
        if ($comune && ($comune->logo_citta || $comune->logo)) {
            return $comune->logo_citta ?: $comune->logo;
        }

        return $struttura->logo_citta ?: null;
    }
}
