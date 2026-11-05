<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CataloghiIndirizziStrutturaService
{
    public function initialize(int $sid): void
    {
        DB::transaction(function () use ($sid) {
            $s = DB::table('struttura')->where('id', $sid)->lockForUpdate()->first();
            if (! $s || $s->indirizzi_inizializzati_at !== null) {
                return;
            }
            foreach (array_unique(config('autorita')) as $name) {
                DB::table('rilasciato_da')->insert(['struttura_id' => $sid, 'name' => $name, 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach (config('tipi_via') as $name) {
                DB::table('tipo_via')->insert(['struttura_id' => $sid, 'abbr' => $name, 'nome' => $name, 'descrizione' => $name, 'attivo' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('struttura')->where('id', $sid)->update(['indirizzi_inizializzati_at' => now()]);
        });
    }

    private function referenceQueries(string $table, int $sid): array
    {
        $authority = $table === 'rilasciato_da';
        $customer = $authority ? ['rilasciato_reg'] : ['typeaway', 'typeaway_az'];
        $schedina = $authority ? ['or_published'] : ['or_typeaway'];
        $queries = [];
        foreach (['clienti' => $customer, 'schedina' => $schedina] as $target => $fields) {
            foreach ($fields as $field) {
                $queries[] = [DB::table($target)->where('struttura_id', $sid), $field];
            }
        }
        if (! $authority) {
            $queries[] = [DB::table('componenti')->where('struttura_id', $sid), 'typeaway'];
        }
        foreach ([\App\Models\Customers::class => $customer, \App\Models\Schedina::class => $schedina, \App\Models\Componenti::class => $authority ? [] : ['typeaway'], \App\Models\WebCheckinRichiesta::class => array_map(fn ($f) => 'schedina->'.$f, $schedina)] as $class => $fields) {
            foreach ($fields as $field) {
                $queries[] = [DB::table('cestino_items')->where('struttura_id', $sid)->where('entity_class', $class), 'payload->'.$field];
            }
        }
        $imports = DB::table('customer_import_rows')->whereIn('batch_id', DB::table('customer_import_batches')->where('struttura_id', $sid)->select('id'));
        $queries[] = [$imports, 'normalized_payload->'.($authority ? 'rilasciato_da' : 'tipo_via_strada')];

        return $queries;
    }

    public function references(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $values = $record->getTable() === 'rilasciato_da' ? [$record->name] : [$record->nome, $record->abbr];
        foreach ($this->referenceQueries($record->getTable(), (int) $record->struttura_id) as [$q, $field]) {
            if ($q->whereIn($field, $values)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function recoverLegacyReferences(): void
    {
        foreach (DB::table('struttura')->pluck('id') as $sid) {
            foreach (['rilasciato_da' => 'name', 'tipo_via' => 'nome'] as $table => $field) {
                $values = [];
                foreach ($this->referenceQueries($table, (int) $sid) as [$q, $column]) {
                    foreach ($q->select($column.' as catalogo_valore')->get()->pluck('catalogo_valore') as $value) {
                        if (is_string($value) && $value !== '') {
                            $values[$value] = true;
                        }
                    }
                }
                foreach (array_keys($values) as $value) {
                    if (DB::table($table)->where('struttura_id', $sid)->where($field, $value)->exists()) {
                        continue;
                    }
                    $sources = DB::table($table)->whereNull('struttura_id')->where($field, $value)->get();
                    if ($sources->count() !== 1) {
                        continue;
                    }
                    $data = (array) $sources->first();
                    unset($data['id']);
                    $data['struttura_id'] = $sid;
                    $unique = $table === 'tipo_via' ? 'abbr' : 'name';
                    if (! DB::table($table)->where('struttura_id', $sid)->where($unique, $data[$unique])->exists()) {
                        DB::table($table)->insert($data);
                    }
                }
            }
        }
    }
}
