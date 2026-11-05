<?php
namespace Tests\Feature;
use App\Models\{Schedina,Customers};
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
class SchedinaFlowValidationTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;
    private function contesto(): array
    {
        $s=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$s->id));$this->cataloghiSchedina($s->id);
        return [$s,$this->validSchedinaPayload()+['relationship'=>'OSPITE SINGOLO']];
    }
    public static function invalidi(): array
    {
        return [
            [['departure'=>'2026-03-15'],'departure'],
            [['departure'=>'2026-03-14'],'departure'],
            [['oa_date_nac'=>'2999-01-01'],'oa_date_nac'],
            [['oa_city'=>'Comune inesistente'],'oa_city'],
            [['or_prov'=>'XX'],'or_prov'],
            [['or_cap'=>'99999'],'or_cap'],
            [['or_doctype'=>'DOCUMENTO INVENTATO'],'or_doctype'],
            [['or_published_country'=>'IT','or_published_city'=>null],'or_published_city'],
            [['or_published_date'=>'2999-01-01'],'or_published_date'],
            [['or_published_date'=>'1980-01-01'],'or_published_date'],
            [['or_expire'=>'2024-01-01'],'or_published_date'],
            [['relationship'=>'TIPO INVENTATO'],'relationship'],
            [['customer_subgroup'=>'Gruppo inesistente'],'customer_subgroup'],
            [['customer_email'=>'non-email'],'customer_email'],
        ];
    }
    #[DataProvider('invalidi')]
    public function test_errori_senza_persistenza_parziale(array $override,string $error): void
    {
        [$s,$payload]=$this->contesto();
        // Un CAP configurato rende significativa la verifica di appartenenza.
        $city=\App\Models\GeoComune::first();$cap=\App\Models\GeoCap::forceCreate(['cap'=>'00100']);$city->caps()->attach($cap->id);
        $this->postJson(route('schedina.store'),array_replace($payload,$override))->assertUnprocessable()->assertJsonValidationErrors($error);
        $this->assertSame(0,Schedina::withoutGlobalScopes()->count());$this->assertSame(0,Customers::withoutGlobalScopes()->count());
    }
    public function test_foreign_localita_libera_province_non_obbligatorie_e_letti_diversi_da_persone(): void
    {
        [$s,$payload]=$this->contesto();
        \App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA','codice_iso2'=>'FR','cittadinanza'=>'FRANCESE','is_italia'=>false]);
        $payload=array_replace($payload,['oa_country'=>'FRANCIA','oa_region'=>null,'oa_prov'=>null,'oa_city'=>'Parigi','oa_city_nac'=>'FRANCESE','or_country'=>'FRANCIA','or_region'=>null,'or_prov'=>null,'or_city'=>'Parigi','or_cap'=>null,'or_published_country'=>'FRANCIA','or_published_city'=>null,'beds'=>2]);
        $this->post(route('schedina.store'),$payload)->assertSessionHasNoErrors()->assertRedirect(route('schedina'));
        $this->assertSame(2,(int)Schedina::sole()->beds);$this->assertSame(1,(int)Schedina::sole()->cant_people);
    }
    public function test_errore_su_componente_annulla_tutto(): void
    {
        [$s,$payload]=$this->contesto();
        $payload=array_replace($payload,['relationship'=>'CAPO FAMIGLIA','cant_people'=>2,'componenti'=>[['name'=>'QA','surname'=>'Schedina','sex'=>'M','date_nac'=>'1990-01-01','relationship'=>'FAMILIARE','exent'=>'NO','country_nac'=>'ITALIA','city_nac'=>'ITALIANA','province_nac'=>'RM','comune_nac'=>'Roma','country'=>'ITALIA','regione'=>'Lazio','province'=>'RM','city'=>'Roma']]]);
        $this->postJson(route('schedina.store'),$payload)->assertUnprocessable()->assertJsonValidationErrors('componenti.0.name');
        $this->assertSame(0,Schedina::withoutGlobalScopes()->count());
    }
    public function test_id_componente_estraneo_rifiutato_dopo_creazione_parent_con_rollback(): void
    {
        [$s,$payload]=$this->contesto();
        \App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA','codice_iso2'=>'FR','cittadinanza'=>'FRANCESE','is_italia'=>false]);
        $other=$this->structureFor(null);
        $parent=Schedina::forceCreate(['struttura_id'=>$other->id,'name'=>'Altro','surname'=>'Sintetico']);
        $component=\App\Models\Componenti::forceCreate(['struttura_id'=>$other->id,'schedina_id'=>$parent->id,'name'=>'Riservato','surname'=>'Sintetico']);
        $before=\Illuminate\Support\Facades\DB::table('schedina')->orderBy('id')->get()->toJson();
        $payload=array_replace($payload,['relationship'=>'CAPO FAMIGLIA','cant_people'=>2,'componenti'=>[['id'=>$component->id,'name'=>'Componente','surname'=>'Sintetico','sex'=>'F','date_nac'=>'1990-01-01','relationship'=>'FAMILIARE','exent'=>'NO','country_nac'=>'FRANCIA','city_nac'=>'FRANCESE','country'=>'FRANCIA','city'=>'Parigi']]]);
        $this->postJson(route('schedina.store'),$payload)->assertUnprocessable()->assertJsonValidationErrors('componenti.0.id');
        $this->assertSame($before,\Illuminate\Support\Facades\DB::table('schedina')->orderBy('id')->get()->toJson());
        $this->assertSame(0,Customers::withoutGlobalScopes()->count());
    }

    public function test_bozza_incompleta_consentita_ma_dimensioni_invalide_rifiutate_senza_sql500(): void
    {
        [$s,$payload]=$this->contesto();
        $this->postJson(route('schedina.store'),['save_mode'=>'draft','name'=>str_repeat('A',101)])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson(route('schedina.store'),['save_mode'=>'draft','observation'=>str_repeat('😀',16384)])->assertUnprocessable()->assertJsonValidationErrors('observation');
        $this->assertSame(0,Schedina::count());
        $this->post(route('schedina.store'),['save_mode'=>'draft','name'=>''])->assertSessionHasNoErrors()->assertRedirect(route('schedina.bozze'));
        $this->assertSame(1,Schedina::count());
    }


    public function test_nascita_e_residenza_estere_ammettono_la_sola_nazione(): void
    {
        [$s, $payload] = $this->contesto();
        $nation = \App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA', 'codice_iso2'=>'FR', 'cittadinanza'=>'FRANCESE', 'is_italia'=>false]);
        $payload = array_replace($payload, [
            'oa_country'=>(string)$nation->id, 'oa_region'=>null, 'oa_prov'=>null, 'oa_city'=>null, 'oa_cap'=>null,
            'oa_city_nac'=>'FRANCESE', 'or_country'=>(string)$nation->id,
            'or_region'=>null, 'or_prov'=>null, 'or_city'=>null, 'or_cap'=>null,
            'or_typeaway'=>null, 'or_address'=>null, 'or_num'=>null,
            'or_published_country'=>(string)$nation->id, 'or_published_city'=>null,
        ]);
        $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('schedina'));
        $sheet = Schedina::sole();
        $this->assertSame('FRANCIA', $sheet->oa_country);
        $this->assertSame('FRANCIA', $sheet->or_country);
        $this->assertNull($sheet->oa_city);
        $this->assertNull($sheet->or_city);
        $this->assertNull($sheet->or_address);
        $this->assertNull($sheet->or_typeaway);
    }

    public function test_italia_continua_a_richiedere_comune_nascita_e_residenza(): void
    {
        [$s, $payload] = $this->contesto();
        $this->postJson(route('schedina.store'), array_replace($payload, ['oa_city'=>null, 'or_city'=>null]))
            ->assertUnprocessable()->assertJsonValidationErrors(['oa_city', 'or_city']);
        $this->assertSame(0, Schedina::withoutGlobalScopes()->count());
    }

    public function test_ripresa_geo_risolve_id_nazione_con_nome_e_modalita_estero(): void
    {
        [$s, $payload] = $this->contesto();
        $nation = \App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA', 'codice_iso2'=>'FR', 'cittadinanza'=>'FRANCESE', 'is_italia'=>false]);
        $initial = app(\App\Services\CustomerWizardValidation::class)->initial(['nazione'=>(string)$nation->id], true);
        $this->assertSame('FRANCIA', $initial['nazione']);
        $this->assertSame($nation->id, $initial['nazione_id']);
        $this->assertTrue($initial['manual']);
        $this->get(route('newschedina'))->assertOk()->assertSee('Altri dati esteri (facoltativi)');
    }


    public function test_geo_componenti_conferma_nazioni_e_coerenza_italiana_senza_perdere_dati_esteri(): void
    {
        [$s, $payload] = $this->contesto();
        $fr = \App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA', 'codice_iso2'=>'FR', 'cittadinanza'=>'FRANCESE', 'is_italia'=>false]);
        $row = ['name'=>'Componente', 'surname'=>'Sintetico', 'sex'=>'F', 'relationship'=>'FAMILIARE', 'exent'=>'NO', 'country_nac'=>(string)$fr->id, 'city_nac'=>'FRANCESE', 'date_nac'=>'2000-01-01', 'country'=>(string)$fr->id, 'city'=>'Parigi', 'cap'=>'75001', 'address'=>'Rue preservata', 'number'=>'8'];
        $payload = array_replace($payload, ['relationship'=>'CAPO FAMIGLIA', 'cant_people'=>2, 'beds'=>2, 'componenti'=>[$row]]);
        $bad = $payload; $bad['componenti'][0]['country'] = null;
        $this->postJson(route('schedina.store'), $bad)->assertUnprocessable()->assertJsonValidationErrors('componenti.0.country');
        $bad['componenti'][0]['country']='ITALIA';
        $this->postJson(route('schedina.store'), $bad)->assertUnprocessable()->assertJsonValidationErrors(['componenti.0.regione','componenti.0.province','componenti.0.city']);
        $this->post(route('schedina.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('schedina'));
        $member=\App\Models\Componenti::sole();
        $this->assertSame('75001',$member->cap); $this->assertSame('Rue preservata',$member->address);
        $this->assertSame('FRANCIA',$member->country_nac);
    }

    public function test_nascita_residenza_cittadinanza_e_paese_documento_restano_indipendenti(): void
    {
        [$s,$payload]=$this->contesto();
        \App\Models\GeoNazione::forceCreate(['nome'=>'FRANCIA','codice_iso2'=>'FR','cittadinanza'=>'FRANCESE','is_italia'=>false]);
        $payload=array_replace($payload,['oa_country'=>'FRANCIA','oa_region'=>null,'oa_prov'=>null,'oa_city'=>null,'oa_cap'=>null]);
        $this->post(route('schedina.store'),$payload)->assertSessionHasNoErrors()->assertRedirect(route('schedina'));
        $row=Schedina::sole();
        $this->assertSame('FRANCIA',$row->oa_country);$this->assertSame('ITALIA',$row->or_country);
        $this->assertSame('ITALIANA',$row->oa_city_nac);$this->assertSame('ITALIA',$row->or_published_country);
        $this->assertSame('Roma',$row->or_city);$this->assertSame($payload['oa_date_nac'],\Carbon\Carbon::parse($row->oa_date_nac)->toDateString());
    }
}
