<?php

namespace App\Services;

use App\Models\CestinoItem;
use App\Models\Componenti;
use App\Models\Customers;
use App\Models\Gruppo;
use App\Models\LicenzaAssegnazione;
use App\Models\LicenzaArticolo;
use App\Models\RilasciatoDa;
use App\Models\Schedina;
use App\Models\SchedinaCamera;
use App\Models\Struttura;
use App\Models\TassaEsenzione;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\TipoVia;
use App\Models\Titolo;
use App\Models\WebCheckinRichiesta;
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
                Struttura::class => $this->restoreSimpleModel(Struttura::class, $payload),
                LicenzaArticolo::class => $this->restoreSimpleModel(LicenzaArticolo::class, $payload),
                LicenzaAssegnazione::class => $this->restoreSimpleModel(LicenzaAssegnazione::class, $payload),
                default => throw new \RuntimeException('Ripristino non supportato per questa entità.'),
            };

            $item->delete();

            return $restored;
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
                'payload' => $model->toArray(),
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
            'Customers' => 'Cliente',
            'Schedina' => 'Schedina',
            'WebCheckinRichiesta' => 'Web Check-in',
            'Componenti' => 'Componente',
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
