<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gruppo;
use App\Models\Titolo;
use App\Models\TipoCliente;
// Gruppi secondari rimossi: tabelle eliminate
use App\Models\TipoVia;
use App\Models\TipoDocumento;
use App\Models\Customers;
use App\Models\RilasciatoDa;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Schedina;
use App\Support\StrutturaCorrente;
use App\Models\GeoComune;
use App\Services\CestinoService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\RequireAuthorizedStruttura::class);
        $this->middleware(function (Request $request, $next) {
            $struttura = \App\Support\StrutturaAccess::authorized($request);
            abort_unless(\App\Support\StrutturaAccess::query($request->user())->whereKey($struttura->id)->exists(), 403);
            return $next($request);
        });
    }

    public function index()
    {
        request()->validate(['q' => ['nullable', 'string', 'max:191'], 'tipo_cliente' => ['nullable', 'string', 'max:191'], 'stato' => ['nullable', Rule::in(['bozza', 'completo'])], 'ordine' => ['nullable', Rule::in(['recenti', 'nome', 'cognome', 'codice'])], 'page' => ['nullable', 'integer', 'min:1']]);
        session()->put('clienti_origine.'.StrutturaCorrente::getId(), 'lista');
        session()->put('clienti_elenco.'.StrutturaCorrente::getId(), request()->only(['q', 'tipo_cliente', 'stato', 'ordine', 'page']));
        $q = trim((string) request('q', ''));
        $tipoCliente = trim((string) request('tipo_cliente', ''));
        $stato = trim((string) request('stato', ''));

        $ordine = request('ordine', 'recenti');
        $colonnaOrdine = ['recenti' => 'id', 'nome' => 'name', 'cognome' => 'surname', 'codice' => 'numero_cliente'][$ordine ?: 'recenti'];

        $customers = Customers::query()
            ->when($tipoCliente !== '', function ($query) use ($tipoCliente) {
                $query->where('type_housed', $tipoCliente);
            })
            ->when(in_array($stato, ['bozza', 'completo'], true), function ($query) use ($stato) {
                $ready = function ($q) {
                    foreach (['name', 'surname', 'type_housed'] as $field) $q->whereNotNull($field)->whereRaw("TRIM(`{$field}`) <> ''");
                    $q->where(function ($types) {
                        $types->where(function ($request) {
                            $request->where('type_housed', 'Richiesta')->where(function ($contacts) {
                                foreach (['email', 'phone', 'cellphone'] as $field) $contacts->orWhereRaw("TRIM(COALESCE(`{$field}`, '')) <> ''");
                            });
                        })->orWhere(function ($guest) {
                            $guest->whereIn('type_housed', ['Ospite', 'Componente']);
                            foreach (['sex', 'country_reg', 'ciudadania_reg', 'nac_reg'] as $field) $guest->whereNotNull($field)->whereRaw("TRIM(`{$field}`) <> ''");
                            $guest->where(function ($birth) {
                                $birth->where('country_reg', '<>', 'Italia')->orWhere(function ($italia) {
                                    $italia->whereRaw("TRIM(COALESCE(city_reg, '')) <> ''")->whereRaw("TRIM(COALESCE(prov_reg, '')) <> ''");
                                });
                            });
                            $guest->where(function ($document) {
                                $document->where('type_housed', 'Componente')->orWhere(function ($principal) {
                                    foreach (['type_doc_reg', 'num_doc_reg', 'country_doc_reg'] as $field) $principal->whereNotNull($field)->whereRaw("TRIM(`{$field}`) <> ''");
                                    $principal->where(function ($place) {
                                        $place->where('country_doc_reg', '<>', 'Italia')->orWhereRaw("TRIM(COALESCE(city_doc_reg, '')) <> ''");
                                    });
                                });
                            });
                        });
                    });
                };
                $stato === 'completo' ? $query->where($ready) : $query->whereNot($ready);
            })
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($inner) use ($like) {
                    $inner->where('id', 'like', $like)
                        ->orWhere('numero_cliente', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('type_housed', 'like', $like)
                        ->orWhere('country', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('group', 'like', $like)
                        ->orWhere('subgroup', 'like', $like)
                        ->orWhere('subgroup1', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('cellphone', 'like', $like);
                });
            })
            ->orderBy($colonnaOrdine, $colonnaOrdine === 'id' ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $customers->getCollection()->transform(function (Customers $customer) {
            $required = ['name', 'surname', 'type_housed'];
            if (in_array($customer->type_housed, ['Ospite', 'Componente'], true)) {
                array_push($required, 'sex', 'country_reg', 'ciudadania_reg', 'nac_reg');
                if ($customer->country_reg === 'Italia') array_push($required, 'city_reg', 'prov_reg');
                if ($customer->type_housed === 'Ospite') {
                    array_push($required, 'type_doc_reg', 'num_doc_reg', 'country_doc_reg');
                    if ($customer->country_doc_reg === 'Italia') $required[] = 'city_doc_reg';
                }
            }
            $missing = collect($required)->contains(fn ($field) => trim((string) $customer->$field) === '');
            $missing = $missing || ! in_array($customer->type_housed, ['Richiesta', 'Ospite', 'Componente'], true);
            if ($customer->type_housed === 'Richiesta') $missing = $missing || collect(['email', 'phone', 'cellphone'])->every(fn ($field) => trim((string) $customer->$field) === '');
            $customer->setAttribute('is_bozza_incompleta', $missing);
            $customer->setAttribute('display_country', $this->resolveGeoLabel($customer->country, 'country'));
            $customer->setAttribute('display_city', $this->resolveGeoLabel($customer->city, 'city'));

            return $customer;
        });

        $tipiClienteDisponibili = TipoCliente::query()->diSistema()
            ->where('attivo', true)
            ->orderBy('id')
            ->pluck('descrizione')
            ->values();

        return view('customers.list', [
            'customers' => $customers,
            'tipiClienteDisponibili' => $tipiClienteDisponibili,
            'tipoClienteSelezionato' => $tipoCliente,
            'statoSelezionato' => $stato,
        ]);
    }

    public function new() 
    { 
        $formData = $this->buildSchedaClienteData();

        return view('customers.new', $formData);
    }

    private function buildSchedaClienteData(): array
    {
        request()->validate(['tab' => ['nullable', 'string', Rule::in(['steparrow-gen-info', 'steparrow-description-info', 'steparrow-azienda-info', 'steparrow-contact-info'])]]);
        app(\App\Services\TitoliStrutturaService::class)->initialize((int) \App\Support\StrutturaCorrente::getId());
        $groups = Schema::hasTable('gruppi')
            ? Gruppo::query()->orderBy('livello')->orderBy('nome')->get(['id', 'nome', 'livello', 'parent_id'])
            : collect();
        $gruppiLivello1 = $groups->where('livello', 1)->values();
        $gruppiLivello2 = $groups->where('livello', 2)->values();
        $gruppiLivello3 = $groups->where('livello', 3)->values();
        $tipiClienti = Schema::hasTable('tipo_cliente')
            ? TipoCliente::query()->diSistema()
                ->where('attivo', true)
                ->orderBy('id')
                ->get(['id', 'codice', 'descrizione'])
            : collect();
        $titoli = Schema::hasTable('titolo')
            ? Titolo::query()
                ->when(Schema::hasColumn('titolo', 'attivo'), fn($query) => $query->where('attivo', true))
                ->orderBy('nome')
                ->get(['id', 'nome'])
            : collect();
        $tipiVia = TipoVia::query()->where('attivo', true)->orderBy('nome')->get(['id', 'nome as name']);
        $tipiDocumento = TipoDocumento::query()->orderBy('descrizione')->get(['id', 'descrizione as name']);
        $nations     = GeoNazione::query()->orderBy('nome')->get(['id', 'nome', 'cittadinanza', 'codice_iso2']);
        $regions     = GeoRegione::query()->orderBy('nome')->get(['id', 'nome']);
        $provinces     = GeoProvincia::query()->orderBy('nome')->get(['id', 'nome', 'sigla']);
        $ciudades     = GeoComune::orderBy('nome')->get(['id', 'nome']);
        $rilasciatoDa = Schema::hasTable('rilasciato_da')
            ? RilasciatoDa::query()
                ->when(Schema::hasColumn('rilasciato_da', 'attivo'), fn($query) => $query->where('attivo', true))
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();
        $cittadinanze = Schema::hasTable('geo_nazioni')
            ? GeoNazione::query()
                ->whereNotNull('cittadinanza')
                ->where('cittadinanza', '<>', '')
                ->orderBy('cittadinanza')
                ->pluck('cittadinanza')
                ->unique()
                ->values()
            : collect();
        $geoNazioni = Schema::hasTable('geo_nazioni')
            ? GeoNazione::query()
                ->orderBy('nome')
                ->get(['id', 'nome', 'cittadinanza', 'codice_iso2', 'is_italia'])
            : collect();
        //dd($nations);
        return [
            'groups'      => $groups,
            'gruppiLivello1' => $gruppiLivello1,
            'gruppiLivello2' => $gruppiLivello2,
            'gruppiLivello3' => $gruppiLivello3,
            'tipiClienti' => $tipiClienti,
            'titoli' => $titoli,
            'tipiVia' => $tipiVia,
            'tipiDocumento'    => $tipiDocumento,
            'nations'     => $nations,
            'regions'     => $regions,
            'provinces'     => $provinces,
            'ciudades'     => $ciudades,
            'rilasciatoDa' => $rilasciatoDa,
            'cittadinanze' => $cittadinanze,
            'geoNazioni' => $geoNazioni,
        ];
    }

   

    public function store(Request $request)
    {
        $this->validateInputShape($request);
        $this->normalizeCustomerRequest($request);
        app(\App\Services\CustomerWizardValidation::class)->validate($request);
        $this->normalizeGeoLabels($request);
        $saveMode = (string) $request->input('save_mode', $request->input('save_mode_intent', 'final'));
        $isDraft = $saveMode === 'draft';
        $tipoCliente = $this->normalizeTipoCliente($request->input('type_cliente', $request->input('type_housed')));
        $request->merge(['type_cliente' => $tipoCliente]);
        $this->validateByTipoCliente($request, $saveMode);

        $hasAzienda = $request->boolean('has_azienda');
        [$countryReg, $cittadinanzaReg] = $this->resolveNationAndCittadinanza(
            $request->input('country_reg'),
            $request->input('ciudadania_reg')
        );
        $aziendaData = $this->collectAziendaData($request, $hasAzienda);

        $customer = DB::transaction(function () use ($request, $tipoCliente, $countryReg, $cittadinanzaReg, $aziendaData) {
            DB::table('struttura')->where('id', StrutturaCorrente::getId())->lockForUpdate()->first();
            $payload = $this->buildCustomerPayload($request, $tipoCliente, $countryReg, $cittadinanzaReg, $aziendaData);
            $submission = $this->submissionKey($request, $payload);
            $existing = Customers::query()->where('wizard_submission_key', $submission['key'])->first();
            if ($existing) {
                if (! hash_equals($existing->wizard_submission_digest, $submission['digest'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['submission_id' => 'Questa scheda è già stata salvata. Riapri il cliente dall’elenco per modificarlo.']);
                }
                return $existing;
            }
            $customer = Customers::query()->create($payload);
            $customer->wizard_submission_key = $submission['key'];
            $customer->wizard_submission_digest = $submission['digest'];
            $this->ensureNumeroCliente($customer, $tipoCliente);
            $customer->save();
            return $customer;
        });

        if ($isDraft) {
            return redirect()
                ->route('customer.edit', ['id' => $customer->id, 'tab' => $request->input('active_tab')])
                ->with('warning', 'Cliente salvato a bozza. Puoi riaprire la scheda e completare i dati.')->with('customer_saved_draft_key', 'clienti.nuovo.draft.v1');
        }

        if ($saveMode === 'to_schedina') {
            return redirect()
                ->route('newschedina', ['customer_id' => $customer->id])
                ->with('success', 'Cliente salvato. La schedina nuova è pronta con i dati precompilati.')->with('customer_saved_id', $customer->id)->with('customer_saved_draft_key', 'clienti.nuovo.draft.v1');
        }

        return redirect('/clienti')->with('success', 'Cliente salvato con successo.')->with('customer_saved_id', $customer->id)->with('customer_saved_draft_key', 'clienti.nuovo.draft.v1');
    }

    public function edit($id)
    {
        $customer = Customers::query()->findOrFail($id);
        $formData = $this->buildSchedaClienteData();
        $formData['customer'] = $customer;

        return view('customers.edit', $formData);
    }

    public function show(int $id)
    {
        return $this->print($id)->with('consultazione', true);
    }

    public function print(int $id)
    {
        /** @var Customers $customer */
        $customer = Customers::query()->findOrFail($id);
        [$customer, $storicoSchedine, $storicoSintetico, $storicoSummary] = $this->buildCustomerHistoryContext($customer);

        foreach (['country_az' => 'country', 'city_az' => 'city'] as $field => $kind) {
            $customer->setAttribute($field, $this->resolveGeoLabel($customer->$field, $kind));
        }

        return view('customers.print', [
            'customer' => $customer,
            'storicoCompleto' => $storicoSchedine->map(fn (Schedina $schedina) => [
                'scheda' => $schedina->storico_label, 'arrivo' => $schedina->arrivo_date,
                'partenza' => $schedina->partenza_date, 'giorni' => $schedina->giorni_soggiorno,
                'componenti' => $schedina->componenti_totali, 'registrata_il' => $schedina->created_at,
                'osservazione' => $schedina->storico_note,
            ]),
            'storicoSummary' => $storicoSummary,
            'storicoTotale' => $storicoSchedine->count(),
        ]);
    }

    public function storico(int $id)
    {
        /** @var Customers $customer */
        $customer = Customers::query()->findOrFail($id);
        [$customer, $storicoSchedine, $storicoSintetico, $storicoSummary] = $this->buildCustomerHistoryContext($customer);

        return view('customers.storico', [
            'customer' => $customer,
            'storicoSchedine' => $storicoSchedine,
            'storicoSintetico' => $storicoSintetico,
            'storicoSummary' => $storicoSummary,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $existing = Customers::query()->findOrFail($id);
        $this->validateInputShape($request);
        $this->normalizeCustomerRequest($request);
        app(\App\Services\CustomerWizardValidation::class)->validate($request, $existing);
        $this->normalizeGeoLabels($request);
        $saveMode = (string) $request->input('save_mode', $request->input('save_mode_intent', 'final'));
        $isDraft = $saveMode === 'draft';
        $tipoCliente = $this->normalizeTipoCliente($request->input('type_cliente', $request->input('type_housed')));
        $request->merge(['type_cliente' => $tipoCliente]);
        $this->validateByTipoCliente($request, $saveMode);

        $hasAzienda = $request->boolean('has_azienda');
        [$countryReg, $cittadinanzaReg] = $this->resolveNationAndCittadinanza(
            $request->input('country_reg'),
            $request->input('ciudadania_reg')
        );
        $aziendaData = $this->collectAziendaData($request, $hasAzienda);
        $customer = DB::transaction(function () use ($request, $id, $tipoCliente, $countryReg, $cittadinanzaReg, $aziendaData) {
            DB::table('struttura')->where('id', StrutturaCorrente::getId())->lockForUpdate()->first();
            $customer = Customers::query()->lockForUpdate()->findOrFail($id);
            $customer->fill($this->buildCustomerPayload(
                $request, $tipoCliente, $countryReg, $cittadinanzaReg, $aziendaData, $customer
            ));
            $this->ensureNumeroCliente($customer, $tipoCliente);
            $customer->save();
            return $customer;
        });

        if ($isDraft) {
            return redirect()
                ->route('customer.edit', ['id' => $customer->id, 'tab' => $request->input('active_tab')])
                ->with('warning', 'Bozza cliente aggiornata. Puoi continuare a completare i dati.')->with('customer_saved_draft_key', 'clienti.edit.'.$customer->id.'.draft.v1');
        }
        if ($saveMode === 'to_schedina') {
            return redirect()
                ->route('newschedina', ['customer_id' => $customer->id])
                ->with('success', 'Cliente aggiornato. La schedina nuova è pronta con i dati precompilati.')->with('customer_saved_id', $customer->id)->with('customer_saved_draft_key', 'clienti.edit.'.$customer->id.'.draft.v1');
        }
        return redirect(\App\Support\CustomerListReturn::url())->with('success', 'Cliente aggiornato con successo.')->with('customer_saved_id', $customer->id)->with('customer_saved_draft_key', 'clienti.edit.'.$customer->id.'.draft.v1');
    }

    public function destroy($id)
    { 
        $customer = Customers::query()->findOrFail($id);
        app(CestinoService::class)->archiveModel($customer, [
            'source' => 'Clienti',
        ]);
        $customer->delete();
            return redirect()->back()->with('success', 'Cliente spostato nel cestino.'); 

    }

    private function resolveNationAndCittadinanza($countryRegInput, $cittadinanzaInput): array
    {
        $countryReg = is_string($countryRegInput) ? trim($countryRegInput) : $countryRegInput;
        $cittadinanzaReg = is_string($cittadinanzaInput) ? trim($cittadinanzaInput) : $cittadinanzaInput;
        if (empty($countryReg)) {
            return [$countryReg, $cittadinanzaReg];
        }

        $nazione = null;
        if (is_numeric($countryReg)) {
            $nazione = GeoNazione::find((int) $countryReg);
        } else {
            $countryText = (string) $countryReg;
            $nazione = GeoNazione::query()
                ->where('nome', $countryText)
                ->orWhere('codice_iso2', strtoupper($countryText))
                ->first();
        }

        if (!$nazione) {
            return [$countryReg, $cittadinanzaReg];
        }

        $countryReg = $nazione->nome;

        return [$countryReg, $cittadinanzaReg];
    }

    private function collectAziendaData(Request $request, bool $enabled): array
    {
        $fields = [
            'azienda',
            'cap_az',
            'cf_az',
            'pi_az',
            'typeaway_az',
            'address_az',
            'number_az',
            'email_az',
            'phone_az',
            'fax_az',
            'cellphone_az',
            'country_az',
            'city_az',
            'region_az',
            'province_az',
            'sdi_az',
            'website',
            'desc_az',
            'nota',
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $enabled ? $request->input($field) : null;
        }

        return $data;
    }

    private function submissionKey(Request $request, array $payload): array
    {
        $data = $payload;
        foreach (['privacy_consent_at', 'marketing_consent_at', 'communication_consent_at'] as $field) unset($data[$field]);
        ksort($data);
        $digest = hash('sha256', json_encode($data));
        $identity = implode('|', [StrutturaCorrente::getId(), auth()->id(), $request->input('submission_id', $digest)]);
        return ['key' => hash('sha256', $identity), 'digest' => $digest];
    }

    private function validateInputShape(Request $request): void
    {
        $rules = [];
        // Il GEO ufficiale invia anche gruppi annidati di campi accessori non persistiti.
        $scalarFields = array_merge((new Customers)->getFillable(), ['type_cliente', 'save_mode', 'save_mode_intent', 'has_azienda', 'active_tab', 'submission_id', 'country_reg_fallback', 'released_reg', 'grupo', 'geo_manual', 'geo_manual_reg', 'geo_manual_az', '_token', '_method']);
        foreach ($scalarFields as $field) {
            $rules[$field] = ['nullable', function ($attribute, $value, $fail) {
                if (! is_scalar($value)) $fail('Il campo deve contenere un valore singolo.');
            }];
        }
        foreach (['privacy_consent', 'marketing_consent', 'communication_consent', 'has_azienda'] as $field) $rules[$field] = ['nullable', 'boolean'];
        Validator::make($request->all(), $rules)->validate();
    }

    private function normalizeCustomerRequest(Request $request): void
    {
        $merge = [];

        $nullableFields = [
            'group', 'subgroup', 'subgroup1', 'type', 'country', 'city', 'region', 'province', 'cap',
            'typeaway', 'address', 'number', 'email', 'phone', 'fax', 'cellphone', 'observation',
            'country_reg', 'region_reg', 'prov_reg', 'city_reg', 'cap_reg', 'ciudadania_reg', 'nac_reg',
            'type_doc_reg', 'num_doc_reg', 'date_pub_reg', 'expire_reg', 'rilasciato_reg', 'country_doc_reg', 'city_doc_reg', 'observation_reg',
            'azienda', 'cap_az', 'cf_az', 'pi_az', 'typeaway_az', 'address_az', 'number_az', 'email_az', 'phone_az', 'fax_az', 'cellphone_az',
            'country_az', 'city_az', 'region_az', 'province_az', 'sdi_az', 'website', 'desc_az', 'nota',
        ];

        foreach ($nullableFields as $field) {
            if (!$request->exists($field)) {
                continue;
            }

            $raw = $request->input($field);
            if (!is_string($raw)) {
                continue;
            }

            $normalized = trim($raw);
            if ($normalized === '' || in_array(mb_strtolower($normalized), ['null', 'undefined'], true)) {
                $merge[$field] = null;
            }
        }

        foreach (['name', 'surname'] as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '') {
                $merge[$field] = preg_replace('/\s+/', ' ', $value);
            }
        }

        foreach (['cf_az', 'pi_az', 'sdi_az'] as $field) {
            $value = strtoupper(trim((string) $request->input($field, '')));
            if ($value !== '') {
                $merge[$field] = preg_replace('/\s+/', '', $value);
            }
        }

        foreach (['region_reg', 'cap_reg', 'country_doc_reg', 'city_doc_reg'] as $field) {
            $value = trim((string) $request->input($field, ''));
            if ($value !== '') {
                $merge[$field] = preg_replace('/\s+/', ' ', $value);
            }
        }

        if ($request->has('geo_manual_reg')) {
            $merge['geo_manual_reg'] = $request->boolean('geo_manual_reg');
        }

        foreach (['privacy_consent', 'marketing_consent', 'communication_consent'] as $field) {
            if ($request->exists($field)) {
                $merge[$field] = $request->boolean($field);
            }
        }

        if (!empty($merge)) {
            $request->merge($merge);
        }
        $address = app(\App\Services\CustomerAddressNormalization::class)->normalize(
            $request->only(['typeaway', 'address', 'number']), (int) StrutturaCorrente::getId()
        );
        if ($address['errors']) throw \Illuminate\Validation\ValidationException::withMessages($address['errors']);
        $request->merge($address['data']);
    }

    private function normalizeGeoLabels(Request $request): void
    {
        foreach (['typeaway' => [TipoVia::class, 'nome'], 'typeaway_az' => [TipoVia::class, 'nome'], 'rilasciato_reg' => [RilasciatoDa::class, 'name'], 'type_doc_reg' => [TipoDocumento::class, 'descrizione']] as $field => [$model, $column]) {
            if (ctype_digit((string) $request->input($field, ''))) {
                $record = $model::query()->find($request->input($field));
                if ($record) $request->merge([$field => $record->$column]);
            }
        }
        $request->merge($this->resolveGeoLabelsFromInput([
            'country' => $request->input('country'),
            'region' => $request->input('region'),
            'province' => $request->input('province'),
            'city' => $request->input('city'),
            'country_reg' => $request->input('country_reg'),
            'country_doc_reg' => $request->input('country_doc_reg'),
            'region_reg' => $request->input('region_reg'),
            'prov_reg' => $request->input('prov_reg'),
            'city_reg' => $request->input('city_reg'),
            'country_az' => $request->input('country_az'),
            'region_az' => $request->input('region_az'),
            'province_az' => $request->input('province_az'),
            'city_az' => $request->input('city_az'),
        ]));
    }

    private function resolveGeoLabelsFromInput(array $data): array
    {
        foreach (['country', 'country_reg', 'country_az', 'country_doc_reg'] as $field) {
            if (! isset($data[$field]) || $data[$field] === '') continue;
            $value = $data[$field];
            $nazione = is_numeric($value)
                ? GeoNazione::query()->find((int) $value)
                : GeoNazione::query()->where('nome', $value)->orWhere('codice_iso2', $value)->first();
            if ($nazione) $data[$field] = $nazione->nome;
        }

        foreach ([
            'region',
            'region_reg',
            'region_az',
        ] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $regione = GeoRegione::query()->find((int) $data[$field], ['nome']);
            if ($regione) {
                $data[$field] = $regione->nome;
            }
        }

        foreach ([
            'province',
            'prov_reg',
            'province_az',
        ] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $provincia = GeoProvincia::query()->find((int) $data[$field], ['nome', 'sigla']);
            if ($provincia) {
                $data[$field] = $provincia->sigla ?: $provincia->nome;
            }
        }

        foreach ([
            'city',
            'city_reg',
            'city_az',
            'city_doc_reg',
        ] as $field) {
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

    private function normalizeTipoCliente($value): string
    {
        return app(\App\Services\CustomerInputValidation::class)->normalizeTipoCliente($value);
    }

    private function validateByTipoCliente(Request $request, string $saveMode = 'final'): void
    {
        app(\App\Services\CustomerInputValidation::class)->validate($request, $saveMode);
    }

    private function ensureNumeroCliente(Customers $customer, string $tipoCliente): void
    {
        if (empty($customer->struttura_id)) {
            $customer->struttura_id = StrutturaCorrente::getId() ?? auth()->user()?->struttura_id;
        }

        $prefix = $this->prefixByTipoCliente($tipoCliente);
        $yearTwoDigits = $customer->created_at
            ? $customer->created_at->format('y')
            : now()->format('y');
        $expectedStart = "{$prefix}-{$yearTwoDigits}-";

        $currentCode = (string) ($customer->numero_cliente ?? '');
        if ($currentCode !== '' && str_starts_with($currentCode, $expectedStart)) {
            return;
        }

        $nextSerial = $this->nextNumeroClienteSerial($customer->struttura_id, $prefix, $yearTwoDigits);
        $customer->numero_cliente = sprintf('%s-%s-%04d', $prefix, $yearTwoDigits, $nextSerial);
    }

    private function prefixByTipoCliente(?string $tipoCliente): string
    {
        return match ($this->normalizeTipoCliente($tipoCliente)) {
            'Richiesta' => 'R',
            'Componente' => 'C',
            default => 'O',
        };
    }

    private function nextNumeroClienteSerial(?int $strutturaId, string $prefix, string $yearTwoDigits): int
    {
        $pattern = "{$prefix}-{$yearTwoDigits}-%";

        $lastSerial = Customers::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->where('numero_cliente', 'like', $pattern)
            ->whereRaw('numero_cliente REGEXP ?', ['^'.$prefix.'-'.$yearTwoDigits.'-[0-9]+$'])
            ->max(DB::raw("CAST(SUBSTRING_INDEX(numero_cliente, '-', -1) AS UNSIGNED)"));

        return ((int) $lastSerial) + 1;

    }

    private function resolveGeoLabel($value, string $field): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (!ctype_digit($raw)) {
            return $raw;
        }

        return match ($field) {
            'country' => (string) (GeoNazione::query()->whereKey((int) $raw)->value('nome') ?? $raw),
            'city' => (string) (GeoComune::query()->whereKey((int) $raw)->value('nome') ?? $raw),
            default => $raw,
        };
    }

    private function parseDateValue($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, (string) $value);
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildCustomerHistoryContext(Customers $customer): array
    {
        $customer->setAttribute('display_country', $this->resolveGeoLabel($customer->country, 'country'));
        $customer->setAttribute('display_city', $this->resolveGeoLabel($customer->city, 'city'));
        $customer->setAttribute('display_country_reg', $this->resolveGeoLabel($customer->country_reg, 'country'));
        $customer->setAttribute('display_city_reg', $this->resolveGeoLabel($customer->city_reg, 'city'));
        $customer->setAttribute('display_country_doc_reg', $this->resolveGeoLabel($customer->country_doc_reg, 'country'));
        $customer->setAttribute('display_city_doc_reg', $this->resolveGeoLabel($customer->city_doc_reg, 'city'));

        $storicoSchedine = Schedina::query()->effettive()
            ->withCount('componenti')
            ->where('customer_id', $customer->id)
            ->orderByDesc('arrive')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Schedina $schedina) {
                $arrivo = $this->parseDateValue($schedina->arrive);
                $partenza = $this->parseDateValue($schedina->departure);
                $giorni = $arrivo && $partenza ? max(1, $arrivo->diffInDays($partenza)) : null;

                $schedina->setAttribute('arrivo_date', $arrivo);
                $schedina->setAttribute('partenza_date', $partenza);
                $schedina->setAttribute('giorni_soggiorno', $giorni);
                $schedina->setAttribute('componenti_totali', (int) ($schedina->componenti_count ?? 0));
                $schedina->setAttribute('storico_label', $schedina->scheda ?: ('Schedina #' . $schedina->id));
                $schedina->setAttribute('storico_note', trim((string) ($schedina->observation ?? $schedina->customer_observation ?? '')));

                return $schedina;
            });

        $storicoSintetico = $storicoSchedine
            ->take(8)
            ->map(function (Schedina $schedina) {
                return [
                    'scheda' => $schedina->storico_label,
                    'arrivo' => $schedina->arrivo_date,
                    'partenza' => $schedina->partenza_date,
                    'giorni' => $schedina->giorni_soggiorno,
                    'componenti' => $schedina->componenti_totali,
                    'registrata_il' => $schedina->created_at,
                    'osservazione' => $schedina->storico_note,
                ];
            })
            ->values();

        $storicoSummary = [
            'totale_soggiorni' => $storicoSchedine->count(),
            'primo_arrivo' => optional($storicoSchedine->sortBy('arrivo_date')->first())->arrivo_date,
            'ultimo_arrivo' => optional($storicoSchedine->sortByDesc('arrivo_date')->first())->arrivo_date,
            'totale_componenti' => (int) $storicoSchedine->sum('componenti_totali'),
        ];

        return [$customer, $storicoSchedine, $storicoSintetico, $storicoSummary];
    }

    private function buildCustomerPayload(
        Request $request,
        string $tipoCliente,
        $countryReg,
        $cittadinanzaReg,
        array $aziendaData,
        ?Customers $existing = null
    ): array {
        $payload = [
            'group' => $request->input('group'),
            'subgroup' => $request->input('subgroup'),
            'subgroup1' => $request->input('subgroup1'),
            'sex' => $request->input('sex', $request->input('grupo')),
            'type_housed' => $tipoCliente,
            'type' => $request->input('type'),
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'region' => $request->input('region'),
            'province' => $request->input('province'),
            'cap' => $request->input('cap'),
            'typeaway' => $request->input('typeaway'),
            'address' => $request->input('address'),
            'number' => $request->input('number'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'fax' => $request->input('fax'),
            'cellphone' => $request->input('cellphone'),
            'observation' => $request->input('observation'),
            'privacy_consent' => $request->boolean('privacy_consent'),
            'privacy_consent_at' => $request->boolean('privacy_consent') ? (($existing?->privacy_consent ? $existing?->privacy_consent_at : null) ?: now()) : null,
            'marketing_consent' => $request->boolean('marketing_consent'),
            'marketing_consent_at' => $request->boolean('marketing_consent') ? (($existing?->marketing_consent ? $existing?->marketing_consent_at : null) ?: now()) : null,
            'communication_consent' => $request->boolean('communication_consent'),
            'communication_consent_at' => $request->boolean('communication_consent') ? (($existing?->communication_consent ? $existing?->communication_consent_at : null) ?: now()) : null,
            'country_reg' => $countryReg,
            'region_reg' => $request->input('region_reg'),
            'city_reg' => $request->input('city_reg'),
            'prov_reg' => $request->input('prov_reg'),
            'cap_reg' => $request->input('cap_reg'),
            'geo_manual_reg' => $request->boolean('geo_manual_reg'),
            'ciudadania_reg' => $cittadinanzaReg,
            'nac_reg' => $request->input('nac_reg'),
            'type_doc_reg' => $request->input('type_doc_reg'),
            'num_doc_reg' => $request->input('num_doc_reg'),
            'date_pub_reg' => $request->input('date_pub_reg'),
            'expire_reg' => $request->input('expire_reg'),
            'rilasciato_reg' => $request->input('rilasciato_reg', $request->input('released_reg')),
            'country_doc_reg' => $request->input('country_doc_reg'),
            'city_doc_reg' => $request->input('city_doc_reg'),
            'observation_reg' => $request->input('observation_reg'),
        ] + $aziendaData;

        return $this->filterClientiPayloadByExistingColumns($payload);
    }

    private function filterClientiPayloadByExistingColumns(array $payload): array
    {
        static $allowedColumns = null;

        if ($allowedColumns === null) {
            $allowedColumns = array_flip(Schema::getColumnListing('clienti'));
        }

        return array_intersect_key($payload, $allowedColumns);
    }
}
