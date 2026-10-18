<?php
namespace Tests\Feature;

use App\Models\{Schedina, Componenti};
use App\Services\{PresenzeReportService, QuesturaTxtExportService, IstatTabellaAService, TassaDiSoggiornoService};
use App\Support\ArriviDay;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArriviLifecycleTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }
    private function context(): array
    {
        Carbon::setTestNow(Carbon::parse('2026-03-15 23:59:50', 'Europe/Rome'));
        $s=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$s->id));$this->cataloghiSchedina($s->id);
        return [$s,array_replace($this->validSchedinaPayload(),['relationship'=>'OSPITE SINGOLO','save_mode'=>'to_arrivi','schedina_struttura_id'=>$s->id])];
    }
    private function arrival(array $payload): Schedina
    {
        $this->post(route('schedina.store'),$payload)->assertSessionHasNoErrors()->assertRedirect(route('arrivals'));
        return Schedina::latest('id')->firstOrFail();
    }
    public function test_salvataggio_arrivi_non_crea_schedina_e_non_sposta_partenza(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival(array_replace($p,['arrive'=>'2020-01-01','departure'=>'2026-03-17']));
        $this->assertTrue($r->isInArrivi());$this->assertSame('2026-03-15',$r->getRawOriginal('arrive'));
        $this->assertSame('2026-03-17',$r->departure);$this->assertSame(0,Schedina::effettive()->count());$this->assertDatabaseCount('schedina',1);
        foreach (['questura_exports','questura_transmissions','istat_exports','istat_transmissions','tassa_exports'] as $table) $this->assertDatabaseCount($table,0);
    }
    public function test_mezzanotte_e_giorni_successivi_senza_scheduler_non_convertono_ne_spostano_partenza(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$stored=DB::table('schedina')->where('id',$r->id)->first();
        Carbon::setTestNow(Carbon::parse('2026-03-16 00:00:00','Europe/Rome'));
        $this->assertSame('2026-03-16',$r->arrive);$this->get(route('arrivals.clock'))->assertOk()->assertJsonPath('today','2026-03-16');
        Carbon::setTestNow(Carbon::parse('2026-03-21 12:00:00','Europe/Rome'));
        $this->get(route('arrivals'))->assertOk()->assertSee('Partenza da correggere');
        $this->assertSame('2026-03-21',$r->fresh()->arrive);$this->assertEquals($stored,DB::table('schedina')->where('id',$r->id)->first());
        $this->assertSame(0,Schedina::effettive()->count());
    }
    public function test_scheda_vecchia_salva_oggi_ma_lascia_partire_invariata_anche_scaduta(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);Carbon::setTestNow('2026-03-20 12:00:00');
        $this->put(route('schedina.update',$r->id),array_replace($p,['save_mode'=>'full','arrivi_context'=>1]))->assertSessionHasNoErrors()->assertRedirect(route('arrivals'));
        $r->refresh();$this->assertSame('2026-03-20',$r->getRawOriginal('arrive'));$this->assertSame('2026-03-17',$r->departure);$this->assertTrue($r->isInArrivi());
    }
    public function test_conversione_scaduta_o_incompleta_non_modifica_dati(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);Carbon::setTestNow('2026-03-20 12:00:00');$before=DB::table('schedina')->where('id',$r->id)->first();
        $response=$this->postJson(route('a_schedina',$r->id),['confirm_conversion'=>1])->assertUnprocessable()->assertJsonValidationErrors('departure');
        $this->assertStringContainsString('La partenza deve essere successiva', $response->json('errors.departure.0'));
        $this->assertEquals($before,DB::table('schedina')->where('id',$r->id)->first());
    }
    public function test_conferma_obbligatoria_e_conversione_esattamente_una_volta(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$this->postJson(route('a_schedina',$r->id),[])->assertUnprocessable()->assertJsonValidationErrors('confirm_conversion');
        $this->assertTrue($r->fresh()->isInArrivi());
        Carbon::setTestNow('2026-03-16 00:00:01');
        foreach ([1,2] as $retry) $this->post(route('a_schedina',$r->id),['confirm_conversion'=>1])->assertSessionHasNoErrors()->assertRedirect(route('schedina.edit',$r->id));
        $r->refresh();$this->assertFalse($r->isInArrivi());$this->assertSame('2026-03-16',$r->arrive);$this->assertSame('2026-03-17',$r->departure);$this->assertSame(1,Schedina::effettive()->count());$this->assertDatabaseCount('schedina',1);
        $this->putJson(route('schedina.update',$r->id),array_replace($p,['arrivi_context'=>1]))->assertStatus(409);
        $this->assertFalse($r->fresh()->isInArrivi());
    }
    public function test_flussi_escludono_arrivi_anche_con_flag_incoerente(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);
        foreach ([['is_arrive'=>1,'circuito'=>'schedina'],['is_arrive'=>0,'circuito'=>'arrivi'],['is_arrive'=>1,'circuito'=>null]] as $flags) {
            DB::table('schedina')->where('id',$r->id)->update($flags);$r->refresh();
            $dal=Carbon::parse('2026-03-01');$al=Carbon::parse('2026-03-31');
            $this->assertCount(0,app(PresenzeReportService::class)->schedinePeriodo($s->id,$dal,$al));
            $this->assertCount(0,app(QuesturaTxtExportService::class)->schedinePerPeriodo($s->id,$dal,$al));
            $this->assertCount(0,app(IstatTabellaAService::class)->schedinePerPeriodo($s->id,$dal,$al));
            $this->assertFalse(app(QuesturaTxtExportService::class)->analizzaSchedine(collect([$r]))->first()['valida']);
            $tax=app(TassaDiSoggiornoService::class)->dettaglioSchedina($r,collect(),null,collect(),$s);$this->assertSame(0,$tax['totale']);$this->assertSame([],$tax['righe']);
            $this->assertSame(0,Schedina::effettive()->count());
        }
    }
    public function test_componenti_preservati_e_rivalidati(): void
    {
        [$s,$p]=$this->context();\App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA','cittadinanza'=>'FRANCESE','codice_iso2'=>'FR']);$p['relationship']='CAPO FAMIGLIA';$p['cant_people']=2;
        $p['componenti']=[['name'=>'Componente','surname'=>'Sintetico','sex'=>'F','relationship'=>'FAMILIARE','exent'=>'NO','country_nac'=>'FRANCIA','city_nac'=>'FRANCESE','city'=>'Parigi','country'=>'Francia','date_nac'=>'1990-01-01']];
        $r=$this->arrival($p);$before=$r->componenti->firstOrFail()->toArray();
        $this->post(route('a_schedina',$r->id),['confirm_conversion'=>1])->assertSessionHasNoErrors();
        $this->assertSame(1,$r->fresh()->componenti->count());$this->assertSame($before,$r->fresh()->componenti->first()->toArray());
    }
    public function test_fallimento_transazionale_preserva_arrivo_e_relazioni(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$before=DB::table('schedina')->where('id',$r->id)->first();
        Schedina::saving(function ($row) { if ($row->circuito==='schedina') throw new \RuntimeException('Guasto sintetico prima del commit'); });
        $this->withoutExceptionHandling();
        try {$this->post(route('a_schedina',$r->id),['confirm_conversion'=>1]);$this->fail('Guasto non raggiunto');} catch (\RuntimeException $e) {$this->assertSame('Guasto sintetico prima del commit',$e->getMessage());}
        $this->assertEquals($before,DB::table('schedina')->where('id',$r->id)->first());
    }
    public function test_isolamento_identificatori_e_cambio_struttura(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$other=$this->structureFor(null);
        $this->postJson(route('a_schedina',$r->id),['confirm_conversion'=>1,'schedina_struttura_id'=>$other->id])->assertUnprocessable();
        $this->actingAs($this->actor('struttura_user',null,$other->id))->withSession(['struttura_corrente_id'=>$other->id]);
        $this->get(route('schedina.edit',$r->id))->assertNotFound();$this->post(route('a_schedina',$r->id),['confirm_conversion'=>1])->assertNotFound();
        $this->assertTrue(Schedina::withoutGlobalScopes()->findOrFail($r->id)->isInArrivi());
    }
    public function test_calendario_notifiche_dashboard_escludono_arrivi_e_riammettono_solo_dopo_conferma(): void
    {
        [$s,$p]=$this->context();$p['oa_date_nac']='1990-03-15';$r=$this->arrival($p);
        $calendar=app(\App\Http\Controllers\CalendarioController::class);$cm=new \ReflectionMethod($calendar,'schedinaMovementEvents');
        $notifications=app(\App\Services\NotificheService::class);$nm=new \ReflectionMethod($notifications,'birthdayNotifications');
        $from=Carbon::parse('2026-03-01');$to=Carbon::parse('2026-03-31');
        $this->assertSame([],$cm->invoke($calendar,$s,$from,$to));$this->assertCount(0,$nm->invoke($notifications,auth()->user()));
        $this->get('/dashboard')->assertOk()->assertViewHas('dashboardData',fn($v)=>$v['schedine']===0 && $v['arrivi']===1);
        $this->post(route('a_schedina',$r->id),['confirm_conversion'=>1])->assertSessionHasNoErrors();
        $this->assertNotEmpty($cm->invoke($calendar,$s,$from,$to));$this->assertNotEmpty($nm->invoke($notifications,auth()->user()));
    }
    public function test_fuso_del_progetto_e_non_utc(): void
    {
        $this->context();Carbon::setTestNow(Carbon::parse('2026-03-15T23:00:00Z'));
        $this->assertSame('2026-03-16',ArriviDay::today());$this->assertSame('Europe/Rome',ArriviDay::clock()['timezone']);
    }
    public function test_conversione_rivalida_componente_incompleto_senza_perderlo(): void
    {
        [$s,$p]=$this->context();$p['relationship']='CAPO FAMIGLIA';$p['cant_people']=2;$p['componenti']=[['name'=>'Parziale','surname'=>'Da completare']];
        $r=$this->arrival($p);$before=DB::table('componenti')->where('schedina_id',$r->id)->get()->toJson();
        $this->postJson(route('a_schedina',$r->id),['confirm_conversion'=>1])->assertUnprocessable();
        $this->assertTrue($r->fresh()->isInArrivi());$this->assertSame($before,DB::table('componenti')->where('schedina_id',$r->id)->get()->toJson());
    }

    public function test_canali_istat_diretti_non_creano_export_o_prenotazioni_per_arrivi(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$service=app(\App\Services\IstatPayloadStore::class);
        try {$service->store($s->id,auth()->id(),now(),now(),'sintetico.xml','',collect([$r]));$this->fail('Arrivi ammesso nello store XML');}
        catch (\Illuminate\Validation\ValidationException $e) {$this->assertStringContainsString('Schede in Arrivi',implode(' ', $e->errors()['istat_export']));}
        $this->assertDatabaseCount('istat_exports',0);
        $export=\App\Models\IstatExport::create(['struttura_id'=>$s->id,'dal'=>'2026-03-15','al'=>'2026-03-17','filename'=>'sintetico.xml','path'=>'istat/struttura_'.$s->id.'/sintetico.xml','sha256'=>str_repeat('a',64),'schedina_ids'=>[$r->id]]);
        try {app(\App\Services\IstatOperationService::class)->reserve($s,$export,'verify',auth()->id());$this->fail('Arrivi ammesso nella prenotazione');}
        catch (\Illuminate\Validation\ValidationException $e) {$this->assertStringContainsString('Arrivi',implode(' ', $e->errors()['istat_ws']));}
        $this->assertDatabaseCount('istat_transmissions',0);$this->assertDatabaseCount('istat_communication_days',0);
    }

    public function test_ricerca_paginazione_ordinamento_contatori_e_ritorno_arrivi(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$r->delete();
        for ($i=1;$i<=12;$i++) {
            $attributes=$r->getAttributes();unset($attributes['id'],$attributes['created_at'],$attributes['updated_at']);
            $attributes['name']='RicercaArrivi';$attributes['scheda']=sprintf('A-26%03d',$i);Schedina::forceCreate($attributes);
        }
        $this->get(route('arrivals',['q'=>'RicercaArrivi','page'=>1]))->assertOk()
            ->assertViewHas('arrivals',fn($v)=>$v->total()===12 && $v->first()->scheda==='A-26012')
            ->assertViewHas('arriviStats',fn($v)=>$v['in_arrivo_oggi']===12 && $v['tot_persone']===12);
        $this->get(route('arrivals',['q'=>'RicercaArrivi','page'=>2]))->assertOk()->assertViewHas('arrivals',fn($v)=>$v->count()===2);
        $this->get(route('arrivals',['q'=>'NessunRisultatoSintetico']))->assertOk()->assertViewHas('arrivals',fn($v)=>$v->total()===0);
        $row=Schedina::firstOrFail();$this->put(route('schedina.update',$row->id),array_replace($p,['arrivi_context'=>1,'arrivi_q'=>'RicercaArrivi','arrivi_page'=>2]))->assertSessionHasNoErrors()->assertRedirect(route('arrivals',['q'=>'RicercaArrivi','page'=>2]));
        $this->assertTrue($row->fresh()->isInArrivi());
    }

    public function test_cestino_non_converte_arrivi_e_vecchia_eliminazione_non_cancella_schedina(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);
        $this->post(route('arrivals.destroy',$r->id),['schedina_struttura_id'=>$s->id])->assertRedirect();
        $item=\App\Models\CestinoItem::where('original_id',$r->id)->where('entity_class',Schedina::class)->firstOrFail();
        Carbon::setTestNow('2026-03-16 00:00:01');$restored=app(\App\Services\CestinoService::class)->restoreItem($item);
        $this->assertTrue($restored->isInArrivi());$this->assertSame('2026-03-16',$restored->getRawOriginal('arrive'));$this->assertSame('2026-03-15 23:59:50',$restored->customer_privacy_consent_at->format('Y-m-d H:i:s'));$this->assertSame('2026-03-16',$restored->arrive);$this->assertSame('2026-03-17',$restored->departure);
        $this->post(route('a_schedina',$restored->id),['confirm_conversion'=>1])->assertSessionHasNoErrors();
        $this->post(route('arrivals.destroy',$restored->id))->assertNotFound();$this->assertTrue(Schedina::effettive()->whereKey($restored->id)->exists());
    }

    public function test_identificatori_malformati_non_raggiungono_mutazioni_o_errori_interni(): void
    {
        [$s,$p]=$this->context();$r=$this->arrival($p);$before=DB::table('schedina')->get()->toJson();
        foreach (['abc','-1','999999999999999999999999'] as $id) {
            $this->get('/schedine/'.$id.'/modifica')->assertNotFound();
            foreach (['/arrivi/'.$id.'/a-schedina','/arrivi/'.$id.'/elimina'] as $url) {
                $res=$this->postJson($url,['confirm_conversion'=>1]);$this->assertContains($res->status(),[404,405]);
            }
        }
        $this->assertSame($before,DB::table('schedina')->get()->toJson());
    }

}
