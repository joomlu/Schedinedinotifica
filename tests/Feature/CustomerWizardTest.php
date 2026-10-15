<?php

namespace Tests\Feature;

use App\Models\Customers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerWizardTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    private function context(): void
    {
        $admin = $this->actor('admin');
        $s = $this->structureFor($this->ownerFor($admin));
        $this->actingAs($this->actor('struttura_user', null, $s->id))
            ->withSession(['struttura_corrente_id' => $s->id]);
    }

    public static function contatti(): array
    {
        return [
            'email' => [['email' => 'persona@example.test'], true],
            'telefono' => [['phone' => '+39 0541 123456'], true],
            'cellulare' => [['cellphone' => '+39 333 1234567'], true],
            'entrambi' => [['email' => 'persona@example.test', 'phone' => '0541123456'], true],
            'nessuno' => [[], false],
            'spazi' => [['email' => '  ', 'phone' => '  '], false],
            'fax' => [['fax' => '0541123456'], false],
            'azienda' => [['email_az' => 'azienda@example.test'], false],
            'email errata' => [['email' => 'errata'], false],
            'telefono senza cifre' => [['phone' => '+ ( ) / -'], false],
        ];
    }

    #[DataProvider('contatti')]
    public function test_richiesta_contatto_proprio_e_privacy_facoltativa(array $contacts, bool $valid): void
    {
        $this->context();
        $before = Customers::count();
        $data = ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'privacy_consent' => 0, 'geo' => ['cittadinanza' => ''], 'birth' => ['indirizzo' => '']] + $contacts;
        $response = $this->from('/clienti/nuovo')->post('/clienti', $data);
        if ($valid) {
            $response->assertSessionHasNoErrors()->assertRedirect('/clienti');
            $this->assertSame($before + 1, Customers::count());
            $this->assertFalse(Customers::latest('id')->first()->privacy_consent);
        } else {
            $response->assertSessionHasErrors();
            $this->assertSame($before, Customers::count());
        }
    }

    public function test_bozza_non_aggira_i_minimi(): void
    {
        $this->context();
        $before = Customers::count();
        $this->post('/clienti', ['save_mode' => 'draft', 'type_cliente' => 'Richiesta'])
            ->assertSessionHasErrors(['name', 'surname', 'email']);
        $this->assertSame($before, Customers::count());
    }
    public function test_richiesta_campi_minimi_catalogo_e_payload_non_scalare(): void
    {
        $this->context();
        $valid = ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'email' => 'persona@example.test'];
        foreach (['type_cliente', 'name', 'surname'] as $field) {
            $data = $valid; unset($data[$field]);
            $this->post('/clienti', $data)->assertSessionHasErrors($field);
        }
        $this->post('/clienti', array_replace($valid, ['type_cliente' => 'Altro']))->assertSessionHasErrors('type_cliente');
        $this->post('/clienti', array_replace($valid, ['name' => ['alterata']]))->assertSessionHasErrors('name');
        $this->post('/clienti', array_replace($valid, ['save_mode' => 'ignora']))->assertSessionHasErrors('save_mode');
        $this->post('/clienti', array_replace($valid, ['country' => '999999']))->assertSessionHasErrors('country');
        $this->post('/clienti', array_replace($valid, ['nac_reg' => '2026-02-31']))->assertSessionHasErrors('nac_reg');
        $this->post('/clienti', array_replace($valid, ['typeaway' => '999999']))->assertSessionHasErrors('typeaway');
        $this->post('/clienti', array_replace($valid, ['active_tab' => 'alterata']))->assertSessionHasErrors('active_tab');
        $this->getJson('/clienti/nuovo?tab[]=alterata')->assertUnprocessable();
        $this->assertSame(0, Customers::count());
    }

    public function test_reinvio_e_modifica_ritorno_elenco_e_nessuna_promozione(): void
    {
        $this->context();
        $data = ['submission_id' => (string) \Illuminate\Support\Str::uuid(), 'type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'phone' => '0541123456'];
        $this->post('/clienti', $data)->assertSessionHasNoErrors()->assertRedirect('/clienti')->assertSessionHas('customer_saved_draft_key', 'clienti.nuovo.draft.v1');
        $c = Customers::sole();
        $number = $c->numero_cliente;
        $this->assertNotEmpty($c->wizard_submission_key);
        \Illuminate\Support\Facades\Cache::flush();
        $this->post('/clienti', $data)->assertSessionHasNoErrors()->assertRedirect('/clienti');
        $this->assertSame(1, Customers::count());
        $this->post('/clienti', array_replace($data, ['name' => 'Altra']))->assertSessionHasErrors('submission_id');
        $this->put('/clienti/'.$c->id, array_replace($data, ['name' => 'Aggiornata', 'numero_cliente' => 'ALTERATO']))
            ->assertSessionHasNoErrors()->assertRedirect('/clienti')->assertSessionHas('customer_saved_draft_key', 'clienti.edit.'.$c->id.'.draft.v1');
        $this->assertSame($number, $c->fresh()->numero_cliente);
        $this->put('/clienti/'.$c->id, $data + ['save_mode' => 'to_schedina'])->assertSessionHasErrors('type_cliente');
        $this->assertSame('Richiesta', $c->fresh()->type_housed);
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id]);
        $this->get('/clienti/'.$c->id.'/modifica')->assertNotFound();
        $this->put('/clienti/'.$c->id, $data)->assertNotFound();
    }

    public function test_minimi_componente_ospite_e_passaggio_schedina(): void
    {
        $this->context();
        \App\Models\GeoNazione::firstOrCreate(['nome' => 'Francia'], ['codice_iso2' => 'FR', 'cittadinanza' => 'Francese', 'is_italia' => false]);
        $document = \App\Models\TipoDocumento::firstOrCreate(['codice' => 'PASOR'], ['descrizione' => 'PASSAPORTO']);
        $data = ['type_cliente' => 'Componente', 'name' => 'Persona', 'surname' => 'Sintetica', 'sex' => 'M', 'country_reg' => 'Francia', 'ciudadania_reg' => 'Francese', 'nac_reg' => '1980-01-01'];
        $this->post('/clienti', $data)->assertSessionHasNoErrors()->assertRedirect('/clienti');
        $component = Customers::sole();
        $this->put('/clienti/'.$component->id, $data + ['save_mode' => 'to_schedina'])->assertSessionHasErrors('type_cliente');
        $ospite = array_replace($data, ['type_cliente' => 'Ospite']);
        $this->post('/clienti', $ospite)->assertSessionHasErrors(['type_doc_reg', 'num_doc_reg', 'country_doc_reg']);
        $ospite += ['type_doc_reg' => $document->descrizione, 'num_doc_reg' => 'QA123', 'country_doc_reg' => 'Francia', 'save_mode' => 'to_schedina'];
        $this->post('/clienti', $ospite)->assertSessionHasNoErrors()->assertRedirect();
        $c = Customers::latest('id')->first();
        $this->get(route('newschedina', ['customer_id' => $c->id]))->assertOk();
        $this->assertSame(0, \App\Models\Schedina::count());
        foreach (['sex', 'country_reg', 'ciudadania_reg', 'nac_reg'] as $field) {
            $missing = $data; unset($missing[$field]);
            $this->post('/clienti', $missing)->assertSessionHasErrors($field);
        }
    }

    public function test_geo_italia_cascata_id_persistenza_e_rifiuti(): void
    {
        $this->context();
        $it = \App\Models\GeoNazione::firstOrCreate(['nome' => 'Italia'], ['codice_iso2' => 'IT', 'cittadinanza' => 'Italiana', 'is_italia' => true]);
        $region = \App\Models\GeoRegione::create(['geo_nazione_id' => $it->id, 'nome' => 'Regione Sintetica', 'codice_regione' => '91']);
        $province = \App\Models\GeoProvincia::create(['geo_regione_id' => $region->id, 'nome' => 'Provincia Sintetica', 'sigla' => 'XX', 'codice_provincia' => '991']);
        $city = \App\Models\GeoComune::create(['geo_provincia_id' => $province->id, 'nome' => 'Comune Sintetico', 'codice_istat' => '991001']);
        $other = \App\Models\GeoProvincia::create(['geo_regione_id' => $region->id, 'nome' => 'Provincia Diversa', 'sigla' => 'XY', 'codice_provincia' => '992']);
        $initial = app(\App\Services\CustomerWizardValidation::class)->initial(['nazione' => 'Italia', 'regione_text' => $region->nome, 'provincia_text' => 'XX', 'comune_text' => $city->nome]);
        $this->assertSame($it->id, $initial['nazione_id']);
        $this->assertSame($city->id, $initial['comune_id']);
        $this->assertFalse($initial['manual']);
        $empty = app(\App\Services\CustomerWizardValidation::class)->initial(['nazione' => null], true);
        $this->assertNull($empty['nazione_id']);
        $this->assertTrue($empty['manual']);
        $data = ['type_cliente' => 'Componente', 'name' => 'Persona', 'surname' => 'Sintetica', 'sex' => 'F', 'country_reg' => (string) $it->id, 'region_reg' => (string) $region->id, 'prov_reg' => (string) $province->id, 'city_reg' => (string) $city->id, 'ciudadania_reg' => 'Italiana', 'nac_reg' => '1980-01-01'];
        $this->post('/clienti', array_replace($data, ['prov_reg' => (string) $other->id]))->assertSessionHasErrors('city_reg');
        $this->post('/clienti', array_replace($data, ['city_reg' => '999999']))->assertSessionHasErrors('city_reg');
        $this->post('/clienti', array_replace($data, ['cap_reg' => '123']))->assertSessionHasErrors('cap_reg');
        $this->post('/clienti', $data)->assertSessionHasNoErrors();
        $c = Customers::sole();
        $this->assertSame('Italia', $c->country_reg);
        $this->assertSame('Comune Sintetico', $c->city_reg);
        $this->assertSame('XX', $c->prov_reg);
        $this->get('/clienti/'.$c->id.'/modifica')->assertOk()->assertSee('Comune Sintetico');
        foreach (['nazioni', 'regioni', 'province', 'comuni', 'cap', 'resolve'] as $endpoint) {
            $this->getJson('/geo/'.$endpoint.'?q[]=alterato')->assertUnprocessable();
        }
    }

    public function test_consensi_indipendenti_timestamp_server_e_azienda_opzionale(): void
    {
        $this->context();
        $data = ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'email' => 'persona@example.test', 'marketing_consent' => 1, 'communication_consent' => 0, 'privacy_consent' => 0, 'has_azienda' => '1', 'azienda' => 'Azienda Sintetica', 'marketing_consent_at' => '1900-01-01 00:00:00'];
        $this->post('/clienti', $data)->assertSessionHasNoErrors();
        $c = Customers::sole();
        $this->assertFalse($c->privacy_consent);
        $this->assertTrue($c->marketing_consent);
        $this->assertFalse($c->communication_consent);
        $this->assertSame(now()->toDateString(), $c->marketing_consent_at->toDateString());
        $this->assertSame('Azienda Sintetica', $c->azienda);
        $timestamp = $c->marketing_consent_at->toDateTimeString();
        $this->put('/clienti/'.$c->id, $data)->assertSessionHasNoErrors();
        $this->assertSame($timestamp, $c->fresh()->marketing_consent_at->toDateTimeString());
        $this->post('/clienti', array_replace($data, ['marketing_consent' => 'forzato']))->assertSessionHasErrors('marketing_consent');
    }

    public function test_errore_seconda_scrittura_non_lascia_cliente_parziale(): void
    {
        $this->context();
        $this->withoutExceptionHandling();
        $event = 'eloquent.updating: '.Customers::class;
        \Illuminate\Support\Facades\Event::listen($event, function () { throw new \RuntimeException('Errore sintetico numerazione'); });
        try {
            try {
                $this->post('/clienti', ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'email' => 'persona@example.test']);
                $this->fail('Guasto sintetico non raggiunto');
            } catch (\RuntimeException $e) {
                $this->assertSame('Errore sintetico numerazione', $e->getMessage());
            }
            $this->assertSame(0, Customers::count());
        } finally {
            \Illuminate\Support\Facades\Event::forget($event);
        }
    }

    public static function ruoli(): array { return [['super_admin'], ['admin'], ['proprietario'], ['struttura_user']]; }

    #[DataProvider('ruoli')]
    public function test_quattro_ruoli_creazione_nella_sola_struttura_attiva(string $role): void
    {
        $admin = $this->actor('admin'); $owner = $this->ownerFor($admin); $s = $this->structureFor($owner); $foreign = $this->structureFor(null);
        $user = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $s->id);
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $s->id]);
        $data = ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'email' => 'persona@example.test', 'struttura_id' => $foreign->id];
        $this->post('/clienti', $data)->assertSessionHasNoErrors();
        $this->assertSame($s->id, Customers::sole()->struttura_id);
        $this->post('/clienti', $data + ['_token' => 'nonvalido'])->assertStatus(419);
        $this->assertSame(1, Customers::count());
    }

    public function test_elenco_completezza_coerente_e_legacy_non_riclassificato(): void
    {
        $this->context();
        Customers::create(['type_housed' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Richiesta', 'email' => 'persona@example.test']);
        Customers::create(['type_housed' => 'Componente', 'name' => 'Persona', 'surname' => 'Componente', 'sex' => 'M', 'country_reg' => 'Francia', 'ciudadania_reg' => 'Francese', 'nac_reg' => '1980-01-01']);
        $legacy = Customers::create(['type_housed' => '', 'name' => 'Persona', 'surname' => 'Legacy']);
        $this->get('/clienti?stato=completo')->assertOk()->assertViewHas('customers', fn ($p) => $p->total() === 2);
        $this->get('/clienti?stato=bozza')->assertOk()->assertViewHas('customers', fn ($p) => $p->total() === 1);
        $this->assertSame('', $legacy->fresh()->type_housed);
    }

    #[DataProvider('ruoli')]
    public function test_id_estranei_lettura_e_modifica_bloccati_per_tutti_i_ruoli(string $role): void
    {
        $admin = $this->actor('admin'); $owner = $this->ownerFor($admin); $s = $this->structureFor($owner);
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        $c = Customers::create(['struttura_id' => $foreign->id, 'type_housed' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Altra', 'email' => 'altra@example.test']);
        $user = $role === 'admin' ? $admin : $this->actor($role, $role === 'proprietario' ? $owner->id : null, $s->id);
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $s->id]);
        $this->get('/clienti/'.$c->id.'/modifica')->assertNotFound();
        $this->put('/clienti/'.$c->id, ['save_mode' => 'draft'])->assertNotFound();
        $this->assertSame('Altra', \Illuminate\Support\Facades\DB::table('clienti')->where('id', $c->id)->value('surname'));
        if ($role !== 'super_admin') {
            $this->withSession(['struttura_corrente_id' => $foreign->id]);
            $this->post('/clienti', ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'email' => 'persona@example.test'])->assertForbidden();
        }
    }

    public function test_numerazione_superiore_a_quattro_cifre_non_ricomincia(): void
    {
        $this->context(); $year = now()->format('y');
        foreach ([9999, 10000] as $serial) Customers::create(['type_housed' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Storica', 'numero_cliente' => "R-{$year}-{$serial}"]);
        $this->post('/clienti', ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Nuova', 'email' => 'nuova@example.test'])->assertSessionHasNoErrors();
        $this->assertSame("R-{$year}-10001", Customers::latest('id')->first()->numero_cliente);
    }

    public function test_filtri_export_consensi_indipendenti_senza_obbligo_privacy(): void
    {
        $this->context();
        $base = ['type_housed' => 'Richiesta', 'name' => 'Persona', 'email' => 'persona@example.test', 'cellphone' => '3331234567', 'privacy_consent' => false];
        $marketing = Customers::create($base + ['surname' => 'Marketing', 'marketing_consent' => true, 'communication_consent' => false]);
        $communication = Customers::create($base + ['surname' => 'Comunicazioni', 'marketing_consent' => false, 'communication_consent' => true]);
        Customers::create($base + ['surname' => 'Nessuno', 'marketing_consent' => false, 'communication_consent' => false]);
        $this->get('/clienti/liste-export?channel=email')->assertOk()->assertViewHas('customers', fn ($p) => $p->total() === 1 && $p->first()->id === $marketing->id);
        $this->get('/clienti/liste-export?channel=whatsapp')->assertOk()->assertViewHas('customers', fn ($p) => $p->total() === 1 && $p->first()->id === $communication->id);
    }

    public function test_limiti_database_campi_opzionali_rifiutati_senza_record_parziali(): void
    {
        $this->context();
        $data = ['type_cliente' => 'Richiesta', 'name' => 'Persona', 'surname' => 'Sintetica', 'email' => 'persona@example.test'];
        foreach (['cap_reg', 'cap_az', 'number_az'] as $field) $this->post('/clienti', $data + [$field => str_repeat('X', 21)])->assertSessionHasErrors($field);
        $this->post('/clienti', $data + ['has_azienda' => '1', 'nota' => str_repeat('A', 300)])->assertSessionHasNoErrors();
        $this->assertSame(300, strlen(Customers::sole()->nota));
    }

    public function test_timestamp_consensi_manipolati_non_rompono_il_ritorno_dopo_errore(): void
    {
        $this->context();
        $this->from('/clienti/nuovo')->post('/clienti', ['type_cliente' => 'Richiesta', 'surname' => 'Sintetica', 'email' => 'persona@example.test', 'privacy_consent_at' => 'data-manipolata', 'marketing_consent_at' => 'data-manipolata', 'communication_consent_at' => 'data-manipolata'])->assertSessionHasErrors('name');
        $this->get('/clienti/nuovo')->assertOk()->assertDontSee('data-manipolata');
    }

    public function test_ospite_legacy_non_mappabile_non_passa_alla_schedina(): void
    {
        $this->context();
        $legacy = Customers::create(['type_housed' => 'Ospite', 'name' => 'Persona', 'surname' => 'Legacy', 'country_reg' => 'Stato legacy non mappabile']);
        $this->put('/clienti/'.$legacy->id, ['save_mode' => 'to_schedina', 'type_cliente' => 'Ospite', 'name' => 'Persona', 'surname' => 'Legacy', 'country_reg' => 'Stato legacy non mappabile'])->assertSessionHasErrors('country_reg');
        $this->assertSame('Stato legacy non mappabile', $legacy->fresh()->country_reg);
        $this->assertSame(0, \App\Models\Schedina::count());
    }

}
