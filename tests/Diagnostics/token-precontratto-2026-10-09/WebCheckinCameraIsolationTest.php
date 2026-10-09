<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinCameraIsolationTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function prenotazione(string $code): array
    {
        $s = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'Principale '.$code, 'surname' => 'Sintetico', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 1]);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => $code, 'numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-10', 'partenza' => '2026-06-13', 'quantita_persone' => 1, 'token' => bin2hex(random_bytes(32)), 'stato' => 'da_inviare']);

        return [$s, $p, $r];
    }

    private function camera(Schedina $p, string $numero): void
    {
        DB::table('schedina_camere')->insert(['struttura_id' => $p->struttura_id, 'schedina_id' => $p->id, 'numero_camera' => $numero, 'posti_letto' => 1]);
    }

    private function payload(array $camere = []): array
    {
        return ['name' => 'Principale aggiornato', 'surname' => 'Sintetico', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'cant_people' => 1, 'camere' => $camere];
    }

    private function stato(): array
    {
        return collect(['schedina', 'schedina_camere', 'componenti', 'web_checkin_richieste'])->mapWithKeys(fn ($t) => [$t => DB::table($t)->orderBy('id')->get()->toJson()])->all();
    }

    public static function percorsi(): array
    {
        return [['completo', true], ['breve', true], ['completo', false], ['breve', false]];
    }

    #[DataProvider('percorsi')]
    public function test_sostituzione_ripetuta_e_riapertura_senza_duplicazioni(string $mode, bool $precedente): void
    {
        [$s, $p, $r] = $this->prenotazione('CAMERA');
        if ($precedente) {
            $this->camera($p, '11');
        }
        $url = $mode === 'completo' ? '/checkin/'.$r->token : '/w/'.$r->codice.'-'.substr($r->token, 0, 8);
        foreach (['12', '12', '13'] as $numero) {
            $this->post($url, $this->payload([['numero_camera' => $numero, 'posti_letto' => 1]]))->assertOk();
            $this->assertSame([$numero], DB::table('schedina_camere')->where('schedina_id', $p->id)->pluck('numero_camera')->all());
            $this->get('/checkin/'.$r->token)->assertOk()->assertViewHas('schedina', fn ($v) => $v->camere->count() === 1 && $v->camere->first()->numero_camera === $numero);
        }
    }

    public function test_camere_multiple_e_rimozione_esplicita_restano_supportate(): void
    {
        [$s, $p, $r] = $this->prenotazione('MULTIPLE');
        $this->camera($p, '11');
        $this->post('/checkin/'.$r->token, $this->payload([['numero_camera' => '12'], ['numero_camera' => '13']]))->assertOk();
        $this->assertSame(['12', '13'], DB::table('schedina_camere')->where('schedina_id', $p->id)->orderBy('id')->pluck('numero_camera')->all());
        $this->post('/checkin/'.$r->token, $this->payload())->assertOk();
        $this->assertSame(0, DB::table('schedina_camere')->where('schedina_id', $p->id)->count());
    }

    public function test_token_prevale_su_sessione_estranea_e_identificatori_forgiati(): void
    {
        [$a, $p, $r] = $this->prenotazione('A');
        [$b, $pb, $rb] = $this->prenotazione('B');
        $same = Schedina::forceCreate(['struttura_id' => $a->id, 'circuito' => 'web']);
        $this->camera($p, '11');
        $this->camera($pb, 'SEGRETA-B');
        $this->camera($same, 'ALTRA-A');
        $other = DB::table('schedina_camere')->where('schedina_id', '!=', $p->id)->orderBy('id')->get()->toJson();
        $this->actingAs($this->actor('struttura_user', null, $b->id));
        $this->get('/checkin/'.$r->token)->assertOk()->assertViewHas('schedina', fn ($v) => $v->camere->pluck('numero_camera')->all() === ['11'])->assertDontSee('SEGRETA-B')->assertDontSee('ALTRA-A');
        $this->post('/checkin/'.$r->token, $this->payload([['id' => 999, 'schedina_id' => $pb->id, 'struttura_id' => $b->id, 'numero_camera' => '12']]))->assertOk();
        $row = DB::table('schedina_camere')->where('schedina_id', $p->id)->first();
        $this->assertSame((int) $a->id, (int) $row->struttura_id);
        $this->assertSame('12', $row->numero_camera);
        $this->assertSame(1, DB::table('schedina_camere')->where('schedina_id', $p->id)->count());
        $this->assertSame($other, DB::table('schedina_camere')->where('schedina_id', '!=', $p->id)->orderBy('id')->get()->toJson());
    }

    public function test_relazione_con_tenant_incoerente_non_viene_letta_o_cancellata(): void
    {
        [$a, $p, $r] = $this->prenotazione('A');
        [$b, $pb, $rb] = $this->prenotazione('B');
        DB::table('schedina_camere')->insert(['struttura_id' => $b->id, 'schedina_id' => $p->id, 'numero_camera' => 'ESTERNA']);
        $before = DB::table('schedina_camere')->where('struttura_id', $b->id)->get()->toJson();
        $this->get('/checkin/'.$r->token)->assertOk()->assertViewHas('schedina', fn ($v) => $v->camere->isEmpty());
        $this->post('/checkin/'.$r->token, $this->payload([['numero_camera' => '12']]))->assertOk();
        $this->assertSame($before, DB::table('schedina_camere')->where('struttura_id', $b->id)->get()->toJson());
        $this->assertSame(1, DB::table('schedina_camere')->where('struttura_id', $a->id)->where('schedina_id', $p->id)->count());
    }

    public function test_errore_finale_ripristina_camera_precedente_e_tutte_le_scritture(): void
    {
        [$s, $p, $r] = $this->prenotazione('ERRORE');
        $this->camera($p, '11');
        $before = $this->stato();
        $event = 'eloquent.updating: '.WebCheckinRichiesta::class;
        Event::listen($event, fn () => throw new \RuntimeException('ERRORE-SINTETICO-A11'));
        $this->withoutExceptionHandling();
        try {
            $this->post('/checkin/'.$r->token, $this->payload([['numero_camera' => '12']]));
            $this->fail('Errore non propagato');
        } catch (\RuntimeException $e) {
            $this->assertSame('ERRORE-SINTETICO-A11', $e->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame($before, $this->stato());
    }

    public function test_rifiuti_422_e_token_invalido_non_alterano_relazioni(): void
    {
        [$s, $p, $r] = $this->prenotazione('NEGATIVO');
        $this->camera($p, '11');
        $before = $this->stato();
        $payload = $this->payload([['numero_camera' => '12']]);
        $payload['componenti'] = [['id' => 999999, 'name' => 'Sintetico', 'surname' => 'Audit', 'sex' => 'F', 'date_nac' => '1980-01-01', 'relationship' => 'FAMILIARE']];
        $this->postJson('/checkin/'.$r->token, $payload)->assertUnprocessable();
        $this->assertSame($before, $this->stato());
        $this->post('/checkin/token-inesistente', $this->payload())->assertNotFound();
        $this->assertSame($before, $this->stato());
    }
}
