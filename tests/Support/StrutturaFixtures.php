<?php

namespace Tests\Support;

use App\Models\Proprietario;
use App\Models\Struttura;
use App\Models\TipologiaGenerale;
use App\Models\TipologiaStruttura;
use App\Models\User;

trait StrutturaFixtures
{
    private function actor(string $role, ?int $owner = null, ?int $structure = null): User
    {
        return User::factory()->create([
            'ruolo' => $role, 'proprietario_id' => $owner,
            'struttura_id' => $structure, 'attivo' => true,
        ]);
    }

    private function ownerFor(User $admin): Proprietario
    {
        return Proprietario::create(['admin_id' => $admin->id, 'nome' => 'Fixture owner', 'attivo' => true]);
    }

    private function structureFor(?Proprietario $owner): Struttura
    {
        $general = TipologiaGenerale::firstOrCreate(['nome' => 'Alberghiera']);
        $type = TipologiaStruttura::firstOrCreate(['nome' => 'Fixture hotel', 'tipologia_generale_id' => $general->id]);
        return Struttura::forceCreate([
            'proprietario_id' => $owner?->id, 'nome_struttura' => 'Fixture Hotel',
            'tipologia_generale' => 'Alberghiera', 'tipologia_struttura' => 'Fixture hotel',
            'tipologia_generale_id' => $general->id, 'tipologia_struttura_id' => $type->id,
            'tipo_apertura' => 'Annuale', 'nazione' => 'Italia', 'regione' => 'Lazio',
            'provincia' => 'RM', 'citta' => 'Roma', 'indirizzo' => 'Via Test', 'cap' => '00100',
            'telefono' => '061234567', 'email' => 'fixture@example.invalid',
            'latitudine' => 41.9, 'longitudine' => 12.5,
            'attiva' => true, 'scadenza_servizio' => now()->addYear()->toDateString(),
        ]);
    }

    private function validStructurePayload(Struttura $structure): array
    {
        return [
            'nome_struttura' => 'Hotel Aggiornato',
            'tipologia_generale_id' => $structure->tipologia_generale_id,
            'tipologia_struttura_id' => $structure->tipologia_struttura_id,
            'tipo_apertura' => 'Annuale', 'nazione' => 'Italia', 'regione' => 'Lazio',
            'provincia' => 'RM', 'citta' => 'Roma', 'indirizzo' => 'Via Test',
            'cap' => '00100', 'telefono' => '061234567', 'email' => 'fixture@example.invalid',
            'latitudine' => 41.9, 'longitudine' => 12.5,
        ];
    }
}
