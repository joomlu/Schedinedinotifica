<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\AdminServizio;
use App\Models\CustomerImportBatch;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
use App\Models\ProprietarioFatturazioneRiga;
use App\Models\Struttura;
use Carbon\Carbon;
use App\Models\User;
use App\Services\CestinoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProprietariController extends Controller
{
    public function indexProforme(Request $request)
    {
        $user = $request->user();
        $q = trim((string) $request->query('q', ''));

        $proforme = ProprietarioFatturazione::query()
            ->with(['proprietario', 'righe.struttura'])
            ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $user->id))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subquery) use ($q) {
                    $subquery->where('numero', 'like', '%' . $q . '%')
                        ->orWhere('intestazione', 'like', '%' . $q . '%')
                        ->orWhere('stato', 'like', '%' . $q . '%')
                        ->orWhereHas('proprietario', fn ($ownerQuery) => $ownerQuery->where('nome', 'like', '%' . $q . '%')->orWhere('ragione_sociale', 'like', '%' . $q . '%'))
                        ->orWhereHas('righe.struttura', fn ($structureQuery) => $structureQuery->where('nome_struttura', 'like', '%' . $q . '%')->orWhere('citta', 'like', '%' . $q . '%'));
                });
            })
            ->orderByDesc('data_documento')
            ->orderByDesc('id')
            ->get();

        return view('shared.proforme.index', [
            'areaLabel' => 'Admin',
            'pageTitle' => 'Proforme',
            'proforme' => $proforme,
            'q' => $q,
            'ownerRoutePrefix' => 'admin.proprietari',
            'indexRoute' => 'admin.proforme.index',
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $proprietari = Proprietario::where('admin_id', $user->id)->orderBy('nome')->get();

        return view('admin.proprietari.index', [
            'proprietari' => $proprietari,
            'admin' => $user,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.proprietari.form', $this->buildFormViewData(
            new Proprietario(),
            $request->user(),
            'create'
        ));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $this->validateFormData($request);

        $proprietario = Proprietario::create($this->extractProprietarioPayload($data) + [
            'admin_id' => $user->id,
            'attivo' => true,
        ]);
        $this->syncProprietarioAccessUser($proprietario, $data);

        return redirect()->route('admin.proprietari.index')->with('status', 'Proprietario creato');
    }

    public function edit(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);

        return view('admin.proprietari.form', $this->buildFormViewData(
            $proprietario,
            $request->user(),
            'edit'
        ));
    }

    public function update(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);

        $data = $this->validateFormData($request, $this->resolveAccessUser($proprietario));
        $passwordAggiornata = filled($data['accesso_password'] ?? null);

        $proprietario->update($this->extractProprietarioPayload($data, $proprietario));
        $this->syncProprietarioAccessUser($proprietario->fresh(), $data);

        return redirect()
            ->route('admin.proprietari.index')
            ->with('status', $passwordAggiornata
                ? 'Proprietario aggiornato. Password di accesso salvata correttamente.'
                : 'Proprietario aggiornato');
    }

    public function disable(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proprietario->attivo = false;
        $proprietario->save();

        return redirect()->route('admin.proprietari.index')->with('status', 'Proprietario disabilitato');
    }

    public function destroy(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);

        DB::transaction(function () use ($proprietario) {
            app(CestinoService::class)->archiveModel($proprietario, [
                'entity_type' => 'Proprietario',
                'source' => 'Proprietari',
                'title' => $proprietario->nome,
            ]);

            Struttura::query()->where('proprietario_id', $proprietario->id)->update(['proprietario_id' => null]);
            User::query()->where('proprietario_id', $proprietario->id)->update(['proprietario_id' => null]);
            LicenzaAssegnazione::query()->where('proprietario_id', $proprietario->id)->update(['proprietario_id' => null]);
            CustomerImportBatch::query()->where('proprietario_id', $proprietario->id)->update(['proprietario_id' => null]);

            $proprietario->delete();
        });

        return redirect()->route('admin.proprietari.index')->with('status', 'Proprietario spostato nel cestino.');
    }

    public function showProforma(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proforma = $this->findOwnedProforma($proprietario, $fatturazione);

        return view('superadmin.proprietari.proforma-show', [
            'proprietario' => $proprietario,
            'proforma' => $proforma,
            'areaLabel' => 'Admin',
            'ownerRoutePrefix' => 'admin.proprietari',
            'canManageProforma' => true,
        ]);
    }

    public function createProforma(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);

        return view('superadmin.proprietari.proforma-form', [
            'proprietario' => $proprietario,
            'proforma' => null,
            'fatturazione' => $this->buildProformaDraftData($proprietario, $request),
            'catalogoServizi' => $this->loadCatalogoServizi($proprietario),
            'customRighe' => [],
            'areaLabel' => 'Admin',
            'ownerRoutePrefix' => 'admin.proprietari',
        ]);
    }

    public function storeProforma(Request $request, int $id)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $payload = $this->validateAndBuildProformaPayload($request, $proprietario);

        $proforma = DB::transaction(function () use ($proprietario, $request, $payload) {
            $fattura = ProprietarioFatturazione::create([
                'proprietario_id' => $proprietario->id,
                'created_by' => $request->user()->id,
                'numero' => $payload['numero'],
                'data_documento' => $payload['data_documento'],
                'stato' => 'proforma',
                'intestazione' => $payload['destinatario']['intestazione'],
                'partita_iva' => $payload['destinatario']['partita_iva'],
                'codice_fiscale' => $payload['destinatario']['codice_fiscale'],
                'pec' => $payload['destinatario']['pec'],
                'indirizzo' => $payload['destinatario']['indirizzo'],
                'cap' => $payload['destinatario']['cap'],
                'citta' => $payload['destinatario']['citta'],
                'provincia' => $payload['destinatario']['provincia'],
                'imponibile' => $payload['totali']['imponibile'],
                'totale_sconto' => $payload['totali']['sconto'],
                'totale_iva' => $payload['totali']['iva'],
                'totale' => $payload['totali']['totale'],
                'numero_fattura' => $payload['numero_fattura'],
                'data_pagamento' => $payload['data_pagamento'],
                'note' => $payload['note'],
            ]);

            foreach ($payload['righe'] as $riga) {
                $fattura->righe()->create($riga);
            }

            return $fattura;
        });

        return redirect()->route('admin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma creata');
    }

    public function editProforma(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proforma = $this->findOwnedProforma($proprietario, $fatturazione);

        return view('superadmin.proprietari.proforma-form', [
            'proprietario' => $proprietario,
            'proforma' => $proforma,
            'fatturazione' => $this->buildProformaDraftData($proprietario, $request, $proforma),
            'catalogoServizi' => $this->loadCatalogoServizi($proprietario),
            'customRighe' => $this->extractCustomRows($proforma),
            'areaLabel' => 'Admin',
            'ownerRoutePrefix' => 'admin.proprietari',
        ]);
    }

    public function updateProforma(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proforma = $this->findOwnedProforma($proprietario, $fatturazione);
        $payload = $this->validateAndBuildProformaPayload($request, $proprietario);

        DB::transaction(function () use ($proforma, $payload) {
            $proforma->update([
                'numero' => $payload['numero'],
                'data_documento' => $payload['data_documento'],
                'intestazione' => $payload['destinatario']['intestazione'],
                'partita_iva' => $payload['destinatario']['partita_iva'],
                'codice_fiscale' => $payload['destinatario']['codice_fiscale'],
                'pec' => $payload['destinatario']['pec'],
                'indirizzo' => $payload['destinatario']['indirizzo'],
                'cap' => $payload['destinatario']['cap'],
                'citta' => $payload['destinatario']['citta'],
                'provincia' => $payload['destinatario']['provincia'],
                'imponibile' => $payload['totali']['imponibile'],
                'totale_sconto' => $payload['totali']['sconto'],
                'totale_iva' => $payload['totali']['iva'],
                'totale' => $payload['totali']['totale'],
                'numero_fattura' => $payload['numero_fattura'],
                'data_pagamento' => $payload['data_pagamento'],
                'note' => $payload['note'],
            ]);

            $proforma->righe()->delete();
            foreach ($payload['righe'] as $riga) {
                $proforma->righe()->create($riga);
            }
        });

        return redirect()->route('admin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma aggiornata');
    }

    public function closeProforma(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proforma = $this->findOwnedProforma($proprietario, $fatturazione);
        $proforma->update(['stato' => 'chiusa']);

        return redirect()->route('admin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma chiusa');
    }

    public function markFatturata(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proforma = $this->findOwnedProforma($proprietario, $fatturazione);
        $data = $request->validate([
            'numero_fattura' => ['required', 'string', 'max:80'],
            'data_pagamento' => ['required'],
        ]);
        $proforma->update([
            'stato' => 'pagata',
            'numero_fattura' => $data['numero_fattura'],
            'data_pagamento' => $data['data_pagamento'],
        ]);

        return redirect()->route('admin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma segnata come pagata');
    }

    public function printProforma(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::where('admin_id', $request->user()->id)->findOrFail($id);
        $proforma = $this->findOwnedProforma($proprietario, $fatturazione);

        return view('superadmin.proprietari.proforma-print', [
            'proprietario' => $proprietario,
            'proforma' => $proforma,
        ]);
    }

    private function buildFormViewData(Proprietario $proprietario, $admin, string $mode): array
    {
        $strutture = $proprietario->exists
            ? $proprietario->strutture()->with(['accessoPrincipale'])->orderBy('nome_struttura')->get()
            : collect();
        $licenze = $proprietario->exists
            ? LicenzaAssegnazione::query()
                ->with(['articolo', 'struttura'])
                ->where('proprietario_id', $proprietario->id)
                ->orderByDesc('data_scadenza')
                ->orderByDesc('id')
                ->get()
            : collect();
        $fatture = $proprietario->exists
            ? $proprietario->fatturazioni()->with('righe.servizio', 'righe.struttura')->orderByDesc('data_documento')->orderByDesc('id')->get()
            : collect();
        $prossimaScadenza = $licenze
            ->filter(fn ($licenza) => $licenza->data_scadenza)
            ->sortBy('data_scadenza')
            ->first()?->data_scadenza;
        $serviziAttiviCount = $licenze
            ->filter(function ($licenza) {
                if (!$licenza->data_scadenza) {
                    return true;
                }

                return Carbon::parse($licenza->data_scadenza)->isFuture() || Carbon::parse($licenza->data_scadenza)->isToday();
            })
            ->count();
        $fatturatoTotale = $fatture->sum(fn ($fattura) => (float) $fattura->totale);

        return [
            'proprietario' => $proprietario,
            'admin' => $admin,
            'mode' => $mode,
            'struttureCount' => $strutture->count(),
            'utentiCount' => $proprietario->exists ? $proprietario->utenti()->count() : 0,
            'accessoPrincipale' => $this->resolveAccessUser($proprietario),
            'struttureStorico' => $strutture,
            'licenzeStorico' => $licenze,
            'fattureStorico' => $fatture,
            'serviziAttiviCount' => $serviziAttiviCount,
            'prossimaScadenza' => $prossimaScadenza,
            'fatturatoTotale' => $fatturatoTotale,
        ];
    }

    private function validateFormData(Request $request, ?User $accessoPrincipale = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'cellulare' => ['nullable', 'string', 'max:50'],
            'ragione_sociale' => ['nullable', 'string', 'max:180'],
            'codice_fiscale' => ['nullable', 'string', 'max:32'],
            'partita_iva' => ['nullable', 'string', 'max:32'],
            'codice_destinatario' => ['nullable', 'string', 'max:32'],
            'codice_unico' => ['nullable', 'string', 'max:7'],
            'pec' => ['nullable', 'email', 'max:180'],
            'indirizzo' => ['nullable', 'string', 'max:180'],
            'numero_civico' => ['nullable', 'string', 'max:20'],
            'cap' => ['nullable', 'string', 'max:16'],
            'citta' => ['nullable', 'string', 'max:120'],
            'provincia' => ['nullable', 'string', 'max:8'],
            'regione' => ['nullable', 'string', 'max:120'],
            'nazione' => ['nullable', 'string', 'max:120'],
            'latitudine' => ['nullable', 'string', 'max:50'],
            'longitudine' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
            'note_amministrative' => ['nullable', 'string'],
            'accesso_nome' => ['nullable', 'string', 'max:255'],
            'accesso_username' => [
                'nullable',
                'required_with:accesso_email,accesso_password,accesso_nome',
                'string',
                'max:120',
                Rule::unique('users', 'username')->whereNull('deleted_at')->ignore($accessoPrincipale?->id),
            ],
            'accesso_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($accessoPrincipale?->id),
            ],
            'accesso_password' => ['nullable', 'string', 'min:8'],
        ], [
            'accesso_username.required_with' => 'Inserisci lo username di accesso del proprietario.',
            'accesso_username.unique' => 'Lo username di accesso e gia utilizzato da un altro utente.',
            'accesso_email.unique' => 'L email di accesso e gia utilizzata da un altro utente.',
            'accesso_password.min' => 'La password di accesso deve contenere almeno 8 caratteri.',
        ]);
    }

    private function extractProprietarioPayload(array $data, ?Proprietario $proprietario = null): array
    {
        return $this->enrichGeoDefaults($this->normalizeGeoLabels([
            'nome' => $data['nome'],
            'email' => $data['email'] ?? ($proprietario->email ?? null),
            'telefono' => $data['telefono'] ?? ($proprietario->telefono ?? null),
            'cellulare' => $data['cellulare'] ?? ($proprietario->cellulare ?? null),
            'ragione_sociale' => $data['ragione_sociale'] ?? ($proprietario->ragione_sociale ?? null),
            'codice_fiscale' => $data['codice_fiscale'] ?? ($proprietario->codice_fiscale ?? null),
            'partita_iva' => $data['partita_iva'] ?? ($proprietario->partita_iva ?? null),
            'codice_destinatario' => $data['codice_destinatario'] ?? ($proprietario->codice_destinatario ?? null),
            'codice_unico' => $data['codice_unico'] ?? ($proprietario->codice_unico ?? null),
            'pec' => $data['pec'] ?? ($proprietario->pec ?? null),
            'indirizzo' => $data['indirizzo'] ?? ($proprietario->indirizzo ?? null),
            'numero_civico' => $data['numero_civico'] ?? ($proprietario->numero_civico ?? null),
            'cap' => $data['cap'] ?? ($proprietario->cap ?? null),
            'citta' => $data['citta'] ?? ($proprietario->citta ?? null),
            'provincia' => $data['provincia'] ?? ($proprietario->provincia ?? null),
            'regione' => $data['regione'] ?? ($proprietario->regione ?? null),
            'nazione' => $data['nazione'] ?? ($proprietario->nazione ?? null),
            'latitudine' => $data['latitudine'] ?? ($proprietario->latitudine ?? null),
            'longitudine' => $data['longitudine'] ?? ($proprietario->longitudine ?? null),
            'note' => $data['note'] ?? ($proprietario->note ?? null),
            'note_amministrative' => $data['note_amministrative'] ?? ($proprietario->note_amministrative ?? null),
        ]));
    }

    private function enrichGeoDefaults(array $data): array
    {
        $geoComuneId = $this->resolveGeoComuneId($data['citta'] ?? null);
        if (!$geoComuneId) {
            return $data;
        }

        $comune = GeoComune::query()->find($geoComuneId, ['id', 'lat', 'lng']);
        if (!$comune) {
            return $data;
        }

        if (blank($data['latitudine'] ?? null) && !blank($comune->lat)) {
            $data['latitudine'] = $comune->lat;
        }

        if (blank($data['longitudine'] ?? null) && !blank($comune->lng)) {
            $data['longitudine'] = $comune->lng;
        }

        return $data;
    }

    private function resolveGeoComuneId(?string $value): ?int
    {
        $label = trim((string) $value);
        if ($label === '') {
            return null;
        }

        if (ctype_digit($label)) {
            return GeoComune::query()->whereKey((int) $label)->value('id');
        }

        return GeoComune::query()
            ->where('nome', $label)
            ->orWhere('nome', str_replace('-', ' ', $label))
            ->orWhere('nome', str_replace(' ', '-', $label))
            ->value('id');
    }

    private function resolveAccessUser(Proprietario $proprietario): ?User
    {
        if (!$proprietario->exists) {
            return null;
        }

        $accesso = $proprietario->accessoPrincipale()->first();
        if ($accesso) {
            return $accesso;
        }

        $email = trim((string) ($proprietario->email ?? ''));
        if ($email !== '') {
            $byEmail = User::query()
                ->where('ruolo', 'proprietario')
                ->where('email', $email)
                ->orderByDesc('attivo')
                ->orderByDesc('id')
                ->first();

            if ($byEmail) {
                return $byEmail;
            }
        }

        $name = trim((string) ($proprietario->nome ?? ''));
        if ($name !== '') {
            $matches = User::query()
                ->where('ruolo', 'proprietario')
                ->where('name', $name)
                ->orderByDesc('attivo')
                ->orderByDesc('id')
                ->get();

            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        return null;
    }

    private function syncProprietarioAccessUser(Proprietario $proprietario, array $data): void
    {
        $accesso = $this->resolveAccessUser($proprietario);
        $hasInput = filled($data['accesso_username'] ?? null)
            || filled($data['accesso_email'] ?? null)
            || filled($data['accesso_password'] ?? null)
            || filled($data['accesso_nome'] ?? null);

        if (!$accesso && !$hasInput) {
            return;
        }

        $username = trim((string) ($data['accesso_username'] ?? $accesso?->username ?? ''));
        if ($username === '') {
            return;
        }

        $name = trim((string) ($data['accesso_nome'] ?? $accesso?->name ?? $proprietario->nome));
        $email = trim((string) ($data['accesso_email'] ?? $accesso?->email ?? $proprietario->email ?? ''));
        if ($email === '') {
            $email = preg_replace('/[^a-z0-9]+/i', '.', strtolower($username));
            $email = trim((string) $email, '.').'.'.$proprietario->id.'@tanggo.local';
        }

        $payload = [
            'name' => $name !== '' ? $name : $proprietario->nome,
            'display_name' => $name !== '' ? $name : $proprietario->nome,
            'username' => $username,
            'email' => $email,
            'avatar' => $accesso?->avatar ?? '',
            'ruolo' => 'proprietario',
            'proprietario_id' => $proprietario->id,
            'attivo' => (bool) ($proprietario->attivo ?? true),
        ];

        if ($accesso) {
            $accesso->fill($payload);
            if (filled($data['accesso_password'] ?? null)) {
                $accesso->password = Hash::make((string) $data['accesso_password']);
            }
            $accesso->save();
            return;
        }

        if (!filled($data['accesso_password'] ?? null)) {
            return;
        }

        User::create($payload + [
            'password' => Hash::make((string) $data['accesso_password']),
        ]);
    }

    private function normalizeGeoLabels(array $data): array
    {
        if (isset($data['nazione']) && is_numeric($data['nazione'])) {
            $nazione = GeoNazione::find((int) $data['nazione']);
            if ($nazione) {
                $data['nazione'] = $nazione->nome;
            }
        }

        if (isset($data['regione']) && is_numeric($data['regione'])) {
            $regione = GeoRegione::find((int) $data['regione']);
            if ($regione) {
                $data['regione'] = $regione->nome;
            }
        }

        if (isset($data['provincia']) && is_numeric($data['provincia'])) {
            $provincia = GeoProvincia::find((int) $data['provincia']);
            if ($provincia) {
                $data['provincia'] = $provincia->sigla ?: $provincia->nome;
            }
        }

        if (isset($data['citta']) && is_numeric($data['citta'])) {
            $comune = GeoComune::find((int) $data['citta']);
            if ($comune) {
                $data['citta'] = $comune->nome;
            }
        }

        return $data;
    }

    private function findOwnedProforma(Proprietario $proprietario, int $fatturazioneId): ProprietarioFatturazione
    {
        return ProprietarioFatturazione::with(['righe.struttura', 'righe.servizio'])
            ->where('proprietario_id', $proprietario->id)
            ->findOrFail($fatturazioneId);
    }

    private function loadCatalogoServizi(Proprietario $proprietario)
    {
        if (!$proprietario->admin_id) {
            return collect();
        }

        return AdminServizio::query()
            ->where('user_id', $proprietario->admin_id)
            ->where('attivo', true)
            ->orderBy('nome')
            ->get();
    }

    private function buildProformaDraftData(Proprietario $proprietario, Request $request, ?ProprietarioFatturazione $proforma = null): array
    {
        $strutture = $proprietario->strutture()->orderBy('nome_struttura')->get();

        if ($proforma) {
            $righe = $proforma->righe->map(function (ProprietarioFatturazioneRiga $riga) {
                return [
                    'key' => 'existing-' . $riga->id,
                    'selected' => true,
                    'struttura_id' => $riga->struttura_id,
                    'struttura_nome' => $riga->struttura?->nome_struttura ?: 'Servizio generale',
                    'admin_servizio_id' => $riga->admin_servizio_id,
                    'descrizione' => $riga->descrizione,
                    'quantita' => $riga->quantita,
                    'prezzo_unitario' => (float) $riga->prezzo_unitario,
                    'sconto_tipo' => $riga->sconto_tipo ?: 'percentuale',
                    'sconto_valore' => (float) $riga->sconto_valore,
                    'aliquota_iva' => (float) $riga->aliquota_iva,
                    'totale' => (float) $riga->totale,
                    'note' => $riga->note,
                    'is_generale' => !$riga->struttura_id,
                ];
            })->values();
        } else {
            $licenze = LicenzaAssegnazione::query()
                ->with(['articolo', 'struttura'])
                ->where('proprietario_id', $proprietario->id)
                ->orderByDesc('data_scadenza')
                ->orderByDesc('id')
                ->get();

            $servizi = $proprietario->adminServizi()
                ->where('admin_servizi.attivo', true)
                ->orderBy('admin_servizi.nome')
                ->get();

            $righe = collect();

            foreach ($licenze as $licenza) {
                $descrizione = trim(implode(' · ', array_filter([
                    $licenza->articolo?->nome ?: 'Licenza',
                    $licenza->numero_licenza,
                ])));
                $quantita = max(1, (int) ($licenza->quantita ?: 1));
                $prezzo = (float) $licenza->prezzo;
                $totale = $quantita * $prezzo * 1.22;

                $righe->push([
                    'key' => 'licenza-' . $licenza->id,
                    'selected' => true,
                    'struttura_id' => $licenza->struttura_id,
                    'struttura_nome' => $licenza->struttura?->nome_struttura ?: 'Servizio generale',
                    'admin_servizio_id' => null,
                    'descrizione' => $descrizione,
                    'quantita' => $quantita,
                    'prezzo_unitario' => $prezzo,
                    'sconto_tipo' => 'percentuale',
                    'sconto_valore' => 0,
                    'aliquota_iva' => 22,
                    'totale' => $totale,
                    'note' => $licenza->note,
                    'is_generale' => !$licenza->struttura_id,
                ]);
            }

            foreach ($servizi as $servizio) {
                $quantita = max(1, (int) ($servizio->pivot->quantita ?: $servizio->quantita_default ?: 1));
                $prezzo = (float) ($servizio->pivot->importo_override ?: $servizio->importo ?: 0);
                $totale = $quantita * $prezzo * 1.22;
                $struttura = $strutture->firstWhere('id', $servizio->pivot->struttura_id);

                $righe->push([
                    'key' => 'servizio-' . $servizio->id . '-' . ($servizio->pivot->struttura_id ?: 'generale'),
                    'selected' => true,
                    'struttura_id' => $servizio->pivot->struttura_id,
                    'struttura_nome' => $struttura?->nome_struttura ?: 'Servizio generale',
                    'admin_servizio_id' => $servizio->id,
                    'descrizione' => $servizio->nome,
                    'quantita' => $quantita,
                    'prezzo_unitario' => $prezzo,
                    'sconto_tipo' => 'percentuale',
                    'sconto_valore' => 0,
                    'aliquota_iva' => 22,
                    'totale' => $totale,
                    'note' => $servizio->pivot->note,
                    'is_generale' => !$servizio->pivot->struttura_id,
                ]);
            }
        }

        $singleStrutturaId = (int) $request->query('struttura_id');
        if (!$proforma && $singleStrutturaId > 0) {
            $righe = $righe->map(function (array $riga) use ($singleStrutturaId) {
                $riga['selected'] = (int) ($riga['struttura_id'] ?? 0) === $singleStrutturaId;
                return $riga;
            });
        }

        return [
            'strutture' => $strutture,
            'righe' => $righe,
        ];
    }

    private function extractCustomRows(?ProprietarioFatturazione $proforma): array
    {
        if (!$proforma) {
            return [];
        }

        return $proforma->righe
            ->filter(fn (ProprietarioFatturazioneRiga $riga) => !$riga->admin_servizio_id)
            ->filter(fn (ProprietarioFatturazioneRiga $riga) => !str_starts_with((string) $riga->descrizione, 'Licenza'))
            ->map(function (ProprietarioFatturazioneRiga $riga) {
                return [
                    'catalogo_servizio_id' => null,
                    'descrizione' => $riga->descrizione,
                    'quantita' => $riga->quantita,
                    'prezzo_unitario' => (float) $riga->prezzo_unitario,
                    'sconto_tipo' => $riga->sconto_tipo ?: 'percentuale',
                    'sconto_valore' => (float) $riga->sconto_valore,
                    'aliquota_iva' => (float) $riga->aliquota_iva,
                    'note' => $riga->note,
                ];
            })
            ->values()
            ->all();
    }

    private function validateAndBuildProformaPayload(Request $request, Proprietario $proprietario): array
    {
        $data = $request->validate([
            'proforma_numero' => ['nullable', 'string', 'max:50'],
            'proforma_data' => ['required'],
            'proforma_note' => ['nullable', 'string'],
            'numero_fattura' => ['nullable', 'string', 'max:80'],
            'data_pagamento' => ['nullable'],
            'proforma_righe' => ['array'],
            'custom_righe' => ['array'],
        ]);

        $rows = collect($request->input('proforma_righe', []))
            ->filter(fn ($riga) => (bool) ($riga['selected'] ?? false))
            ->map(fn ($riga) => $this->normalizeProformaRow($riga))
            ->filter(fn ($riga) => $riga !== null);

        $customRows = collect($request->input('custom_righe', []))
            ->map(fn ($riga) => $this->normalizeCustomProformaRow($riga))
            ->filter(fn ($riga) => $riga !== null);

        $righe = $rows->concat($customRows)->values();

        if ($righe->isEmpty()) {
            abort(422, 'Seleziona almeno una voce per generare la proforma.');
        }

        $destinatario = $this->resolveProformaDestinatario($proprietario, $righe);
        $totali = $this->calculateProformaTotals($righe);

        return [
            'numero' => trim((string) ($data['proforma_numero'] ?? '')) ?: $this->nextProformaNumber($proprietario),
            'data_documento' => $data['proforma_data'],
            'note' => $data['proforma_note'] ?? null,
            'numero_fattura' => $data['numero_fattura'] ?? null,
            'data_pagamento' => $data['data_pagamento'] ?? null,
            'righe' => $righe->all(),
            'totali' => $totali,
            'destinatario' => $destinatario,
        ];
    }

    private function normalizeProformaRow(array $riga): ?array
    {
        $descrizione = trim((string) ($riga['descrizione'] ?? ''));
        if ($descrizione === '') {
            return null;
        }

        $quantita = max(1, (int) ($riga['quantita'] ?? 1));
        $prezzo = max(0, (float) ($riga['prezzo_unitario'] ?? 0));
        $scontoTipo = ($riga['sconto_tipo'] ?? 'percentuale') === 'importo' ? 'importo' : 'percentuale';
        $scontoValore = max(0, (float) ($riga['sconto_valore'] ?? 0));
        $aliquota = max(0, (float) ($riga['aliquota_iva'] ?? 22));
        $subtotale = $quantita * $prezzo;
        $sconto = $scontoTipo === 'importo' ? min($subtotale, $scontoValore) : ($subtotale * $scontoValore / 100);
        $imponibile = max(0, $subtotale - $sconto);
        $totaleIva = $imponibile * $aliquota / 100;

        return [
            'struttura_id' => !empty($riga['struttura_id']) ? (int) $riga['struttura_id'] : null,
            'admin_servizio_id' => !empty($riga['admin_servizio_id']) ? (int) $riga['admin_servizio_id'] : null,
            'descrizione' => $descrizione,
            'quantita' => $quantita,
            'prezzo_unitario' => $prezzo,
            'sconto_tipo' => $scontoTipo,
            'sconto_valore' => $scontoValore,
            'imponibile' => $imponibile,
            'aliquota_iva' => $aliquota,
            'totale_iva' => $totaleIva,
            'totale' => $imponibile + $totaleIva,
            'note' => $riga['note'] ?? null,
        ];
    }

    private function normalizeCustomProformaRow(array $riga): ?array
    {
        $descrizione = trim((string) ($riga['descrizione'] ?? ''));
        if ($descrizione === '') {
            return null;
        }

        $normalized = $this->normalizeProformaRow(array_merge($riga, [
            'selected' => true,
            'struttura_id' => $riga['struttura_id'] ?? null,
            'admin_servizio_id' => $riga['catalogo_servizio_id'] ?? null,
        ]));

        return $normalized;
    }

    private function calculateProformaTotals($righe): array
    {
        return [
            'imponibile' => (float) $righe->sum('imponibile'),
            'sconto' => (float) $righe->sum(function ($riga) {
                $subtotale = ((float) $riga['quantita']) * ((float) $riga['prezzo_unitario']);
                return max(0, $subtotale - (float) $riga['imponibile']);
            }),
            'iva' => (float) $righe->sum('totale_iva'),
            'totale' => (float) $righe->sum('totale'),
        ];
    }

    private function resolveProformaDestinatario(Proprietario $proprietario, $righe): array
    {
        $strutturaIds = $righe->pluck('struttura_id')->filter()->unique()->values();
        $hasGeneralRows = $righe->contains(fn ($riga) => empty($riga['struttura_id']));

        if ($strutturaIds->count() === 1 && !$hasGeneralRows) {
            $struttura = $proprietario->strutture()->find($strutturaIds->first());
            if ($struttura) {
                return [
                    'intestazione' => $struttura->ragione_sociale ?: $struttura->nome_struttura,
                    'partita_iva' => $struttura->partita_iva,
                    'codice_fiscale' => $struttura->codice_fiscale,
                    'pec' => null,
                    'indirizzo' => trim(implode(' ', array_filter([$struttura->indirizzo, $struttura->numero_civico]))),
                    'cap' => $struttura->cap,
                    'citta' => $struttura->citta,
                    'provincia' => $struttura->provincia,
                ];
            }
        }

        return [
            'intestazione' => $proprietario->ragione_sociale ?: $proprietario->nome,
            'partita_iva' => $proprietario->partita_iva,
            'codice_fiscale' => $proprietario->codice_fiscale,
            'pec' => $proprietario->pec,
            'indirizzo' => trim(implode(' ', array_filter([$proprietario->indirizzo, $proprietario->numero_civico]))),
            'cap' => $proprietario->cap,
            'citta' => $proprietario->citta,
            'provincia' => $proprietario->provincia,
        ];
    }

    private function nextProformaNumber(Proprietario $proprietario): string
    {
        $prefix = 'PRO-P-';
        $last = ProprietarioFatturazione::query()
            ->where('proprietario_id', $proprietario->id)
            ->where('numero', 'like', $prefix . '%')
            ->orderByDesc('numero')
            ->value('numero');

        $next = 1;
        if ($last && preg_match('/(\d+)$/', $last, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
