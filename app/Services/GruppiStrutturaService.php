<?php

namespace App\Services;

use App\Models\Gruppo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GruppiStrutturaService
{
    public function initialize(int $strutturaId): void
    {
        DB::transaction(function () use ($strutturaId) {
            $structure = DB::table('struttura')->where('id', $strutturaId)->lockForUpdate()->first();
            if (! $structure || $structure->gruppi_inizializzati_at !== null) {
                return;
            }
            $ids = [];
            foreach (config('gruppi') as $level => $entries) {
                foreach ($entries as $entry) {
                    $parent = $level === 1 ? null : ($ids[$level - 1][$entry['parent']] ?? null);
                    if ($level > 1 && ! $parent) {
                        throw new \RuntimeException('Base Gruppi incoerente.');
                    }
                    $ids[$level][$entry['nome']] = DB::table('gruppi')->insertGetId([
                        'struttura_id' => $strutturaId, 'nome' => $entry['nome'],
                        'descrizione' => $entry['descrizione'] ?? null, 'livello' => $level,
                        'parent_id' => $parent, 'tipo' => Gruppo::tipoFromLivello($level),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
            DB::table('struttura')->where('id', $strutturaId)->update(['gruppi_inizializzati_at' => now()]);
        });
    }

    public function path(Gruppo $group): array
    {
        $path = [];
        $seen = [];
        do {
            if (in_array($group->id, $seen, true)) {
                throw ValidationException::withMessages(['parent_id' => 'Gerarchia ciclica.']);
            }
            $seen[] = $group->id;
            $path[match ($group->livello) {
                1 => 'group', 2 => 'subgroup', 3 => 'subgroup1', default => 'group'
            }] = $group->nome;
            $group = $group->parent;
        } while ($group);

        return $path;
    }

    public function references(Gruppo $group): bool
    {
        foreach (['clienti' => '', 'schedina' => 'customer_'] as $table => $prefix) {
            $query = DB::table($table)->where('struttura_id', $group->struttura_id);
            foreach ($this->path($group) as $column => $name) {
                $query->where($prefix.$column, $name);
            }
            if ($query->exists()) {
                return true;
            }
        }
        foreach ($this->archivedQueries((int) $group->struttura_id) as [$query, $prefix]) {
            foreach ($this->path($group) as $column => $name) {
                $query->where('payload->'.$prefix.$column, $name);
            }
            if ($query->exists()) {
                return true;
            }
        }
        $imports = $this->importQuery((int) $group->struttura_id)->whereNull('imported_customer_id');
        foreach ($this->path($group) as $column => $name) {
            $imports->where('normalized_payload->'.($column === 'group' ? 'gruppo' : $column), $name);
        }
        if ($imports->exists()) {
            return true;
        }
        foreach ($group->children as $child) {
            if ($this->references($child)) {
                return true;
            }
        }

        return false;
    }

    public function renameReferences(Gruppo $group, string $name): void
    {
        $path = $this->path($group);
        $field = match ($group->livello) {
            1 => 'group', 2 => 'subgroup', 3 => 'subgroup1', default => 'group'
        };
        foreach (['clienti' => '', 'schedina' => 'customer_'] as $table => $prefix) {
            $query = DB::table($table)->where('struttura_id', $group->struttura_id);
            foreach ($path as $column => $value) {
                $query->where($prefix.$column, $value);
            }
            $query->update([$prefix.$field => $name]);
        }
        foreach ($this->archivedQueries((int) $group->struttura_id) as [$query, $prefix]) {
            foreach ($path as $column => $value) {
                $query->where('payload->'.$prefix.$column, $value);
            }
            $query->update(['payload->'.$prefix.$field => $name]);
        }
        $imports = $this->importQuery((int) $group->struttura_id);
        foreach ($path as $column => $value) {
            $imports->where('normalized_payload->'.($column === 'group' ? 'gruppo' : $column), $value);
        }
        $imports->update(['normalized_payload->'.($field === 'group' ? 'gruppo' : $field) => $name]);
    }

    private function archivedQueries(int $sid): array
    {
        return array_map(fn ($relation) => [DB::table('cestino_items')->where('struttura_id', $sid)
            ->where('entity_class', $relation[0]), $relation[1]], [
                [\App\Models\Customers::class, ''],
                [\App\Models\Schedina::class, 'customer_'],
                [\App\Models\WebCheckinRichiesta::class, 'schedina->customer_'],
            ]);
    }

    private function importQuery(int $sid): \Illuminate\Database\Query\Builder
    {
        return DB::table('customer_import_rows')->whereIn('batch_id',
            DB::table('customer_import_batches')->where('struttura_id', $sid)->select('id'));
    }

    // Solo ricostruzioni univoche; originali e riferimenti non vengono riscritti.
    public function recoverLegacyReferences(): void
    {
        $legacy = DB::table('gruppi')->whereNull('struttura_id')->get();
        foreach (DB::table('struttura')->pluck('id') as $sid) {
            $paths = DB::table('clienti')->where('struttura_id', $sid)->get(['group', 'subgroup', 'subgroup1'])
                ->merge(DB::table('schedina')->where('struttura_id', $sid)
                    ->selectRaw('customer_group AS `group`, customer_subgroup AS subgroup, customer_subgroup1 AS subgroup1')->get())
                ->map(fn ($row) => (array) $row)->all();
            foreach (DB::table('cestino_items')->where('struttura_id', $sid)->get(['entity_class', 'payload']) as $item) {
                $payload = json_decode($item->payload, true);
                if ($item->entity_class === \App\Models\WebCheckinRichiesta::class) {
                    $payload = $payload['schedina'] ?? [];
                    $prefix = 'customer_';
                } elseif ($item->entity_class === \App\Models\Schedina::class) {
                    $prefix = 'customer_';
                } elseif ($item->entity_class === \App\Models\Customers::class) {
                    $prefix = '';
                } else {
                    continue;
                }
                $paths[] = array_combine(['group', 'subgroup', 'subgroup1'], array_map(
                    fn ($field) => $payload[$prefix.$field] ?? null, ['group', 'subgroup', 'subgroup1']));
            }
            foreach ($this->importQuery((int) $sid)->pluck('normalized_payload') as $json) {
                $payload = json_decode($json, true);
                $paths[] = ['group' => $payload['gruppo'] ?? null, 'subgroup' => $payload['subgroup'] ?? null, 'subgroup1' => $payload['subgroup1'] ?? null];
            }
            foreach ($paths as $path) {
                $sourceParent = $targetParent = null;
                foreach ([1 => 'group', 2 => 'subgroup', 3 => 'subgroup1'] as $level => $field) {
                    $name = $path[$field] ?? null;
                    if (! is_string($name) || $name === '') {
                        break;
                    }
                    $matches = $legacy->filter(fn ($g) => $g->nome === $name && (int) $g->livello === $level && $g->parent_id == $sourceParent);
                    if ($matches->count() !== 1) {
                        break;
                    }
                    $source = $matches->first();
                    $targets = DB::table('gruppi')->where('struttura_id', $sid)->where('livello', $level)
                        ->where('nome', $name)->where('parent_id', $targetParent)->pluck('id');
                    if ($targets->count() > 1) {
                        break;
                    }
                    $targetParent = $targets->first() ?? DB::table('gruppi')->insertGetId([
                        'struttura_id' => $sid, 'nome' => $source->nome, 'descrizione' => $source->descrizione,
                        'tipo' => $source->tipo, 'livello' => $level, 'parent_id' => $targetParent,
                        'created_at' => $source->created_at, 'updated_at' => $source->updated_at,
                    ]);
                    $sourceParent = $source->id;
                }
            }
        }
    }

    public function validateSelection(array $values, int $strutturaId, array $unchanged = [], string $prefix = ''): void
    {
        foreach (['group', 'subgroup', 'subgroup1'] as $field) {
            if (isset($values[$field]) && ! is_string($values[$field])) {
                throw ValidationException::withMessages([$prefix.$field => 'Il gruppo deve essere un testo valido.']);
            }
        }
        $normalize = fn ($v) => array_map(fn ($f) => trim((string) ($v[$f] ?? '')), ['group', 'subgroup', 'subgroup1']);
        $same = $unchanged !== [] && $normalize($values) === $normalize($unchanged);
        $parent = null;
        foreach ([1 => 'group', 2 => 'subgroup', 3 => 'subgroup1'] as $level => $field) {
            $name = trim((string) ($values[$field] ?? ''));
            if ($name === '') {
                $parent = null;

                continue;
            }
            $matches = DB::table('gruppi')->where('struttura_id', $strutturaId)->where('livello', $level)
                ->where('nome', $name)->where('parent_id', $parent)->pluck('id');
            if ($matches->count() !== 1) {
                // I valori storici irrisolti possono essere conservati, mai attribuiti ad altri tenant.
                if ($same) {
                    return;
                }
                throw ValidationException::withMessages([$prefix.$field => 'Il gruppo selezionato non appartiene alla struttura o alla gerarchia corrente.']);
            }
            $parent = $matches->first();
        }
    }
}
