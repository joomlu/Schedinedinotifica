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

class CustomerController extends Controller
{
    public function index()
    {
        $q = trim((string) request('q', ''));
        $tipoCliente = trim((string) request('tipo_cliente', ''));
        $stato = trim((string) request('stato', ''));

        $customers = Customers::query()
            ->when($tipoCliente !== '', function ($query) use ($tipoCliente) {
                $query->where('type_housed', $tipoCliente);
            })
            ->when($stato !== '', function ($query) use ($stato) {
                if ($stato === 'bozza') {
                    $query->where(function ($inner) {
                        $inner->whereNull('name')
                            ->orWhere('name', '')
                            ->orWhereNull('surname')
                            ->orWhere('surname', '')
                            ->orWhereNull('type_housed')
                            ->orWhere('type_housed', '')
                            ->orWhere(function ($docMissing) {
                                $docMissing
                                    ->whereIn('type_housed', ['Ospite', 'Componente'])
                                    ->where(function ($required) {
                                        $required->whereNull('nac_reg')
                                            ->orWhere('nac_reg', '')
                                            ->orWhereNull('type_doc_reg')
                                            ->orWhere('type_doc_reg', '')
                                            ->orWhereNull('num_doc_reg')
                                            ->orWhere('num_doc_reg', '');
                                    });
                            });
                    });
                    return;
                }

                if ($stato === 'completo') {
                    $query->whereNotNull('name')
                        ->where('name', '<>', '')
                        ->whereNotNull('surname')
                        ->where('surname', '<>', '')
                        ->whereNotNull('type_housed')
                        ->where('type_housed', '<>', '')
                        ->where(function ($complete) {
                            $complete->whereNotIn('type_housed', ['Ospite', 'Componente'])
                                ->orWhere(function ($docReady) {
                                    $docReady->whereIn('type_housed', ['Ospite', 'Componente'])
                                        ->whereNotNull('nac_reg')
                                        ->where('nac_reg', '<>', '')
                                        ->whereNotNull('type_doc_reg')
                                        ->where('type_doc_reg', '<>', '')
                                        ->whereNotNull('num_doc_reg')
                                        ->where('num_doc_reg', '<>', '');
                                });
                        });
                }
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
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $customers->getCollection()->transform(function (Customers $customer) {
            $requiredBaseMissing = empty($customer->name) || empty($customer->surname) || empty($customer->type_housed);
            $requiredDocMissing = in_array(trim((string) $customer->type_housed), ['Ospite', 'Componente'], true)
                && (empty($customer->nac_reg) || empty($customer->type_doc_reg) || empty($customer->num_doc_reg));

            $customer->setAttribute('is_bozza_incompleta', $requiredBaseMissing || $requiredDocMissing);
            $customer->setAttribute('display_country', $this->resolveGeoLabel($customer->country, 'country'));
            $customer->setAttribute('display_city', $this->resolveGeoLabel($customer->city, 'city'));

            return $customer;
        });

        $tipiClienteDisponibili = TipoCliente::query()
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
        $groups = Schema::hasTable('gruppi')
            ? Gruppo::query()->orderBy('livello')->orderBy('nome')->get(['id', 'nome', 'livello', 'parent_id'])
            : collect();
        $gruppiLivello1 = $groups->where('livello', 1)->values();
        $gruppiLivello2 = $groups->where('livello', 2)->values();
        $gruppiLivello3 = $groups->where('livello', 3)->values();
        $tipiClienti = Schema::hasTable('tipo_cliente')
            ? TipoCliente::query()
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
        $tipiVia = TipoVia::query()->orderBy('nome')->get(['id', 'nome as name']);
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
        $this->normalizeCustomerRequest($request);
        $this->normalizeGeoLabels($request);
        $saveMode = (string) $request->input('save_mode', $request->input('save_mode_intent', 'final'));
        $isDraft = $saveMode === 'draft';
        $tipoCliente = $this->normalizeTipoCliente($request->input('type_cliente', $request->input('type_housed')));
        $request->merge(['type_cliente' => $tipoCliente]);
        if (! $isDraft) {
            $this->validateByTipoCliente($request, $saveMode);
        }

        $hasAzienda = $request->input('has_azienda') === '1';
        [$countryReg, $cittadinanzaReg] = $this->resolveNationAndCittadinanza(
            $request->input('country_reg'),
            $request->input('ciudadania_reg'),
            $request->input('country_reg_fallback')
        );
        $aziendaData = $this->collectAziendaData($request, $hasAzienda);

        $customer = Customers::query()->create($this->buildCustomerPayload(
            $request,
            $tipoCliente,
            $countryReg,
            $cittadinanzaReg,
            $aziendaData
        ));
        $this->ensureNumeroCliente($customer, $tipoCliente);
        $customer->save();

        if ($isDraft) {
            return redirect()
                ->route('customer.edit', ['id' => $customer->id, 'tab' => $request->input('active_tab')])
                ->with('warning', 'Bozza cliente salvata: dati non completi. Stato cliente: Bozza/Incompleto.');
        }

        if ($saveMode === 'to_schedina') {
            return redirect()
                ->route('newschedina', ['customer_id' => $customer->id])
                ->with('success', 'Cliente salvato. La schedina nuova è pronta con i dati precompilati.');
        }

        return redirect('/clienti')->with('success', 'Cliente salvato con successo.');
    }

    public function edit($id)
    {
        $customer = Customers::query()->findOrFail($id);
        $formData = $this->buildSchedaClienteData();
        $formData['customer'] = $customer;

        return view('customers.edit', $formData);
    }

    public function print(int $id)
    {
        /** @var Customers $customer */
        $customer = Customers::query()->findOrFail($id);
        [$customer, $storicoSchedine, $storicoSintetico, $storicoSummary] = $this->buildCustomerHistoryContext($customer);

        return view('customers.print', [
            'customer' => $customer,
            'storicoSintetico' => $storicoSintetico,
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
                $this->normalizeCustomerRequest($request);
                $this->normalizeGeoLabels($request);
                $saveMode = (string) $request->input('save_mode', $request->input('save_mode_intent', 'final'));
                $isDraft = $saveMode === 'draft';
                $tipoCliente = $this->normalizeTipoCliente($request->input('type_cliente', $request->input('type_housed')));
                $request->merge(['type_cliente' => $tipoCliente]);
                if (! $isDraft) {
                    $this->validateByTipoCliente($request, $saveMode);
                }

                $hasAzienda = $request->input('has_azienda') === '1';
                [$countryReg, $cittadinanzaReg] = $this->resolveNationAndCittadinanza(
                    $request->input('country_reg'),
                    $request->input('ciudadania_reg'),
                    $request->input('country_reg_fallback')
                );
                $aziendaData = $this->collectAziendaData($request, $hasAzienda);
  
                $customer = Customers::query()->findOrFail($id);
                $customer->fill($this->buildCustomerPayload(
                    $request,
                    $tipoCliente,
                    $countryReg,
                    $cittadinanzaReg,
                    $aziendaData
                ));
                $this->ensureNumeroCliente($customer, $tipoCliente);
                $customer->save();

            if ($isDraft) {
                return redirect()
                    ->route('customer.edit', ['id' => $customer->id, 'tab' => $request->input('active_tab')])
                    ->with('warning', 'Bozza cliente aggiornata: dati non completi. Stato cliente: Bozza/Incompleto.');
            }

            if ($saveMode === 'to_schedina') {
                return redirect()
                    ->route('newschedina', ['customer_id' => $customer->id])
                    ->with('success', 'Cliente aggiornato. La schedina nuova è pronta con i dati precompilati.');
            }

            return redirect()
                ->route('customer.edit', ['id' => $customer->id, 'tab' => $request->input('active_tab')])
                ->with('success', 'Cliente aggiornato con successo.');
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

    private function resolveNationAndCittadinanza($countryRegInput, $cittadinanzaInput, $countryRegFallback = null): array
    {
        $countryReg = is_string($countryRegInput) ? trim($countryRegInput) : $countryRegInput;
        $cittadinanzaReg = is_string($cittadinanzaInput) ? trim($cittadinanzaInput) : $cittadinanzaInput;
        $countryRegFallback = is_string($countryRegFallback) ? trim($countryRegFallback) : $countryRegFallback;

        if (empty($countryReg) && !empty($countryRegFallback)) {
            $countryReg = $countryRegFallback;
        }

        // Fallback finale: non perdere mai la nazione in estero.
        if (empty($countryReg) && !empty($cittadinanzaReg)) {
            $countryReg = $cittadinanzaReg;
        }

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
        if (empty($cittadinanzaReg)) {
            $cittadinanzaReg = $nazione->cittadinanza ?: $nazione->nome;
        }

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
                $merge[$field] = preg_replace('/\s+/', ' ', preg_replace('/[0-9]+/', '', $value));
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
    }

    private function normalizeGeoLabels(Request $request): void
    {
        $request->merge($this->resolveGeoLabelsFromInput([
            'country' => $request->input('country'),
            'region' => $request->input('region'),
            'province' => $request->input('province'),
            'city' => $request->input('city'),
            'country_reg' => $request->input('country_reg'),
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
        foreach ([
            'country',
            'country_reg',
            'country_az',
        ] as $field) {
            if (!array_key_exists($field, $data) || !is_numeric($data[$field])) {
                continue;
            }

            $nazione = GeoNazione::query()->find((int) $data[$field], ['nome']);
            if ($nazione) {
                $data[$field] = $nazione->nome;
            }
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
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return '';
        }

        $map = [
            'ospite' => 'Ospite',
            'componente' => 'Componente',
            'richiesta' => 'Richiesta',
        ];

        $key = mb_strtolower($normalized);
        return $map[$key] ?? $normalized;
    }

    private function validateByTipoCliente(Request $request, string $saveMode = 'final'): void
    {
        $validator = Validator::make(
            $request->all(),
            $this->rulesByTipoCliente($request->input('type_cliente'), $saveMode),
            $this->validationMessages(),
            $this->validationAttributes()
        );
        $validator->after(function ($validator) use ($request) {
            $this->validateGruppiCascade($validator, $request);
            $this->validateSchedinaTransferMode($validator, $request);
        });

        $validator->validate();
    }

    private function rulesByTipoCliente(?string $tipoCliente, string $saveMode = 'final'): array
    {
        $tipo = $this->normalizeTipoCliente($tipoCliente);
        $strictClienteMode = in_array($saveMode, ['final', 'to_schedina'], true);
        $nameRule = ['required', 'string', 'max:191', 'regex:/^[\pL\s\'\-\.]+$/u'];
        $phoneRule = ['nullable', 'string', 'max:191', 'regex:/^[0-9\+\(\)\s\-\/]*$/'];
        $emailRule = ['nullable', 'email', 'max:191'];
        $websiteRule = ['nullable', 'url', 'max:191'];

        $base = [
            'type_cliente' => ['required', Rule::in(['Ospite', 'Componente', 'Richiesta'])],
            'name' => $nameRule,
            'surname' => $nameRule,
            'group' => ['nullable', 'string', 'max:191'],
            'subgroup' => ['nullable', 'string', 'max:191'],
            'subgroup1' => ['nullable', 'string', 'max:191'],
            'email' => $emailRule,
            'phone' => $phoneRule,
            'fax' => $phoneRule,
            'cellphone' => $phoneRule,
            'sex' => ['nullable', Rule::in(['M', 'F'])],
            'privacy_consent' => [$strictClienteMode ? 'required' : 'nullable', 'boolean'],
            'marketing_consent' => ['nullable', 'boolean'],
            'communication_consent' => ['nullable', 'boolean'],
            'website' => $websiteRule,
            'num_doc_reg' => ['nullable', 'string', 'max:191', 'regex:/^[A-Za-z0-9\/\-\s]+$/'],
            'cf_az' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z0-9]+$/'],
            'pi_az' => ['nullable', 'digits:11'],
            'sdi_az' => ['nullable', 'size:7', 'regex:/^[A-Z0-9]+$/'],
            'phone_az' => $phoneRule,
            'fax_az' => $phoneRule,
            'cellphone_az' => $phoneRule,
            'email_az' => $emailRule,
            'country_az' => ['nullable', 'string', 'max:191'],
            'city_az' => ['nullable', 'string', 'max:191'],
        ];

        if ($tipo === 'Richiesta') {
            return $this->withAziendaRules($base + [
                'email' => ['required', 'email', 'max:191'],
                'phone' => ['required', 'string', 'max:191', 'regex:/^[0-9\+\(\)\s\-\/]*$/'],
            ]);
        }

        return $this->withAziendaRules($base + [
            'sex' => ['required', Rule::in(['M', 'F'])],
            'country' => ['required', 'string', 'max:191'],
            'region' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191'],
            'province' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191'],
            'city' => ['required', 'string', 'max:191'],
            'typeaway' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191'],
            'address' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191'],
            'number' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:50'],
            'email' => [$strictClienteMode ? 'required' : 'nullable', 'email', 'max:191'],
            'phone' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191', 'regex:/^[0-9\+\(\)\s\-\/]*$/'],
            'country_reg' => ['required', 'string', 'max:191'],
            'region_reg' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191'],
            'prov_reg' => [$strictClienteMode ? 'required' : 'nullable', 'string', 'max:191'],
            'city_reg' => ['required', 'string', 'max:191'],
            'cap_reg' => ['nullable', 'string', 'max:50'],
            'ciudadania_reg' => ['required', 'string', 'max:191'],
            'nac_reg' => ['required', 'string', 'max:50'],
            'type_doc_reg' => ['required', 'string', 'max:191'],
            'num_doc_reg' => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9\/\-\s]+$/'],
            'date_pub_reg' => ['required', 'string', 'max:191'],
            'expire_reg' => ['required', 'string', 'max:50'],
            'rilasciato_reg' => ['required', 'string', 'max:191'],
            'country_doc_reg' => ['required', 'string', 'max:191'],
            'city_doc_reg' => [
                Rule::requiredIf(function () {
                    $country = trim((string) request()->input('country_doc_reg', ''));
                    $normalized = \Illuminate\Support\Str::of($country)->ascii()->lower()->value();
                    return $normalized === 'italia' || str_contains($normalized, 'italia');
                }),
                'nullable',
                'string',
                'max:191',
            ],
        ]);
    }

    private function validateSchedinaTransferMode($validator, Request $request): void
    {
        $saveMode = (string) $request->input('save_mode', $request->input('save_mode_intent', 'final'));
        if ($saveMode !== 'to_schedina') {
            return;
        }

        $tipoCliente = $this->normalizeTipoCliente($request->input('type_cliente', $request->input('type_housed')));
        if ($tipoCliente !== 'Ospite') {
            $validator->errors()->add('type_cliente', 'Per salvare in schedina il cliente deve essere di tipo Ospite.');
        }
    }

    private function withAziendaRules(array $rules): array
    {
        $requiredWhenEnabled = Rule::requiredIf(fn () => request()->input('has_azienda') === '1');

        $rules['azienda'] = [$requiredWhenEnabled, 'nullable', 'string', 'max:191'];
        $rules['cf_az'] = [$requiredWhenEnabled, 'nullable', 'string', 'max:16', 'regex:/^[A-Za-z0-9]+$/'];
        $rules['sdi_az'] = [$requiredWhenEnabled, 'nullable', 'size:7', 'regex:/^[A-Z0-9]+$/'];
        $rules['email_az'] = [$requiredWhenEnabled, 'nullable', 'email', 'max:191'];
        $rules['phone_az'] = [$requiredWhenEnabled, 'nullable', 'string', 'max:191', 'regex:/^[0-9\+\(\)\s\-\/]*$/'];
        $rules['country_az'] = [$requiredWhenEnabled, 'nullable', 'string', 'max:191'];
        $rules['city_az'] = [$requiredWhenEnabled, 'nullable', 'string', 'max:191'];
        $rules['website'] = ['nullable', 'url', 'max:191'];

        return $rules;
    }

    private function validationAttributes(): array
    {
        return [
            'type_cliente' => 'tipo cliente',
            'name' => 'nome',
            'surname' => 'cognome',
            'group' => 'gruppo I',
            'subgroup' => 'gruppo II',
            'subgroup1' => 'gruppo III',
            'sex' => 'sesso',
            'country' => 'nazione residenza',
            'region' => 'regione residenza',
            'province' => 'provincia residenza',
            'city' => 'comune residenza',
            'typeaway' => 'tipo via residenza',
            'address' => 'indirizzo residenza',
            'number' => 'numero civico residenza',
            'phone' => 'telefono',
            'email' => 'email',
            'country_reg' => 'nazione anagrafica',
            'region_reg' => 'regione anagrafica',
            'prov_reg' => 'provincia anagrafica',
            'city_reg' => 'comune anagrafico',
            'privacy_consent' => 'consenso privacy',
            'marketing_consent' => 'consenso marketing',
            'communication_consent' => 'consenso comunicazioni',
            'ciudadania_reg' => 'cittadinanza',
            'nac_reg' => 'data di nascita',
            'type_doc_reg' => 'tipo documento',
            'num_doc_reg' => 'numero documento',
            'date_pub_reg' => 'data rilascio',
            'expire_reg' => 'scadenza documento',
            'rilasciato_reg' => 'rilasciato da',
            'country_doc_reg' => 'paese rilascio',
            'city_doc_reg' => 'città rilascio',
            'cf_az' => 'codice fiscale azienda',
            'pi_az' => 'partita IVA azienda',
            'sdi_az' => 'codice SDI',
            'email_az' => 'email azienda',
            'phone_az' => 'telefono azienda',
            'country_az' => 'nazione azienda',
            'city_az' => 'comune azienda',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'Completa il campo :attribute.',
            'email' => 'Inserisci un indirizzo email valido per :attribute.',
            'url' => 'Inserisci un sito web valido per :attribute.',
            'digits' => 'Il campo :attribute deve contenere esattamente :digits numeri.',
            'size.string' => 'Il campo :attribute deve contenere esattamente :size caratteri.',
            'regex' => 'Il formato del campo :attribute non e valido.',
            'in' => 'Seleziona un valore valido per :attribute.',
            'pi_az.digits' => 'La partita IVA azienda deve contenere esattamente 11 numeri.',
            'sdi_az.size' => 'Il codice SDI deve contenere esattamente 7 caratteri.',
            'sdi_az.regex' => 'Il codice SDI puo contenere solo lettere maiuscole e numeri.',
        ];
    }

    private function validateGruppiCascade($validator, Request $request): void
    {
        if (!Schema::hasTable('gruppi')) {
            return;
        }

        $group = trim((string) $request->input('group', ''));
        $subgroup = trim((string) $request->input('subgroup', ''));
        $subgroup1 = trim((string) $request->input('subgroup1', ''));

        if ($group === '' && $subgroup !== '') {
            $validator->errors()->add('subgroup', 'Seleziona prima un gruppo principale.');
            return;
        }

        if ($group !== '' && $subgroup !== '') {
            $parent = Gruppo::query()->where('livello', 1)->where('nome', $group)->first();
            $child = Gruppo::query()->where('livello', 2)->where('nome', $subgroup)->first();

            if (!$parent || !$child || (int) $child->parent_id !== (int) $parent->id) {
                $validator->errors()->add('subgroup', 'Il gruppo II non appartiene al gruppo I selezionato.');
            }
        }

        if ($subgroup === '' && $subgroup1 !== '') {
            $validator->errors()->add('subgroup1', 'Seleziona prima un gruppo di secondo livello.');
            return;
        }

        if ($subgroup !== '' && $subgroup1 !== '') {
            $parent = Gruppo::query()->where('livello', 2)->where('nome', $subgroup)->first();
            $child = Gruppo::query()->where('livello', 3)->where('nome', $subgroup1)->first();

            if (!$parent || !$child || (int) $child->parent_id !== (int) $parent->id) {
                $validator->errors()->add('subgroup1', 'Il gruppo III non appartiene al gruppo II selezionato.');
            }
        }
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

        $lastCode = Customers::query()
            ->withoutGlobalScopes()
            ->when($strutturaId, fn($query) => $query->where('struttura_id', $strutturaId))
            ->where('numero_cliente', 'like', $pattern)
            ->orderByDesc('numero_cliente')
            ->value('numero_cliente');

        if (!$lastCode || !preg_match('/-(\d{4})$/', $lastCode, $matches)) {
            return 1;
        }

        return ((int) $matches[1]) + 1;
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

        $storicoSchedine = Schedina::query()
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
        array $aziendaData
    ): array {
        $payload = [
            'numero_cliente' => $request->input('numero_cliente'),
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
            'privacy_consent_at' => $request->boolean('privacy_consent') ? ($request->input('privacy_consent_at') ?: now()) : null,
            'marketing_consent' => $request->boolean('marketing_consent'),
            'marketing_consent_at' => $request->boolean('marketing_consent') ? ($request->input('marketing_consent_at') ?: now()) : null,
            'communication_consent' => $request->boolean('communication_consent'),
            'communication_consent_at' => $request->boolean('communication_consent') ? ($request->input('communication_consent_at') ?: now()) : null,
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
