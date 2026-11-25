<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\Schedina;
use App\Models\TipoAlloggiato;
use App\Support\StrutturaCorrente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class AccessImportazioneTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;
    private string $token;
    private $hotel;
    private $reception;

    protected function setUp(): void
    {
        parent::setUp();
        $owner = $this->ownerFor($this->actor('admin'));
        $this->hotel = $this->structureFor($owner);
        $this->reception = $this->actor('struttura_user', $owner->id, $this->hotel->id);
        $this->token = str_repeat('x', 43);
        config(['access.connections.igea' => ['enabled' => true, 'struttura_id' => $this->hotel->id,
            'proprietario_id' => $owner->id, 'user_id' => $this->reception->id, 'token_hash' => hash('sha256', $this->token)]]);
        foreach ([16=>'OSPITE SINGOLO',17=>'CAPOFAMIGLIA',18=>'CAPOGRUPPO',19=>'FAMILIARE',20=>'MEMBRO GRUPPO'] as $code=>$name) TipoAlloggiato::firstOrCreate(['codice'=>$code], ['descrizione'=>$name]);
    }
    private function dati(): array
    {
        return ['versione'=>1,'richiesta_id'=>(string) \Illuminate\Support\Str::uuid(),'codice_checkin'=>'CI-TEST-001',
            'confermato_il'=>now()->toIso8601String(),
            'prenotazione'=>['riferimento'=>'PRENOTAZIONE SINTETICA','numero_ospiti'=>2,'camere_occupate'=>1,'letti_occupati'=>2,'email_ospite'=>'ospite@example.test','telefono_ospite'=>'+390000000001'],
            'titolare'=>['tipo_documento'=>'passaporto','dati'=>['nome'=>'TITOLARE','cognome'=>'FITTIZIO','sesso'=>'M','data_nascita'=>'01/01/1980','numero_documento'=>'TEST-SINTETICO','data_scadenza'=>'01/01/2099','autorita_rilascio'=>'MINISTRO AFFARI ESTERI','paese_residenza'=>'ITALIA']],
            'gruppo'=>['tipo_alloggiato'=>'17','data_arrivo'=>'2026-11-25','data_partenza'=>'2026-11-28',
                'componenti'=>[['id'=>(string) \Illuminate\Support\Str::uuid(),'tipo_alloggiato'=>'19','dati'=>['nome'=>'COMPONENTE','cognome'=>'FITTIZIO','sesso'=>'F','data_nascita'=>'03/02/1990','cittadinanza'=>'ITALIANA','stato_nascita'=>'ITALIA','provincia_nascita'=>'RM','comune_nascita'=>'ROMA','paese_residenza'=>'ITALIA','comune_residenza'=>'ROMA']]]]];
    }
    private function invia(array $data, string $key='igea', ?string $token=null)
    {
        return $this->withHeaders(['Authorization'=>'Bearer '.($token??$this->token),'X-Access-Connessione'=>$key])->postJson('/api/access/v1/checkin',$data);
    }
    public function test_crea_solo_bozza_con_titolare_componenti_e_proprieta_corrette(): void
    {
        $reply=$this->invia($this->dati())->assertCreated()->assertJsonPath('stato','bozza')->assertJsonPath('struttura_id',$this->hotel->id);
        $s=Schedina::withoutGlobalScopes()->findOrFail($reply['schedina_id']);
        $this->assertSame('TITOLARE',$s->name);$this->assertSame('CAPOFAMIGLIA',$s->relationship);
        $this->assertSame('1980-01-01',$s->oa_date_nac);$this->assertSame('MINISTRO AFFARI ESTERI',$s->or_published);
        $this->assertFalse($s->customer_privacy_consent);$this->assertNull($s->questura_sent_at);$this->assertNull($s->istat_sent_at);
        $c=Componenti::withoutGlobalScopes()->where('schedina_id',$s->id)->sole();
        $this->assertEquals($s->struttura_id,$c->struttura_id);$this->assertSame('COMPONENTE',$c->name);$this->assertSame('1990-02-03',$c->date_nac);
        $this->assertSame('FAMILIARE',$c->relationship);$this->assertNull($c->customer_id);
        $this->assertDatabaseCount('access_importazioni',1);$this->assertNotEmpty($reply['avvisi']);
    }
    public function test_ripetizione_identica_non_duplica_ne_sovrascrive_una_schedina_confermata(): void
    {
        $data=$this->dati();$first=$this->invia($data)->assertCreated();
        Schedina::withoutGlobalScopes()->whereKey($first['schedina_id'])->update(['circuito'=>'schedina','name'=>'MODIFICATO IN GESTIONALE']);
        $this->invia($data)->assertOk()->assertJsonPath('schedina_id',$first['schedina_id'])->assertJsonPath('stato','schedina');
        $this->assertSame('MODIFICATO IN GESTIONALE',Schedina::withoutGlobalScopes()->find($first['schedina_id'])->name);
        $this->assertDatabaseCount('schedina',1);$this->assertDatabaseCount('componenti',1);
    }
    public function test_modifica_successiva_non_sovrascrive_la_bozza(): void
    {
        $data=$this->dati();$this->invia($data)->assertCreated();$data['titolare']['dati']['nome']='ALTRO';
        $this->invia($data)->assertConflict();$this->assertSame('TITOLARE',Schedina::withoutGlobalScopes()->first()->name);
    }
    public function test_rimini_non_configurata_e_credenziali_errate_rifiutate(): void
    {
        $this->invia($this->dati(),'rimini')->assertUnauthorized();
        $this->invia($this->dati(),'igea',str_repeat('y',43))->assertUnauthorized();$this->assertDatabaseCount('schedina',0);
    }
    public function test_collegamento_disabilitato_rifiutato(): void
    {
        config(['access.connections.igea.enabled'=>false]);$this->invia($this->dati())->assertUnauthorized();$this->assertDatabaseCount('access_importazioni',0);
    }
    public function test_non_accetta_proprietario_o_reception_di_altra_struttura(): void
    {
        config(['access.connections.igea.proprietario_id'=>999999]);$this->invia($this->dati())->assertForbidden();
        config(['access.connections.igea.proprietario_id'=>$this->hotel->proprietario_id,'access.connections.igea.user_id'=>$this->actor('struttura_user',null,$this->structureFor(null)->id)->id]);
        $this->invia($this->dati())->assertForbidden();$this->assertDatabaseCount('schedina',0);
    }
    public function test_stesso_checkin_isolato_fra_due_strutture_abilitate(): void
    {
        $other=$this->structureFor($this->hotel->proprietario);$actor=$this->actor('struttura_user',$other->proprietario_id,$other->id);
        config(['access.connections.bellaria'=>['enabled'=>true,'struttura_id'=>$other->id,'proprietario_id'=>$other->proprietario_id,'user_id'=>$actor->id,'token_hash'=>hash('sha256',$this->token)]]);
        $data=$this->dati();$a=$this->invia($data)->assertCreated();$b=$this->invia($data,'bellaria')->assertCreated();
        $this->assertNotEquals($a['schedina_id'],$b['schedina_id']);$this->assertEquals($other->id,$b['struttura_id']);$this->assertDatabaseCount('access_importazioni',2);
    }
    public function test_ruoli_o_totali_errati_non_lasciano_scritture_parziali(): void
    {
        $data=$this->dati();$data['gruppo']['componenti'][0]['tipo_alloggiato']='20';$this->invia($data)->assertUnprocessable();
        $data=$this->dati();$data['prenotazione']['numero_ospiti']=1;$this->invia($data)->assertUnprocessable();
        $this->assertDatabaseCount('schedina',0);$this->assertDatabaseCount('componenti',0);$this->assertDatabaseCount('access_importazioni',0);
    }
    public function test_id_e_campi_di_controllo_esterni_non_possono_cambiare_struttura_o_stato(): void
    {
        $data=$this->dati();$data['struttura_id']=9999;$data['customer_id']=123;$data['circuito']='schedina';$data['customer_privacy_consent']=true;
        $this->invia($data)->assertCreated()->assertJsonPath('struttura_id',$this->hotel->id)->assertJsonPath('stato','bozza');
        $s=Schedina::withoutGlobalScopes()->first();$this->assertNull($s->customer_id);$this->assertFalse($s->customer_privacy_consent);
    }
    public function test_data_non_valida_e_documento_oggetto_rifiutati(): void
    {
        $data=$this->dati();$data['titolare']['dati']['data_nascita']='31/02/1980';$this->invia($data)->assertUnprocessable();
        $data=$this->dati();$data['titolare']['dati']['numero_documento']=['id'=>1];$this->invia($data)->assertUnprocessable();$this->assertDatabaseCount('schedina',0);
    }
    public function test_giornata_ammessa_solo_in_bozza_con_avviso(): void
    {
        $data=$this->dati();$data['gruppo']['data_partenza']=$data['gruppo']['data_arrivo'];
        $r=$this->invia($data)->assertCreated();$this->assertStringContainsString('giornata',implode(' ',$r['avvisi']));
    }
    public function test_singolo_e_gruppo_mantengono_i_ruoli_senza_creare_altri_clienti(): void
    {
        $single=$this->dati();$single['prenotazione']['numero_ospiti']=1;$single['gruppo']['tipo_alloggiato']='16';$single['gruppo']['componenti']=[];
        $a=$this->invia($single)->assertCreated();
        $this->assertSame('OSPITE SINGOLO',Schedina::withoutGlobalScopes()->find($a['schedina_id'])->relationship);
        $group=$this->dati();$group['gruppo']['tipo_alloggiato']='18';$group['gruppo']['componenti'][0]['tipo_alloggiato']='20';
        $b=$this->invia($group)->assertCreated();
        $this->assertSame('CAPOGRUPPO',Schedina::withoutGlobalScopes()->find($b['schedina_id'])->relationship);
        $this->assertSame('MEMBRO GRUPPO',Componenti::withoutGlobalScopes()->where('schedina_id',$b['schedina_id'])->sole()->relationship);
        $this->assertSame(0, \App\Models\Customers::withoutGlobalScopes()->count());
    }
    public function test_riconcilia_cataloghi_per_etichetta_senza_copiare_id_esterni(): void
    {
        \App\Models\GeoNazione::create(['nome'=>'Italia','cittadinanza'=>'Italiana','codice_iso2'=>'IT','is_italia'=>true]);
        \App\Models\TipoDocumento::create(['codice'=>'PASOR','descrizione'=>'PASSAPORTO ORDINARIO']);
        $data=$this->dati();$data['titolare']['dati']['cittadinanza']='ITALIANA';$data['titolare']['dati']['codice_fiscale']='TEST-SINTETICO-CF';
        $r=$this->invia($data)->assertCreated();$sheet=Schedina::withoutGlobalScopes()->find($r['schedina_id']);
        $this->assertSame('PASSAPORTO ORDINARIO',$sheet->or_doctype);$this->assertSame('Italiana',$sheet->oa_city_nac);$this->assertSame('Italia',$sheet->or_country);
        $this->assertStringContainsString('TEST-SINTETICO-CF',$sheet->observation);
    }
    public function test_reception_inattiva_o_servizio_scaduto_non_importano(): void
    {
        $this->reception->update(['attivo'=>false]);$this->invia($this->dati())->assertForbidden();
        $this->reception->update(['attivo'=>true]);$this->hotel->update(['scadenza_servizio'=>now()->subDay()->toDateString()]);
        $this->invia($this->dati())->assertForbidden();$this->assertDatabaseCount('schedina',0);
    }
    public function test_errore_componenti_annulla_anche_schedina_e_riferimento_importazione(): void
    {
        Componenti::creating(function () { throw new \RuntimeException('Guasto sintetico del salvataggio componente'); });
        $this->invia($this->dati())->assertStatus(500);
        $this->assertDatabaseCount('schedina',0);$this->assertDatabaseCount('componenti',0);$this->assertDatabaseCount('access_importazioni',0);
    }

    public function test_la_bozza_importata_si_apre_nel_circuito_esistente_della_reception(): void
    {
        $r=$this->invia($this->dati())->assertCreated();
        $this->actingAs($this->reception)->withSession(['struttura_corrente_id'=>$this->hotel->id]);
        $this->get('/schedine/'.$r['schedina_id'].'/modifica')->assertOk()->assertSee('TITOLARE')->assertSee('FITTIZIO')->assertSee('CI-TEST-001');
    }

    public function test_accetta_il_payload_generato_dal_circuito_access_node24(): void
    {
        // Fixture sintetica realmente prodotta da invito, documenti, approvazione, selfie e check-in Access.
        $data = json_decode(<<<'JSON'
{
  "versione": 1,
  "richiesta_id": "22222222-2222-4222-8222-222222222222",
  "codice_checkin": "CI-00000001",
  "confermato_il": "2026-11-25T01:04:48.848Z",
  "prenotazione": {
    "riferimento": "COLLAUDO-PERSISTENZA",
    "email_ospite": "ospite@example.test",
    "telefono_ospite": "+390000000001",
    "numero_ospiti": "1",
    "camere_occupate": "1",
    "letti_occupati": "1"
  },
  "titolare": {
    "tipo_documento": "passaporto",
    "dati": {
      "cognome": "FITTIZIO",
      "nome": "PERSISTENZA",
      "data_nascita": "01/01/1990",
      "luogo_nascita": "ROMA (RM)",
      "sesso": "M",
      "cittadinanza": "ITALIANA",
      "stato_emittente": "ITALIA",
      "numero_documento": "FITTIZIO-STORICO",
      "data_rilascio": "01/01/2020",
      "data_scadenza": "01/01/2099",
      "autorita_rilascio": "MINISTRO AFFARI ESTERI",
      "paese_residenza": "ITALIA",
      "comune_residenza": "ROMA",
      "indirizzo_residenza": "VIA FITTIZIA 1"
    }
  },
  "gruppo": {
    "tipo_alloggiato": "16",
    "data_arrivo": "2090-01-01",
    "data_partenza": "2090-01-02",
    "componenti": []
  }
}
JSON, true, flags: JSON_THROW_ON_ERROR);
        $r=$this->invia($data)->assertCreated();$sheet=Schedina::withoutGlobalScopes()->find($r['schedina_id']);
        $this->assertSame('FITTIZIO',$sheet->surname);$this->assertSame('PERSISTENZA',$sheet->name);
        $this->assertSame('MINISTRO AFFARI ESTERI',$sheet->or_published);$this->assertSame('1990-01-01',$sheet->oa_date_nac);
        $this->assertSame('2090-01-01',$sheet->arrive);$this->assertSame('2090-01-02',$sheet->departure);
        $this->assertEquals(1,$sheet->cant_people);$this->assertSame('bozza',$sheet->circuito);
        $this->assertNull($sheet->questura_sent_at);$this->assertNull($sheet->istat_sent_at);
    }

}
