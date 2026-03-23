<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AdminServizio;
use App\Models\GeoComune;
use App\Models\GeoNazione;
use App\Models\GeoProvincia;
use App\Models\GeoRegione;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\ProprietarioFatturazione;
use App\Models\Struttura;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProprietariController extends Controller
{
    public function index()
    {
        $proprietari = Proprietario::with('admin')->withCount('strutture')->orderBy('nome')->get();
        $admins = User::where('ruolo', 'admin')->orderBy('name')->get();

        return view('superadmin.proprietari.index', [
            'proprietari' => $proprietari,
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return view('superadmin.proprietari.form', [
            'proprietario' => new Proprietario(),
            'summary' => [
                'strutture' => 0,
                'strutture_attive' => 0,
                'utenti' => 0,
                'servizi_attivi' => 0,
            ],
            'serviziDisponibili' => collect(),
            'serviziAssegnati' => collect(),
            'fatturazione' => [
                'righe' => collect(),
                'totale' => 0,
                'strutture' => collect(),
            ],
            'proforme' => collect(),
            'licenzeProprietario' => collect(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProprietario($request);
        $data = $this->normalizeGeoFields($data);

        $proprietario = Proprietario::create($this->buildPayload($data));

        return redirect()
            ->route('superadmin.proprietari.edit', ['id' => $proprietario->id, 'tab' => $data['active_tab'] ?? 'personale'])
            ->with('success', 'Proprietario creato.');
    }

    public function edit(int $id)
    {
        $proprietario = Proprietario::with(['admin', 'servizi', 'strutture'])->findOrFail($id);

        return view('superadmin.proprietari.form', [
            'proprietario' => $proprietario,
            'summary' => $this->buildSummary($proprietario),
            'serviziDisponibili' => $serviziDisponibili = ($proprietario->admin_id
                ? AdminServizio::where('user_id', $proprietario->admin_id)->orderBy('nome')->get()
                : collect()),
            'serviziAssegnati' => $this->buildServiziAssegnati($proprietario, $serviziDisponibili),
            'struttureDisponibili' => Struttura::query()
                ->with('proprietario')
                ->where(function ($query) use ($proprietario) {
                    $query->whereNull('proprietario_id')
                        ->orWhere('proprietario_id', '!=', $proprietario->id);
                })
                ->orderBy('nome_struttura')
                ->get(),
            'fatturazione' => $this->buildFatturazione($proprietario),
            'proforme' => $proprietario->fatturazioni()->with('righe')->latest('data_documento')->latest('id')->get(),
            'licenzeProprietario' => LicenzaAssegnazione::with(['articolo', 'struttura'])
                ->where('proprietario_id', $proprietario->id)
                ->orderByDesc('attiva')
                ->orderBy('struttura_id')
                ->orderBy('data_scadenza')
                ->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $proprietario = Proprietario::with('strutture')->findOrFail($id);
        $data = $this->validateProprietario($request);
        $data = $this->normalizeProprietarioPayload($data);
        $data = $this->normalizeGeoFields($data);

        $proprietario->update($this->buildPayload($data));

        if ($proprietario->admin_id) {
            $this->syncServiziAssegnati($proprietario, $data);
        }

        return redirect()
            ->route('superadmin.proprietari.edit', ['id' => $proprietario->id, 'tab' => $data['active_tab'] ?? 'personale'])
            ->with('success', 'Proprietario aggiornato.');
    }

    public function createProformaPage(int $id)
    {
        $proprietario = Proprietario::with(['admin', 'servizi', 'strutture'])->findOrFail($id);

        return view('superadmin.proprietari.proforma-form', [
            'proprietario' => $proprietario,
            'fatturazione' => $this->buildFatturazione($proprietario),
            'catalogoServizi' => $proprietario->admin_id
                ? AdminServizio::where('user_id', $proprietario->admin_id)->where('attivo', true)->orderBy('nome')->get()
                : collect(),
            'proforma' => null,
            'customRighe' => [],
        ]);
    }

    public function storeProforma(Request $request, int $id)
    {
        $proprietario = Proprietario::with(['admin', 'strutture'])->findOrFail($id);
        $data = $this->validateProforma($request);

        $fattura = DB::transaction(function () use ($proprietario, $data) {
            return $this->createProforma($proprietario, array_merge($data, [
                'fatturazione_action' => 'create',
            ]));
        });

        if (!$fattura) {
            return redirect()->back()->withInput()->with('warning', 'Aggiungi almeno una riga valida alla proforma prima di salvarla.');
        }

        return redirect()
            ->route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $fattura->id])
            ->with('success', 'Proforma creata correttamente.');
    }

    public function showProforma(int $id, int $fatturazione)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proforma = ProprietarioFatturazione::with(['righe.struttura', 'righe.servizio'])
            ->where('proprietario_id', $proprietario->id)
            ->findOrFail($fatturazione);

        return view('superadmin.proprietari.proforma-show', [
            'proprietario' => $proprietario,
            'proforma' => $proforma,
        ]);
    }

    public function editProforma(int $id, int $fatturazione)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proforma = ProprietarioFatturazione::with(['righe.struttura', 'righe.servizio'])
            ->where('proprietario_id', $proprietario->id)
            ->findOrFail($fatturazione);

        if ($proforma->stato !== 'proforma') {
            return redirect()
                ->route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
                ->with('warning', 'Questa proforma non è più modificabile.');
        }

        return view('superadmin.proprietari.proforma-form', [
            'proprietario' => $proprietario,
            'fatturazione' => $this->buildFatturazioneFromProforma($proforma),
            'catalogoServizi' => $proprietario->admin_id
                ? AdminServizio::where('user_id', $proprietario->admin_id)->where('attivo', true)->orderBy('nome')->get()
                : collect(),
            'proforma' => $proforma,
            'customRighe' => $this->buildCustomRowsFromProforma($proforma),
        ]);
    }

    public function updateProforma(Request $request, int $id, int $fatturazione)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proforma = ProprietarioFatturazione::with('righe')
            ->where('proprietario_id', $proprietario->id)
            ->findOrFail($fatturazione);

        if ($proforma->stato !== 'proforma') {
            return redirect()
                ->route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
                ->with('warning', 'Questa proforma non è più modificabile.');
        }

        $data = $this->validateProforma($request);

        $updated = DB::transaction(function () use ($proprietario, $proforma, $data): bool {
            return $this->replaceProformaContent($proprietario, $proforma, $data);
        });

        if (!$updated) {
            return redirect()->back()->withInput()->with('warning', 'Aggiungi almeno una riga valida alla proforma prima di aggiornarla.');
        }

        return redirect()
            ->route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])
            ->with('success', 'Proforma aggiornata correttamente.');
    }

    public function closeProforma(int $id, int $fatturazione)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proforma = ProprietarioFatturazione::where('proprietario_id', $proprietario->id)->findOrFail($fatturazione);
        if ($proforma->stato === 'fatturata') {
            return redirect()->back()->with('warning', 'La proforma è già fatturata.');
        }
        $proforma->stato = 'chiusa';
        $proforma->save();

        return redirect()->route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])->with('success', 'Proforma chiusa.');
    }

    public function markProformaFatturata(int $id, int $fatturazione)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proforma = ProprietarioFatturazione::where('proprietario_id', $proprietario->id)->findOrFail($fatturazione);
        $proforma->stato = 'fatturata';
        $proforma->save();

        return redirect()->route('superadmin.proprietari.proforme.show', ['id' => $proprietario->id, 'fatturazione' => $proforma->id])->with('success', 'Proforma segnata come fatturata.');
    }

    public function printProforma(int $id, int $fatturazione)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proforma = ProprietarioFatturazione::with(['righe.struttura', 'righe.servizio'])
            ->where('proprietario_id', $proprietario->id)
            ->findOrFail($fatturazione);

        return view('superadmin.proprietari.proforma-print', [
            'proprietario' => $proprietario,
            'proforma' => $proforma,
        ]);
    }

    public function disable(int $id)
    {
        $proprietario = Proprietario::findOrFail($id);
        $proprietario->attivo = false;
        $proprietario->save();

        return redirect()->route('superadmin.proprietari.index')->with('success', 'Proprietario disabilitato.');
    }

    public function assegnaAdmin(Request $request, int $id)
    {
        $proprietario = Proprietario::findOrFail($id);
        $data = $request->validate([
            'admin_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('ruolo', 'admin'))],
        ]);
        $proprietario->admin_id = $data['admin_id'] ?? null;
        $proprietario->save();

        return redirect()->route('superadmin.proprietari.index')->with('success', 'Admin assegnato.');
    }

    public function assegnaStruttura(Request $request, int $id)
    {
        $proprietario = Proprietario::findOrFail($id);
        $data = $request->validate([
            'struttura_id' => ['required', 'integer', 'exists:struttura,id'],
        ]);

        $struttura = Struttura::findOrFail($data['struttura_id']);
        $struttura->proprietario_id = $proprietario->id;
        $struttura->save();

        return redirect()
            ->route('superadmin.proprietari.edit', ['id' => $proprietario->id, 'tab' => 'strutture'])
            ->with('success', 'Struttura assegnata al proprietario.');
    }

    private function validateProprietario(Request $request): array
    {
        return $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'telefono' => ['nullable', 'string', 'max:50'],
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
                'geo_manual' => ['nullable', 'boolean'],
                'latitudine' => ['nullable', 'numeric'],
                'longitudine' => ['nullable', 'numeric'],
                'note' => ['nullable', 'string'],
            'note_amministrative' => ['nullable', 'string'],
            'active_tab' => ['nullable', 'string', 'max:50'],
            'servizi_generali' => ['nullable', 'array'],
            'servizi_generali.*.selected' => ['nullable', 'boolean'],
            'servizi_generali.*.quantita' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'servizi_generali.*.importo_override' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'servizi_generali.*.note' => ['nullable', 'string'],
            'servizi_struttura' => ['nullable', 'array'],
            'servizi_struttura.*' => ['nullable', 'array'],
            'servizi_struttura.*.*.selected' => ['nullable', 'boolean'],
            'servizi_struttura.*.*.quantita' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'servizi_struttura.*.*.importo_override' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'servizi_struttura.*.*.note' => ['nullable', 'string'],
        ],
            [
                'nome.required' => 'Il nome del proprietario è obbligatorio.',
                'email.email' => 'L email del proprietario non è valida.',
                'pec.email' => 'La PEC deve essere un indirizzo email valido.',
                'codice_unico.max' => 'Il codice unico può contenere al massimo 7 caratteri.',
                'codice_destinatario.max' => 'Il codice destinatario può contenere al massimo 32 caratteri.',
                'ragione_sociale.max' => 'La ragione sociale può contenere al massimo 180 caratteri.',
                'partita_iva.max' => 'La partita IVA può contenere al massimo 32 caratteri.',
                'codice_fiscale.max' => 'Il codice fiscale può contenere al massimo 32 caratteri.',
                'telefono.max' => 'Il telefono può contenere al massimo 50 caratteri.',
                'indirizzo.max' => 'L indirizzo può contenere al massimo 180 caratteri.',
                'numero_civico.max' => 'Il numero civico può contenere al massimo 20 caratteri.',
                'cap.max' => 'Il CAP può contenere al massimo 16 caratteri.',
                'citta.max' => 'La città può contenere al massimo 120 caratteri.',
                'provincia.max' => 'La provincia può contenere al massimo 8 caratteri.',
                'regione.max' => 'La regione può contenere al massimo 120 caratteri.',
                'nazione.max' => 'La nazione può contenere al massimo 120 caratteri.',
                'latitudine.numeric' => 'La latitudine deve essere un numero valido.',
                'longitudine.numeric' => 'La longitudine deve essere un numero valido.',
                'servizi_generali.*.quantita.min' => 'La quantità del servizio deve essere almeno 1.',
                'servizi_generali.*.importo_override.numeric' => 'L importo personalizzato del servizio deve essere numerico.',
                'servizi_struttura.*.*.quantita.min' => 'La quantità del servizio per struttura deve essere almeno 1.',
                'servizi_struttura.*.*.importo_override.numeric' => 'L importo personalizzato del servizio per struttura deve essere numerico.',
            ],
            [
                'nome' => 'nome',
                'email' => 'email',
                'telefono' => 'telefono',
                'ragione_sociale' => 'ragione sociale',
                'codice_fiscale' => 'codice fiscale',
                'partita_iva' => 'partita IVA',
                'codice_destinatario' => 'codice destinatario',
                'codice_unico' => 'codice unico',
                'pec' => 'PEC',
                'indirizzo' => 'indirizzo',
                'numero_civico' => 'numero civico',
                'cap' => 'CAP',
                'citta' => 'città',
                'provincia' => 'provincia',
                'regione' => 'regione',
                'nazione' => 'nazione',
                'latitudine' => 'latitudine',
                'longitudine' => 'longitudine',
                'note_amministrative' => 'note amministrative',
            ]
        );
    }

    private function validateProforma(Request $request): array
    {
        $request->merge($this->normalizeProformaPayload($request->all()));

        return $request->validate([
            'proforma_numero' => ['nullable', 'string', 'max:50'],
            'proforma_data' => ['required', 'date'],
            'proforma_note' => ['nullable', 'string'],
            'filtro_strutture_modalita' => ['nullable', 'in:all,single,multiple'],
            'filtro_struttura_id' => ['nullable', 'integer', 'exists:struttura,id'],
            'filtro_strutture_ids' => ['nullable', 'array'],
            'filtro_strutture_ids.*' => ['nullable', 'integer', 'exists:struttura,id'],
            'proforma_righe' => ['nullable', 'array'],
            'proforma_righe.*.selected' => ['nullable', 'boolean'],
            'proforma_righe.*.struttura_id' => ['nullable', 'integer', 'exists:struttura,id'],
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

    private function buildPayload(array $data): array
    {
        return [
            'nome' => $data['nome'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
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
            'note' => $data['note'] ?? null,
            'note_amministrative' => $data['note_amministrative'] ?? null,
            'attivo' => true,
        ];
    }

    private function buildSummary(Proprietario $proprietario): array
    {
        return [
            'strutture' => $proprietario->strutture()->count(),
            'strutture_attive' => $proprietario->strutture()->where('attiva', true)->count(),
            'utenti' => $proprietario->utenti()->count(),
            'servizi_attivi' => DB::table('admin_servizio_proprietario')
                ->where('proprietario_id', $proprietario->id)
                ->count(),
        ];
    }

    private function buildFatturazione(Proprietario $proprietario): array
    {
        $strutture = $proprietario->strutture()->orderBy('nome_struttura')->get()->keyBy('id');
        $servizi = $proprietario->admin_id
            ? AdminServizio::where('user_id', $proprietario->admin_id)->orderBy('nome')->get()->keyBy('id')
            : collect();
        $assegnazioni = $this->buildServiziAssegnati($proprietario, $servizi);
        $righe = collect();

        foreach ($assegnazioni as $assegnazione) {
            $servizio = $servizi->get((int) $assegnazione['admin_servizio_id']);
            if (!$servizio) {
                continue;
            }

            $quantita = max(1, (int) ($assegnazione['quantita'] ?? 1));
            $unitario = (float) ($assegnazione['importo_effettivo'] ?? 0);
            $totale = $quantita * $unitario;
            $struttura = !empty($assegnazione['struttura_id']) ? $strutture->get((int) $assegnazione['struttura_id']) : null;

            $righe->push([
                'key' => ($assegnazione['struttura_id'] ?: 'owner') . '-' . $servizio->id,
                'selected' => true,
                'is_generale' => blank($assegnazione['struttura_id']),
                'struttura_id' => $assegnazione['struttura_id'] ?: null,
                'struttura_nome' => $struttura?->nome_struttura ?: 'Intero proprietario',
                'admin_servizio_id' => $servizio->id,
                'descrizione' => $servizio->nome,
                'quantita' => $quantita,
                'prezzo_unitario' => $unitario,
                'aliquota_iva' => 22,
                'sconto_tipo' => 'percentuale',
                'sconto_valore' => 0,
                'imponibile' => $totale,
                'totale_iva' => round($totale * 0.22, 2),
                'totale' => $totale,
                'note' => $assegnazione['note'] ?? null,
            ]);
        }

        return [
            'righe' => $righe,
            'totale' => $righe->sum('totale'),
            'strutture' => $strutture->values(),
        ];
    }

    private function buildServiziAssegnati(Proprietario $proprietario, $serviziDisponibili)
    {
        $raw = DB::table('admin_servizio_proprietario')
            ->where('proprietario_id', $proprietario->id)
            ->get()
            ->map(function ($row) use ($serviziDisponibili) {
                $servizio = $serviziDisponibili->firstWhere('id', (int) $row->admin_servizio_id);
                $baseImporto = (float) ($servizio->importo ?? 0);
                $override = $row->importo_override;

                return [
                    'admin_servizio_id' => (int) $row->admin_servizio_id,
                    'struttura_id' => $row->struttura_id ? (int) $row->struttura_id : null,
                    'quantita' => (int) ($row->quantita ?? 1),
                    'importo_override' => $override,
                    'importo_effettivo' => filled($override) ? (float) $override : $baseImporto,
                    'note' => $row->note,
                ];
            });

        $fallbackPerStruttura = $raw
            ->filter(function ($row) use ($serviziDisponibili) {
                $servizio = $serviziDisponibili->firstWhere('id', $row['admin_servizio_id']);
                return $servizio && $servizio->tipo_costo === 'per_struttura' && !$row['struttura_id'];
            })
            ->keyBy('admin_servizio_id');

        $expanded = collect();
        $strutture = $proprietario->strutture()->orderBy('nome_struttura')->get();

        foreach ($raw as $row) {
            $servizio = $serviziDisponibili->firstWhere('id', $row['admin_servizio_id']);
            if (!$servizio) {
                continue;
            }

            if ($servizio->tipo_costo === 'per_struttura' && !$row['struttura_id']) {
                foreach ($strutture as $struttura) {
                    $expanded->push(array_merge($row, ['struttura_id' => $struttura->id]));
                }
                continue;
            }

            $expanded->push($row);
        }

        if ($expanded->isEmpty() && $fallbackPerStruttura->isNotEmpty()) {
            foreach ($fallbackPerStruttura as $row) {
                foreach ($strutture as $struttura) {
                    $expanded->push(array_merge($row, ['struttura_id' => $struttura->id]));
                }
            }
        }

        return $expanded->values();
    }

    private function syncServiziAssegnati(Proprietario $proprietario, array $data): void
    {
        $validServiceIds = AdminServizio::where('user_id', $proprietario->admin_id)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $validStructureIds = $proprietario->strutture()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $rows = [];

        foreach ($data['servizi_generali'] ?? [] as $servizioId => $servizioData) {
            $servizioId = (int) $servizioId;
            if (!in_array($servizioId, $validServiceIds, true) || !($servizioData['selected'] ?? false)) {
                continue;
            }

            $rows[] = [
                'admin_servizio_id' => $servizioId,
                'proprietario_id' => $proprietario->id,
                'struttura_id' => null,
                'quantita' => max(1, (int) ($servizioData['quantita'] ?? 1)),
                'importo_override' => filled($servizioData['importo_override'] ?? null) ? $servizioData['importo_override'] : null,
                'note' => $servizioData['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($data['servizi_struttura'] ?? [] as $servizioId => $struttureData) {
            $servizioId = (int) $servizioId;
            if (!in_array($servizioId, $validServiceIds, true) || !is_array($struttureData)) {
                continue;
            }

            foreach ($struttureData as $strutturaId => $servizioData) {
                $strutturaId = (int) $strutturaId;
                if (!in_array($strutturaId, $validStructureIds, true) || !($servizioData['selected'] ?? false)) {
                    continue;
                }

                $rows[] = [
                    'admin_servizio_id' => $servizioId,
                    'proprietario_id' => $proprietario->id,
                    'struttura_id' => $strutturaId,
                    'quantita' => max(1, (int) ($servizioData['quantita'] ?? 1)),
                    'importo_override' => filled($servizioData['importo_override'] ?? null) ? $servizioData['importo_override'] : null,
                    'note' => $servizioData['note'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('admin_servizio_proprietario')->where('proprietario_id', $proprietario->id)->delete();
        if (!empty($rows)) {
            DB::table('admin_servizio_proprietario')->insert($rows);
        }
    }

    private function buildFatturazioneFromProforma(ProprietarioFatturazione $proforma): array
    {
        $righe = $proforma->righe
            ->filter(fn ($riga) => filled($riga->struttura_id))
            ->values()
            ->map(function ($riga) {
                return [
                    'key' => 'stored-' . $riga->id,
                    'selected' => true,
                    'is_generale' => blank($riga->struttura_id),
                    'struttura_id' => $riga->struttura_id,
                    'struttura_nome' => $riga->struttura?->nome_struttura ?: 'Struttura #' . $riga->struttura_id,
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
            'righe' => $righe,
            'totale' => $righe->sum('totale'),
            'strutture' => collect(),
        ];
    }

    private function buildCustomRowsFromProforma(ProprietarioFatturazione $proforma): array
    {
        return $proforma->righe
            ->filter(fn ($riga) => blank($riga->struttura_id))
            ->values()
            ->map(fn ($riga) => [
                'catalogo_servizio_id' => $riga->admin_servizio_id,
                'descrizione' => $riga->descrizione,
                'quantita' => (int) $riga->quantita,
                'prezzo_unitario' => (float) $riga->prezzo_unitario,
                'sconto_tipo' => $riga->sconto_tipo ?: 'percentuale',
                'sconto_valore' => (float) ($riga->sconto_valore ?? 0),
                'aliquota_iva' => (float) ($riga->aliquota_iva ?? 22),
                'note' => $riga->note,
            ])
            ->all();
    }

    private function createProforma(Proprietario $proprietario, array $data): ?ProprietarioFatturazione
    {
        if (($data['fatturazione_action'] ?? null) !== 'create') {
            return null;
        }

        $righeBase = collect($data['proforma_righe'] ?? [])
            ->filter(fn (array $row) => ($row['selected'] ?? false))
            ->filter(fn (array $row) => filled($row['descrizione'] ?? null) || filled($row['admin_servizio_id'] ?? null))
            ->map(fn (array $row) => $this->buildProformaLine($row))
            ->values();

        $catalogo = $proprietario->admin_id ? AdminServizio::where('user_id', $proprietario->admin_id)->get()->keyBy('id') : collect();

        $righeCustom = collect($data['custom_righe'] ?? [])
            ->filter(fn (array $row) => filled($row['descrizione'] ?? null) || filled($row['catalogo_servizio_id'] ?? null))
            ->map(function (array $row) use ($catalogo) {
                $catalogService = isset($row['catalogo_servizio_id']) ? $catalogo->get((int) $row['catalogo_servizio_id']) : null;
                if ($catalogService) {
                    $row['admin_servizio_id'] = $catalogService->id;
                    $row['descrizione'] = trim((string) ($row['descrizione'] ?? '')) ?: $catalogService->nome;
                    if (!filled($row['prezzo_unitario'] ?? null)) {
                        $row['prezzo_unitario'] = $catalogService->importo;
                    }
                }
                return $this->buildProformaLine($row);
            })
            ->values();

        $righe = $righeBase->concat($righeCustom)->values();
        if ($righe->isEmpty()) {
            return null;
        }

        $fattura = $proprietario->fatturazioni()->create([
            'created_by' => auth()->id(),
            'numero' => trim((string) ($data['proforma_numero'] ?? '')) ?: $this->nextProformaNumber($proprietario),
            'data_documento' => $data['proforma_data'] ?? now()->toDateString(),
            'stato' => 'proforma',
            'intestazione' => $proprietario->ragione_sociale ?: $proprietario->nome,
            'partita_iva' => $proprietario->partita_iva,
            'codice_fiscale' => $proprietario->codice_fiscale,
            'pec' => $proprietario->pec,
            'indirizzo' => trim(collect([$proprietario->indirizzo, $proprietario->numero_civico])->filter()->implode(' ')) ?: null,
            'cap' => $proprietario->cap,
            'citta' => $proprietario->citta,
            'provincia' => $proprietario->provincia,
            'imponibile' => $righe->sum('imponibile'),
            'totale_sconto' => $righe->sum('sconto_importo'),
            'totale_iva' => $righe->sum('totale_iva'),
            'totale' => $righe->sum('totale'),
            'note' => $data['proforma_note'] ?? null,
        ]);

        $fattura->righe()->createMany($righe->map(fn ($row) => collect($row)->except('sconto_importo')->all())->all());

        return $fattura;
    }

    private function replaceProformaContent(Proprietario $proprietario, ProprietarioFatturazione $proforma, array $data): bool
    {
        $new = $this->createProforma($proprietario, array_merge($data, [
            'fatturazione_action' => 'create',
            'proforma_numero' => $this->nextTemporaryProformaNumber($proprietario, $proforma),
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
                'struttura_id',
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

    private function buildProformaLine(array $row): array
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

        return [
            'struttura_id' => $row['struttura_id'] ?? null,
            'admin_servizio_id' => $row['admin_servizio_id'] ?? null,
            'descrizione' => $row['descrizione'] ?? 'Servizio',
            'quantita' => $quantita,
            'prezzo_unitario' => $prezzoUnitario,
            'sconto_tipo' => $scontoTipo,
            'sconto_valore' => $scontoValore,
            'sconto_importo' => $scontoImporto,
            'imponibile' => $imponibile,
            'aliquota_iva' => $aliquotaIva,
            'totale_iva' => $totaleIva,
            'totale' => $imponibile + $totaleIva,
            'note' => $row['note'] ?? null,
        ];
    }

    private function normalizeProprietarioPayload(array $data): array
    {
        if (!isset($data['servizi']) || !is_array($data['servizi'])) {
            return $data;
        }

        foreach ($data['servizi'] as $id => $row) {
            if (!is_array($row)) {
                continue;
            }
            if (array_key_exists('importo_override', $row)) {
                $data['servizi'][$id]['importo_override'] = $this->normalizeDecimalInput($row['importo_override']);
            }
        }

        return $data;
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
            return str_replace(',', '.', $value);
        }

        if (str_contains($value, ',')) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    private function nextProformaNumber(Proprietario $proprietario): string
    {
        $lastId = (int) $proprietario->fatturazioni()->max('id');
        return 'PRO-P-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function nextTemporaryProformaNumber(Proprietario $proprietario, ProprietarioFatturazione $current): string
    {
        $base = 'TMP-P-' . $current->id . '-';
        $counter = 1;
        do {
            $candidate = $base . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            $exists = $proprietario->fatturazioni()->where('numero', $candidate)->exists();
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
