<?php

namespace App\Services;

use App\Models\Titolo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TitoliStrutturaService
{
    public function initialize(int $sid): void
    {
        DB::transaction(function () use ($sid) {
            $s = DB::table('struttura')->where('id', $sid)->lockForUpdate()->first();
            if (! $s || $s->titoli_inizializzati_at !== null) return;
            $this->recoverBase($sid);
            DB::table('struttura')->where('id', $sid)->update(['titoli_inizializzati_at' => now()]);
        });
    }

    // Recupero aggiuntivo volontario: conserva nomi, descrizioni, stato, ID e relazioni.
    // Un nome occupato da una personalizzazione viene segnalato, mai sovrascritto.
    public function recoverBase(int $sid): array
    {
        return DB::transaction(function () use ($sid) {
            abort_unless(DB::table('struttura')->where('id', $sid)->lockForUpdate()->first(), 404);
            $added = 0; $conflicts = [];
            foreach (config('titoli') as $entry) {
                $q = DB::table('titolo')->where('struttura_id', $sid);
                $lineage = (clone $q)->where('base_nome', $entry['nome'])->first();
                if ($lineage && $lineage->nome === $entry['nome']) continue;
                if ((clone $q)->where('nome', $entry['nome'])->exists()) {
                    $existing = (clone $q)->where('nome', $entry['nome'])->first();
                    if ($existing->descrizione !== $entry['descrizione'] || ! $existing->attivo) $conflicts[] = $entry['nome'];
                    continue;
                }
                DB::table('titolo')->insert([
                    'struttura_id' => $sid, 'base_nome' => $lineage ? null : $entry['nome'], 'nome' => $entry['nome'],
                    'descrizione' => $entry['descrizione'], 'attivo' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $added++;
            }
            return ['aggiunti' => $added, 'conflitti' => $conflicts];
        });
    }

    private function referenceQueries(int $sid): array
    {
        return [
            [DB::table('clienti')->where('struttura_id', $sid), 'type'],
            [DB::table('schedina')->where('struttura_id', $sid), 'type'],
            [DB::table('cestino_items')->where('struttura_id', $sid)->whereIn('entity_class', [\App\Models\Customers::class, \App\Models\Schedina::class]), 'payload->type'],
            [DB::table('cestino_items')->where('struttura_id', $sid)->where('entity_class', \App\Models\WebCheckinRichiesta::class), 'payload->schedina->type'],
        ];
    }

    public function references(Titolo $title): bool
    {
        foreach ($this->referenceQueries((int) $title->struttura_id) as [$q, $field]) {
            if ($q->where($field, $title->nome)->exists()) return true;
        }
        return false;
    }

    public function renameReferences(Titolo $title, string $name): void
    {
        foreach ($this->referenceQueries((int) $title->struttura_id) as [$q, $field]) {
            $q->where($field, $title->nome)->update([$field => $name]);
        }
    }

    public function validateSelection(mixed $value, int $sid, mixed $old = null): void
    {
        if ($value === null || $value === '') return;
        if (! is_string($value)) throw ValidationException::withMessages(['type' => 'Seleziona un titolo valido.']);
        if ($value === $old) return; // Conserva valori storici o inattivi già associati.
        if (! DB::table('titolo')->where('struttura_id', $sid)->where('nome', $value)->where('attivo', true)->exists()) {
            throw ValidationException::withMessages(['type' => 'Il titolo non è attivo oppure non appartiene alla struttura corrente.']);
        }
    }

    public function recoverLegacyReferences(): void
    {
        foreach (DB::table('struttura')->pluck('id') as $sid) {
            $names = [];
            foreach ($this->referenceQueries((int) $sid) as [$q, $field]) {
                foreach ($q->pluck($field) as $name) if (is_string($name) && $name !== '') $names[$name] = true;
            }
            foreach (array_keys($names) as $name) {
                if (DB::table('titolo')->where('struttura_id', $sid)->where('nome', $name)->exists()) continue;
                $sources = DB::table('titolo')->whereNull('struttura_id')->where('nome', $name)->get();
                if ($sources->count() !== 1) continue;
                $source = $sources->first();
                DB::table('titolo')->insert([
                    'struttura_id' => $sid, 'base_nome' => null, 'nome' => $source->nome,
                    'descrizione' => $source->descrizione, 'attivo' => $source->attivo,
                    'created_at' => $source->created_at, 'updated_at' => $source->updated_at,
                ]);
            }
        }
    }
}
