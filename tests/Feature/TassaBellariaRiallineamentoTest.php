<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\StrutturaAuditLog;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaBellariaRiallineamentoTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function legacy(): array
    {
        $a = $this->structureFor(null);
        $a->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $u = $this->actor('struttura_user', null, $a->id);
        $u->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($u);
        $c = TassaDiSoggiorno::create(['struttura_id' => $a->id, 'tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-03-01', 'fine' => '2026-10-01', 'max_age_children' => 17, 'min_age_adult' => 18, 'note' => 'Nota sintetica preservata']);

        return [$a, $c, $u];
    }

    private function token(): string
    {
        return $this->get('/tassa_di_soggiorno?anno_fiscale=2026')->assertOk()->viewData('riallineamentoToken');
    }

    public function test_legacy_vuota_bloccata_get_non_scrive_e_riallineamento_esplicito_abilita_rapporto_csv(): void
    {
        [$a, $c] = $this->legacy();
        $prima = $c->fresh()->getAttributes();
        $this->getJson('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertUnprocessable()->assertJsonValidationErrors('configurazione_legacy_discordante');
        $this->get('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertRedirect('/tassa_di_soggiorno?anno_fiscale=2026');
        $t = $this->token();
        $this->assertSame($prima, $c->fresh()->getAttributes());
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t])->assertUnprocessable();
        $this->post('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertRedirect();
        $this->assertSame('2026-06-01', $c->fresh()->inizio->toDateString());
        $this->assertSame('2026-09-30', $c->fresh()->fine->toDateString());
        $this->assertSame($prima['note'], $c->fresh()->note);
        $this->assertSame(2, StrutturaAuditLog::where('entita_tipo', 'configurazione_tassa')->count());
        $this->get('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk();
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertStatus(409);
    }

    public function test_anteprima_obsoleta_token_manomesso_e_ruolo_non_autorizzato(): void
    {
        [$a, $c, $u] = $this->legacy();
        $t = $this->token();
        $c->update(['note' => 'Modifica concorrente']);
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertStatus(409);
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => 'non-valido', 'consenso' => 1])->assertStatus(409);
        $u->update(['ruolo_operativo' => 'reception']);
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertForbidden();
        $this->assertSame('2026-03-01', $c->fresh()->inizio->toDateString());
    }

    public function test_anno_non_certificato_categoria_mancante_tenant_e_rollback_audit(): void
    {
        [$a, $c, $u] = $this->legacy();
        $this->get('/tassa_di_soggiorno?anno_fiscale=2027')->assertOk()->assertViewHas('riallineamentoToken', null);
        $this->getJson('/tassa_di_soggiorno/rapporto?mese=6&anno=2027')->assertUnprocessable();
        $t = $this->token();
        $b = $this->structureFor(null);
        $altro = $this->actor('struttura_user', null, $b->id);
        $altro->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($altro);
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertStatus(409);
        $altro->update(['ruolo_operativo' => 'reception']);
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertForbidden();
        $this->actingAs($u);
        StrutturaAuditLog::saving(fn ($log) => $log->entita_tipo === 'configurazione_tassa' ? false : null);
        $this->postJson('/tassa_di_soggiorno/riallinea', ['token' => $t, 'consenso' => 1])->assertStatus(500);
        $this->assertSame('2026-03-01', $c->fresh()->inizio->toDateString());
        $this->assertSame(0, StrutturaAuditLog::where('entita_tipo', 'configurazione_tassa')->count());
        $a->update(['classificazione' => null, 'classificazione_id' => null]);
        $this->get('/tassa_di_soggiorno?anno_fiscale=2026')->assertOk()->assertViewHas('riallineamentoToken', null);
    }

    public function test_ricevuta_report_csv_e_storico_preservati_dopo_riallineamento(): void
    {
        [$a, $c] = $this->legacy();
        $c->update(['inizio' => '2026-06-01', 'fine' => '2026-09-30']);
        $p = Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico', 'surname' => 'Audit', 'arrive' => '2026-06-10', 'departure' => '2026-06-13', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 4.5);
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertRedirect();
        $export = TassaExport::firstOrFail()->getAttributes();
        $c->update(['fine' => '2026-10-01']);
        $this->post('/tassa_di_soggiorno/riallinea', ['token' => $this->token(), 'consenso' => 1])->assertRedirect();
        $this->assertSame($export, TassaExport::firstOrFail()->getAttributes());
        $this->get('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertOk()->assertViewHas('totalePeriodo', 4.5);
        $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk();
        $p->update(['arrive' => '2026-12-31', 'departure' => '2027-06-13']);
        $this->getJson('/schedine/'.$p->id.'/tassa/print')->assertUnprocessable();
        $this->getJson('/tassa_di_soggiorno/rapporto?data_da=2026-12-01&data_a=2027-06-30')->assertUnprocessable();
        $this->getJson('/tassa_di_soggiorno/rapporto/csv?data_da=2026-12-01&data_a=2027-06-30')->assertUnprocessable();
    }
}
