<?php
namespace Tests\Feature;

use App\Models\{Schedina, Componenti, GeoNazione, Struttura};
use App\Services\PresenzeReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class PresenzeCompleteTest extends TestCase
{
    use DatabaseTransactions, StrutturaFixtures;

    private function soggiorno(Struttura $s, string $dal, string $al, int $persone = 1, array $extra = []): Schedina
    {
        return Schedina::forceCreate(array_merge(['struttura_id'=>$s->id,'name'=>'Persona sintetica','surname'=>'Presenze',
            'arrive'=>$dal,'departure'=>$al,'cant_people'=>$persone,'room'=>1,'beds'=>$persone,'is_arrive'=>false,'circuito'=>'schedina','oa_date_nac'=>'1980-01-01'], $extra));
    }

    public function test_riconcilia_mesi_dettaglio_giorni_movimenti_e_saldo(): void
    {
        $s=$this->structureFor(null);
        // Gennaio: saldo iniziale 3; arrivi 3; partenze 5; saldo finale 1; 2+4+1 notti=7.
        $this->soggiorno($s,'2025-12-30','2026-01-01',2);
        $this->soggiorno($s,'2026-01-10','2026-01-12',2);
        $this->soggiorno($s,'2026-01-31','2026-02-02');
        $this->soggiorno($s,'2025-12-31','2026-01-03');
        $srv=new PresenzeReportService;
        $jan=$srv->riepilogoAnno($s->id,2026)->first()['totale'];
        $this->assertSame(3,$jan['presenti']);
        $this->assertSame(3,$jan['arrivi']);
        $this->assertSame(5,$jan['partenze']);
        $this->assertSame(7,$jan['presenze']);
        $this->assertSame(1,$jan['presenti']+$jan['arrivi']-$jan['partenze']);
        $dal=Carbon::parse('2026-01-01');$al=Carbon::parse('2026-01-31');
        $d=$srv->dettaglioPeriodo($s->id,$dal,$al)['totali'];
        $m=$srv->movimentiPeriodo($s->id,$dal,$al)['totali'];
        $this->assertSame($jan['presenze'],$d['presenze']);
        $this->assertSame($jan['arrivi'],$d['arrivi']);
        $this->assertSame($jan['arrivi'],$m['persone_arrivo']);
        $this->assertSame($jan['partenze'],$m['persone_partenza']);
        $notti=0;
        for($g=$dal->copy();$g->lte($al);$g->addDay())$notti+=$srv->situazioneOggi($s->id,$g)['presenti_totali'];
        $this->assertSame(7,$notti);
        $anno=$srv->riepilogoAnno($s->id,2026)->sum('totale.presenze');
        $this->assertSame($anno,$srv->dettaglioPeriodo($s->id,$dal,Carbon::parse('2026-12-31'))['totali']['presenze']);
    }

    public function test_residenza_individuale_sconosciuti_e_filtro_gruppo_misto(): void
    {
        $it=GeoNazione::create(['nome'=>'Italia sintetica','codice_iso2'=>'IT','is_italia'=>true]);
        $fr=GeoNazione::create(['nome'=>'Estero sintetico','codice_iso2'=>'FR','is_italia'=>false]);
        $s=$this->structureFor(null);
        $h=$this->soggiorno($s,'2024-02-28','2024-03-01',3,['or_country'=>$it->id,'oa_country'=>$fr->id]);
        Componenti::forceCreate(['struttura_id'=>$s->id,'schedina_id'=>$h->id,'name'=>'Componente sintetico','surname'=>'Estero','country'=>$fr->id,'date_nac'=>'2015-01-01']);
        $srv=new PresenzeReportService;$dal=Carbon::parse('2024-02-01');$al=Carbon::parse('2024-02-29');
        $m=$srv->riepilogoAnno($s->id,2024)[1];
        foreach(['italiane','straniere','sconosciute'] as $key)$this->assertSame(2,$m[$key]['presenze']);
        foreach(['italiane','straniere','sconosciute'] as $key){
            $d=$srv->dettaglioPeriodo($s->id,$dal,$al,$key)['totali'];
            $this->assertSame(1,$d['arrivi']);$this->assertSame(2,$d['presenze']);
        }
        $day=$srv->situazioneOggi($s->id,Carbon::parse('2024-02-29'));
        $this->assertSame(3,$day['presenti_totali']);$this->assertSame(1,$day['italiani']);$this->assertSame(1,$day['stranieri']);$this->assertSame(1,$day['sconosciuti']);
    }

    public function test_esclude_bozze_arrivi_e_altri_tenant_senza_duplicare_camere(): void
    {
        $s=$this->structureFor(null);$altro=$this->structureFor(null);
        $this->soggiorno($s,'2026-06-01','2026-06-03',8,['circuito'=>'web_checkin']);
        $this->soggiorno($s,'2026-06-01','2026-06-03',9,['circuito'=>'arrivi','is_arrive'=>true]);
        $this->soggiorno($altro,'2026-06-01','2026-06-03',10);
        $h=$this->soggiorno($s,'2026-06-01','2026-06-03',2);
        foreach(['101','102'] as $numero)$h->camere()->create(['struttura_id'=>$s->id,'numero_camera'=>$numero,'posti_letto'=>2]);
        $r=(new PresenzeReportService)->situazioneOggi($s->id,Carbon::parse('2026-06-01'));
        $this->assertSame(2,$r['presenti_totali']);$this->assertSame(2,$r['camere_occupate']);
        $this->assertSame(2,$r['arrivi_oggi']);$this->assertSame(1,$r['arrivi_rows']->count());
    }

    public function test_capacita_variabile_zero_e_percentuale_ponderata_non_limitata(): void
    {
        $s=$this->structureFor(null);$s->forceFill(['tipo_apertura'=>'Stagionale','data_apertura'=>'2026-06-02','data_chiusura'=>'2026-06-03'])->save();
        $this->soggiorno($s,'2026-06-02','2026-06-04',3,['room'=>3]);
        $srv=new PresenzeReportService;
        $r=$srv->occupazionePeriodo($s->id,Carbon::parse('2026-06-01'),Carbon::parse('2026-06-03'),2,2);
        $this->assertNull($r['rows'][0]['occupazione_camere']);
        $this->assertSame(150.0,$r['totali']['media_occupazione_camere']);
        $this->assertSame(150.0,$r['totali']['media_occupazione_letti']);
        $this->assertSame(2,$r['totali']['incoerenze']);
        $this->assertEquals(4,$r['totali']['camere_notti_disponibili']);
        $zero=$srv->occupazionePeriodo($s->id,Carbon::parse('2026-06-01'),Carbon::parse('2026-06-01'),0,0);
        $this->assertNull($zero['totali']['media_occupazione_camere']);
    }

    public function test_http_filtri_stampe_ruoli_tenancy_e_nessuna_modifica_soggiorni(): void
    {
        $s=$this->structureFor(null);$altro=$this->structureFor(null);$u=$this->actor('struttura_user',null,$s->id);
        $this->soggiorno($s,'2026-06-01','2026-06-03');
        $prima=Schedina::withoutGlobalScopes()->get()->toJson();
        $this->actingAs($u)->withSession(['struttura_id'=>$s->id]);
        foreach(['riepilogo','dettaglio','oggi','movimenti','occupazione'] as $tab){
            $this->get('/statistica/presenze?tab='.$tab.'&anno=2026&dal=2026-06-01&al=2026-06-30')->assertOk()->assertSee('Presenze');
        }
        $this->get('/statistica/presenze/stampa-riepilogo?anno=2026')->assertOk();
        $this->get('/statistica/presenze/stampa-dettaglio?dal=2026-06-01&al=2026-06-30')->assertOk();
        foreach(['dal=2026-02-30','dal=2026-06-30&al=2026-06-01','mese_da=12&mese_a=1','occupazione_mese=99','anno=foo','tab=inesistente'] as $bad)$this->getJson('/statistica/presenze?'.$bad)->assertUnprocessable();
        foreach(['','/stampa-riepilogo','/stampa-dettaglio'] as $path)$this->get('/statistica/presenze'.$path.'?struttura_id='.$altro->id)->assertForbidden();
        $this->assertSame($prima,Schedina::withoutGlobalScopes()->get()->toJson());
    }
    public function test_riepilogo_annuale_senza_n_piu_uno_e_periodo_vuoto(): void
    {
        $s=$this->structureFor(null);
        for($i=0;$i<25;$i++)$this->soggiorno($s,'2026-01-01','2026-12-31');
        \Illuminate\Support\Facades\DB::enableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();
        $srv=new PresenzeReportService;
        $rows=$srv->riepilogoAnno($s->id,2026);
        $queries=\Illuminate\Support\Facades\DB::getQueryLog();
        \Illuminate\Support\Facades\DB::disableQueryLog();
        $this->assertLessThanOrEqual(4,count($queries));
        $this->assertSame(25*364,$rows->sum('totale.presenze'));
        $this->assertSame(0,$srv->dettaglioPeriodo($s->id,Carbon::parse('2024-01-01'),Carbon::parse('2024-12-31'))['totali']['presenze']);
    }

    public function test_modifica_date_riletta_e_camere_duplicate_non_duplicano_persone(): void
    {
        $s=$this->structureFor(null);
        $h=$this->soggiorno($s,'2026-06-01','2026-06-04',2);
        $h->camere()->create(['struttura_id'=>$s->id,'numero_camera'=>'101','posti_letto'=>2]);
        $other=$this->soggiorno($s,'2026-06-01','2026-06-04');
        $other->camere()->create(['struttura_id'=>$s->id,'numero_camera'=>'101','posti_letto'=>2]);
        $r=(new PresenzeReportService)->situazioneOggi($s->id,Carbon::parse('2026-06-02'));
        $this->assertSame(3,$r['presenti_totali']);$this->assertSame(1,$r['camere_occupate']);
        $h->update(['departure'=>'2026-06-02']);
        $r=(new PresenzeReportService)->situazioneOggi($s->id,Carbon::parse('2026-06-02'));
        $this->assertSame(1,$r['presenti_totali']);$this->assertSame(2,$r['partenze_oggi']);
    }

    public function test_ruoli_sola_lettura_sessione_contraffatta_e_utenti_inattivi(): void
    {
        $admin=$this->actor('admin');$owner=$this->ownerFor($admin);$s=$this->structureFor($owner);
        foreach([$admin,$this->actor('super_admin'),$this->actor('proprietario',$owner->id),$this->actor('struttura_user',$owner->id,$s->id)] as $actor){
            $this->actingAs($actor)->withSession(['struttura_corrente_id'=>$s->id])->get('/statistica/presenze')->assertOk();
        }
        $altro=$this->structureFor($this->ownerFor($this->actor('admin')));
        $u=$this->actor('struttura_user',$owner->id,$s->id);
        $this->actingAs($u)->withSession(['struttura_corrente_id'=>$altro->id])->get('/statistica/presenze')->assertForbidden();
        $u->update(['attivo'=>false]);
        $this->actingAs($u)->withSession(['struttura_corrente_id'=>$s->id])->get('/statistica/presenze')->assertRedirect('/login');
    }

    public function test_periodo_del_riepilogo_trasferito_anche_al_primo_accesso(): void
    {
        $s=$this->structureFor(null);$u=$this->actor('struttura_user',null,$s->id);
        $r=$this->actingAs($u)->withSession(['struttura_corrente_id'=>$s->id])->get('/statistica/presenze?anno=2024&mese_da=2&mese_a=2');
        $r->assertOk()->assertViewHas('dal',fn($d)=>$d->toDateString()==='2024-02-01')
            ->assertViewHas('al',fn($d)=>$d->toDateString()==='2024-02-29')
            ->assertViewHas('occupazioneMese','periodo');
    }

}
