<?php

namespace App\Services;

use App\Models\CestinoItem;
use App\Models\Componenti;
use App\Models\Customers;
use App\Models\CrmLead;
use App\Models\Gruppo;
use App\Models\LicenzaAssegnazione;
use App\Models\LicenzaArticolo;
use App\Models\Proprietario;
use App\Models\RilasciatoDa;
use App\Models\Schedina;
use App\Models\SchedinaCamera;
use App\Models\Struttura;
use App\Models\TassaEsenzione;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\TipoVia;
use App\Models\Titolo;
use App\Models\User;
use App\Models\WebCheckinRichiesta;
use App\Models\CustomerImportBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CestinoService
{
    public function archiveModel(Model $model, array $meta = []): CestinoItem
    {
        $snapshot = $this->buildSnapshot($model);

        return CestinoItem::query()->create([
            'struttura_id' => $meta['struttura_id'] ?? $this->resolveStrutturaId($model),
            'user_id' => $meta['user_id'] ?? Auth::id(),
            'entity_type' => $meta['entity_type'] ?? $this->resolveEntityType($model),
            'entity_class' => get_class($model),
            'original_id' => $model->getKey(),
            'title' => $meta['title'] ?? ($snapshot['title'] ?? null),
            'code' => $meta['code'] ?? ($snapshot['code'] ?? null),
            'circuito' => $meta['circuito'] ?? ($snapshot['circuito'] ?? null),
            'source' => $meta['source'] ?? ($snapshot['source'] ?? null),
            'payload' => $snapshot['payload'] ?? $model->toArray(),
            'deleted_at' => now(),
        ]);
    }

    public function restoreItem(CestinoItem $item): Model
    {
        return DB::transaction(function () use ($item) {
            $payload = is_array($item->payload) ? $item->payload : [];
            $restored = match ($item->entity_class) {
                Schedina::class => $this->restoreSchedina($payload),
                WebCheckinRichiesta::class => $this->restoreWebCheckinRichiesta($payload),
                Customers::class => $this->restoreSimpleModel(Customers::class, $payload),
                Componenti::class => $this->restoreComponente($payload),
                Gruppo::class => $this->restoreSimpleModel(Gruppo::class, $payload),
                Titolo::class => $this->restoreSimpleModel(Titolo::class, $payload),
                TipoCliente::class => $this->restoreSimpleModel(TipoCliente::class, $payload),
                TipoVia::class => $this->restoreSimpleModel(TipoVia::class, $payload),
                TipoDocumento::class => $this->restoreSimpleModel(TipoDocumento::class, $payload),
                RilasciatoDa::class => $this->restoreSimpleModel(RilasciatoDa::class, $payload),
                TassaEsenzione::class => $this->restoreSimpleModel(TassaEsenzione::class, $payload),
                User::class => $this->restoreAmministratore($payload),
                Proprietario::class => $this->restoreProprietario($payload),
                Struttura::class => $this->restoreStruttura($payload),
                LicenzaArticolo::class => $this->restoreSimpleModel(LicenzaArticolo::class, $payload),
                LicenzaAssegnazione::class => $this->restoreSimpleModel(LicenzaAssegnazione::class, $payload),
                default => throw new \RuntimeException('Ripristino non supportato per questa entità.'),
            };

            $item->delete();

            return $restored;
        });
    }

    public function purgeItem(CestinoItem $item): void
    {
        DB::transaction(function () use ($item) {
            $class = $item->entity_class;
            $originalId = $item->original_id;

            if (in_array($class, [User::class, Proprietario::class, Struttura::class], true) && $originalId) {
                $record = $class::query()->withoutGlobalScopes()->find($originalId);

                if ($record && method_exists($record, 'forceDelete')) {
                    $record->forceDelete();
                }
            }

            $item->delete();
        });
    }

    private function buildSnapshot(Model $model): array
    {
        if ($model instanceof Schedina) {
            $model->loadMissing(['componenti', 'camere']);
            $title = trim(($model->surname ?? '') . ' ' . ($model->name ?? ''));

            return [
                'title' => $title !== '' ? $title : ($model->scheda ?: 'Schedina'),
                'code' => $model->scheda,
                'circuito' => $model->circuito,
                'source' => $this->labelForCircuito($model->circuito),
                'payload' => array_merge($model->toArray(), [
                    'componenti' => $model->componenti->toArray(),
                    'camere' => $model->camere->toArray(),
                ]),
            ];
        }

        if ($model instanceof WebCheckinRichiesta) {
            $model->loadMissing(['struttura']);
            $schedina = $model->schedina_id
                ? Schedina::query()->withoutGlobalScopes()->with(['componenti', 'camere'])->find($model->schedina_id)
                : null;

            return [
                'title' => $model->nome_referente ?: ('Prenotazione ' . $model->numero_prenotazione),
                'code' => $model->codice,
                'circuito' => 'web',
                'source' => 'Web Check-in',
                'payload' => array_merge($model->toArray(), [
                    'schedina' => $schedina?->toArray(),
                    'schedina_componenti' => $schedina?->componenti?->toArray() ?? [],
                    'schedina_camere' => $schedina?->camere?->toArray() ?? [],
                    'struttura' => $model->struttura?->only(['id', 'nome_struttura']),
                ]),
            ];
        }

        if ($model instanceof Customers) {
            $title = trim(($model->surname ?? '') . ' ' . ($model->name ?? ''));

            return [
                'title' => $title !== '' ? $title : 'Cliente',
                'code' => $model->numero_cliente,
                'source' => 'Clienti',
                'payload' => $model->toArray(),
            ];
        }

        if ($model instanceof User) {
            return [
                'title' => $model->name ?: ($model->username ?: $model->email),
                'code' => $model->username ?: $model->email,
                'source' => 'Amministratori',
                'payload' => array_merge($model->getAttributes(), [
                    'managed_proprietario_ids' => $model->proprietariGestiti()->withoutGlobalScopes()->pluck('id')->all(),
                    'licenza_admin_ids' => LicenzaAssegnazione::query()->withoutGlobalScopes()->where('admin_id', $model->id)->pluck('id')->all(),
                    'assigned_crm_lead_ids' => CrmLead::query()->withoutGlobalScopes()->where('assigned_admin_id', $model->id)->pluck('id')->all(),
                ]),
            ];
        }

        if ($model instanceof Proprietario) {
            return [
                'title' => $model->nome ?: 'Proprietario',
                'code' => (string) $model->id,
                'source' => 'Proprietari',
                'payload' => array_merge($model->toArray(), [
                    'struttura_ids' => $model->strutture()->withoutGlobalScopes()->pluck('id')->all(),
                    'user_ids' => $model->utenti()->withoutGlobalScopes()->pluck('id')->all(),
                    'licenza_ids' => LicenzaAssegnazione::query()->withoutGlobalScopes()->where('proprietario_id', $model->id)->pluck('id')->all(),
                    'import_batch_ids' => CustomerImportBatch::query()->withoutGlobalScopes()->where('proprietario_id', $model->id)->pluck('id')->all(),
                ]),
            ];
        }

        if ($model instanceof Componenti) {
            $title = trim(($model->surname ?? '') . ' ' . ($model->name ?? ''));

            return [
                'title' => $title !== '' ? $title : 'Componente',
                'code' => $model->schedina?->scheda,
                'source' => 'Componenti',
                'payload' => array_merge($model->toArray(), [
                    'schedina' => $model->schedina?->only(['id', 'scheda', 'circuito']),
                ]),
            ];
        }

        if ($model instanceof Struttura) {
            return [
                'title' => $model->nome_struttura,
                'code' => (string) $model->id,
                'source' => 'Strutture',
                'payload' => array_merge($model->toArray(), [
                    'access_user_ids' => User::query()->withoutGlobalScopes()->where('struttura_id', $model->id)->pluck('id')->all(),
                    'licenza_ids' => LicenzaAssegnazione::query()->withoutGlobalScopes()->where('struttura_id', $model->id)->pluck('id')->all(),
                    'crm_lead_ids' => CrmLead::query()->withoutGlobalScopes()->where('struttura_id', $model->id)->pluck('id')->all(),
                ]),
            ];
        }

        $title = trim((string) ($model->nome ?? $model->name ?? $model->descrizione ?? $model->codice ?? $model->id));
        $code = $model->codice ?? $model->numero_cliente ?? $model->scheda ?? null;

        return [
            'title' => $title !== '' ? $title : class_basename($model),
            'code' => $code ? (string) $code : null,
            'source' => class_basename($model),
            'payload' => $model->toArray(),
        ];
    }

    private function resolveStrutturaId(Model $model): ?int
    {
        $strutturaId = $model->getAttribute('struttura_id');
        return $strutturaId ? (int) $strutturaId : null;
    }

    private function restoreSchedina(array $payload): Schedina
    {
        $componenti = Arr::pull($payload, 'componenti', []);
        $camere = Arr::pull($payload, 'camere', []);
        $normalizedCircuit = $this->normalizedSchedinaCircuit($payload);
        $strutturaId = isset($payload['struttura_id']) ? (int) $payload['struttura_id'] : null;

        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        if ($normalizedCircuit === 'arrivi') {
            $payload['circuito'] = 'arrivi';
            $payload['is_arrive'] = 1;
        } elseif ($normalizedCircuit === 'web') {
            $payload['circuito'] = 'web';
            $payload['is_arrive'] = 0;
        } elseif ($normalizedCircuit === 'bozza') {
            $payload['circuito'] = 'bozza';
            $payload['is_arrive'] = 0;
        } elseif ($normalizedCircuit === 'schedina') {
            $payload['circuito'] = 'schedina';
            $payload['is_arrive'] = 0;
        }

        if ($strutturaId && in_array($normalizedCircuit, ['schedina', 'arrivi', 'web', 'bozza'], true)) {
            $payload['scheda'] = $this->nextSchedaCode($strutturaId, $normalizedCircuit);
        }

        /** @var Schedina $schedina */
        $schedina = $this->restoreSimpleModel(Schedina::class, $payload, [
            'preserve_id' => false,
        ]);

        foreach ($componenti as $componentePayload) {
            if (!is_array($componentePayload)) {
                continue;
            }

            $componentePayload['schedina_id'] = $schedina->id;
            $this->restoreComponente($componentePayload);
        }

        foreach ($camere as $cameraPayload) {
            if (!is_array($cameraPayload)) {
                continue;
            }

            $cameraPayload['schedina_id'] = $schedina->id;
            $this->restoreSimpleModel(SchedinaCamera::class, $cameraPayload);
        }

        return $schedina;
    }

    private function restoreWebCheckinRichiesta(array $payload): WebCheckinRichiesta
    {
        $schedinaPayload = Arr::pull($payload, 'schedina');
        Arr::pull($payload, 'schedina_componenti');
        Arr::pull($payload, 'schedina_camere');
        Arr::pull($payload, 'struttura');
        $strutturaId = isset($payload['struttura_id']) ? (int) $payload['struttura_id'] : null;

        if (is_array($schedinaPayload)) {
            $schedina = $this->restoreSchedina($schedinaPayload);
            $payload['schedina_id'] = $schedina->id;
        } elseif (!empty($payload['schedina_id']) && !$this->baseQueryForClass(Schedina::class)->whereKey($payload['schedina_id'])->exists()) {
            $payload['schedina_id'] = null;
        }

        $payload['arrivo'] = $this->normalizeDateValue($payload['arrivo'] ?? null);
        $payload['partenza'] = $this->normalizeDateValue($payload['partenza'] ?? null);
        $payload['ultimo_accesso_at'] = $this->normalizeDateTimeValue($payload['ultimo_accesso_at'] ?? null);
        $payload['compilato_at'] = $this->normalizeDateTimeValue($payload['compilato_at'] ?? null);
        $payload['convertito_at'] = $this->normalizeDateTimeValue($payload['convertito_at'] ?? null);
        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        if ($strutturaId) {
            $payload['codice'] = $this->nextRichiestaCode($strutturaId);
        }
        $payload['token'] = $this->nextWebCheckinToken();

        /** @var WebCheckinRichiesta $richiesta */
        $richiesta = $this->restoreSimpleModel(WebCheckinRichiesta::class, $payload, [
            'preserve_id' => false,
        ]);
        return $richiesta;
    }

    private function restoreComponente(array $payload): Componenti
    {
        Arr::pull($payload, 'schedina');

        if (!empty($payload['schedina_id']) && !$this->baseQueryForClass(Schedina::class)->whereKey($payload['schedina_id'])->exists()) {
            $payload['schedina_id'] = null;
        }

        if (!empty($payload['customer_id']) && !$this->baseQueryForClass(Customers::class)->whereKey($payload['customer_id'])->exists()) {
            $payload['customer_id'] = null;
        }

        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        /** @var Componenti $componente */
        $componente = $this->restoreSimpleModel(Componenti::class, $payload, [
            'preserve_id' => false,
        ]);
        return $componente;
    }

    private function restoreAmministratore(array $payload): User
    {
        $proprietarioIds = Arr::pull($payload, 'managed_proprietario_ids', []);
        $licenzaIds = Arr::pull($payload, 'licenza_admin_ids', []);
        $crmLeadIds = Arr::pull($payload, 'assigned_crm_lead_ids', []);

        /** @var User $admin */
        $admin = $this->restoreSoftDeletedModel(User::class, $payload);

        Proprietario::query()->withoutGlobalScopes()
            ->whereIn('id', $proprietarioIds)
            ->whereNull('admin_id')
            ->update(['admin_id' => $admin->id]);

        LicenzaAssegnazione::query()->withoutGlobalScopes()
            ->whereIn('id', $licenzaIds)
            ->whereNull('admin_id')
            ->update(['admin_id' => $admin->id]);

        CrmLead::query()->withoutGlobalScopes()
            ->whereIn('id', $crmLeadIds)
            ->whereNull('assigned_admin_id')
            ->update(['assigned_admin_id' => $admin->id]);

        return $admin;
    }

    private function restoreProprietario(array $payload): Proprietario
    {
        $strutturaIds = Arr::pull($payload, 'struttura_ids', []);
        $userIds = Arr::pull($payload, 'user_ids', []);
        $licenzaIds = Arr::pull($payload, 'licenza_ids', []);
        $importBatchIds = Arr::pull($payload, 'import_batch_ids', []);

        /** @var Proprietario $proprietario */
        $proprietario = $this->restoreSoftDeletedModel(Proprietario::class, $payload);

        Struttura::query()->withoutGlobalScopes()
            ->whereIn('id', $strutturaIds)
            ->whereNull('proprietario_id')
            ->update(['proprietario_id' => $proprietario->id]);

        User::query()->withoutGlobalScopes()
            ->whereIn('id', $userIds)
            ->whereNull('proprietario_id')
            ->update(['proprietario_id' => $proprietario->id]);

        LicenzaAssegnazione::query()->withoutGlobalScopes()
            ->whereIn('id', $licenzaIds)
            ->whereNull('proprietario_id')
            ->update(['proprietario_id' => $proprietario->id]);

        CustomerImportBatch::query()->withoutGlobalScopes()
            ->whereIn('id', $importBatchIds)
            ->whereNull('proprietario_id')
            ->update(['proprietario_id' => $proprietario->id]);

        return $proprietario;
    }

    private function restoreStruttura(array $payload): Struttura
    {
        $userIds = Arr::pull($payload, 'access_user_ids', []);
        $licenzaIds = Arr::pull($payload, 'licenza_ids', []);
        $crmLeadIds = Arr::pull($payload, 'crm_lead_ids', []);

        /** @var Struttura $struttura */
        $struttura = $this->restoreSoftDeletedModel(Struttura::class, $payload);

        User::query()->withoutGlobalScopes()
            ->whereIn('id', $userIds)
            ->whereNull('struttura_id')
            ->update(['struttura_id' => $struttura->id]);

        LicenzaAssegnazione::query()->withoutGlobalScopes()
            ->whereIn('id', $licenzaIds)
            ->whereNull('struttura_id')
            ->update(['struttura_id' => $struttura->id]);

        CrmLead::query()->withoutGlobalScopes()
            ->whereIn('id', $crmLeadIds)
            ->whereNull('struttura_id')
            ->update(['struttura_id' => $struttura->id]);

        return $struttura;
    }

    private function restoreSoftDeletedModel(string $class, array $payload): Model
    {
        $model = new $class();
        $data = Arr::only($payload, array_merge(['id'], $model->getFillable()));
        $id = isset($payload['id']) ? (int) $payload['id'] : null;

        if ($id) {
            $existing = $this->baseQueryForClass($class)->find($id);
            if ($existing) {
                if (method_exists($existing, 'trashed') && $existing->trashed()) {
                    $existing->restore();
                }

                $existing->fill(Arr::except($data, ['id']));
                $existing->save();

                return $existing->fresh();
            }
        }

        return $this->restoreSimpleModel($class, $payload);
    }

    private function restoreSimpleModel(string $class, array $payload, array $options = []): Model
    {
        /** @var Model $model */
        $model = new $class();
        $table = $model->getTable();
        $preferredId = isset($payload['id']) ? (int) $payload['id'] : null;
        $preserveId = (bool) ($options['preserve_id'] ?? true);
        $data = Arr::only($payload, array_merge(['id'], $model->getFillable()));

        if ($model->usesTimestamps()) {
            $data['created_at'] = now();
            $data['updated_at'] = now();
        }

        $baseQuery = $this->baseQueryForClass($class);

        if ($preserveId && $preferredId && !$baseQuery->whereKey($preferredId)->exists()) {
            $data['id'] = $preferredId;
            DB::table($table)->insert($data);

            return $this->baseQueryForClass($class)->findOrFail($preferredId);
        }

        unset($data['id']);
        $id = DB::table($table)->insertGetId($data);

        return $this->baseQueryForClass($class)->findOrFail($id);
    }

    private function resolveEntityType(Model $model): string
    {
        return match (class_basename($model)) {
            'User' => 'Amministratore',
            'Proprietario' => 'Proprietario',
            'Customers' => 'Cliente',
            'Schedina' => 'Schedina',
            'WebCheckinRichiesta' => 'Web Check-in',
            'Componenti' => 'Componente',
            'Struttura' => 'Struttura',
            'Gruppo' => 'Gruppo',
            'Titolo' => 'Titolo',
            'TipoCliente' => 'Tipo Cliente',
            'TipoVia' => 'Tipo Via',
            'TipoDocumento' => 'Tipo Documento',
            'RilasciatoDa' => 'Rilasciato da',
            'TassaEsenzione' => 'Esenzione tassa',
            'LicenzaArticolo' => 'Articolo',
            'LicenzaAssegnazione' => 'Licenza',
            default => class_basename($model),
        };
    }

    private function labelForCircuito(?string $circuito): ?string
    {
        return match ($circuito) {
            'schedina' => 'Schedine',
            'arrivi' => 'Arrivi',
            'bozza' => 'Schedine Bozze',
            'web' => 'Web Check-in',
            default => null,
        };
    }

    private function normalizedSchedinaCircuit(array $payload): ?string
    {
        $circuito = Str::of((string) ($payload['circuito'] ?? ''))->trim()->lower()->value();

        if (in_array($circuito, ['arrivo', 'arrivi', 'to_arrivi'], true)) {
            return 'arrivi';
        }

        if (in_array($circuito, ['web', 'web-checkin', 'web_checkin'], true)) {
            return 'web';
        }

        if (in_array($circuito, ['bozza', 'bozze', 'draft'], true)) {
            return 'bozza';
        }

        if ($circuito !== '') {
            return 'schedina';
        }

        return !empty($payload['is_arrive']) ? 'arrivi' : 'schedina';
    }

    private function nextSchedaCode(int $strutturaId, string $circuito): string
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

    private function nextRichiestaCode(int $strutturaId): string
    {
        $codes = WebCheckinRichiesta::query()
            ->withoutGlobalScopes()
            ->where('struttura_id', $strutturaId)
            ->pluck('codice');

        $max = 0;
        foreach ($codes as $code) {
            if (preg_match('/^WC(\d+)$/i', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'WC' . ($max + 1);
    }

    private function nextWebCheckinToken(): string
    {
        do {
            $token = Str::random(80);
        } while (WebCheckinRichiesta::query()->withoutGlobalScopes()->where('token', $token)->exists());

        return $token;
    }

    private function circuitCodePrefix(string $circuito): string
    {
        return match ($circuito) {
            'arrivi' => 'A',
            'web' => 'W',
            default => 'S',
        };
    }

    private function baseQueryForClass(string $class)
    {
        return $class::query()->withoutGlobalScopes();
    }

    private function normalizeDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return now()->parse((string) $value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeDateTimeValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return now()->parse((string) $value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
