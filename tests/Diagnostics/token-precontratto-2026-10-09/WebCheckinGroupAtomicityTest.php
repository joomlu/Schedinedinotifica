<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinGroupAtomicityTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function gruppo(string $code): array
    {
        $s = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'Principale '.$code, 'surname' => 'Sintetico', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 2]);
        $c = Componenti::forceCreate(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'name' => 'Componente '.$code, 'surname' => 'Sintetico', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => $code, 'numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-10', 'partenza' => '2026-06-13', 'quantita_persone' => 2, 'token' => bin2hex(random_bytes(32)), 'stato' => 'da_inviare']);
        DB::table('schedina_camere')->insert(['schedina_id' => $p->id, 'numero_camera' => '11', 'posti_letto' => 2]);

        return [$s, $p, $c, $r];
    }

    private function stato(): array
    {
        return collect(['schedina', 'componenti', 'schedina_camere', 'web_checkin_richieste'])->mapWithKeys(fn ($t) => [$t => DB::table($t)->orderBy('id')->get()->toJson()])->all();
    }

    private function payload(Componenti $c): array
    {
        return ['name' => 'Principale aggiornato', 'surname' => 'Sintetico', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 2, 'room' => 12, 'beds' => 2, 'componenti' => [['id' => $c->id, 'name' => 'Componente aggiornato', 'surname' => 'Sintetico', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']]];
    }

    public static function percorsi(): array
    {
        return [['completo'], ['breve']];
    }

    private function url(WebCheckinRichiesta $r, string $mode): string
    {
        return $mode === 'completo' ? '/checkin/'.$r->token : '/w/'.$r->codice.'-'.substr($r->token, 0, 8);
    }

    #[DataProvider('percorsi')]
    public function test_salvataggio_e_riapertura_componenti_senza_duplicati(string $mode): void
    {
        [$s, $p, $c, $r] = $this->gruppo('GRUPPOA');
        $this->post($this->url($r, $mode), $this->payload($c))->assertOk()->assertViewHas('componentiCount', 1);
        $this->assertSame('Principale aggiornato', DB::table('schedina')->where('id', $p->id)->value('name'));
        $this->assertSame('Componente aggiornato', DB::table('componenti')->where('id', $c->id)->value('name'));
        $this->assertSame(1, DB::table('componenti')->where('schedina_id', $p->id)->count());
        $this->get('/checkin/'.$r->token)->assertOk()->assertViewHas('componenti', fn ($items) => $items->contains('id', $c->id));
        $this->get('/checkin/'.$r->token.'/completato')->assertOk()->assertViewHas('componentiCount', 1);
    }

    #[DataProvider('percorsi')]
    public function test_componente_esterno_rollback_principale_camere_componenti_richiesta(string $mode): void
    {
        [$a, $p, $c, $r] = $this->gruppo('GRUPPOA');
        [$b, $pb, $cb, $rb] = $this->gruppo('GRUPPOB');
        $before = $this->stato();
        $this->postJson($this->url($r, $mode), $this->payload($cb))->assertUnprocessable()->assertJsonValidationErrors('componenti.0.id');
        $this->assertSame($before, $this->stato());
    }

    public function test_sessione_estranea_non_cambia_contesto_del_token(): void
    {
        [$a, $p, $c, $r] = $this->gruppo('GRUPPOA');
        [$b, $pb, $cb, $rb] = $this->gruppo('GRUPPOB');
        $other = DB::table('componenti')->where('id', $cb->id)->first();
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get('/checkin/'.$r->token)->assertOk()->assertSee('Componente GRUPPOA')->assertDontSee('Componente GRUPPOB');
        $this->post('/checkin/'.$r->token, $this->payload($c))->assertOk();
        $this->assertEquals($other, DB::table('componenti')->where('id', $cb->id)->first());
    }

    public function test_errore_tardivo_db_ripristina_tutte_le_scritture(): void
    {
        [$s, $p, $c, $r] = $this->gruppo('GRUPPOA');
        $before = $this->stato();
        $event = 'eloquent.updating: '.WebCheckinRichiesta::class;
        Event::listen($event, fn () => throw new \RuntimeException('ERRORE-SINTETICO-PERSISTENZA'));
        $this->withoutExceptionHandling();
        try {
            $this->post('/checkin/'.$r->token, $this->payload($c));
            $this->fail('Errore sintetico non propagato');
        } catch (\RuntimeException $e) {
            $this->assertSame('ERRORE-SINTETICO-PERSISTENZA', $e->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame($before, $this->stato());
    }

    public function test_convertito_non_accetta_modifiche_e_conserva_conteggio(): void
    {
        [$s, $p, $c, $r] = $this->gruppo('GRUPPOA');
        $r->update(['stato' => 'convertito']);
        $before = $this->stato();
        $this->post('/checkin/'.$r->token, $this->payload($c))->assertOk()->assertViewHas('componentiCount', 1)->assertViewHas('isLockedAfterConversion', true);
        $this->assertSame($before, $this->stato());
    }
}
