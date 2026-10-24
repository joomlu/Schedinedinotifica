<?php

namespace Tests\Feature;

use App\Models\CestinoItem;
use App\Models\Customers;
use App\Models\Schedina;
use App\Models\WebCheckinComunicazione;
use App\Models\WebCheckinRichiesta;
use App\Services\CestinoService;
use App\Services\WebCheckinLink;
use App\Services\WebCheckinVersione;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebCheckinCircuitoTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture, \Tests\Support\WebCheckinPublicInput;

    private function scenario(): WebCheckinRichiesta
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->cataloghiSchedina($s->id);
        $values = array_replace($this->validSchedinaPayload(), ['relationship'=>'OSPITE SINGOLO','arrive'=>now()->addDays(5)->toDateString(),'departure'=>now()->addDays(7)->toDateString()]);
        $p = Schedina::forceCreate($values + ['struttura_id'=>$s->id,'circuito'=>'web']);
        $r = WebCheckinRichiesta::create(['struttura_id'=>$s->id,'schedina_id'=>$p->id,'codice'=>'WC'.Str::ulid(),'numero_prenotazione'=>'ESTERNA-SINTETICA','nome_referente'=>'Referente diverso dal titolare','email'=>'prova@example.invalid','arrivo'=>$p->arrive,'partenza'=>$p->departure,'quantita_persone'=>1,'numero_adulti'=>1,'numero_bambini'=>0,'note'=>'NOTA-RISERVATA','messaggio_ospite'=>'Benvenuto nel nostro hotel','token'=>Str::random(64),'stato'=>'compilato','compilato_at'=>now()]);
        app(WebCheckinLink::class)->issue($r);
        return $r->fresh();
    }

    private function converti(WebCheckinRichiesta $r, array $extra = [])
    {
        return $this->post(route('web_checkin.to_schedina',$r->id), $extra + ['web_versione_richiesta'=>app(WebCheckinVersione::class)->of($r)]);
    }

    public function test_elenco_dettaglio_storico_e_link_non_modificano_dati_nella_consultazione(): void
    {
        $r=$this->scenario();$before=$r->getAttributes();$sheet=$r->schedina->getAttributes();
        $this->get(route('schedina.web'))->assertOk()->assertSee('Modifica')->assertSee('ESTERNA-SINTETICA');
        $this->get(route('web_checkin.edit',$r->id))->assertOk()->assertSee('NOTA-RISERVATA')->assertSee('Referente diverso dal titolare');
        $this->get(route('web_checkin.storico'))->assertOk()->assertDontSee('ESTERNA-SINTETICA');
        $this->assertSame($before,$r->fresh()->getAttributes());$this->assertSame($sheet,$r->schedina->fresh()->getAttributes());
    }

    public function test_richiesta_con_riferimento_mancante_apribile_senza_inventare_persone(): void
    {
        $r=$this->scenario();$r->forceFill(['schedina_id'=>999999])->save();$count=Schedina::count();
        $this->get(route('web_checkin.edit',$r->id))->assertOk()->assertSee('NOTA-RISERVATA');
        $this->get(route('schedina.web'))->assertOk();$this->assertSame($count,Schedina::count());
    }

    public function test_conversione_esplicita_idempotente_crea_cliente_schedina_e_storico_senza_trasmissioni(): void
    {
        $r=$this->scenario();$id=$r->schedina_id;
        $this->converti($r)->assertSessionHasNoErrors()->assertRedirect(route('web_checkin.attivita',[$r->id,'ospiti']));
        $this->assertSame('convertito',$r->fresh()->stato);$this->assertSame('schedina',$r->schedina->fresh()->circuito);$this->assertSame($id,$r->fresh()->schedina_id);
        $this->assertDatabaseCount('schedina',1);$this->assertDatabaseCount('clienti',1);$customer=Customers::firstOrFail();$this->assertSame($customer->id,$r->schedina->fresh()->customer_id);
        $this->assertNotNull($r->fresh()->conversione_snapshot);$this->assertStringNotContainsString('Schedina',DB::table('web_checkin_richieste')->where('id',$r->id)->value('conversione_snapshot'));
        $this->converti($r)->assertSessionHasNoErrors()->assertRedirect(route('schedina.edit',$id));$this->assertDatabaseCount('schedina',1);$this->assertDatabaseCount('clienti',1);
        $this->get(route('web_checkin.storico'))->assertOk()->assertSee('ESTERNA-SINTETICA');$this->get(route('schedina.web'))->assertDontSee('ESTERNA-SINTETICA');
        $this->get('/checkin/'.$r->token)->assertOk()->assertSee('Grazie! Abbiamo ricevuto')->assertDontSee('NOTA-RISERVATA')->assertDontSee('<form',false);
        foreach(['questura_transmissions','istat_transmissions','tassa_exports']as$table)$this->assertDatabaseCount($table,0);
    }

    public function test_errore_conversione_rollback_integrale_e_nessun_cliente_orfano(): void
    {
        $r=$this->scenario();$r->schedina->forceFill(['room'=>null])->save();$before=$r->fresh()->getAttributes();$sheet=$r->schedina->fresh()->getAttributes();
        $this->converti($r)->assertSessionHasErrors('room');$this->assertSame($before,$r->fresh()->getAttributes());$this->assertSame($sheet,$r->schedina->fresh()->getAttributes());$this->assertDatabaseCount('clienti',0);
    }

    public function test_guasto_dopo_creazione_cliente_ripristina_intera_conversione(): void
    {
        $r=$this->scenario();$before=$r->getAttributes();$sheet=$r->schedina->getAttributes();
        $this->app->instance(\App\Services\WebCheckinCircuito::class,new class extends \App\Services\WebCheckinCircuito {
            public function finalizza(WebCheckinRichiesta $r, Schedina $s): void { parent::finalizza($r,$s); throw new \RuntimeException('Guasto sintetico dopo creazione cliente'); }
        });
        $this->converti($r)->assertStatus(500);$this->assertDatabaseCount('clienti',0);$this->assertSame($before,$r->fresh()->getAttributes());$this->assertSame($sheet,$r->schedina->fresh()->getAttributes());
    }

    public function test_revisione_e_bozza_non_archiviano_ne_cancellano_dati_omessi(): void
    {
        $r=$this->scenario();$payload=$r->schedina->getAttributes()+['save_mode'=>'review_web'];
        $this->putWeb(route('schedina.update',$r->schedina_id),$payload)->assertSessionHasNoErrors()->assertRedirect(route('web_checkin.attivita',[$r->id,'ospiti']));
        $this->assertSame('web',$r->schedina->fresh()->circuito);$this->assertNotSame('convertito',$r->fresh()->stato);$this->assertNull($r->fresh()->convertito_at);
        $payload=$r->schedina->fresh()->getAttributes()+['save_mode'=>'draft'];$this->putWeb(route('schedina.update',$r->schedina_id),$payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('bozza',$r->schedina->fresh()->circuito);$this->assertFalse($r->fresh()->processata());$this->get(route('web_checkin.storico'))->assertDontSee('ESTERNA-SINTETICA');
        $payload=$r->schedina->fresh()->getAttributes()+['save_mode'=>'review_web'];$this->putWeb(route('schedina.update',$r->schedina_id),$payload)->assertSessionHasNoErrors()->assertRedirect(route('web_checkin.attivita',[$r->id,'ospiti']));$this->assertSame('bozza',$r->schedina->fresh()->circuito);
    }

    public function test_cestino_conserva_schedina_clienti_storico_comunicazioni_e_restore_non_duplica(): void
    {
        $r=$this->scenario();$this->converti($r)->assertSessionHasNoErrors();$r->refresh();$id=$r->schedina_id;$old=$r->token;
        $c=WebCheckinComunicazione::create(['struttura_id'=>$r->struttura_id,'richiesta_id'=>$r->id,'utente_id'=>auth()->id(),'idempotenza'=>Str::uuid(),'canale'=>'testo','stato'=>'preparato','contenuto'=>['corpo'=>'Sintetico']]);
        $this->delete(route('web_checkin.destroy',$r->id))->assertRedirect();$this->assertDatabaseHas('schedina',['id'=>$id]);$this->assertDatabaseCount('clienti',1);$this->get('/checkin/'.$old)->assertNotFound();
        $item=CestinoItem::where('entity_class',WebCheckinRichiesta::class)->firstOrFail();$this->delete(route('cestino.destroy',$item->id))->assertForbidden();
        $restored=app(CestinoService::class)->restoreItem($item);$this->assertSame($id,$restored->schedina_id);$this->assertDatabaseCount('schedina',1);$this->assertNotSame($old,$restored->token);$this->assertNotNull($restored->link_revoked_at);
        $this->assertSame($restored->id,$c->fresh()->richiesta_id);$this->assertNotNull($restored->conversione_snapshot);$this->get('/checkin/'.$restored->token)->assertNotFound();
    }

    public function test_ripristino_conserva_date_e_istanti_anche_al_cambio_ora(): void
    {
        $r = $this->scenario();
        $r->forceFill([
            'arrivo' => '2026-10-25', 'partenza' => '2026-10-27',
            'ultimo_accesso_at' => '2026-10-24 23:45:00',
            'compilato_at' => '2026-10-25 03:15:00',
            'convertito_at' => '2026-10-25 03:30:00',
        ])->save();
        $r->refresh();
        $dates = [$r->arrivo->toDateString(), $r->partenza->toDateString()];
        $instants = [$r->ultimo_accesso_at->timestamp, $r->compilato_at->timestamp, $r->convertito_at->timestamp];
        $this->delete(route('web_checkin.destroy', $r->id))->assertRedirect();
        $item = CestinoItem::where('entity_class', WebCheckinRichiesta::class)->firstOrFail();
        $restored = app(CestinoService::class)->restoreItem($item)->fresh();
        $this->assertSame($dates, [$restored->arrivo->toDateString(), $restored->partenza->toDateString()]);
        $this->assertSame($instants, [$restored->ultimo_accesso_at->timestamp, $restored->compilato_at->timestamp, $restored->convertito_at->timestamp]);
        $this->assertSame($r->schedina_id, $restored->schedina_id);
        $this->assertNotSame($r->token, $restored->token);
        $this->assertNotNull($restored->link_revoked_at);
        $this->get('/checkin/'.$restored->token)->assertNotFound();
    }

    public function test_ricreazione_stessa_prenotazione_link_indipendente_e_note_non_pubbliche(): void
    {
        $r=$this->scenario();$old=$r->token;$this->delete(route('web_checkin.destroy',$r->id))->assertRedirect();
        $this->post(route('web_checkin.store'),['numero_prenotazione'=>$r->numero_prenotazione,'nome_referente'=>'Altro referente','email'=>'secondo@example.invalid','arrivo'=>$r->arrivo->toDateString(),'partenza'=>$r->partenza->toDateString(),'numero_adulti'=>1,'numero_bambini'=>0,'quantita_persone'=>1,'note'=>'ALTRA-NOTA-RISERVATA','messaggio_ospite'=>'Messaggio pubblico'])->assertSessionHasNoErrors()->assertRedirect();
        $new=WebCheckinRichiesta::firstOrFail();$this->assertNotSame($r->id,$new->id);$this->assertNotSame($old,$new->token);$this->get('/checkin/'.$old)->assertNotFound();$this->get('/checkin/'.$new->token)->assertOk()->assertSee('Messaggio pubblico')->assertDontSee('ALTRA-NOTA-RISERVATA');
    }

    public function test_struttura_estranea_non_legge_modifica_converte_o_elimina(): void
    {
        $r=$this->scenario();$other=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$other->id));$this->withSession(['struttura_corrente_id'=>$other->id]);
        $this->get(route('web_checkin.edit',$r->id))->assertNotFound();$this->putJson(route('web_checkin.update',$r->id),[])->assertNotFound();$this->postJson(route('web_checkin.to_schedina',$r->id),[])->assertNotFound();$this->delete(route('web_checkin.destroy',$r->id))->assertNotFound();
    }

    public function test_eta_calcolata_all_arrivo_e_ripartizione_non_coerente_rifiutata(): void
    {
        $r=$this->scenario();$birth=$r->arrivo->copy()->subYears(18)->toDateString();$r->schedina->forceFill(['oa_date_nac'=>$birth])->save();$r->refresh();$input=new \Illuminate\Http\Request($r->schedina->getAttributes());
        $counts=app(\App\Services\WebCheckinCircuito::class)->verificaPersone($r,$input);$this->assertSame(['numero_adulti'=>1,'numero_bambini'=>0],$counts);
        $r->schedina->forceFill(['oa_date_nac'=>$r->arrivo->copy()->subYears(18)->addDay()->toDateString()])->save();$this->converti($r)->assertSessionHasErrors('cant_people');$this->assertSame('compilato',$r->fresh()->stato);
    }

    public function test_famiglia_con_adulti_bambino_completa_converte_e_conserva_componenti(): void
    {
        $r=$this->scenario();$r->forceFill(['quantita_persone'=>3,'numero_adulti'=>2,'numero_bambini'=>1])->save();$r->schedina->forceFill(['cant_people'=>3,'relationship'=>'CAPO FAMIGLIA'])->save();
        $row=['exent'=>'NO','surname'=>'Sintetico','sex'=>'F','relationship'=>'FAMILIARE','country_nac'=>'ITALIA','regione_nac'=>'Lazio','province_nac'=>'RM','comune_nac'=>'Roma','city_nac'=>'ITALIANA','country'=>'ITALIA','regione'=>'Lazio','province'=>'RM','city'=>'Roma','typeaway'=>'Via','address'=>'Via Test','number'=>'1'];
        $data=array_replace($r->schedina->fresh()->getAttributes(),['save_mode'=>'web_complete','componenti'=>[array_replace($row,['name'=>'Adulto','date_nac'=>'1985-01-01']),array_replace($row,['name'=>'Bambino','date_nac'=>'2017-01-01'])]]);
        $this->postWeb('/checkin/'.$r->token,$data)->assertSessionHasNoErrors()->assertOk();$this->assertNotNull($r->fresh()->compilato_at);$this->assertSame('web',$r->schedina->fresh()->circuito);$this->assertDatabaseCount('componenti',2);
        $this->converti($r->fresh())->assertSessionHasNoErrors()->assertRedirect();$this->assertDatabaseCount('componenti',2);$this->assertCount(2,$r->fresh()->conversione_snapshot['componenti']);$this->assertSame([$r->schedina->fresh()->customer_id],\App\Models\Componenti::where('schedina_id',$r->schedina_id)->pluck('customer_id')->unique()->values()->all());$this->assertSame('convertito',$r->fresh()->stato);
    }

    public function test_finalizzazione_incompleta_quantita_incoerenti_e_date_invalide_non_salvano(): void
    {
        $r=$this->scenario();$before=$r->getAttributes();
        $this->postJsonWeb('/checkin/'.$r->token,['save_mode'=>'web_complete','name'=>'Incompleto','cant_people'=>1])->assertUnprocessable();$this->assertSame($before,$r->fresh()->getAttributes());
        $data=['numero_prenotazione'=>'NUOVA','nome_referente'=>'Sintetico','email'=>'prova@example.invalid','arrivo'=>'2026-11-10','partenza'=>'2026-11-09','numero_adulti'=>1,'numero_bambini'=>1,'quantita_persone'=>2];
        $this->postJson(route('web_checkin.store'),$data)->assertUnprocessable()->assertJsonValidationErrors('partenza');$data['partenza']='2026-11-12';$data['quantita_persone']=3;
        $this->postJson(route('web_checkin.store'),$data)->assertUnprocessable()->assertJsonValidationErrors('quantita_persone');$this->assertDatabaseCount('web_checkin_richieste',1);
    }

    public function test_snapshot_storico_non_cambia_con_modifiche_successive_alla_schedina(): void
    {
        $r=$this->scenario();$this->converti($r)->assertSessionHasNoErrors();$original=$r->fresh()->conversione_snapshot;$r->schedina->forceFill(['name'=>'MODIFICATO-DOPO'])->save();
        $this->get(route('web_checkin.edit',$r->id))->assertOk()->assertDontSee('MODIFICATO-DOPO');$this->assertSame($original,$r->fresh()->conversione_snapshot);
    }
    public function test_cliente_esistente_riutilizzato_senza_sovrascrivere_contatti(): void
    {
        $r=$this->scenario();$this->converti($r)->assertSessionHasNoErrors();$customer=Customers::firstOrFail();$customer->forceFill(['email'=>'conservare@example.invalid'])->save();$before=$customer->getAttributes();
        $sheet=$r->schedina->fresh()->replicate();$sheet->forceFill(['circuito'=>'web','customer_id'=>null,'scheda'=>null])->save();$new=$r->fresh()->replicate();$new->forceFill(['schedina_id'=>$sheet->id,'codice'=>'WC'.Str::ulid(),'token'=>Str::random(64),'short_token'=>null,'stato'=>'compilato','convertito_at'=>null,'conversione_snapshot'=>null])->save();
        $this->converti($new)->assertSessionHasNoErrors();$this->assertDatabaseCount('clienti',1);$this->assertSame($customer->id,$sheet->fresh()->customer_id);$this->assertSame($before,$customer->fresh()->getAttributes());
    }

    public function test_clienti_omonimi_ambigui_richiedono_selezione_e_non_vengono_uniti(): void
    {
        $r=$this->scenario();$this->converti($r)->assertSessionHasNoErrors();$customer=Customers::firstOrFail();$copy=$customer->replicate();$copy->forceFill(['numero_cliente'=>99999,'email'=>'altro@example.invalid'])->save();
        $sheet=$r->schedina->fresh()->replicate();$sheet->forceFill(['circuito'=>'web','customer_id'=>null,'scheda'=>null])->save();$new=$r->fresh()->replicate();$new->forceFill(['schedina_id'=>$sheet->id,'codice'=>'WC'.Str::ulid(),'token'=>Str::random(64),'short_token'=>null,'stato'=>'compilato','convertito_at'=>null,'conversione_snapshot'=>null])->save();
        $this->converti($new)->assertSessionHasErrors('customer_id');$this->assertDatabaseCount('clienti',2);$this->assertSame('web',$sheet->fresh()->circuito);$this->assertNull($new->fresh()->conversione_snapshot);
    }

    public function test_documento_gia_presente_con_identita_diversa_non_crea_duplicato(): void
    {
        $r=$this->scenario();$this->converti($r)->assertSessionHasNoErrors();$sheet=$r->schedina->fresh()->replicate();$sheet->forceFill(['circuito'=>'web','customer_id'=>null,'scheda'=>null,'name'=>'Nome da verificare'])->save();$new=$r->fresh()->replicate();$new->forceFill(['schedina_id'=>$sheet->id,'codice'=>'WC'.Str::ulid(),'token'=>Str::random(64),'short_token'=>null,'stato'=>'compilato','convertito_at'=>null,'conversione_snapshot'=>null])->save();
        $this->converti($new)->assertSessionHasErrors('customer_id');$this->assertDatabaseCount('clienti',1);$this->assertSame('web',$sheet->fresh()->circuito);
    }

    public function test_cestino_non_legge_scheda_estranea_e_restore_non_collide_con_wc1_globale(): void
    {
        $r=$this->scenario();$other=$this->structureFor(null);$foreign=$r->schedina->replicate();$foreign->forceFill(['struttura_id'=>$other->id,'name'=>'SEGRETO-ALTRA-STRUTTURA'])->save();
        $legacy=$r->replicate();$legacy->forceFill(['struttura_id'=>$other->id,'schedina_id'=>$foreign->id,'codice'=>'WC1','token'=>Str::random(64),'short_token'=>null])->save();
        $r->forceFill(['schedina_id'=>$foreign->id])->save();
        $this->get(route('web_checkin.edit',$r->id))->assertNotFound()->assertDontSee('SEGRETO-ALTRA-STRUTTURA');
        $item=app(CestinoService::class)->archiveModel($r);$r->delete();
        $restored=app(CestinoService::class)->restoreItem($item);$this->assertNull($restored->schedina_id);$this->assertMatchesRegularExpression('/^WC[0-9A-Z]{26}$/',$restored->codice);$this->assertSame('SEGRETO-ALTRA-STRUTTURA',$foreign->fresh()->name);
        $this->assertDatabaseHas('web_checkin_richieste',['id'=>$legacy->id,'codice'=>'WC1']);
    }

    public function test_revisione_non_crea_scheda_web_priva_di_richiesta(): void
    {
        $r=$this->scenario();$count=Schedina::count();$payload=$this->validSchedinaPayload()+['save_mode'=>'review_web','schedina_struttura_id'=>$r->struttura_id];
        $this->post(route('schedina.store'),$payload)->assertStatus(422);$this->assertSame($count,Schedina::count());
    }

    private function datiRichiestaPrecedente(WebCheckinRichiesta $r, string $version): array
    {
        return ['numero_prenotazione'=>'PRENOTAZIONE-RECUPERATA','nome_referente'=>'Referente indicato manualmente','email'=>'referente-nuovo@example.invalid','arrivo'=>$r->arrivo->toDateString(),'partenza'=>$r->partenza->toDateString(),'numero_adulti'=>2,'numero_bambini'=>0,'quantita_persone'=>2,'scheda_precedente'=>$r->schedina_id,'versione_scheda_precedente'=>$version];
    }

    public function test_scheda_precedente_consultabile_e_richiesta_esplicita_conserva_stesso_id_e_componenti(): void
    {
        $r=$this->scenario();$sheet=$r->schedina;$sheet->forceFill(['cant_people'=>2,'relationship'=>'CAPO FAMIGLIA'])->save();$r->forceFill(['quantita_persone'=>2])->save();$person=\App\Models\Componenti::forceCreate(['struttura_id'=>$r->struttura_id,'schedina_id'=>$sheet->id,'name'=>'Accompagnatore precedente','surname'=>'Sintetico','relationship'=>'FAMILIARE','date_nac'=>'1990-01-01']);$before=$person->fresh()->getAttributes();$name=$sheet->name;$email=$sheet->customer_email;$r->delete();
        $this->get(route('schedina.web'))->assertOk()->assertSee('Schede Web precedenti')->assertSee($name);$this->assertDatabaseCount('web_checkin_richieste',0);
        $this->putJson(route('schedina.update',$sheet->id),$sheet->getAttributes()+['save_mode'=>'full'])->assertUnprocessable()->assertJsonValidationErrors('save_mode');
        $form=$this->get(route('web_checkin.create',['scheda_precedente'=>$sheet->id]))->assertOk()->assertSee('Accompagnatore precedente');$data=$this->datiRichiestaPrecedente($r,$form->viewData('versioneSchedaPrecedente'));
        $this->post(route('web_checkin.store'),$data)->assertSessionHasNoErrors()->assertRedirect();$new=WebCheckinRichiesta::firstOrFail();$this->assertSame($sheet->id,$new->schedina_id);$this->assertDatabaseCount('schedina',1);$this->assertSame($before,$person->fresh()->getAttributes());$this->assertSame($name,$sheet->fresh()->name);$this->assertSame($email,$sheet->fresh()->customer_email);$this->assertSame('Referente indicato manualmente',$new->nome_referente);
        $this->post(route('web_checkin.store'),$data)->assertNotFound();$this->assertDatabaseCount('web_checkin_richieste',1);
    }

    public function test_scheda_precedente_versione_vecchia_e_struttura_estranea_non_collegano_dati(): void
    {
        $r=$this->scenario();$sheet=$r->schedina;$r->delete();$form=$this->get(route('web_checkin.create',['scheda_precedente'=>$sheet->id]))->assertOk();$data=$this->datiRichiestaPrecedente($r,$form->viewData('versioneSchedaPrecedente'));$sheet->forceFill(['name'=>'Aggiornato da altro operatore'])->save();
        $this->postJson(route('web_checkin.store'),$data)->assertStatus(409);$this->assertDatabaseCount('web_checkin_richieste',0);$other=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$other->id))->withSession(['struttura_corrente_id'=>$other->id]);
        $this->get(route('web_checkin.create',['scheda_precedente'=>$sheet->id]))->assertNotFound();$this->postJson(route('web_checkin.store'),$data)->assertNotFound();$this->assertSame('Aggiornato da altro operatore',$sheet->fresh()->name);
    }

    public function test_richiesta_nel_cestino_non_riappare_come_scheda_precedente(): void
    {
        $r=$this->scenario();$r->schedina->forceFill(['name'=>'Precedente nel Cestino'])->save();$this->delete(route('web_checkin.destroy',$r->id))->assertRedirect();
        $this->get(route('schedina.web'))->assertOk()->assertDontSee('Precedente nel Cestino');$this->get(route('web_checkin.create',['scheda_precedente'=>$r->schedina_id]))->assertNotFound();$this->assertDatabaseHas('schedina',['id'=>$r->schedina_id]);
    }

    public function test_scheda_web_precedente_nel_cestino_non_eliminabile_definitivamente(): void
    {
        $r=$this->scenario();$sheet=$r->schedina;$r->delete();$item=app(CestinoService::class)->archiveModel($sheet);$sheet->delete();
        $this->delete(route('cestino.destroy',$item->id))->assertForbidden();$this->assertDatabaseHas('cestino_items',['id'=>$item->id]);$restored=app(CestinoService::class)->restoreItem($item);$this->assertSame($sheet->name,$restored->name);$this->assertSame('web',$restored->circuito);$this->assertDatabaseCount('schedina',1);
    }

    public function test_note_e_classificazioni_riservate_non_compaiono_nel_modulo_pubblico(): void
    {
        $r=$this->scenario();\App\Models\Gruppo::forceCreate(['struttura_id'=>$r->struttura_id,'nome'=>'GRUPPO-RISERVATO-SINTETICO','livello'=>1,'tipo'=>\App\Models\Gruppo::tipoFromLivello(1)]);
        $r->schedina->forceFill(['observation'=>'SEGRETO-OPERATIVO','customer_observation'=>'SEGRETO-CLIENTE','customer_anag_observation'=>'SEGRETO-ANAGRAFICA','customer_group'=>'GRUPPO-RISERVATO-SINTETICO'])->save();
        $public=$this->get('/checkin/'.$r->token)->assertOk();foreach(['SEGRETO-OPERATIVO','SEGRETO-CLIENTE','SEGRETO-ANAGRAFICA','GRUPPO-RISERVATO-SINTETICO'] as $secret)$public->assertDontSee($secret);
        $this->get(route('schedina.edit',$r->schedina_id))->assertOk()->assertSee('SEGRETO-OPERATIVO')->assertSee('SEGRETO-CLIENTE');
    }

    public function test_post_ospite_non_modifica_note_classificazioni_camere_o_statistiche_interne(): void
    {
        $r=$this->scenario();$sheet=$r->schedina;$sheet->forceFill(['observation'=>'SEGRETO-OPERATIVO','customer_observation'=>'SEGRETO-CLIENTE','customer_anag_observation'=>'SEGRETO-ANAGRAFICA'])->save();$before=$sheet->fresh()->only(['observation','customer_observation','customer_anag_observation','customer_group','customer_type_housed','room','beds','istat_non_turista']);
        $result=$this->postWeb('/checkin/'.$r->token,['save_mode'=>'web','name'=>'Nome inserito dall’ospite','cant_people'=>1,'observation'=>'SOVRASCRITTO','customer_observation'=>'SOVRASCRITTO','customer_anag_observation'=>'SOVRASCRITTO','customer_group'=>'GRUPPO-ESTRANEO','customer_type_housed'=>'Agenzia','room'=>99,'beds'=>99,'camere'=>[],'istat_non_turista'=>1])->assertSessionHasNoErrors()->assertOk();
        $this->assertSame($before,$sheet->fresh()->only(array_keys($before)));$this->assertSame('Nome inserito dall’ospite',$sheet->fresh()->name);foreach(['SEGRETO-OPERATIVO','SEGRETO-CLIENTE','SEGRETO-ANAGRAFICA']as$secret)$result->assertDontSee($secret);
    }

    public function test_conversione_da_revisione_rivalida_componenti_persistiti_anche_se_omessi_dal_post(): void
    {
        $r=$this->scenario();$person=\App\Models\Componenti::forceCreate(['struttura_id'=>$r->struttura_id,'schedina_id'=>$r->schedina_id,'name'=>'Persona da conservare','surname'=>'Sintetico']);$before=$person->fresh()->getAttributes();
        $this->putWeb(route('schedina.update',$r->schedina_id),$r->schedina->getAttributes()+['save_mode'=>'full'])->assertSessionHasErrors();
        $this->assertSame('web',$r->schedina->fresh()->circuito);$this->assertSame('compilato',$r->fresh()->stato);$this->assertSame($before,$person->fresh()->getAttributes());$this->assertDatabaseCount('clienti',0);
    }

}
