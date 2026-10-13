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
        foreach ([\App\Models\Customers::class => '', \App\Models\Schedina::class => 'customer_'] as $class => $prefix) {
            $query = DB::table('cestino_items')->where('struttura_id', $group->struttura_id)->where('entity_class', $class);
            foreach ($this->path($group) as $column => $name) {
                $query->where('payload->'.$prefix.$column, $name);
            }
            if ($query->exists()) {
                return true;
            }
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
        foreach ([\App\Models\Customers::class => '', \App\Models\Schedina::class => 'customer_'] as $class => $prefix) {
            $query = DB::table('cestino_items')->where('struttura_id', $group->struttura_id)->where('entity_class', $class);
            foreach ($path as $column => $value) {
                $query->where('payload->'.$prefix.$column, $value);
            }
            $query->update(['payload->'.$prefix.$field => $name]);
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
