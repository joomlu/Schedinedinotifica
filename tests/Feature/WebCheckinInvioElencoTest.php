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

class WebCheckinInvioElencoTest extends TestCase
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

    public function test_attivita_separate_dalla_modifica_e_isolate_per_struttura(): void
    {
        $r = $this->scenario();
        $this->get(route('web_checkin.edit', $r->id))->assertOk()
            ->assertSeeText('Modifica richiesta')->assertDontSee('id="web-camera"', false)
            ->assertDontSeeText('Revoca link')->assertDontSeeText('Conferma check-in');
        foreach (['ospiti', 'link', 'check-in', 'cestino'] as $attivita) {
            $this->get(route('web_checkin.attivita', [$r->id, $attivita]))->assertOk();
        }
        $this->get(route('web_checkin.attivita', [$r->id, 'link']))->assertSeeText('Copia link')->assertDontSee('id="web-camera"', false);
        $this->get(route('web_checkin.attivita', [$r->id, 'check-in']))->assertSee('id="web-camera"', false)->assertDontSeeText('Revoca link');
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id]);
        foreach (['ospiti', 'link', 'check-in', 'cestino'] as $attivita) {
            $this->get(route('web_checkin.attivita', [$r->id, $attivita]))->assertNotFound();
        }
    }

    public function test_invito_preparato_ha_azione_visibile_per_invio_nellelenco(): void
    {
        $r=$this->scenario(); $c=$this->prepara($r);
        config(['web_checkin.email_abilitata'=>true,'web_checkin.mailer'=>'smtp','mail.mailers.smtp.transport'=>'smtp','mail.mailers.smtp.host'=>'smtp.example.invalid','mail.from.address'=>'reception@example.invalid']);
        $this->get(route('schedina.web'))->assertOk()->assertSeeText('Anteprima e invio')->assertSee(route('web_checkin.comunicazioni',$r->id),false)->assertSeeText('Pronto per l’invio')->assertSeeText('In attesa di compilazione');
    }
    public function test_sola_visita_non_dimostra_compilazione_e_date_trascorse_non_si_riattivano(): void
    {
        $r=$this->scenario();
        $this->get('/checkin/'.$r->token)->assertOk();
        $this->get(route('schedina.web'))->assertOk()->assertSeeText('In attesa di compilazione')->assertDontSeeText('Compilazione in corso');
        $this->get(route('schedina.web',['stato'=>'da_inviare']))->assertOk()->assertSeeText($r->numero_prenotazione);
        $this->get(route('schedina.web',['stato'=>'in_compilazione']))->assertOk()->assertDontSeeText($r->numero_prenotazione);
        $r->forceFill(['arrivo'=>now()->subDays(10),'partenza'=>now()->subDays(8),'link_revoked_at'=>now()])->save();
        $this->get(route('schedina.web'))->assertOk()->assertSeeText('Link revocato')->assertSeeText('Date del soggiorno trascorse o non coerenti')->assertDontSeeText('Invia Web Check-in');
    }
    public function test_filtri_storico_non_confondono_compilato_con_checkin_effettuato(): void
    {
        $r=$this->scenario();$r->forceFill(['stato'=>'convertito','compilato_at'=>now(),'conversione_snapshot'=>['prova'=>'sintetica']])->save();
        foreach(['da_inviare','in_compilazione','compilato'] as $state) $this->get(route('web_checkin.storico',['stato'=>$state]))->assertOk()->assertDontSeeText($r->numero_prenotazione);
        $this->get(route('web_checkin.storico',['stato'=>'convertito']))->assertOk()->assertSeeText($r->numero_prenotazione)->assertSeeText('Check-in effettuato');
    }
}
