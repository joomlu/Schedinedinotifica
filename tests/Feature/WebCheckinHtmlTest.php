<?php

namespace Tests\Feature;

use App\Mail\WebCheckinInvito;
use App\Models\Schedina;
use App\Models\WebCheckinComunicazione;
use App\Models\WebCheckinModello;
use App\Models\WebCheckinRichiesta;
use App\Services\WebCheckinIdentita;
use App\Services\WebCheckinLink;
use App\Services\WebCheckinMessaggio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebCheckinHtmlTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\WebCheckinPublicInput;
    use \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    private function logo(int $id, int $red = 30): string
    {
        $chunk = fn ($type, $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $png = "\x89PNG\r\n\x1a\n".$chunk('IHDR', pack('NNCCCCC', 2, 1, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress("\x00".str_repeat(pack('CCC', $red, 90, 140), 2))).$chunk('IEND', '');
        $path = 'uploads/loghi/hotel-sintetico-'.$id.'.png';
        Storage::disk('public')->put($path, $png);
        return 'storage/'.$path;
    }

    private function scenario(int $people = 1, bool $logo = true): WebCheckinRichiesta
    {
        $s = $this->structureFor(null);
        $s->forceFill(['nome_struttura' => 'Hotel sintetico '.$s->id, 'logo' => $logo ? $this->logo($s->id) : null, 'web_checkin_colore' => '#345678'])->save();
        $this->actingAs($this->actor('struttura_user', null, $s->id))->withSession(['struttura_corrente_id' => $s->id]);
        $this->cataloghiSchedina($s->id);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'cant_people' => $people,
            'arrive' => now()->addDays(10)->toDateString(), 'departure' => now()->addDays(12)->toDateString()]);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'HTML'.$p->id,
            'numero_prenotazione' => 'RISERVA-SINTETICA-'.$p->id, 'email' => 'controllato@example.invalid', 'whatsapp' => '+390000000000',
            'nome_referente' => 'Referente sintetico', 'arrivo' => $p->arrive, 'partenza' => $p->departure,
            'quantita_persone' => $people, 'note' => 'NOTA-PRIVATA-DA-NON-ESPORRE', 'token' => Str::random(64)]);
        app(WebCheckinLink::class)->issue($r);
        return $r;
    }

    private function prepara(WebCheckinRichiesta $r, array $override = []): WebCheckinComunicazione
    {
        $data = array_replace(['oggetto' => '{{struttura}} · Il tuo check-in online', 'corpo' => 'Apri {{link}}',
            'canale' => 'email', 'idempotenza' => (string) Str::uuid(), 'impostazioni' => WebCheckinMessaggio::layoutIniziale()], $override);
        $this->postJsonWeb(route('web_checkin.prepara', $r->id), $data)->assertRedirect();
        return WebCheckinComunicazione::where('richiesta_id', $r->id)->latest('id')->firstOrFail();
    }

    /** Trasporto catturato in memoria: nessuna connessione SMTP esterna nel collaudo. */
    private function trasportoSimulato()
    {
        config(['web_checkin.email_abilitata' => true, 'web_checkin.mailer' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.invalid',
            'mail.from.address' => 'reception@example.invalid']);
        $mailer = app('mail.manager')->mailer('array');
        $mailer->alwaysFrom('reception@example.invalid', 'Reception sintetica');
        $factory = \Mockery::mock(\Illuminate\Contracts\Mail\Factory::class);
        $factory->shouldReceive('mailer')->with('smtp')->andReturn($mailer);
        Mail::swap($factory);
        return $mailer->getSymfonyTransport();
    }

    public function test_mime_reale_contiene_html_testo_logo_cid_pulsante_diretto_e_dati_corretti(): void
    {
        $r = $this->scenario(); $c = $this->prepara($r); $transport = $this->trasportoSimulato();
        $this->postWeb(route('web_checkin.comunicazione.invia', [$r->id, $c->id]), ['conferma_destinatario' => '1'])->assertRedirect();
        $this->assertCount(1, $transport->messages());
        $email = $transport->messages()->first()->getOriginalMessage();
        $this->assertStringContainsString('text/html', $email->toString());
        $this->assertStringContainsString('text/plain', $email->toString());
        $this->assertStringContainsString('cid:', $email->getHtmlBody());
        $this->assertStringContainsString('Completa il web check-in', $email->getHtmlBody());
        $this->assertStringContainsString(route('web_checkin.public.show', $r->token), $email->getHtmlBody());
        $this->assertStringContainsString($r->numero_prenotazione, $email->getHtmlBody());
        $this->assertStringContainsString('Inserisci i tuoi dati.', $email->getHtmlBody());
        $this->assertStringNotContainsString('quelli degli altri ospiti', $email->getHtmlBody());
        $this->assertStringContainsString('Puoi salvare e continuare più tardi.', $email->getTextBody());
        $this->assertStringNotContainsString('<table', $email->getTextBody());
        $this->assertStringNotContainsString('NOTA-PRIVATA', $email->toString());
        $this->assertCount(1, $email->getAttachments());
        $this->assertSame('accettato', $c->fresh()->stato);
        $this->assertNotNull($c->fresh()->accettato_at);
        $this->get(route('web_checkin.comunicazione', [$r->id, $c->id]))->assertOk()->assertSeeText('consegna non confermata');
    }

    public function test_due_invi_reiterati_producono_un_solo_messaggio(): void
    {
        $r = $this->scenario(); $c = $this->prepara($r); $transport = $this->trasportoSimulato();
        $url = route('web_checkin.comunicazione.invia', [$r->id, $c->id]);
        for ($i=0; $i<3; $i++) $this->postWeb($url, ['conferma_destinatario' => '1'])->assertRedirect();
        $this->assertCount(1, $transport->messages());
        $this->assertSame(1, WebCheckinComunicazione::count());
    }

    public function test_errore_trasporto_esito_incerto_non_ritenta_e_non_scrive_dati_sensibili_nei_log(): void
    {
        $r = $this->scenario(); $c = $this->prepara($r); $this->trasportoSimulato();
        $pending = \Mockery::mock(); $pending->shouldReceive('send')->once()->andThrow(new \RuntimeException('SEGRETO-SMTP-'.$r->token));
        $mailer = \Mockery::mock(); $mailer->shouldReceive('to')->once()->andReturn($pending);
        $factory = \Mockery::mock(\Illuminate\Contracts\Mail\Factory::class); $factory->shouldReceive('mailer')->with('smtp')->once()->andReturn($mailer); Mail::swap($factory);
        $url = route('web_checkin.comunicazione.invia', [$r->id, $c->id]);
        $this->postWeb($url, ['conferma_destinatario'=>'1'])->assertRedirect();
        $this->postWeb($url, ['conferma_destinatario'=>'1'])->assertRedirect();
        $this->assertSame('esito_incerto', $c->fresh()->stato); $this->assertNull($c->fresh()->accettato_at);
        $this->assertSame('esito_non_verificabile', $c->fresh()->errore_codice);
        foreach (glob(storage_path('logs/*.log')) as $path) $this->assertStringNotContainsString('SEGRETO-SMTP', file_get_contents($path));
    }

    public function test_configurazione_assente_o_simulata_non_dichiara_invio(): void
    {
        $r=$this->scenario();$c=$this->prepara($r);
        foreach (['array','log','failover'] as $transport) {
            config(['web_checkin.email_abilitata'=>true, 'web_checkin.mailer'=>'smtp', 'mail.mailers.smtp.transport'=>$transport]);
            $this->postJsonWeb(route('web_checkin.comunicazione.invia',[$r->id,$c->id]),['conferma_destinatario'=>'1'])->assertUnprocessable();
        }
        $this->assertNull($c->fresh()->tentativo_at); $this->assertSame('preparato',$c->fresh()->stato);
    }

    public function test_logo_assente_blocca_solo_invio_e_non_accesso_richiesta_o_modulo(): void
    {
        $r=$this->scenario(1,false);$c=$this->prepara($r);$transport=$this->trasportoSimulato();
        $this->get(route('web_checkin.comunicazioni',$r->id))->assertOk()->assertSeeText('Manca un logo hotel valido')->assertSee(route('struttura.edit'),false);
        $this->postJsonWeb(route('web_checkin.comunicazione.invia',[$r->id,$c->id]),['conferma_destinatario'=>'1'])->assertUnprocessable();
        $this->get('/checkin/'.$r->token)->assertOk()->assertSee('schedina-form');
        $this->assertCount(0,$transport->messages());$this->assertNull($c->fresh()->tentativo_at);
    }

    public function test_dati_logo_colore_e_link_cambiati_richiedono_nuova_preparazione(): void
    {
        $r=$this->scenario();$c=$this->prepara($r);$transport=$this->trasportoSimulato();
        $this->logo($r->struttura_id,190);
        $this->postJsonWeb(route('web_checkin.comunicazione.invia',[$r->id,$c->id]),['conferma_destinatario'=>'1'])->assertUnprocessable();
        $this->assertCount(0,$transport->messages());$this->assertNull($c->fresh()->tentativo_at);
    }

    public function test_personalizzazione_escapata_e_pulsante_sempre_valido_senza_variabili_o_header_rotti(): void
    {
        $r=$this->scenario();$layout=WebCheckinMessaggio::layoutIniziale();$layout['saluto']='<script>prova</script> {{referente}}';$layout['pulsante']='Entra ora';
        $c=$this->prepara($r,['impostazioni'=>$layout]);
        $response=$this->get(route('web_checkin.comunicazione.anteprima',[$r->id,$c->id]));
        $response->assertOk()->assertSee('&lt;script&gt;prova&lt;/script&gt;',false)->assertDontSee('<script>',false)->assertSee('Entra ora');
        $this->assertStringContainsString(route('web_checkin.public.show',$r->token),$response->getContent());
        foreach (['{{ignota}}','',str_repeat('x',81)] as $label) {
            $layout['pulsante']=$label;
            $this->postJsonWeb(route('web_checkin.prepara',$r->id),['oggetto'=>'Invito','corpo'=>'{{link}}','canale'=>'email','idempotenza'=>(string)Str::uuid(),'impostazioni'=>$layout])->assertUnprocessable();
        }
        $this->postJsonWeb(route('web_checkin.prepara',$r->id),['oggetto'=>"Invito\r\nBcc: altro@example.invalid",'corpo'=>'{{link}}','canale'=>'email','idempotenza'=>(string)Str::uuid()])->assertUnprocessable();
    }

    public function test_eliminazione_modelli_non_ricrea_e_ripristino_esplicito_conserva_personalizzazione(): void
    {
        $r=$this->scenario();$url=route('web_checkin.comunicazioni',$r->id);$this->get($url)->assertOk();
        $m=WebCheckinModello::firstOrFail();$m->update(['nome'=>'Personalizzato prima della rimozione']);
        $this->get($url)->assertOk()->assertSee('action="'.route('web_checkin.modello.elimina',[$r->id,$m->id],false).'"',false)->assertSee('data-confirm-button="Sì, elimina"',false);
        $this->postWeb(route('web_checkin.modello.elimina',[$r->id,$m->id]),['versione'=>99])->assertStatus(409);
        $this->postWeb(route('web_checkin.modello.elimina',[$r->id,$m->id]),['versione'=>$m->versione])->assertRedirect();
        $this->get($url)->assertOk();$this->get($url)->assertOk();$this->assertSame(2,WebCheckinModello::count());$this->assertTrue($m->fresh()->trashed());
        $this->postWeb(route('web_checkin.modelli.ripristina',$r->id))->assertRedirect();
        $this->assertSame(3,WebCheckinModello::count());$this->assertSame('Personalizzato prima della rimozione',$m->fresh()->nome);$this->assertGreaterThan(1,$m->fresh()->versione);
    }

    public function test_cronologia_singola_e_multipla_conserva_idempotenza_richiesta_e_ospiti(): void
    {
        $r=$this->scenario();$idempotenza=(string)Str::uuid();$c=$this->prepara($r,['idempotenza'=>$idempotenza]);$d=$this->prepara($r);
        $before=$r->fresh()->getAttributes();$sheet=$r->schedina->getAttributes();
        $url=route('web_checkin.cronologia.rimuovi',$r->id);
        $this->postWeb($url,['comunicazioni'=>[$c->id]])->assertRedirect();
        $this->get(route('web_checkin.comunicazione',[$r->id,$c->id]))->assertNotFound();
        $this->prepara($r,['idempotenza'=>$idempotenza]);$this->assertSame(2,WebCheckinComunicazione::count());
        $this->assertNotNull($c->fresh()->nascosta_at);
        $this->postWeb($url,['comunicazioni'=>[$c->id,$d->id]])->assertRedirect();
        $this->assertSame(0,WebCheckinComunicazione::whereNull('nascosta_at')->count());
        $this->assertSame($before,$r->fresh()->getAttributes());$this->assertSame($sheet,$r->schedina->fresh()->getAttributes());
    }

    public function test_anteprima_reception_non_registra_visita_e_alias_ospite_la_registra(): void
    {
        $r=$this->scenario();$before=$r->fresh()->getAttributes();
        $this->get(route('web_checkin.anteprima_modulo',$r->id))->assertOk()->assertSeeText('Anteprima riservata alla reception')->assertSee('<fieldset disabled',false);
        $this->assertSame($before,$r->fresh()->getAttributes());
        $this->app['auth']->forgetGuards();$this->get('/checkin/'.$r->token)->assertOk()->assertDontSeeText('Configura prima la tassa')->assertDontSeeText('Configurazione fiscale');$this->assertNotNull($r->fresh()->ultimo_accesso_at);
    }

    public function test_html_e_logo_restano_nel_tenant_corretto_con_sessione_estranea(): void
    {
        $a=$this->scenario(2);$c=$this->prepara($a);$actorA=auth()->user();$this->get(route('web_checkin.comunicazioni',$a->id))->assertOk();$modelA=WebCheckinModello::where('struttura_id',$a->struttura_id)->firstOrFail();$b=$this->scenario();$cb=$this->prepara($b);
        $this->get(route('web_checkin.comunicazione.anteprima',[$a->id,$c->id]))->assertNotFound();
        $this->get(route('web_checkin.anteprima_modulo',$a->id))->assertNotFound();
        $this->postJsonWeb(route('web_checkin.comunicazione.invia',[$b->id,$c->id]),['conferma_destinatario'=>'1'])->assertNotFound();
        $this->postJsonWeb(route('web_checkin.modello.elimina',[$b->id,$modelA->id]),['versione'=>$modelA->versione])->assertNotFound();
        $this->postJsonWeb(route('web_checkin.cronologia.rimuovi',$b->id),['comunicazioni'=>[$c->id]])->assertNotFound();
        $this->get('/checkin/'.$a->token)->assertOk()->assertSee('Hotel sintetico '.$a->struttura_id)->assertDontSee('Hotel sintetico '.$b->struttura_id);
        $content=$c->contenuto;$html=view('emails.web-checkin',['invito'=>$content,'logo_src'=>$content['identita']['logo_url']])->render();
        $this->assertStringContainsString('quelli degli altri ospiti',$html);$this->assertStringNotContainsString($b->struttura->logo,$html);
        $this->actingAs($actorA)->withSession(['struttura_corrente_id'=>$a->struttura_id]);
        $this->postJsonWeb(route('web_checkin.cronologia.rimuovi',$a->id),['comunicazioni'=>[$c->id,$cb->id]])->assertNotFound();
        $this->assertNull($c->fresh()->nascosta_at);$this->assertNull($cb->fresh()->nascosta_at);
    }

    public function test_conclusione_pubblica_non_inventa_camera_letti_e_reception_li_richiede(): void
    {
        $r=$this->scenario();$data=$this->validSchedinaPayload();unset($data['room'],$data['beds']);
        $data['arrive']=$r->arrivo->toDateString();$data['departure']=$r->partenza->toDateString();$data['relationship']='OSPITE SINGOLO';$data['save_mode']='web_complete';
        $this->postJsonWeb('/checkin/'.$r->token,$data)->assertOk()->assertSeeText('Grazie! Abbiamo ricevuto i tuoi dati.');
        $sheet=Schedina::withoutGlobalScopes()->findOrFail($r->schedina_id);$this->assertNull($sheet->room);$this->assertNull($sheet->beds);$this->assertSame('web',$sheet->circuito);
        $data['save_mode']='full';
        $this->putJsonWeb(route('schedina.update',$sheet->id),$data+['web_versione_gestione'=>app(\App\Services\WebCheckinVersione::class)->of($r->fresh())])->assertUnprocessable()->assertJsonValidationErrors(['room','beds']);
    }

    public function test_logo_esterno_traversal_e_svg_non_vengono_caricati(): void
    {
        $r=$this->scenario();
        foreach(['https://non-autorizzato.invalid/logo.png','storage/uploads/loghi/../../secret.png','storage/uploads/loghi/attivo.svg']as$path){$r->struttura->forceFill(['logo'=>$path])->save();$this->assertNull(app(WebCheckinIdentita::class)->of($r->struttura)['logo_path']);}
    }
}
