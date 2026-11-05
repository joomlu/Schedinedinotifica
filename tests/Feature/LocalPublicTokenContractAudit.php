<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LocalPublicTokenContractAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_contratto_osservato_token_vecchio_revoca_e_middleware_pubblico(): void
    {
        $s = $this->structureFor(null);
        $parent = Schedina::forceCreate(['struttura_id' => $s->id, 'name' => 'TOKEN-SINTETICO', 'surname' => 'Audit', 'circuito' => 'web']);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $parent->id, 'codice' => 'TOKENAUDIT', 'numero_prenotazione' => 'SINTETICA', 'nome_referente' => 'Sintetico', 'email' => 'token@example.invalid', 'arrivo' => now()->subDays(30), 'partenza' => now()->subDays(28), 'quantita_persone' => 1, 'token' => str_repeat('k', 64), 'stato' => 'da_inviare', 'created_at' => now()->subDays(90)]);
        // Rilevazione del contratto esistente, non approvazione della politica pubblica.
        $this->get('/checkin/'.$r->token)->assertOk();
        $route = app('router')->getRoutes()->match(Request::create('/checkin/'.$r->token, 'GET'));
        $middleware = $route->gatherMiddleware();
        fwrite(STDOUT, '\nCONTRATTO_TOKEN '.json_encode(['token_vecchio_accettato' => true, 'middleware' => $middleware, 'politica_ttl_definita' => false]).'\n');
        $old = $r->token;
        $r->update(['token' => str_repeat('m', 64)]);
        $this->get('/checkin/'.$old)->assertNotFound();
        $this->get('/checkin/'.$r->token)->assertOk();
        $r->delete();
        $this->get('/checkin/'.str_repeat('m', 64))->assertNotFound();
    }
}
