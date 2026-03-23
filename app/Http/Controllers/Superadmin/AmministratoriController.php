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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AmministratoriController extends Controller
{
    public function index()
    {
        $admins = User::where('ruolo', 'admin')
            ->withCount([
                'proprietariGestiti as proprietari_count',
                'proprietariGestiti as proprietari_attivi_count' => fn ($query) => $query->where('attivo', true),
            ])
            ->orderBy('name')
            ->get();

        $admins->each(function (User $admin): void {
            $admin->strutture_count = Struttura::whereHas('proprietario', function ($query) use ($admin) {
                $query->where('admin_id', $admin->id);
            })->count();
        });

        return view('superadmin.amministratori.index', [
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return view('superadmin.amministratori.form', [
            'admin' => new User(),
            'proprietari' => Proprietario::with('admin')->withCount('strutture')->orderBy('nome')->get(),
            'selectedProprietari' => [],
            'summary' => [
                'proprietari' => 0,
                'strutture' => 0,
                'strutture_attive' => 0,
            ],
            'servizi' => collect(),
            'fatturazione' => [
                'proprietari' => collect(),
                'totale' => 0,
                'righe' => collect(),
            ],
            'proforme' => collect(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAdmin($request);
        $data = $this->normalizeGeoFields($data);
        $createdAdmin = null;

        DB::transaction(function () use ($data, &$createdAdmin): void {
            $createdAdmin = User::create($this->buildAdminPayload($data, true));
            $this->syncProprietari($createdAdmin, $data['proprietari'] ?? []);
            $this->createProprietarioFromAdmin($createdAdmin, $data);
            $this->syncServizi($createdAdmin, $data);
        });

        return redirect()
            ->route('superadmin.amministratori.edit', ['id' => $createdAdmin->id, 'tab' => $data['active_tab'] ?? 'personale'])
            ->with('success', 'Amministratore creato correttamente.');
    }

    public function edit(int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);

        return view('superadmin.amministratori.form', [
            'admin' => $admin,
            'proprietari' => Proprietario::with('admin')->withCount('strutture')->orderBy('nome')->get(),
            'selectedProprietari' => Proprietario::where('admin_id', $admin->id)->pluck('id')->all(),
            'summary' => $this->buildSummary($admin),
            'servizi' => $admin->adminServizi()->orderBy('nome')->get(),
            'fatturazione' => $this->buildFatturazione($admin),
            'proforme' => $admin->fatturazioniAmministratore()->with('righe')->latest('data_documento')->latest('id')->get(),
            'mode' => 'edit',
        ]);
    }

    public function createProformaPage(int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);

        return view('superadmin.amministratori.proforma-form', [
            'admin' => $admin,
            'fatturazione' => $this->buildFatturazione($admin),
            'catalogoServizi' => $admin->adminServizi()->where('attivo', true)->orderBy('nome')->get(),
            'proforma' => null,
            'customRighe' => [],
        ]);
    }

    public function storeProforma(Request $request, int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $data = $this->validateProforma($request);

        $fattura = DB::transaction(function () use ($admin, $data) {
            return $this->createProforma($admin, array_merge($data, [
                'fatturazione_action' => 'create',
            ]));
        });

        if (!$fattura) {
            return redirect()
                ->back()
                ->withInput()
                ->with('warning', 'Aggiungi almeno una riga valida alla proforma prima di salvarla.');
        }

        return redirect()
            ->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $fattura->id])
            ->with('success', 'Proforma creata correttamente.');
    }

    public function showProforma(int $id, int $fatturazione)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $proforma = AdminFatturazione::with(['righe.proprietario', 'righe.servizio'])
            ->where('user_id', $admin->id)
            ->findOrFail($fatturazione);

        return view('superadmin.amministratori.proforma-show', [
            'admin' => $admin,
            'proforma' => $proforma,
        ]);
    }

    public function editProforma(int $id, int $fatturazione)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $proforma = AdminFatturazione::with(['righe.proprietario', 'righe.servizio'])
            ->where('user_id', $admin->id)
            ->findOrFail($fatturazione);

        if ($proforma->stato !== 'proforma') {
            return redirect()
                ->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
                ->with('warning', 'Questa proforma non è più modificabile.');
        }

        return view('superadmin.amministratori.proforma-form', [
            'admin' => $admin,
            'fatturazione' => $this->buildFatturazioneFromProforma($proforma),
            'catalogoServizi' => $admin->adminServizi()->where('attivo', true)->orderBy('nome')->get(),
            'proforma' => $proforma,
            'customRighe' => $this->buildCustomRowsFromProforma($proforma),
        ]);
    }

    public function updateProforma(Request $request, int $id, int $fatturazione)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $proforma = AdminFatturazione::with('righe')
            ->where('user_id', $admin->id)
            ->findOrFail($fatturazione);

        if ($proforma->stato !== 'proforma') {
            return redirect()
                ->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
                ->with('warning', 'Questa proforma non è più modificabile.');
        }

        $data = $this->validateProforma($request);

        $updated = DB::transaction(function () use ($admin, $proforma, $data): bool {
            return $this->replaceProformaContent($admin, $proforma, $data);
        });

        if (!$updated) {
            return redirect()
                ->back()
                ->withInput()
                ->with('warning', 'Aggiungi almeno una riga valida alla proforma prima di aggiornarla.');
        }

        return redirect()
            ->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('success', 'Proforma aggiornata correttamente.');
    }

    public function closeProforma(int $id, int $fatturazione)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $proforma = AdminFatturazione::where('user_id', $admin->id)->findOrFail($fatturazione);

        if ($proforma->stato === 'fatturata') {
            return redirect()->back()->with('warning', 'La proforma è già fatturata.');
        }

        $proforma->stato = 'chiusa';
        $proforma->save();

        return redirect()
            ->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('success', 'Proforma chiusa.');
    }

    public function markProformaFatturata(int $id, int $fatturazione)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $proforma = AdminFatturazione::where('user_id', $admin->id)->findOrFail($fatturazione);

        $proforma->stato = 'fatturata';
        $proforma->save();

        return redirect()
            ->route('superadmin.amministratori.proforme.show', ['id' => $admin->id, 'fatturazione' => $proforma->id])
            ->with('success', 'Proforma segnata come fatturata.');
    }

    public function printProforma(int $id, int $fatturazione)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $proforma = AdminFatturazione::with(['righe.proprietario', 'righe.servizio'])
            ->where('user_id', $admin->id)
            ->findOrFail($fatturazione);

        return view('superadmin.amministratori.proforma-print', [
            'admin' => $admin,
            'proforma' => $proforma,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);

        $data = $this->validateAdmin($request, $admin);
        $data = $this->normalizeGeoFields($data);

        DB::transaction(function () use ($admin, $data): void {
            $admin->fill($this->buildAdminPayload($data, false));
            $admin->save();

            $this->syncProprietari($admin, $data['proprietari'] ?? []);
            $this->createProprietarioFromAdmin($admin, $data);
            $this->syncServizi($admin, $data);
            $this->createProforma($admin, $data);
        });

        return redirect()
            ->route('superadmin.amministratori.edit', ['id' => $admin->id, 'tab' => $data['active_tab'] ?? 'personale'])
            ->with('success', ($data['fatturazione_action'] ?? null) === 'create' ? 'Proforma creata correttamente.' : 'Amministratore aggiornato correttamente.');
    }

    public function disable(int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $admin->attivo = false;
        $admin->save();

        return redirect()->route('superadmin.amministratori.index')->with('success', 'Amministratore disabilitato.');
    }

    public function enable(int $id)
    {
        $admin = User::where('ruolo', 'admin')->findOrFail($id);
        $admin->attivo = true;
        $admin->save();

        return redirect()->route('superadmin.amministratori.index')->with('success', 'Amministratore riattivato.');
    }

    private function validateAdmin(Request $request, ?User $admin = null): array
    {
        $emailRule = 'unique:users,email';
        if ($admin) {
            $emailRule .= ',' . $admin->id;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'telefono' => ['nullable', 'string', 'max:50'],
            'qualifica' => ['nullable', 'string', 'max:120'],
            'ragione_sociale' => ['nullable', 'string', 'max:180'],
            'codice_fiscale' => ['nullable', 'string', 'max:32'],
            'partita_iva' => ['nullable', 'string', 'max:32'],
            'codice_destinatario' => ['nullable', 'string', 'max:32'],
            'codice_unico' => ['nullable', 'string', 'max:7', 'regex:/^[A-Z0-9]{0,7}$/i'],
            'pec' => ['nullable', 'email', 'max:180'],
            'indirizzo' => ['nullable', 'string', 'max:180'],
            'numero_civico' => ['nullable', 'string', 'max:20'],
            'cap' => ['nullable', 'string', 'max:16'],
            'citta' => ['nullable', 'string', 'max:120'],
            'provincia' => ['nullable', 'string', 'max:8'],
            'regione' => ['nullable', 'string', 'max:120'],
            'nazione' => ['nullable', 'string', 'max:120'],
            'geo_manual' => ['nullable', 'boolean'],
            'latitudine' => ['nullable', 'numeric'],
            'longitudine' => ['nullable', 'numeric'],
            'compenso_servizio' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'note_servizio' => ['nullable', 'string'],
            'note_amministrative' => ['nullable', 'string'],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8'],
            'active_tab' => ['nullable', 'string', 'max:50'],
            'proprietari' => ['nullable', 'array'],
            'proprietari.*' => ['integer', 'exists:proprietari,id'],
            'nuovo_proprietario_nome' => ['nullable', 'string', 'max:255'],
            'nuovo_proprietario_email' => ['nullable', 'email', 'max:255'],
            'nuovo_proprietario_telefono' => ['nullable', 'string', 'max:50'],
            'nuovo_proprietario_note' => ['nullable', 'string'],
            'servizi' => ['nullable', 'array'],
            'servizi.*.nome' => ['nullable', 'string', 'max:160'],
            'servizi.*.tipo_costo' => ['nullable', 'in:per_struttura,flat,percentuale'],
            'servizi.*.importo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'servizi.*.quantita_default' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'servizi.*.note' => ['nullable', 'string'],
            'servizi.*.attivo' => ['nullable', 'boolean'],
            'servizi.*.delete' => ['nullable', 'boolean'],
            'nuovo_servizio_nome' => ['nullable', 'string', 'max:160'],
            'nuovo_servizio_tipo_costo' => ['nullable', 'in:per_struttura,flat,percentuale'],
            'nuovo_servizio_importo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'nuovo_servizio_quantita_default' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'nuovo_servizio_note' => ['nullable', 'string'],
            'fatturazione_action' => ['nullable', 'in:create'],
            'proforma_numero' => ['nullable', 'string', 'max:50'],
            'proforma_data' => ['nullable', 'date'],
            'proforma_note' => ['nullable', 'string'],
            'proforma_righe' => ['nullable', 'array'],
            'proforma_righe.*.selected' => ['nullable', 'boolean'],
            'proforma_righe.*.proprietario_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'proforma_righe.*.admin_servizio_id' => ['nullable', 'integer', 'exists:admin_servizi,id'],
            'proforma_righe.*.descrizione' => ['nullable', 'string', 'max:255'],
            'proforma_righe.*.quantita' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'proforma_righe.*.prezzo_unitario' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'proforma_righe.*.note' => ['nullable', 'string'],
        ]);
    }

    private function validateProforma(Request $request): array
    {
        $request->merge($this->normalizeProformaPayload($request->all()));

        return $request->validate([
            'proforma_numero' => ['nullable', 'string', 'max:50'],
            'proforma_data' => ['required', 'date'],
            'proforma_note' => ['nullable', 'string'],
            'proforma_righe' => ['nullable', 'array'],
            'proforma_righe.*.selected' => ['nullable', 'boolean'],
            'proforma_righe.*.proprietario_id' => ['nullable', 'integer', 'exists:proprietari,id'],
            'proforma_righe.*.admin_servizio_id' => ['nullable', 'integer', 'exists:admin_servizi,id'],
            'proforma_righe.*.descrizione' => ['nullable', 'string', 'max:255'],
            'proforma_righe.*.quantita' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'proforma_righe.*.prezzo_unitario' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'proforma_righe.*.aliquota_iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'proforma_righe.*.sconto_tipo' => ['nullable', 'in:percentuale,importo'],
            'proforma_righe.*.sconto_valore' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'proforma_righe.*.note' => ['nullable', 'string'],
            'custom_righe' => ['nullable', 'array'],
            'custom_righe.*.catalogo_servizio_id' => ['nullable', 'integer', 'exists:admin_servizi,id'],
            'custom_righe.*.descrizione' => ['nullable', 'string', 'max:255'],
            'custom_righe.*.quantita' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'custom_righe.*.prezzo_unitario' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'custom_righe.*.aliquota_iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'custom_righe.*.sconto_tipo' => ['nullable', 'in:percentuale,importo'],
            'custom_righe.*.sconto_valore' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'custom_righe.*.note' => ['nullable', 'string'],
        ]);
    }

    private function normalizeProformaPayload(array $data): array
    {
        $normalizeRows = function (array $rows): array {
            foreach ($rows as $key => $row) {
                if (!is_array($row)) {
                    continue;
                }

                foreach (['prezzo_unitario', 'aliquota_iva', 'sconto_valore'] as $field) {
                    if (array_key_exists($field, $row)) {
                        $rows[$key][$field] = $this->normalizeDecimalInput($row[$field]);
                    }
                }
            }

            return $rows;
        };

        if (isset($data['proforma_righe']) && is_array($data['proforma_righe'])) {
            $data['proforma_righe'] = $normalizeRows($data['proforma_righe']);
        }

        if (isset($data['custom_righe']) && is_array($data['custom_righe'])) {
            $data['custom_righe'] = $normalizeRows($data['custom_righe']);
        }

        return $data;
    }

    private function normalizeDecimalInput($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $value = str_replace([' ', "\xc2\xa0"], '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);

            return $value;
        }

        if (str_contains($value, ',')) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    private function buildAdminPayload(array $data, bool $creating): array
    {
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'qualifica' => $data['qualifica'] ?? null,
            'ragione_sociale' => $data['ragione_sociale'] ?? null,
            'codice_fiscale' => $data['codice_fiscale'] ?? null,
            'partita_iva' => $data['partita_iva'] ?? null,
            'codice_destinatario' => $data['codice_destinatario'] ?? null,
            'codice_unico' => $data['codice_unico'] ?? null,
            'pec' => $data['pec'] ?? null,
            'indirizzo' => $data['indirizzo'] ?? null,
            'numero_civico' => $data['numero_civico'] ?? null,
            'cap' => $data['cap'] ?? null,
            'citta' => $data['citta'] ?? null,
            'provincia' => $data['provincia'] ?? null,
            'regione' => $data['regione'] ?? null,
            'nazione' => $data['nazione'] ?? null,
            'geo_manual' => (bool) ($data['geo_manual'] ?? false),
            'latitudine' => $data['latitudine'] ?? null,
            'longitudine' => $data['longitudine'] ?? null,
            'compenso_servizio' => $data['compenso_servizio'] ?? null,
            'note_servizio' => $data['note_servizio'] ?? null,
            'note_amministrative' => $data['note_amministrative'] ?? null,
        ];

        if ($creating) {
            $payload['avatar'] = '';
            $payload['password'] = Hash::make($data['password']);
            $payload['ruolo'] = 'admin';
            $payload['attivo'] = true;
        } elseif (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        return $payload;
    }

    private function syncProprietari(User $admin, array $proprietari): void
    {
        $selectedIds = collect($proprietari)->map(fn ($id) => (int) $id)->all();

        Proprietario::where('admin_id', $admin->id)
            ->whereNotIn('id', $selectedIds === [] ? [0] : $selectedIds)
            ->update(['admin_id' => null]);

        if ($selectedIds !== []) {
            Proprietario::whereIn('id', $selectedIds)->update(['admin_id' => $admin->id]);
        }
    }

    private function buildSummary(User $admin): array
    {
        $struttureQuery = Struttura::whereHas('proprietario', function ($query) use ($admin) {
            $query->where('admin_id', $admin->id);
        });

        return [
            'proprietari' => Proprietario::where('admin_id', $admin->id)->count(),
            'strutture' => (clone $struttureQuery)->count(),
            'strutture_attive' => (clone $struttureQuery)->where('attiva', true)->count(),
        ];
    }

    private function buildFatturazione(User $admin): array
    {
        $servizi = $admin->adminServizi()
            ->orderBy('nome')
            ->get()
            ->map(function (AdminServizio $servizio) use ($admin) {
                $quantita = max(1, (int) ($servizio->quantita_default ?? 1));
                $prezzo = (float) ($servizio->importo ?? 0);
                $imponibile = $quantita * $prezzo;

                return [
                    'key' => 'servizio-' . $servizio->id,
                    'destinazione' => $admin->ragione_sociale ?: $admin->name,
                    'selected' => false,
                    'admin_servizio_id' => $servizio->id,
                    'descrizione' => $servizio->nome,
                    'tipo_costo' => $servizio->tipo_costo,
                    'quantita' => $quantita,
                    'prezzo_unitario' => $prezzo,
                    'aliquota_iva' => 22,
                    'sconto_tipo' => 'percentuale',
                    'sconto_valore' => 0,
                    'imponibile' => $imponibile,
                    'totale_iva' => round($imponibile * 0.22, 2),
                    'totale' => $imponibile,
                    'note' => $servizio->note,
                ];
            });

        return [
            'servizi' => $servizi,
            'totale' => $servizi->sum('totale'),
            'righe' => $servizi,
        ];
    }

    private function buildFatturazioneFromProforma(AdminFatturazione $proforma): array
    {
        $righe = $proforma->righe
            ->filter(fn ($riga) => filled($riga->admin_servizio_id))
            ->values()
            ->map(function ($riga) {
                $key = 'stored-' . $riga->id;

                return [
                    'key' => $key,
                    'destinazione' => 'Amministratore',
                    'selected' => true,
                    'admin_servizio_id' => $riga->admin_servizio_id,
                    'descrizione' => $riga->descrizione,
                    'quantita' => (int) $riga->quantita,
                    'prezzo_unitario' => (float) $riga->prezzo_unitario,
                    'aliquota_iva' => (float) ($riga->aliquota_iva ?? 22),
                    'sconto_tipo' => $riga->sconto_tipo ?: 'percentuale',
                    'sconto_valore' => (float) ($riga->sconto_valore ?? 0),
                    'imponibile' => (float) ($riga->imponibile ?? 0),
                    'totale_iva' => (float) ($riga->totale_iva ?? 0),
                    'totale' => (float) ($riga->totale ?? 0),
                    'note' => $riga->note,
                ];
            });

        return [
            'servizi' => $righe,
            'totale' => $righe->sum('totale'),
            'righe' => $righe,
        ];
    }

    private function buildCustomRowsFromProforma(AdminFatturazione $proforma): array
    {
        return $proforma->righe
            ->filter(fn ($riga) => blank($riga->admin_servizio_id))
            ->values()
            ->map(function ($riga) {
                return [
                    'catalogo_servizio_id' => $riga->admin_servizio_id,
                    'descrizione' => $riga->descrizione,
                    'quantita' => (int) $riga->quantita,
                    'prezzo_unitario' => (float) $riga->prezzo_unitario,
                    'sconto_tipo' => $riga->sconto_tipo ?: 'percentuale',
                    'sconto_valore' => (float) ($riga->sconto_valore ?? 0),
                    'aliquota_iva' => (float) ($riga->aliquota_iva ?? 22),
                    'note' => $riga->note,
                ];
            })
            ->all();
    }

    private function createProprietarioFromAdmin(User $admin, array $data): void
    {
        $nome = trim((string) ($data['nuovo_proprietario_nome'] ?? ''));
        if ($nome === '') {
            return;
        }

        Proprietario::create([
            'admin_id' => $admin->id,
            'nome' => $nome,
            'email' => $data['nuovo_proprietario_email'] ?? null,
            'telefono' => $data['nuovo_proprietario_telefono'] ?? null,
            'note' => $data['nuovo_proprietario_note'] ?? null,
            'attivo' => true,
        ]);
    }

    private function syncServizi(User $admin, array $data): void
    {
        $servizi = $data['servizi'] ?? [];

        foreach ($servizi as $servizioId => $row) {
            $servizio = $admin->adminServizi()->whereKey($servizioId)->first();
            if (!$servizio) {
                continue;
            }

            if ((bool) ($row['delete'] ?? false)) {
                $servizio->delete();
                continue;
            }

            if (!array_key_exists('nome', $row)) {
                continue;
            }

            $nome = trim((string) ($row['nome'] ?? ''));
            if ($nome === '') {
                continue;
            }

            $servizio->update([
                'nome' => $nome,
                'tipo_costo' => $row['tipo_costo'] ?? 'per_struttura',
                'importo' => $row['importo'] ?? null,
                'quantita_default' => max(1, (int) ($row['quantita_default'] ?? 1)),
                'note' => $row['note'] ?? null,
                'attivo' => (bool) ($row['attivo'] ?? false),
            ]);
        }

        $nuovoNome = trim((string) ($data['nuovo_servizio_nome'] ?? ''));
        if ($nuovoNome === '') {
            return;
        }

        $admin->adminServizi()->create([
            'nome' => $nuovoNome,
            'tipo_costo' => $data['nuovo_servizio_tipo_costo'] ?? 'per_struttura',
            'importo' => $data['nuovo_servizio_importo'] ?? null,
            'quantita_default' => max(1, (int) ($data['nuovo_servizio_quantita_default'] ?? 1)),
            'note' => $data['nuovo_servizio_note'] ?? null,
            'attivo' => true,
        ]);
    }

    private function createProforma(User $admin, array $data): ?AdminFatturazione
    {
        if (($data['fatturazione_action'] ?? null) !== 'create') {
            return null;
        }

        $righeBase = collect($data['proforma_righe'] ?? [])
            ->filter(function (array $row) {
                $selected = filter_var($row['selected'] ?? false, FILTER_VALIDATE_BOOLEAN);

                return $selected && (filled($row['descrizione'] ?? null) || filled($row['admin_servizio_id'] ?? null));
            })
            ->map(fn (array $row) => $this->buildProformaLine($row, false))
            ->values();

        $catalogo = $admin->adminServizi()->get()->keyBy('id');

        $righeCustom = collect($data['custom_righe'] ?? [])
            ->filter(function (array $row) {
                return filled($row['descrizione'] ?? null) || filled($row['catalogo_servizio_id'] ?? null);
            })
            ->map(function (array $row) use ($catalogo) {
                $catalogService = isset($row['catalogo_servizio_id']) ? $catalogo->get((int) $row['catalogo_servizio_id']) : null;

                if ($catalogService) {
                    $row['admin_servizio_id'] = $catalogService->id;
                    $row['descrizione'] = trim((string) ($row['descrizione'] ?? '')) ?: $catalogService->nome;
                    if (!filled($row['prezzo_unitario'] ?? null)) {
                        $row['prezzo_unitario'] = $catalogService->importo;
                    }
                }

                return $this->buildProformaLine($row, true);
            })
            ->values();

        $righe = $righeBase->concat($righeCustom)->values();

        if ($righe->isEmpty()) {
            return null;
        }

        $fattura = $admin->fatturazioniAmministratore()->create([
            'created_by' => auth()->id(),
            'numero' => trim((string) ($data['proforma_numero'] ?? '')) ?: $this->nextProformaNumber($admin),
            'data_documento' => $data['proforma_data'] ?? now()->toDateString(),
            'stato' => 'proforma',
            'intestazione' => $admin->ragione_sociale ?: $admin->name,
            'partita_iva' => $admin->partita_iva,
            'codice_fiscale' => $admin->codice_fiscale,
            'pec' => $admin->pec,
            'indirizzo' => trim(collect([$admin->indirizzo, $admin->numero_civico])->filter()->implode(' ')) ?: null,
            'cap' => $admin->cap,
            'citta' => $admin->citta,
            'provincia' => $admin->provincia,
            'imponibile' => $righe->sum('imponibile'),
            'totale_sconto' => $righe->sum('sconto_importo'),
            'totale_iva' => $righe->sum('totale_iva'),
            'totale' => $righe->sum('totale'),
            'note' => $data['proforma_note'] ?? null,
        ]);

        $fattura->righe()->createMany($righe->map(fn ($row) => collect($row)->except('sconto_importo')->all())->all());

        return $fattura;
    }

    private function replaceProformaContent(User $admin, AdminFatturazione $proforma, array $data): bool
    {
        $new = $this->createProforma($admin, array_merge($data, [
            'fatturazione_action' => 'create',
            'proforma_numero' => $this->nextTemporaryProformaNumber($admin, $proforma),
        ]));

        if (!$new) {
            return false;
        }

        $new->load('righe');

        $proforma->update([
            'data_documento' => $new->data_documento,
            'stato' => 'proforma',
            'intestazione' => $new->intestazione,
            'partita_iva' => $new->partita_iva,
            'codice_fiscale' => $new->codice_fiscale,
            'pec' => $new->pec,
            'indirizzo' => $new->indirizzo,
            'cap' => $new->cap,
            'citta' => $new->citta,
            'provincia' => $new->provincia,
            'imponibile' => $new->imponibile,
            'totale_sconto' => $new->totale_sconto,
            'totale_iva' => $new->totale_iva,
            'totale' => $new->totale,
            'note' => $new->note,
        ]);

        $proforma->righe()->delete();
        $proforma->righe()->createMany(
            $new->righe->map(fn ($riga) => $riga->only([
                'proprietario_id',
                'admin_servizio_id',
                'descrizione',
                'quantita',
                'prezzo_unitario',
                'sconto_tipo',
                'sconto_valore',
                'imponibile',
                'aliquota_iva',
                'totale_iva',
                'totale',
                'note',
            ]))->all()
        );

        $new->righe()->delete();
        $new->delete();

        return true;
    }

    private function buildProformaLine(array $row, bool $custom): array
    {
        $quantita = max(1, (int) ($row['quantita'] ?? 1));
        $prezzoUnitario = (float) ($row['prezzo_unitario'] ?? 0);
        $subtotale = $quantita * $prezzoUnitario;
        $scontoTipo = $row['sconto_tipo'] ?? 'percentuale';
        $scontoValore = (float) ($row['sconto_valore'] ?? 0);
        $scontoImporto = $scontoTipo === 'importo'
            ? min($subtotale, $scontoValore)
            : round($subtotale * ($scontoValore / 100), 2);
        $imponibile = max(0, $subtotale - $scontoImporto);
        $aliquotaIva = (float) ($row['aliquota_iva'] ?? 22);
        $totaleIva = round($imponibile * ($aliquotaIva / 100), 2);
        $totale = $imponibile + $totaleIva;

        return [
            'proprietario_id' => $row['proprietario_id'] ?? null,
            'admin_servizio_id' => $custom ? ($row['admin_servizio_id'] ?? null) : ($row['admin_servizio_id'] ?? null),
            'descrizione' => $row['descrizione'] ?? ($custom ? 'Servizio personalizzato' : 'Servizio'),
            'quantita' => $quantita,
            'prezzo_unitario' => $prezzoUnitario,
            'sconto_tipo' => $scontoTipo,
            'sconto_valore' => $scontoValore,
            'sconto_importo' => $scontoImporto,
            'imponibile' => $imponibile,
            'aliquota_iva' => $aliquotaIva,
            'totale_iva' => $totaleIva,
            'totale' => $totale,
            'note' => $row['note'] ?? null,
        ];
    }

    private function nextProformaNumber(User $admin): string
    {
        $lastId = (int) $admin->fatturazioniAmministratore()->max('id');

        return 'PRO-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function nextTemporaryProformaNumber(User $admin, AdminFatturazione $current): string
    {
        $base = 'TMP-' . $current->id . '-';
        $counter = 1;

        do {
            $candidate = $base . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            $exists = $admin->fatturazioniAmministratore()
                ->where('numero', $candidate)
                ->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }

    private function normalizeGeoFields(array $data): array
    {
        $nazione = $data['nazione'] ?? null;
        $regione = $data['regione'] ?? null;
        $provincia = $data['provincia'] ?? null;
        $citta = $data['citta'] ?? null;

        if (is_numeric($nazione)) {
            $nazioneEntity = GeoNazione::find((int) $nazione);
            $nazione = $nazioneEntity?->nome ?? $nazione;
        }

        if (is_numeric($regione)) {
            $regioneEntity = GeoRegione::find((int) $regione);
            $regione = $regioneEntity?->nome ?? $regione;
        }

        if (is_numeric($provincia)) {
            $provinciaEntity = GeoProvincia::find((int) $provincia);
            $provincia = $provinciaEntity?->sigla ?: ($provinciaEntity?->nome ?? $provincia);
        }

        if (is_numeric($citta)) {
            $comune = GeoComune::find((int) $citta, ['nome', 'lat', 'lng']);
            $citta = $comune?->nome ?? $citta;

            if (blank($data['latitudine'] ?? null) && filled($comune?->lat)) {
                $data['latitudine'] = $comune->lat;
            }

            if (blank($data['longitudine'] ?? null) && filled($comune?->lng)) {
                $data['longitudine'] = $comune->lng;
            }
        }

        $data['nazione'] = $nazione;
        $data['regione'] = $regione;
        $data['provincia'] = $provincia;
        $data['citta'] = $citta;

        return $data;
    }
}
