<?php
namespace Tests\Feature;

use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class SchedinaCustomerPickerTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_ricerca_nome_cognome_omonimi_limite_e_dati_minimi(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        foreach (range(1,12) as $i) Customers::forceCreate(['struttura_id'=>$a->id,'name'=>'Nome'.$i,'surname'=>'Omonimo','nac_reg'=>'1990-01-01','email'=>'privato@example.invalid']);
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'Omonimo']))->assertOk()->assertJsonCount(10)->assertJsonMissingPath('0.email')->assertJsonStructure([['id','name','surname','nac_reg','city']]);
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'Nome12']))->assertOk()->assertJsonCount(1)->assertJsonPath('0.name','Nome12');
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'Zzzassente']))->assertExactJson([]);
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'N']))->assertExactJson([]);
    }

    public function test_selezione_non_trasferisce_soggiorni_ne_modifica_cliente_e_consensi(): void
    {
        $a = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $a->id));
        $c=Customers::forceCreate(['struttura_id'=>$a->id,'name'=>'Nome','surname'=>'Omonimo','privacy_consent'=>true,'privacy_consent_at'=>'2025-01-02 12:00:00']);
        $before=$c->fresh()->getRawOriginal();
        $r=$this->getJson(route('schedina.clienti.ricerca',['selected'=>$c->id]));
        $r->assertOk()->assertJsonPath('customer_id',$c->id)->assertJsonMissingPath('arrive')->assertJsonMissingPath('departure')->assertJsonMissingPath('exent')->assertJsonMissingPath('componenti');
        $this->get(route('newschedina',['customer_id'=>$c->id]))->assertOk()->assertSee('Cliente collegato');
        $this->post(route('schedina.store'),['save_mode'=>'draft','customer_id'=>$c->id,'name'=>'Nome diverso','surname'=>'Omonimo'])->assertSessionHasNoErrors();
        $this->assertSame(1,Customers::withoutGlobalScopes()->count());
        $this->assertSame($c->id,(int)Schedina::sole()->customer_id);
        $this->assertSame($before,$c->fresh()->getRawOriginal());
        $this->assertSame('2025-01-02 12:00:00',Schedina::sole()->getRawOriginal('customer_privacy_consent_at'));
    }

    public function test_utente_struttura_non_enumera_ne_seleziona_cliente_di_altra_struttura_della_stessa_catena(): void
    {
        $owner=$this->ownerFor($this->actor('admin'));
        $a=$this->structureFor($owner);$b=$this->structureFor($owner);
        $c=Customers::forceCreate(['struttura_id'=>$b->id,'name'=>'Riservato','surname'=>'Altro']);
        $this->actingAs($this->actor('struttura_user',null,$a->id));
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'Riservato']))->assertExactJson([]);
        $this->getJson(route('schedina.clienti.ricerca',['selected'=>$c->id]))->assertNotFound();
        $this->get(route('newschedina',['customer_id'=>$c->id]))->assertNotFound();
        $this->postJson(route('schedina.store'),['save_mode'=>'draft','customer_id'=>$c->id])->assertUnprocessable()->assertJsonValidationErrors('customer_id');
        $this->assertSame(0,Schedina::withoutGlobalScopes()->count());
    }
    public static function ruoli(): array { return [['super_admin'],['admin'],['proprietario'],['struttura_user']]; }
    #[\PHPUnit\Framework\Attributes\DataProvider('ruoli')]
    public function test_ricerca_e_selezione_nei_quattro_ruoli(string $role): void
    {
        $admin=$this->actor('admin');$owner=$this->ownerFor($admin);$a=$this->structureFor($owner);
        $actor=match($role) {'admin'=>$admin,'proprietario'=>$this->actor($role,$owner->id),default=>$this->actor($role,null,$a->id)};
        $own=Customers::forceCreate(['struttura_id'=>$a->id,'name'=>'Accessibile','surname'=>'Ruolo']);
        $foreignStructure=$this->structureFor($this->ownerFor($this->actor('admin')));
        $foreign=Customers::forceCreate(['struttura_id'=>$foreignStructure->id,'name'=>'Inaccessibile','surname'=>'Altrove']);
        $this->actingAs($actor)->withSession(['struttura_corrente_id'=>$a->id]);
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'Accessibile']))->assertOk()->assertJsonCount(1)->assertJsonPath('0.id',$own->id);
        $this->getJson(route('schedina.clienti.ricerca',['selected'=>$foreign->id]))->assertNotFound();
        $this->get(route('newschedina',['customer_id'=>$foreign->id]))->assertNotFound();
    }

    public function test_consenso_senza_data_non_rinnovato_da_selezione_o_modifica(): void
    {
        $a=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$a->id));
        $c=Customers::forceCreate(['struttura_id'=>$a->id,'name'=>'Consenso','surname'=>'Sintetico','privacy_consent'=>true]);
        $data=['save_mode'=>'draft','customer_id'=>$c->id,'name'=>'Consenso','surname'=>'Sintetico','customer_privacy_consent'=>'1'];
        $this->post(route('schedina.store'),$data)->assertSessionHasNoErrors();$s=Schedina::sole();
        $this->assertTrue((bool)$s->customer_privacy_consent);$this->assertNull($s->customer_privacy_consent_at);
        $this->put(route('schedina.update',['id'=>$s->id]),$data)->assertSessionHasNoErrors();
        $this->assertNull($s->fresh()->customer_privacy_consent_at);$this->assertNull($c->fresh()->privacy_consent_at);
    }

    public function test_campo_svuotato_esplicitamente_non_ripristinato_dal_cliente(): void
    {
        $a=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$a->id));
        $c=Customers::forceCreate(['struttura_id'=>$a->id,'name'=>'Campi','surname'=>'Sintetici','email'=>'master@example.invalid']);
        $this->post(route('schedina.store'),['save_mode'=>'draft','customer_id'=>$c->id,'name'=>'Campi','surname'=>'Sintetici','customer_email'=>''])->assertSessionHasNoErrors();
        $this->assertNull(Schedina::sole()->customer_email);$this->assertSame('master@example.invalid',$c->fresh()->email);
    }

    public function test_cambio_struttura_in_altra_scheda_non_salva_il_vecchio_modulo_nel_tenant_nuovo(): void
    {
        $owner=$this->ownerFor($this->actor('admin'));$a=$this->structureFor($owner);$b=$this->structureFor($owner);
        $this->actingAs($this->actor('proprietario',$owner->id))->withSession(['struttura_corrente_id'=>$b->id]);
        $response=$this->postJson(route('schedina.store'),['save_mode'=>'draft','schedina_struttura_id'=>$a->id,'name'=>'Contesto','surname'=>'Sintetico']);
        $this->assertSame(0,Schedina::withoutGlobalScopes()->count());
        $response->assertUnprocessable()->assertJsonValidationErrors('schedina_struttura_id');
        $this->getJson(route('schedina.clienti.ricerca',['query'=>'Contesto','schedina_struttura_id'=>$a->id]))->assertUnprocessable()->assertJsonValidationErrors('schedina_struttura_id');
    }

    public function test_cambio_struttura_non_sovrascrive_la_bozza_temporanea_per_importazione(): void
    {
        $owner=$this->ownerFor($this->actor('admin'));$a=$this->structureFor($owner);$b=$this->structureFor($owner);
        $user=$this->actor('proprietario',$owner->id);$this->actingAs($user)->withSession(['struttura_corrente_id'=>$b->id]);
        $this->postJson(route('schedina.componenti.import.new.prepare'),['schedina_struttura_id'=>$a->id,'name'=>'Contesto'])->assertUnprocessable()->assertJsonValidationErrors('schedina_struttura_id')->assertSessionMissing('componenti_import_new_schedina.'.$user->id.'.'.$b->id);
    }

    public static function contestiCompatibili(): array { return [['corrente'],['legacy']]; }
    #[\PHPUnit\Framework\Attributes\DataProvider('contestiCompatibili')]
    public function test_preparazione_import_con_contesto_corrente_o_chiamante_legacy_resta_disponibile(string $mode): void
    {
        $a=$this->structureFor(null);$user=$this->actor('struttura_user',null,$a->id);$this->actingAs($user);
        $payload=['name'=>'Importazione sintetica'];if($mode==='corrente')$payload['schedina_struttura_id']=$a->id;
        $this->post(route('schedina.componenti.import.new.prepare'),$payload)->assertRedirect(route('schedina.componenti.import.new.index'))->assertSessionHas('componenti_import_new_schedina.'.$user->id.'.'.$a->id.'.name','Importazione sintetica');
        $this->assertSame(0,Schedina::count());
    }

}
