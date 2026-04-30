<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AdminFatturazione;
use App\Models\AdminServizio;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\Proprietario;
use App\Models\Struttura;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AmministratoriController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $admins = User::query()
            ->whereIn('ruolo', ['admin', 'admin_disabled'])
            ->withCount(['proprietariGestiti as proprietari_count', 'adminServizi as servizi_count', 'adminFatturazioni as fatture_count'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subquery) use ($q) {
                    $subquery->where('name', 'like', '%' . $q . '%')
                        ->orWhere('display_name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%')
                        ->orWhere('username', 'like', '%' . $q . '%')
                        ->orWhere('ragione_sociale', 'like', '%' . $q . '%')
                        ->orWhere('partita_iva', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $summary = [
            'totale_admin' => $admins->count(),
            'attivi' => $admins->where('attivo', true)->count(),
            'disattivi' => $admins->where('attivo', false)->count(),
            'proprietari' => $admins->sum('proprietari_count'),
            'servizi' => $admins->sum('servizi_count'),
            'fatture' => $admins->sum('fatture_count'),
        ];

        return view('superadmin.amministratori.index', [
            'admins' => $admins,
            'q' => $q,
            'summary' => $summary,
        ]);
    }

    public function create()
    {
        return view('superadmin.amministratori.form', $this->buildFormViewData(
            new User(['ruolo' => 'admin', 'attivo' => true]),
            'create'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateFormData($request);

        $admin = User::create($this->extractAdminPayload($data) + [
            'password' => Hash::make((string) $data['password']),
            'avatar' => '',
            'ruolo' => 'admin',
            'attivo' => true,
        ]);

        $this->syncAdminServizi($admin, $data['servizi'] ?? []);

        return redirect()->route('superadmin.amministratori.index')->with('status', 'Amministratore creato');
    }

    public function edit(int $id)
    {
        $admin = User::query()
            ->whereIn('ruolo', ['admin', 'admin_disabled'])
            ->findOrFail($id);

        return view('superadmin.amministratori.form', $this->buildFormViewData(
            $admin,
            'edit'
        ));
    }

    public function update(Request $request, int $id)
    {
        $admin = User::query()
            ->whereIn('ruolo', ['admin', 'admin_disabled'])
            ->findOrFail($id);

        $data = $this->validateFormData($request, $admin);
        $passwordAggiornata = filled($data['password'] ?? null);

        $admin->fill($this->extractAdminPayload($data, $admin));
        if (filled($data['password'] ?? null)) {
            $admin->password = Hash::make((string) $data['password']);
        }
        $admin->save();

        $this->syncAdminServizi($admin, $data['servizi'] ?? []);

        return redirect()->route('superadmin.amministratori.index')->with(
            'status',
            $passwordAggiornata
                ? 'Amministratore aggiornato. Password di accesso salvata correttamente.'
                : 'Amministratore aggiornato'
        );
    }

    public function disable(int $id)
    {
        $admin = User::query()->whereIn('ruolo', ['admin', 'admin_disabled'])->findOrFail($id);
        $admin->ruolo = 'admin_disabled';
        $admin->attivo = false;
        $admin->save();

        return redirect()->route('superadmin.amministratori.index')->with('status', 'Amministratore disabilitato');
    }

    public function createProforma(Request $request, int $id)
    {
        $admin = $this->findAdmin($id);

        return view('superadmin.amministratori.proforma-form', [
            'admin' => $admin,
            'proforma' => null,
            'fatturazione' => $this->buildProformaDraftData($admin, $request),
            'catalogoServizi' => $admin->adminServizi()->orderBy('nome')->get(),
            'customRighe' => [],
        ]);
    }

    public function storeProforma(Request $request, int $id)
    {
        $admin = $this->findAdmin($id);
        $payload = $this->validateAndBuildProformaPayload($request, $admin);

        $proforma = DB::transaction(function () use ($admin, $request, $payload) {
            $fattura = AdminFatturazione::create([
                'user_id' => $admin->id,
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

        return redirect()->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma creata');
    }

    public function showProforma(int $id, int $fatturazione)
    {
        $admin = $this->findAdmin($id);
        $proforma = $this->findOwnedProforma($admin, $fatturazione);

        return view('superadmin.amministratori.proforma-show', [
            'admin' => $admin,
            'proforma' => $proforma,
        ]);
    }

    public function editProforma(Request $request, int $id, int $fatturazione)
    {
        $admin = $this->findAdmin($id);
        $proforma = $this->findOwnedProforma($admin, $fatturazione);

        return view('superadmin.amministratori.proforma-form', [
            'admin' => $admin,
            'proforma' => $proforma,
            'fatturazione' => $this->buildProformaDraftData($admin, $request, $proforma),
            'catalogoServizi' => $admin->adminServizi()->orderBy('nome')->get(),
            'customRighe' => $this->extractCustomRows($proforma),
        ]);
    }

    public function updateProforma(Request $request, int $id, int $fatturazione)
    {
        $admin = $this->findAdmin($id);
        $proforma = $this->findOwnedProforma($admin, $fatturazione);
        $payload = $this->validateAndBuildProformaPayload($request, $admin);

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

        return redirect()->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma aggiornata');
    }

    public function closeProforma(int $id, int $fatturazione)
    {
        $admin = $this->findAdmin($id);
        $proforma = $this->findOwnedProforma($admin, $fatturazione);
        $proforma->update(['stato' => 'chiusa']);

        return redirect()->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma chiusa');
    }

    public function markFatturata(Request $request, int $id, int $fatturazione)
    {
        $admin = $this->findAdmin($id);
        $proforma = $this->findOwnedProforma($admin, $fatturazione);
        $data = $request->validate([
            'numero_fattura' => ['required', 'string', 'max:80'],
            'data_pagamento' => ['required'],
        ]);

        $proforma->update([
            'stato' => 'pagata',
            'numero_fattura' => $data['numero_fattura'],
            'data_pagamento' => $data['data_pagamento'],
        ]);

        return redirect()->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('status', 'Proforma segnata come pagata');
    }

    public function printProforma(int $id, int $fatturazione)
    {
        $admin = $this->findAdmin($id);
        $proforma = $this->findOwnedProforma($admin, $fatturazione);

        return view('superadmin.amministratori.proforma-print', [
            'admin' => $admin,
            'proforma' => $proforma,
        ]);
    }

    private function buildFormViewData(User $admin, string $mode): array
    {
        $proprietari = $admin->exists
            ? $admin->proprietariGestiti()->withCount('strutture')->orderBy('nome')->get()
            : collect();
        $strutture = $admin->exists
            ? Struttura::query()->with('proprietario')
                ->whereHas('proprietario', fn ($query) => $query->where('admin_id', $admin->id))
                ->orderBy('nome_struttura')
                ->get()
            : collect();
        $servizi = $admin->exists
            ? $admin->adminServizi()->orderByDesc('attivo')->orderBy('nome')->get()
            : collect();
        $fatture = $admin->exists
            ? $admin->adminFatturazioni()->with('righe.proprietario', 'righe.servizio')->orderByDesc('data_documento')->orderByDesc('id')->get()
            : collect();
        $prossimaScadenza = $strutture
            ->filter(fn ($struttura) => $struttura->scadenza_servizio)
            ->sortBy('scadenza_servizio')
            ->first()?->scadenza_servizio;
        $fatturatoTotale = $fatture->sum(fn ($fattura) => (float) $fattura->totale);

        return [
            'admin' => $admin,
            'mode' => $mode,
            'proprietariGestiti' => $proprietari,
            'struttureGestite' => $strutture,
            'serviziStorico' => $servizi,
            'fattureStorico' => $fatture,
            'proprietariCount' => $proprietari->count(),
            'struttureCount' => $strutture->count(),
            'serviziCount' => $servizi->where('attivo', true)->count(),
            'fatturatoTotale' => $fatturatoTotale,
            'prossimaScadenza' => $prossimaScadenza,
        ];
    }

    private function validateFormData(Request $request, ?User $admin = null): array
    {
        $passwordRule = $admin ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'];

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'display_name' => ['nullable', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin?->id)],
                'username' => ['required', 'string', 'max:120', Rule::unique('users', 'username')->ignore($admin?->id)],
                'password' => $passwordRule,
                'telefono' => ['nullable', 'string', 'max:40'],
                'qualifica' => ['nullable', 'string', 'max:120'],
                'ragione_sociale' => ['nullable', 'string', 'max:180'],
                'codice_fiscale' => ['nullable', 'string', 'max:32'],
                'partita_iva' => ['nullable', 'string', 'max:32'],
                'codice_destinatario' => ['nullable', 'string', 'max:32'],
                'pec' => ['nullable', 'email', 'max:180'],
                'indirizzo' => ['nullable', 'string', 'max:180'],
                'numero_civico' => ['nullable', 'string', 'max:20'],
                'cap' => ['nullable', 'string', 'max:16'],
                'citta' => ['nullable', 'string', 'max:120'],
                'provincia' => ['nullable', 'string', 'max:8'],
                'regione' => ['nullable', 'string', 'max:120'],
                'nazione' => ['nullable', 'string', 'max:120'],
                'attivo' => ['nullable', 'boolean'],
                'servizi' => ['nullable', 'array'],
                'servizi.*.id' => ['nullable', 'integer'],
                'servizi.*.nome' => ['nullable', 'string', 'max:160'],
                'servizi.*.tipo_costo' => ['nullable', 'string', 'max:30'],
                'servizi.*.importo' => ['nullable', 'numeric', 'min:0'],
                'servizi.*.quantita_default' => ['nullable', 'integer', 'min:1'],
                'servizi.*.note' => ['nullable', 'string'],
                'servizi.*.attivo' => ['nullable', 'boolean'],
            ],
            [
                'name.required' => 'Il nome dell’amministratore è obbligatorio.',
                'email.required' => 'L’email di accesso è obbligatoria.',
                'email.email' => 'L’email di accesso non è valida.',
                'email.unique' => 'L’email di accesso è già utilizzata da un altro utente.',
                'username.required' => 'Lo username è obbligatorio.',
                'username.unique' => 'Lo username è già utilizzato da un altro utente.',
                'password.required' => 'La password è obbligatoria.',
                'password.min' => 'La password deve contenere almeno 8 caratteri.',
            ]
        );
    }

    private function extractAdminPayload(array $data, ?User $admin = null): array
    {
        return $this->normalizeGeoLabels([
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? ($admin->display_name ?? null),
            'email' => $data['email'],
            'username' => $data['username'],
            'telefono' => $data['telefono'] ?? ($admin->telefono ?? null),
            'qualifica' => $data['qualifica'] ?? ($admin->qualifica ?? null),
            'ragione_sociale' => $data['ragione_sociale'] ?? ($admin->ragione_sociale ?? null),
            'codice_fiscale' => $data['codice_fiscale'] ?? ($admin->codice_fiscale ?? null),
            'partita_iva' => $data['partita_iva'] ?? ($admin->partita_iva ?? null),
            'codice_destinatario' => $data['codice_destinatario'] ?? ($admin->codice_destinatario ?? null),
            'pec' => $data['pec'] ?? ($admin->pec ?? null),
            'indirizzo' => $data['indirizzo'] ?? ($admin->indirizzo ?? null),
            'numero_civico' => $data['numero_civico'] ?? ($admin->numero_civico ?? null),
            'cap' => $data['cap'] ?? ($admin->cap ?? null),
            'citta' => $data['citta'] ?? ($admin->citta ?? null),
            'provincia' => $data['provincia'] ?? ($admin->provincia ?? null),
            'regione' => $data['regione'] ?? ($admin->regione ?? null),
            'attivo' => (bool) ($data['attivo'] ?? ($admin->attivo ?? true)),
        ]);
    }

    private function syncAdminServizi(User $admin, array $serviziRows): void
    {
        $seenIds = [];

        foreach ($serviziRows as $row) {
            $nome = trim((string) ($row['nome'] ?? ''));
            if ($nome === '') {
                continue;
            }

            $servizio = null;
            if (!empty($row['id'])) {
                $servizio = $admin->adminServizi()->whereKey($row['id'])->first();
                $seenIds[] = (int) $row['id'];
            }

            $payload = [
                'nome' => $nome,
                'tipo_costo' => trim((string) ($row['tipo_costo'] ?? 'per_struttura')) ?: 'per_struttura',
                'importo' => $row['importo'] ?? 0,
                'quantita_default' => max(1, (int) ($row['quantita_default'] ?? 1)),
                'note' => $row['note'] ?? null,
                'attivo' => (bool) ($row['attivo'] ?? false),
            ];

            if ($servizio) {
                $servizio->update($payload);
                continue;
            }

            $admin->adminServizi()->create($payload);
        }

        if ($admin->exists) {
            $admin->adminServizi()
                ->when(!empty($seenIds), fn ($query) => $query->whereNotIn('id', $seenIds))
                ->update(['attivo' => false]);
        }
    }

    private function buildProformaDraftData(User $admin, Request $request, ?AdminFatturazione $proforma = null): array
    {
        $rows = collect();

        if ($proforma) {
            $rows = $proforma->righe->map(function ($riga) {
                return [
                    'key' => 'riga_' . $riga->id,
                    'admin_servizio_id' => $riga->admin_servizio_id,
                    'proprietario_id' => $riga->proprietario_id,
                    'destinazione' => $riga->proprietario?->nome ?: 'Amministratore',
                    'descrizione' => $riga->descrizione,
                    'quantita' => $riga->quantita,
                    'prezzo_unitario' => $riga->prezzo_unitario,
                    'sconto_tipo' => $riga->sconto_tipo ?: 'percentuale',
                    'sconto_valore' => $riga->sconto_valore ?: 0,
                    'aliquota_iva' => $riga->aliquota_iva ?: 22,
                    'totale' => $riga->totale,
                    'selected' => true,
                ];
            })->values();
        } else {
            $servizi = $admin->adminServizi()->where('attivo', true)->with('proprietari')->orderBy('nome')->get();

            foreach ($servizi as $servizio) {
                if ($servizio->proprietari->isNotEmpty()) {
                    foreach ($servizio->proprietari as $proprietario) {
                        $rows->push([
                            'key' => 'servizio_' . $servizio->id . '_' . ($proprietario->pivot->struttura_id ?: $proprietario->id),
                            'admin_servizio_id' => $servizio->id,
                            'proprietario_id' => $proprietario->id,
                            'destinazione' => $proprietario->nome,
                            'descrizione' => $servizio->nome,
                            'quantita' => $proprietario->pivot->quantita ?: ($servizio->quantita_default ?: 1),
                            'prezzo_unitario' => $proprietario->pivot->importo_override ?? $servizio->importo,
                            'sconto_tipo' => 'percentuale',
                            'sconto_valore' => 0,
                            'aliquota_iva' => 22,
                            'totale' => 0,
                            'selected' => true,
                        ]);
                    }
                } else {
                    $rows->push([
                        'key' => 'servizio_' . $servizio->id,
                        'admin_servizio_id' => $servizio->id,
                        'proprietario_id' => null,
                        'destinazione' => 'Amministratore',
                        'descrizione' => $servizio->nome,
                        'quantita' => $servizio->quantita_default ?: 1,
                        'prezzo_unitario' => $servizio->importo,
                        'sconto_tipo' => 'percentuale',
                        'sconto_valore' => 0,
                        'aliquota_iva' => 22,
                        'totale' => 0,
                        'selected' => true,
                    ]);
                }
            }
        }

        return [
            'numero' => old('proforma_numero', $proforma?->numero ?: $this->nextProformaNumber($admin)),
            'data_documento' => old('proforma_data', optional($proforma?->data_documento)->toDateString() ?: now()->toDateString()),
            'righe' => $rows,
        ];
    }

    private function validateAndBuildProformaPayload(Request $request, User $admin): array
    {
        $data = $request->validate([
            'proforma_numero' => ['required', 'string', 'max:50'],
            'proforma_data' => ['required'],
            'proforma_note' => ['nullable', 'string'],
            'numero_fattura' => ['nullable', 'string', 'max:80'],
            'data_pagamento' => ['nullable'],
            'proforma_righe' => ['nullable', 'array'],
            'custom_righe' => ['nullable', 'array'],
        ]);

        $righe = collect();

        foreach ((array) $request->input('proforma_righe', []) as $row) {
            if (!filter_var($row['selected'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $righe->push($this->normalizeProformaRow($row));
        }

        foreach ((array) $request->input('custom_righe', []) as $row) {
            $descrizione = trim((string) ($row['descrizione'] ?? ''));
            if ($descrizione === '') {
                continue;
            }

            $righe->push($this->normalizeCustomProformaRow($row));
        }

        if ($righe->isEmpty()) {
            abort(422, 'La proforma deve contenere almeno una riga.');
        }

        $totali = $this->calculateProformaTotals($righe);

        return [
            'numero' => $data['proforma_numero'],
            'data_documento' => $data['proforma_data'],
            'note' => $data['proforma_note'] ?? null,
            'numero_fattura' => $data['numero_fattura'] ?? null,
            'data_pagamento' => $data['data_pagamento'] ?? null,
            'destinatario' => [
                'intestazione' => $admin->ragione_sociale ?: $admin->name,
                'partita_iva' => $admin->partita_iva,
                'codice_fiscale' => $admin->codice_fiscale,
                'pec' => $admin->pec,
                'indirizzo' => trim(collect([$admin->indirizzo, $admin->numero_civico])->filter()->implode(' ')),
                'cap' => $admin->cap,
                'citta' => $admin->citta,
                'provincia' => $admin->provincia,
            ],
            'righe' => $righe->all(),
            'totali' => $totali,
        ];
    }

    private function normalizeProformaRow(array $row): array
    {
        $quantita = max(1, (int) ($row['quantita'] ?? 1));
        $prezzo = (float) ($row['prezzo_unitario'] ?? 0);
        $scontoTipo = ($row['sconto_tipo'] ?? 'percentuale') === 'importo' ? 'importo' : 'percentuale';
        $scontoValore = (float) ($row['sconto_valore'] ?? 0);
        $aliquotaIva = (float) ($row['aliquota_iva'] ?? 22);
        $lordo = $quantita * $prezzo;
        $sconto = $scontoTipo === 'importo'
            ? min($lordo, $scontoValore)
            : min($lordo, ($lordo * $scontoValore) / 100);
        $imponibile = max(0, $lordo - $sconto);
        $totaleIva = ($imponibile * $aliquotaIva) / 100;
        $totale = $imponibile + $totaleIva;

        return [
            'proprietario_id' => $row['proprietario_id'] ?: null,
            'admin_servizio_id' => $row['admin_servizio_id'] ?: null,
            'descrizione' => trim((string) ($row['descrizione'] ?? 'Servizio amministratore')),
            'quantita' => $quantita,
            'prezzo_unitario' => $prezzo,
            'sconto_tipo' => $scontoTipo,
            'sconto_valore' => $scontoValore,
            'imponibile' => $imponibile,
            'aliquota_iva' => $aliquotaIva,
            'totale_iva' => $totaleIva,
            'totale' => $totale,
            'note' => $row['note'] ?? null,
        ];
    }

    private function normalizeCustomProformaRow(array $row): array
    {
        $row['selected'] = true;
        $row['admin_servizio_id'] = $row['catalogo_servizio_id'] ?? null;

        return $this->normalizeProformaRow($row);
    }

    private function calculateProformaTotals($righe): array
    {
        $righe = collect($righe);

        return [
            'imponibile' => (float) $righe->sum('imponibile'),
            'sconto' => (float) $righe->sum(function ($riga) {
                $lordo = ((int) ($riga['quantita'] ?? 1)) * ((float) ($riga['prezzo_unitario'] ?? 0));

                return max(0, $lordo - (float) ($riga['imponibile'] ?? 0));
            }),
            'iva' => (float) $righe->sum('totale_iva'),
            'totale' => (float) $righe->sum('totale'),
        ];
    }

    private function extractCustomRows(AdminFatturazione $proforma): array
    {
        return $proforma->righe
            ->filter(fn ($riga) => !$riga->admin_servizio_id)
            ->map(function ($riga) {
                return [
                    'catalogo_servizio_id' => null,
                    'descrizione' => $riga->descrizione,
                    'quantita' => $riga->quantita,
                    'prezzo_unitario' => $riga->prezzo_unitario,
                    'sconto_tipo' => $riga->sconto_tipo,
                    'sconto_valore' => $riga->sconto_valore,
                    'aliquota_iva' => $riga->aliquota_iva,
                    'note' => $riga->note,
                ];
            })
            ->values()
            ->all();
    }

    private function nextProformaNumber(User $admin): string
    {
        $lastNumber = AdminFatturazione::query()
            ->where('user_id', $admin->id)
            ->orderByDesc('id')
            ->value('numero');

        $next = 1;
        if ($lastNumber && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'ADM-P-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function findAdmin(int $id): User
    {
        return User::query()
            ->whereIn('ruolo', ['admin', 'admin_disabled'])
            ->findOrFail($id);
    }

    private function findOwnedProforma(User $admin, int $fatturazioneId): AdminFatturazione
    {
        return AdminFatturazione::with(['righe.proprietario', 'righe.servizio'])
            ->where('user_id', $admin->id)
            ->findOrFail($fatturazioneId);
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
}
