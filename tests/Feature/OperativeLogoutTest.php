<?php

namespace Tests\Feature;

use App\Models\StrutturaAccesso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class OperativeLogoutTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public static function metodi(): array
    {
        return [['GET'], ['POST']];
    }

    #[DataProvider('metodi')]
    public function test_logout_chiude_registro_e_sessione_senza_toccare_altri_utenti(string $method): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $other = $this->actor('struttura_user', null, $s->id);
        $access = StrutturaAccesso::withoutGlobalScopes()->create([
            'struttura_id' => $s->id, 'user_id' => $u->id, 'entrata_at' => now()->subMinute(),
        ]);
        $foreign = StrutturaAccesso::withoutGlobalScopes()->create([
            'struttura_id' => $s->id, 'user_id' => $other->id, 'entrata_at' => now()->subMinute(),
        ]);
        $this->actingAs($u)->withSession([
            'struttura_corrente_id' => $s->id, 'accesso_operativo_id' => $access->id,
        ])->call($method, '/logout')->assertRedirect($method === 'GET' ? '/login' : '/');
        $this->assertGuest();
        $this->assertNotNull($access->fresh()->uscita_at);
        $this->assertNotNull($u->fresh()->ultima_uscita_at);
        $this->assertNull($foreign->fresh()->uscita_at);
        $this->get('/gestione-operativa')->assertRedirect('/login');
    }
}
