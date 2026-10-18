<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebCheckinEsclusioniConversioneTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    public static function circuiti(): array
    {
        return [['web'], ['bozza']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('circuiti')]
    public function test_web_e_bozza_esclusi_da_servizi_soggiorni_e_calendario(string $circuit): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $r = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => $circuit, 'name' => 'PENDENTE-SINTETICO', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(3)->toDateString(), 'cant_people' => 2]);
        $from = Carbon::now()->startOfMonth(); $to = Carbon::now()->addMonth()->endOfMonth();
        $this->assertCount(0, app(\App\Services\PresenzeReportService::class)->schedinePeriodo($s->id, $from, $to));
        foreach ([\App\Services\QuesturaTxtExportService::class, \App\Services\IstatTabellaAService::class] as $service) {
            $this->assertCount(0, app($service)->schedinePerPeriodo($s->id, $from, $to));
        }
        $taxReport = app(\App\Http\Controllers\TassaReportController::class);
        foreach (['dataDa' => $from, 'dataA' => $to] as $property => $value) {
            (new \ReflectionProperty($taxReport, $property))->setValue($taxReport, $value);
        }
        $dataset = (new \ReflectionMethod($taxReport, 'buildControlDataset'))->invoke($taxReport, (int) $from->month, (int) $from->year, $s->id, $s, null, collect());
        $this->assertSame(0, $dataset['summary']['totale_schedine']);
        $this->assertSame(0.0, $dataset['summary']['totale_tassa']);
        $calendar = app(\App\Http\Controllers\CalendarioController::class);
        $method = new \ReflectionMethod($calendar, 'schedinaMovementEvents');
        $this->assertSame([], $method->invoke($calendar, $s, $from, $to));
        $this->assertSame(0, Schedina::effettive()->count());
        $this->get(route('schedina.tassa.print', $r->id))->assertNotFound();
        $this->get('/dashboard')->assertOk()->assertViewHas('dashboardData', fn ($v) => $v['schedine'] === 0 && $v['arrivi'] === 0);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('circuiti')]
    public function test_conversione_diretta_da_web_o_bozza_una_sola_scheda_senza_invio(string $circuit): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id)); $this->cataloghiSchedina($s->id);
        $payload = array_replace($this->validSchedinaPayload(), ['arrive' => now()->addDays(3)->toDateString(), 'departure' => now()->addDays(6)->toDateString(), 'relationship' => 'OSPITE SINGOLO']);
        $sheet = Schedina::forceCreate($payload + ['struttura_id' => $s->id, 'circuito' => $circuit, 'id_prenotazione_esterna' => 'RIFERIMENTO-SINTETICO', 'fonte_prenotazione' => 'WEB CHECK-IN']);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $sheet->id, 'codice' => 'CONVERSIONE'.$sheet->id, 'numero_prenotazione' => 'RIFERIMENTO-SINTETICO', 'email' => 'sintetico@example.invalid', 'nome_referente' => 'Referente', 'arrivo' => $payload['arrive'], 'partenza' => $payload['departure'], 'quantita_persone' => 1, 'note' => 'NOTE-INTERNE-SINTETICHE', 'token' => \Illuminate\Support\Str::random(64)]);
        app(\App\Services\WebCheckinLink::class)->issue($r);
        $r->refresh();
        $payload += ['save_mode' => 'full', 'web_versione_gestione' => app(\App\Services\WebCheckinVersione::class)->of($r)];
        $this->put(route('schedina.update', $sheet->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $after = $sheet->fresh()->getAttributes();
        $this->putJson(route('schedina.update', $sheet->id), $payload)->assertStatus(409);
        $this->assertSame($after, $sheet->fresh()->getAttributes());
        $this->assertSame('schedina', $sheet->fresh()->circuito); $this->assertDatabaseCount('schedina', 1);
        $this->assertSame('RIFERIMENTO-SINTETICO', $sheet->fresh()->id_prenotazione_esterna);
        $this->assertSame('NOTE-INTERNE-SINTETICHE', $r->fresh()->note);
        $this->get('/checkin/'.$r->token)->assertOk()->assertContent('Check-in recibido');
        foreach (['questura_transmissions', 'istat_transmissions', 'tassa_exports'] as $table) $this->assertDatabaseCount($table, 0);
    }
}
