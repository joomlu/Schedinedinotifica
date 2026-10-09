<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\WebCheckinRichiesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinFiscalContextTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function contesto(string $arrival = '2026-06-10', string $departure = '2026-06-13'): array
    {
        $s = $this->structureFor(null);
        $s->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $cfg = TassaDiSoggiorno::create(['struttura_id' => $s->id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-06-01', 'fine' => '2026-09-30', 'max_age_children' => 17, 'min_age_adult' => 18]);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'PERSONA-SINTETICA-A07', 'surname' => 'Prova', 'arrive' => $arrival, 'departure' => $departure, 'cant_people' => 1, 'oa_date_nac' => '1980-01-01', 'exent' => 'NO']);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'WC'.$p->id, 'numero_prenotazione' => 'SINTETICA', 'email' => 'test@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => $arrival, 'partenza' => $departure, 'token' => bin2hex(random_bytes(32)), 'stato' => 'da_inviare']);

        return [$s, $cfg, $p, $r];
    }

    private function short(WebCheckinRichiesta $r): string
    {
        return '/w/'.$r->codice.'-'.substr($r->token, 0, 8);
    }

    private function fiscale(): array
    {
        return [DB::table('tassa_di_soggiorno')->orderBy('id')->get()->toJson(), DB::table('tassa_esenzioni')->orderBy('id')->get()->toJson()];
    }

    public function test_anonimo_legge_configurazione_propria_e_non_allenta_scope_gestionale(): void
    {
        [$s, $cfg, $p, $r] = $this->contesto();
        $before = $this->fiscale();
        $this->assertFalse(TassaDiSoggiorno::query()->whereKey($cfg->id)->exists());
        $this->get('/checkin/'.$r->token)->assertOk()->assertSee('schedina-form')
            ->assertViewHas('tassaConfig', fn ($c) => $c->id === $cfg->id && (int) $c->struttura_id === $s->id)
            ->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] == 4.5);
        $this->get($this->short($r))->assertOk()->assertSee('/checkin/'.$r->token);
        $this->get('/w/'.$r->token)->assertOk()->assertSee('schedina-form');
        $this->assertFalse(TassaDiSoggiorno::query()->whereKey($cfg->id)->exists());
        $this->assertSame($before, $this->fiscale());
    }

    public function test_token_determina_tenant_e_non_parametri_o_sessione_estranea(): void
    {
        [$a, $cfgA, $pA, $rA] = $this->contesto();
        [$b, $cfgB, $pB, $rB] = $this->contesto();
        TassaEsenzione::create(['struttura_id' => $a->id, 'codice' => '450', 'descrizione' => 'ESENZIONE-TENANT-A', 'attivo' => true]);
        TassaEsenzione::create(['struttura_id' => $b->id, 'codice' => '450', 'descrizione' => 'RISERVATO-ESENZIONE-B', 'attivo' => true]);
        TassaEsenzione::create(['struttura_id' => $a->id, 'codice' => '777', 'descrizione' => 'NON-SELEZIONABILE', 'attivo' => true]);
        TassaEsenzione::create(['struttura_id' => $a->id, 'codice' => '999', 'descrizione' => 'INATTIVA', 'attivo' => false]);
        $before = $this->fiscale();
        foreach ([false, true] as $logged) {
            if ($logged) {
                $this->actingAs($this->actor('struttura_user', null, $b->id));
            }
            $this->get('/checkin/'.$rA->token.'?struttura_id='.$b->id.'&config_id='.$cfgB->id)
                ->assertOk()->assertSee('ESENZIONE-TENANT-A')->assertDontSee('RISERVATO-ESENZIONE-B')
                ->assertDontSee('NON-SELEZIONABILE')->assertDontSee('INATTIVA')
                ->assertViewHas('tassaConfig', fn ($c) => $c->id === $cfgA->id)
                ->assertViewHas('esenzioni', fn ($items) => $items->every(fn ($e) => (int) $e->struttura_id === $a->id));
        }
        $this->assertFalse(TassaDiSoggiorno::query()->whereKey($cfgA->id)->exists());
        $this->assertSame($before, $this->fiscale());
    }

    public static function invalidi(): array
    {
        return [['assente'], ['aliquota'], ['periodo']];
    }

    #[DataProvider('invalidi')]
    public function test_profilo_mancante_o_discordante_non_usa_configurazione_altro_tenant(string $case): void
    {
        [$s, $cfg, $p, $r] = $this->contesto();
        $this->contesto(); // Un'altra configurazione valida non costituisce fallback.
        if ($case === 'assente') {
            $cfg->delete();
        } elseif ($case === 'aliquota') {
            $cfg->update(['tassa_soggiorno' => 9]);
        } else {
            $cfg->update(['inizio' => '2026-03-01']);
        }
        $before = $this->fiscale();
        $this->getJson('/checkin/'.$r->token)->assertStatus(422)->assertJsonValidationErrors(match ($case) {
            'assente' => 'configurazione_tassa', 'aliquota' => 'categoria_tassa', default => 'periodo_tassa',
        })->assertDontSee('PERSONA-SINTETICA-A07');
        $this->assertSame($before, $this->fiscale());
    }

    public static function periodi(): array
    {
        return [['2026-05-10', '2026-05-13', 0], ['2026-05-31', '2026-06-03', 3]];
    }

    #[DataProvider('periodi')]
    public function test_fuori_periodo_e_cavallo_periodo_con_configurazione_valida(string $start, string $end, float $total): void
    {
        [$s, $cfg, $p, $r] = $this->contesto($start, $end);
        $this->get('/checkin/'.$r->token)->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] == $total);
        $this->assertSame($start, DB::table('schedina')->where('id', $p->id)->value('arrive'));
        $this->assertSame($end, DB::table('schedina')->where('id', $p->id)->value('departure'));
    }

    public static function anniNonCoperti(): array
    {
        return [['2027-06-01', '2027-06-03'], ['2026-12-31', '2027-01-02']];
    }

    #[DataProvider('anniNonCoperti')]
    public function test_anno_non_certificato_e_crossyear_restano_bloccati(string $start, string $end): void
    {
        [$s, $cfg, $p, $r] = $this->contesto($start, $end);
        $before = $this->fiscale();
        $this->getJson('/checkin/'.$r->token)->assertStatus(422)->assertJsonValidationErrors('regola_non_disponibile')->assertDontSee('PERSONA-SINTETICA-A07');
        $this->assertSame($before, $this->fiscale());
    }

    public function test_parent_estero_e_token_invalidi_non_espongono_contesto_fiscale(): void
    {
        [$a, $cfg, $p, $r] = $this->contesto();
        [$b, $cfgB, $pB, $rB] = $this->contesto();
        $r->update(['schedina_id' => $pB->id]);
        foreach (['/checkin/'.$r->token, $this->short($r), '/checkin/'.str_repeat('z', 64)] as $url) {
            $this->getJson($url)->assertNotFound()->assertDontSee('PERSONA-SINTETICA-A07')->assertDontSee('tassa_soggiorno');
        }
    }

    public function test_revoca_e_tempo_non_introducono_nuova_politica_di_scadenza(): void
    {
        [$s, $cfg, $p, $r] = $this->contesto();
        $old = $r->token;
        $short = $this->short($r);
        $this->travelTo(now()->setDate(2030, 1, 1));
        $this->get('/checkin/'.$old)->assertOk(); // Nessuna scadenza temporale implementata.
        $r->update(['token' => str_repeat('Z', 64)]);
        $this->get('/checkin/'.$old)->assertNotFound();
        $this->get($short)->assertNotFound();
        $r->delete();
        $this->get('/checkin/'.str_repeat('Z', 64))->assertNotFound();
        $this->travelBack();
    }
}
