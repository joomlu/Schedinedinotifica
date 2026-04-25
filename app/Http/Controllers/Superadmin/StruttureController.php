<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\LicenzaArticolo;
use App\Models\LicenzaAssegnazione;
use App\Models\Proprietario;
use App\Models\Struttura;
use App\Models\User;
use App\Models\GeoNazione;
use App\Models\GeoRegione;
use App\Models\GeoProvincia;
use App\Models\GeoComune;
use App\Models\GeoComuneCap;
use App\Models\StrutturaZona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class StruttureController extends Controller
{
    public function index()
    {
        $strutture = Struttura::with('proprietario')->orderBy('nome_struttura')->get();
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.index', [
            'strutture' => $strutture,
            'proprietari' => $proprietari,
        ]);
    }

    public function create()
    {
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.form', $this->buildFormViewData(
            new Struttura(),
            $proprietari,
            'create'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateFormData($request);

        $struttura = Struttura::create($this->extractStrutturaPayload($data));
        $this->syncPrimaryLicenzaFromStruttura($struttura, $struttura->proprietario?->admin_id, $data['articolo_id'] ?? null);
        $this->syncPrimaryAccessUser($struttura, $data);

        return redirect()->route('superadmin.strutture.index')->with('status', 'Struttura creata');
    }

    public function edit(int $id)
    {
        $struttura = Struttura::findOrFail($id);
        $proprietari = Proprietario::orderBy('nome')->get();

        return view('superadmin.strutture.form', $this->buildFormViewData(
            $struttura,
            $proprietari,
            'edit'
        ));
    }

    public function update(Request $request, int $id)
    {
        $struttura = Struttura::findOrFail($id);
        $data = $this->validateFormData($request, $this->resolvePrimaryAccessUser($struttura));

        $struttura->update($this->extractStrutturaPayload($data, $struttura));
        $struttura = $struttura->fresh();
        $this->syncPrimaryLicenzaFromStruttura($struttura, $struttura->proprietario?->admin_id, $data['articolo_id'] ?? null);
        $this->syncPrimaryAccessUser($struttura, $data);

        return redirect()->route('superadmin.strutture.index')->with('status', 'Struttura aggiornata');
    }

    public function updateServizio(Request $request, int $id)
    {
        $struttura = Struttura::findOrFail($id);

        $data = $request->validate([
            'articolo_id' => ['nullable', 'integer', 'exists:licenza_articoli,id'],
            'attiva' => ['nullable', 'boolean'],
            'avviso' => ['nullable', 'string', 'max:30'],
            'scadenza_servizio' => ['nullable', 'date'],
            'piano' => ['nullable', 'string', 'max:100'],
            'stato_pagamento' => ['nullable', 'string', 'max:100'],
        ]);

        $struttura->update([
            'attiva' => $data['attiva'] ?? $struttura->attiva,
            'avviso' => $data['avviso'] ?? $struttura->avviso,
            'scadenza_servizio' => $data['scadenza_servizio'] ?? $struttura->scadenza_servizio,
            'piano' => $data['piano'] ?? $struttura->piano,
            'stato_pagamento' => $data['stato_pagamento'] ?? $struttura->stato_pagamento,
        ]);
        $this->syncPrimaryLicenzaFromStruttura($struttura->fresh(), $struttura->proprietario?->admin_id, $data['articolo_id'] ?? null);

        return redirect()->route('superadmin.strutture.index')->with('status', 'Servizio aggiornato');
    }

    private function buildFormViewData(Struttura $struttura, $proprietari, string $mode): array
    {
        $geoComuneId = $this->resolveGeoComuneId($struttura->citta);

        return [
            'struttura' => $struttura,
            'proprietari' => $proprietari,
            'mode' => $mode,
            'accessoPrincipale' => $this->resolvePrimaryAccessUser($struttura),
            'licenzeStorico' => $this->loadLicenzeStorico($struttura),
            'articoliCatalogo' => LicenzaArticolo::query()->where('attivo', true)->whereNull('parent_id')->orderBy('ordine')->orderBy('nome')->get(),
            'zoneOptions' => $this->buildZoneOptions($struttura, $geoComuneId, 'zona'),
            'localitaOptions' => $this->buildZoneOptions($struttura, $geoComuneId, 'localita'),
        ];
    }

    private function validateFormData(Request $request, ?User $accessoPrincipale = null): array
    {
        return $request->validate(
            [
                'nome_struttura' => ['required', 'string', 'max:255'],
                'nazione' => ['nullable', 'string', 'max:120'],
                'regione' => ['nullable', 'string', 'max:120'],
                'citta' => ['nullable', 'string', 'max:255'],
                'provincia' => ['nullable', 'string', 'max:255'],
                'localita' => ['nullable', 'string', 'max:255'],
                'zona' => ['nullable', 'string', 'max:255'],
                'indirizzo' => ['nullable', 'string', 'max:255'],
                'numero_civico' => ['nullable', 'string', 'max:30'],
                'cap' => ['nullable', 'string', 'max:20'],
                'latitudine' => ['nullable', 'string', 'max:50'],
                'longitudine' => ['nullable', 'string', 'max:50'],
                'articolo_id' => ['nullable', 'integer', 'exists:licenza_articoli,id'],
                'proprietario_id' => ['nullable', 'integer', 'exists:proprietari,id'],
                'attiva' => ['nullable', 'boolean'],
                'avviso' => ['nullable', Rule::in(['attivo', 'sospeso', 'inattivo'])],
                'messaggio_offline' => ['nullable', 'string'],
                'messaggio_avviso' => ['nullable', 'string'],
                'scadenza_servizio' => ['nullable', 'date'],
                'piano' => ['nullable', 'string', 'max:100'],
                'stato_pagamento' => ['nullable', Rule::in(['ok', 'pagato', 'da_pagare', 'sospeso'])],
                'numero_ricevuta_pagamento' => ['nullable', 'string', 'max:120'],
                'accesso_nome' => ['nullable', 'string', 'max:255'],
                'accesso_username' => [
                    'nullable',
                    'required_with:accesso_email,accesso_password,accesso_nome',
                    'string',
                    'max:120',
                    Rule::unique('users', 'username')->ignore($accessoPrincipale?->id),
                ],
                'accesso_email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($accessoPrincipale?->id),
                ],
                'accesso_password' => ['nullable', 'string', 'min:8'],
            ],
            [
                'accesso_username.unique' => 'Il nome di accesso e gia utilizzato da un altro utente.',
                'accesso_email.unique' => 'L\'email di accesso e gia utilizzata da un altro utente.',
            ]
        );
    }

    private function extractStrutturaPayload(array $data, ?Struttura $struttura = null): array
    {
        return $this->normalizeGeoLabels([
            'nome_struttura' => $data['nome_struttura'],
            'nazione' => $data['nazione'] ?? ($struttura->nazione ?? null),
            'regione' => $data['regione'] ?? ($struttura->regione ?? null),
            'citta' => $data['citta'] ?? ($struttura->citta ?? null),
            'provincia' => $data['provincia'] ?? ($struttura->provincia ?? null),
            'localita' => $data['localita'] ?? ($struttura->localita ?? null),
            'zona' => $data['zona'] ?? ($struttura->zona ?? null),
            'indirizzo' => $data['indirizzo'] ?? ($struttura->indirizzo ?? null),
            'numero_civico' => $data['numero_civico'] ?? ($struttura->numero_civico ?? null),
            'cap' => $data['cap'] ?? ($struttura->cap ?? null),
            'latitudine' => $data['latitudine'] ?? ($struttura->latitudine ?? null),
            'longitudine' => $data['longitudine'] ?? ($struttura->longitudine ?? null),
            'proprietario_id' => $data['proprietario_id'] ?? ($struttura->proprietario_id ?? null),
            'attiva' => (bool) ($data['attiva'] ?? ($struttura->attiva ?? true)),
            'avviso' => $data['avviso'] ?? ($struttura->avviso ?? 'attivo'),
            'messaggio_offline' => $data['messaggio_offline'] ?? ($struttura->messaggio_offline ?? null),
            'messaggio_avviso' => $data['messaggio_avviso'] ?? ($struttura->messaggio_avviso ?? null),
            'scadenza_servizio' => $data['scadenza_servizio'] ?? ($struttura->scadenza_servizio ?? null),
            'piano' => $data['piano'] ?? ($struttura->piano ?? null),
            'stato_pagamento' => $data['stato_pagamento'] ?? ($struttura->stato_pagamento ?? 'pagato'),
            'numero_ricevuta_pagamento' => $data['numero_ricevuta_pagamento'] ?? ($struttura->numero_ricevuta_pagamento ?? null),
        ]);
    }

    private function loadLicenzeStorico(Struttura $struttura)
    {
        if (!$struttura->exists) {
            return collect();
        }

        return LicenzaAssegnazione::query()
            ->with('articolo')
            ->where('struttura_id', $struttura->id)
            ->orderByDesc('data_scadenza')
            ->orderByDesc('id')
            ->get();
    }

    private function resolvePrimaryAccessUser(Struttura $struttura): ?User
    {
        if (!$struttura->exists) {
            return null;
        }

        return User::query()
            ->where('struttura_id', $struttura->id)
            ->where('ruolo', 'struttura_user')
            ->orderByDesc('attivo')
            ->orderByDesc('ruolo_operativo')
            ->orderBy('id')
            ->first();
    }

    private function syncPrimaryAccessUser(Struttura $struttura, array $data): void
    {
        $accesso = $this->resolvePrimaryAccessUser($struttura);
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

        $name = trim((string) ($data['accesso_nome'] ?? $accesso?->name ?? ($struttura->nome_struttura.' Accesso')));
        $email = trim((string) ($data['accesso_email'] ?? $accesso?->email ?? ''));
        if ($email === '') {
            $email = $this->fallbackAccessEmail($username, $struttura->id);
        }

        $payload = [
            'name' => $name !== '' ? $name : ($struttura->nome_struttura.' Accesso'),
            'display_name' => $name !== '' ? $name : ($struttura->nome_struttura.' Accesso'),
            'username' => $username,
            'email' => $email,
            'avatar' => $accesso?->avatar ?? '',
            'ruolo' => 'struttura_user',
            'ruolo_operativo' => 'proprietario',
            'struttura_id' => $struttura->id,
            'proprietario_id' => $struttura->proprietario_id,
            'attivo' => (bool) $struttura->attiva,
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

    private function fallbackAccessEmail(string $username, int $strutturaId): string
    {
        $base = preg_replace('/[^a-z0-9]+/i', '.', strtolower($username));
        $base = trim((string) $base, '.');
        if ($base === '') {
            $base = 'struttura';
        }

        return $base.'.'.$strutturaId.'@tanggo.local';
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

        if ($struttura->exists) {
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
        }

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

        if ($struttura->exists && $cityName) {
            $values = $values->merge($this->collectSiblingZoneValues($struttura, $cityName, $tipo));
        }

        return $values
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item))
            ->values()
            ->all();
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

    private function syncPrimaryLicenzaFromStruttura(Struttura $struttura, ?int $adminId, ?int $selectedArticoloId = null): void
    {
        $primaryLicenza = LicenzaAssegnazione::query()
            ->with('articolo')
            ->where('struttura_id', $struttura->id)
            ->whereHas('articolo', fn ($query) => $query->whereNull('parent_id'))
            ->orderByDesc('attiva')
            ->orderByDesc('data_scadenza')
            ->orderByDesc('id')
            ->first();

        $articolo = $this->resolvePrimaryArticolo($struttura, $selectedArticoloId ?: $primaryLicenza?->articolo_id);
        if (!$primaryLicenza && !$articolo) {
            return;
        }

        $payload = [
            'articolo_id' => $articolo?->id ?? $primaryLicenza?->articolo_id,
            'proprietario_id' => $struttura->proprietario_id,
            'struttura_id' => $struttura->id,
            'admin_id' => $adminId,
            'quantita' => max(1, (int) ($primaryLicenza?->quantita ?? 1)),
            'prezzo' => (float) ($primaryLicenza?->prezzo ?? $articolo?->prezzo_base ?? 0),
            'stato_pagamento' => $struttura->stato_pagamento ?: ($primaryLicenza?->stato_pagamento ?? 'pagato'),
            'data_inizio' => $primaryLicenza?->data_inizio ?: now()->toDateString(),
            'data_scadenza' => $struttura->scadenza_servizio,
            'attiva' => (bool) $struttura->attiva,
            'note' => $primaryLicenza?->note,
        ];

        if ($primaryLicenza) {
            $primaryLicenza->update($payload);
            return;
        }

        LicenzaAssegnazione::create(array_merge($payload, [
            'numero_licenza' => $this->nextLicenzaNumber(),
        ]));
    }

    private function resolvePrimaryArticolo(Struttura $struttura, ?int $existingArticoloId = null): ?LicenzaArticolo
    {
        if ($existingArticoloId) {
            $existing = LicenzaArticolo::query()->whereKey($existingArticoloId)->whereNull('parent_id')->first();
            if ($existing) {
                return $existing;
            }
        }

        $query = LicenzaArticolo::query()->where('attivo', true)->whereNull('parent_id');
        $piano = mb_strtolower(trim((string) $struttura->piano));

        if ($piano !== '') {
            $match = (clone $query)->get()->first(function (LicenzaArticolo $articolo) use ($piano) {
                $haystack = mb_strtolower(trim(implode(' ', array_filter([
                    $articolo->nome,
                    $articolo->codice,
                    $articolo->accesso_key,
                ]))));

                return str_contains($haystack, $piano);
            });

            if ($match) {
                return $match;
            }
        }

        return (clone $query)->orderBy('ordine')->orderBy('nome')->first();
    }

    private function nextLicenzaNumber(): string
    {
        $latest = LicenzaAssegnazione::query()
            ->whereNotNull('numero_licenza')
            ->where('numero_licenza', 'like', 'LIC-%')
            ->orderByDesc('numero_licenza')
            ->pluck('numero_licenza')
            ->first();

        $next = 1;
        if ($latest && preg_match('/LIC-(\d+)/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = 'LIC-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $exists = LicenzaAssegnazione::where('numero_licenza', $candidate)->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }
}
