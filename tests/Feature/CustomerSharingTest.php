<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Services\CustomerSharing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CustomerSharingTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function setupGroup(): array
    {
        config(['customer_sharing.enabled' => true]);
        $owner = $this->ownerFor($this->actor('admin'));
        $a = $this->structureFor($owner); $b = $this->structureFor($owner); $c = $this->structureFor($owner);
        $user = $this->actor('proprietario', $owner->id);
        $this->actingAs($user);
        return [$user, $a, $b, $c, app(CustomerSharing::class)];
    }

    private function customer(int $sid, array $extra = []): Customers
    {
        return Customers::forceCreate(array_replace(['struttura_id' => $sid, 'name' => 'Persona', 'surname' => 'Sintetica',
            'sex' => 'F', 'nac_reg' => '1980-01-02', 'country_reg' => 'Italia', 'city_reg' => 'Comune sintetico',
            'prov_reg' => 'XX', 'type_housed' => 'Componente', 'numero_cliente' => 'LEGACY-'.$sid], $extra));
    }

    public function test_senza_gruppo_nessuna_copia_o_persona(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id);
        $this->assertSame(1, Customers::withoutGlobalScopes()->count());
        $this->assertSame(0, DB::table('customer_persons')->count());
        $this->assertNull($sharing->groupFor($b->id));
    }

    public function test_due_hotel_condividono_terzo_escluso_e_consensi_locali(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $first = $this->customer($a->id, ['privacy_consent' => true, 'marketing_consent' => true, 'observation' => 'Nota riservata']);
        $group = $sharing->configure($user, null, 0, 'Gruppo sintetico', [$a->id, $b->id]);
        $second = Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->sole();
        $this->assertSame($first->name, $second->name);
        $this->assertFalse((bool) $second->marketing_consent);
        $this->assertEmpty($second->observation);
        $this->assertSame(0, Customers::withoutGlobalScopes()->where('struttura_id', $c->id)->count());
        $this->assertSame(0, DB::table('schedina')->count());
        $this->assertSame([$a->id, $b->id], $sharing->members($group));
        $this->assertSame(1, $sharing->packageQuery(Customers::withoutGlobalScopes()->where('struttura_id', $a->id), $a->id)->count());
        $this->assertSame(0, $sharing->packageQuery(Customers::withoutGlobalScopes()->where('struttura_id', $b->id), $b->id)->count());
    }

    public function test_aggiornamento_comune_e_richiesta_obsoleta_rifiutata(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $first = $this->customer($a->id);
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $stale = Customers::withoutGlobalScopes()->findOrFail($first->id);
        $second = Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->sole();
        $second->email = 'nuovo@example.invalid'; $second->save();
        $this->assertSame('nuovo@example.invalid', $first->fresh()->email);
        $stale->email = 'vecchio@example.invalid';
        try { $stale->save(); $this->fail('Aggiornamento obsoleto accettato.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertSame('nuovo@example.invalid', $first->fresh()->email);
    }

    public function test_corrispondenza_certa_preserva_codici_e_un_solo_risultato(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id); $two = $this->customer($b->id);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->assertSame(1, DB::table('customer_persons')->where('group_id', $group)->count());
        $this->assertSame('LEGACY-'.$a->id, $one->fresh()->numero_cliente);
        $this->assertSame('LEGACY-'.$b->id, $two->fresh()->numero_cliente);
        $this->assertSame(1, $sharing->uniqueQuery(Customers::withoutGlobalScopes()->where('struttura_id', $a->id), $a->id)->count());
    }

    public function test_omonimi_con_nascite_diverse_restano_separati(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id); $this->customer($b->id, ['nac_reg' => '1970-03-04']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->assertSame(2, DB::table('customer_persons')->where('group_id', $group)->count());
        $this->assertCount(1, $sharing->candidates($group));
        $this->assertSame(2, $sharing->uniqueQuery(Customers::withoutGlobalScopes()->where('struttura_id', $a->id), $a->id)->count());
    }

    public function test_terzo_hotel_aggiunto_esplicitamente(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $sharing->configure($user, $group, 1, 'Gruppo', [$a->id, $b->id, $c->id]);
        $this->assertSame(1, Customers::withoutGlobalScopes()->where('struttura_id', $c->id)->count());
        $this->assertFalse((bool) DB::table('customer_person_links')->where('struttura_id', $c->id)->value('provenienza'));
    }

    public function test_strutture_di_altro_proprietario_rifiutate_senza_effetti(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        try { $sharing->configure($user, null, 0, 'Non valido', [$a->id, $foreign->id]); $this->fail('Struttura estranea accettata.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->assertSame(0, DB::table('customer_sharing_groups')->count());
    }

    public function test_gruppi_sovrapposti_rifiutati(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $sharing->configure($user, null, 0, 'Primo', [$a->id, $b->id]);
        try { $sharing->configure($user, null, 0, 'Secondo', [$b->id, $c->id]); $this->fail('Sovrapposizione accettata.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('strutture', $e->errors()); }
        $this->assertSame(1, DB::table('customer_sharing_groups')->count());
    }

    public function test_nuovo_cliente_propagato_senza_copiare_ruoli_o_note(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->customer($a->id, ['observation' => 'Solo hotel A', 'type_housed' => 'Ospite']);
        $other = Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->sole();
        $this->assertEmpty($other->observation);
        $this->assertSame('Componente', $other->type_housed);
        $this->assertSame(1, DB::table('customer_persons')->count());
    }

    public function test_residenze_discordanti_non_producono_indirizzo_ibrido(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id, ['country' => 'Italia', 'city' => 'Comune A', 'cap' => null]);
        $this->customer($b->id, ['country' => 'Italia', 'city' => 'Comune B', 'cap' => '99999']);
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->assertEmpty($one->fresh()->city);
        $this->assertEmpty($one->fresh()->cap);
        $this->assertSame('Comune sintetico', $one->fresh()->city_reg);
        $this->assertSame('da_confermare', $one->fresh()->sharingResidenceStatus);
        $this->assertSame(2, DB::table('customer_person_versions')->count());
        $this->assertSame(2, DB::table('customer_sharing_operations')->where('tipo', 'collegamento')->count());
    }

    public function test_separazione_conserva_origini_e_oscura_soli_riferimenti_tecnici(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $first = $this->customer($a->id);
        $second = $this->customer($b->id, ['name' => 'Altra', 'surname' => 'Persona']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $sharing->separate($user, $group, $b->id, 1, true);
        $this->assertNull($sharing->groupFor($b->id));
        $visible = $sharing->visibleQuery(Customers::withoutGlobalScopes())->where('struttura_id', $b->id)->get();
        $this->assertCount(1, $visible);
        $this->assertSame($second->id, $visible->first()->id);
        $this->assertSame(4, DB::table('clienti')->count());
        $this->assertSame(2, $sharing->visibleQuery(Customers::withoutGlobalScopes())->where('struttura_id', $a->id)->count());
        $detached = $second->fresh(); $detached->email = 'indipendente@example.invalid'; $detached->save();
        $this->assertEmpty($sharing->visibleQuery(Customers::withoutGlobalScopes())->where('struttura_id', $a->id)->where('name', 'Altra')->sole()->email);
    }

    public function test_separazione_versione_obsoleta_non_applicata(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        try { $sharing->separate($user, $group, $b->id, 9, true); $this->fail('Versione obsoleta accettata.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertSame([$a->id, $b->id], $sharing->members($group));
    }

    public function test_fusione_confermata_e_annullamento_senza_nuove_scritture(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id, ['nac_reg' => null]); $this->customer($b->id, ['nac_reg' => null, 'email' => 'integra@example.invalid']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $persons = DB::table('customer_persons')->orderBy('id')->get();
        $sharing->merge($user, $group, $persons[0]->id, $persons[1]->id, 1, 1);
        $this->assertSame(1, DB::table('customer_persons')->whereNull('merged_into_id')->count());
        $operation = DB::table('customer_sharing_operations')->where('tipo', 'fusione')->sole();
        $sharing->undoMerge($user, $group, $operation->id);
        $this->assertSame(2, DB::table('customer_persons')->whereNull('merged_into_id')->count());
        $this->assertSame(4, DB::table('customer_person_links')->whereNull('detached_at')->count());
    }

    public function test_fusione_identita_discordante_rifiutata(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id); $this->customer($b->id, ['nac_reg' => '1970-01-01']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $persons = DB::table('customer_persons')->orderBy('id')->get();
        try { $sharing->merge($user, $group, $persons[0]->id, $persons[1]->id, 1, 1); $this->fail('Fusione errata accettata.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('fusione', $e->errors()); }
        $this->assertSame(2, DB::table('customer_persons')->whereNull('merged_into_id')->count());
    }

    public function test_proprietario_puo_aprire_pagina_utente_struttura_no(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->get(route('proprietario.clienti-condivisione.index'))->assertOk()->assertSee('Condivisione clienti');
        $this->actingAs($this->actor('struttura_user', $user->proprietario_id, $a->id));
        $this->get(route('proprietario.clienti-condivisione.index'))->assertForbidden();
    }

    public function test_riepilogo_persistente_del_gruppo_vuoto_e_popolato(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $sharing->configure($user, null, 0, 'Gruppo riepilogo', [$a->id, $b->id]);
        $url = route('proprietario.clienti-condivisione.index');
        foreach ([1, 2] as $visit) {
            $this->get($url)->assertOk()->assertSee('data-sharing-status="attivo"', false)
                ->assertSee('Condivisione attiva')->assertSee('L’archivio condiviso è ancora vuoto');
        }
        $this->customer($a->id);
        $this->get($url)->assertOk()->assertSee('Condivisione attiva')
            ->assertDontSee('L’archivio condiviso è ancora vuoto');
        $this->assertSame(1, DB::table('customer_sharing_groups')->count());
        $this->assertSame(2, DB::table('customer_sharing_memberships')->count());
    }

    public function test_anteprima_non_mutante_e_conferma_vincolata_ai_dati(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->post(route('proprietario.clienti-condivisione.preview'), ['nome' => 'Gruppo', 'strutture' => [$a->id, $b->id]])->assertOk();
        $p = session('customer_sharing_preview');
        $this->assertSame(0, DB::table('customer_sharing_groups')->count());
        $this->customer($a->id);
        $this->post(route('proprietario.clienti-condivisione.store'), ['token' => $p['token'], 'conferma' => 1])->assertStatus(409);
        $this->assertSame(0, DB::table('customer_sharing_groups')->count());
    }

    public function test_cliente_condiviso_non_cancellabile_ne_archiviabile(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id);
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        try { app(\App\Services\CestinoService::class)->archiveModel($one); $this->fail('Archiviazione accettata.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('cliente', $e->errors()); }
        $this->assertSame(0, DB::table('cestino_items')->count());
        $this->assertSame(2, DB::table('clienti')->count());
    }

    public function test_cambio_proprietario_richiede_separazione(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $a->proprietario_id = null;
        try { $a->save(); $this->fail('Cambio proprietario accettato.'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('struttura', $e->errors()); }
        $this->assertSame($user->proprietario_id, $a->fresh()->proprietario_id);
    }

    public function test_attivazione_disabilitata_per_impostazione_predefinita(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        config(['customer_sharing.enabled' => false]);
        try { $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]); $this->fail('Attivazione non qualificata accettata.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertSame(0, DB::table('customer_sharing_groups')->count());
    }

    public function test_dati_incompleti_e_contatti_uguali_non_bastano_a_unire(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id, ['nac_reg' => null, 'email' => 'famiglia@example.invalid', 'phone' => '0123456789']);
        $this->customer($b->id, ['nac_reg' => null, 'email' => 'famiglia@example.invalid', 'phone' => '0123456789']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->assertSame(2, DB::table('customer_persons')->where('group_id', $group)->count());
    }

    public function test_conferma_residenza_esplicita_registra_data_e_propagazione(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id, ['country' => 'Italia', 'city' => 'Comune A', 'cap' => '11111']);
        $this->customer($b->id, ['country' => 'Italia', 'city' => 'Comune B', 'cap' => '22222']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $person = DB::table('customer_persons')->where('group_id', $group)->sole();
        $this->assertNull($person->residence_confirmed_at);
        $version = DB::table('customer_person_versions')->where('struttura_id', $b->id)->sole();
        $sharing->confirmResidence($user, $group, $person->id, $version->id, $person->version);
        $this->assertSame('Comune B', $one->fresh()->city);
        $this->assertSame('confermata', $one->fresh()->sharingResidenceStatus);
        $this->assertNotNull(DB::table('customer_persons')->where('id', $person->id)->value('residence_confirmed_at'));
    }

    public function test_coppia_mantenuta_separata_riappare_se_cambia_la_versione(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id, ['nac_reg' => null]);
        $this->customer($b->id, ['nac_reg' => null]);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $people = DB::table('customer_persons')->orderBy('id')->get();
        $sharing->keepSeparate($user, $group, $people[0]->id, $people[1]->id, 1, 1);
        $this->assertCount(0, $sharing->candidates($group));
        $this->assertCount(1, $sharing->candidates($group, true));
        $one = $one->fresh(); $one->phone = '0123456789'; $one->save();
        $this->assertCount(1, $sharing->candidates($group));
    }

    public function test_altro_proprietario_non_scarica_le_fonti(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $other = $this->ownerFor($this->actor('admin'));
        $this->actingAs($this->actor('proprietario', $other->id));
        $this->get(route('proprietario.clienti-condivisione.sources', $group))->assertNotFound();
    }

    public function test_pacchetto_separato_conserva_alternative_e_non_segue_gli_aggiornamenti(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $this->customer($a->id, ['country' => 'Italia', 'city' => 'Comune A']);
        $two = $this->customer($b->id, ['country' => 'Italia', 'city' => 'Comune B']);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $sharing->separate($user, $group, $b->id, 1, true);
        $package = DB::table('customer_sharing_packages')->where('struttura_id', $b->id)->sole();
        $payload = $sharing->decode($package->data);
        $this->assertCount(2, $payload['residenze']);
        $two = $two->fresh(); $two->city = 'Nuova residenza'; $two->save();
        $this->assertSame($package->data, DB::table('customer_sharing_packages')->where('id', $package->id)->value('data'));
        $this->get(route('proprietario.clienti-condivisione.package', $b->id))->assertOk()->assertJsonPath('struttura_id', $b->id);
    }

    public function test_stesso_cliente_in_due_soggiorni_rimane_nei_due_pacchetti(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id);
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $other = Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->sole();
        foreach ([[$a, $one, 'COND-STAY-A'], [$b, $other, 'COND-STAY-B']] as [$hotel, $customer, $code]) {
            \App\Models\Schedina::forceCreate(['struttura_id' => $hotel->id, 'customer_id' => $customer->id,
                'name' => $customer->name, 'surname' => $customer->surname, 'scheda' => $code,
                'arrive' => '2026-06-01', 'departure' => '2026-06-03', 'circuito' => 'schedina', 'is_arrive' => false]);
        }
        $this->withSession(['struttura_corrente_id' => $a->id])->get(route('customer.storico', $one->id))->assertOk()->assertSee('COND-STAY-A')->assertSee('COND-STAY-B');
        $sharing->separate($user, $group, $b->id, 1, true);
        $this->assertNotNull($one->fresh());
        $this->assertNotNull($other->fresh());
        $this->assertSame(2, DB::table('clienti')->count());
        $this->assertSame(1, DB::table('customer_sharing_packages')->where('struttura_id', $b->id)->count());
        $this->withSession(['struttura_corrente_id' => $a->id])->get(route('customer.storico', $one->id))->assertOk()->assertSee('COND-STAY-A')->assertDontSee('COND-STAY-B');
        $this->withSession(['struttura_corrente_id' => $b->id])->get(route('customer.storico', $other->id))->assertOk()->assertSee('COND-STAY-B')->assertDontSee('COND-STAY-A');
    }

    public function test_consensi_discordanti_stesso_hotel_non_diventano_consenso_di_gruppo(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $one = $this->customer($a->id, ['numero_cliente' => 'A-UNO', 'marketing_consent' => true]);
        $two = $this->customer($a->id, ['numero_cliente' => 'A-DUE', 'marketing_consent' => false]);
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->assertFalse($one->fresh()->marketing_consent);
        $this->assertFalse($two->fresh()->marketing_consent);
        $one = $one->fresh(); $one->marketing_consent = true; $one->save();
        $this->assertTrue($two->fresh()->marketing_consent);
        $this->assertFalse((bool) Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->sole()->marketing_consent);
    }

    public function test_soggiorno_completo_senza_cliente_crea_identita_condivisa_una_volta(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        foreach ([$a, $b] as $hotel) {
            $sheet = \App\Models\Schedina::forceCreate(['struttura_id' => $hotel->id, 'name' => 'Persona', 'surname' => 'Sintetica', 'sex' => 'F',
                'scheda' => 'SINTETICA-'.$hotel->id, 'arrive' => '2026-06-01', 'departure' => '2026-06-03', 'circuito' => 'schedina', 'is_arrive' => false,
                'oa_country' => 'Italia', 'oa_city' => 'Comune sintetico', 'oa_prov' => 'XX', 'oa_date_nac' => '1980-01-02', 'oa_city_nac' => 'Italiana',
                'or_country' => 'Italia', 'or_city' => 'Comune residenza', 'or_prov' => 'XX', 'or_doc' => 'DOCUMENTO-SINTETICO', 'or_doctype' => 'PASSAPORTO ORDINARIO']);
            DB::transaction(fn () => $sharing->linkStay($sheet));
            $this->assertNotNull($sheet->fresh()->customer_id);
        }
        $this->assertSame(1, DB::table('customer_persons')->whereNull('merged_into_id')->count());
        $this->assertSame(2, DB::table('schedina')->whereNotNull('customer_id')->count());
        $this->assertSame(2, DB::table('clienti')->count());
    }
    public function test_uscita_senza_pacchetto_preserva_archivio_locale_e_revoca_schede_e_storici_estranei(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $sharedA = $this->customer($a->id);
        $sharedB = $this->customer($b->id);
        $onlyA = $this->customer($a->id, ['name' => 'Riservata', 'numero_cliente' => 'SOLO-A']);
        $onlyB = $this->customer($b->id, ['name' => 'Locale B', 'numero_cliente' => 'SOLO-B', 'observation' => 'Nota solo B']);
        foreach ([[$a, $sharedA, 'STORICO-A'], [$b, $sharedB, 'STORICO-B']] as [$hotel, $customer, $code]) {
            \App\Models\Schedina::forceCreate(['struttura_id' => $hotel->id, 'customer_id' => $customer->id,
                'name' => $customer->name, 'surname' => $customer->surname, 'scheda' => $code,
                'arrive' => '2026-06-01', 'departure' => '2026-06-03', 'circuito' => 'schedina', 'is_arrive' => false]);
        }
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $foreignProjection = Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->where('name', 'Riservata')->sole();
        $this->post(route('proprietario.clienti-condivisione.separate', $group), [
            'struttura_id' => $b->id, 'version' => 1, 'conferma' => '1', 'consegna' => '0',
        ])->assertRedirect(route('proprietario.clienti-condivisione.index'));
        $this->assertNull($sharing->groupFor($b->id));
        $this->assertSame(0, DB::table('customer_sharing_packages')->count());
        $this->assertSame('Nota solo B', $onlyB->fresh()->observation);
        $this->assertSame('SOLO-B', $onlyB->fresh()->numero_cliente);
        $this->assertSame(2, $sharing->visibleQuery(Customers::withoutGlobalScopes())->where('struttura_id', $b->id)->count());
        $this->assertSame(3, $sharing->uniqueQuery(Customers::withoutGlobalScopes()->where('struttura_id', $a->id), $a->id)->count());
        $this->assertSame(2, DB::table('schedina')->count());
        $this->actingAs($this->actor('struttura_user', $user->proprietario_id, $b->id));
        $this->withSession(['struttura_corrente_id' => $b->id])->get(route('customer.storico', $sharedB->id))
            ->assertOk()->assertSee('STORICO-B')->assertDontSee('STORICO-A');
        $this->get(route('customer.storico', $foreignProjection->id))->assertNotFound();
        $this->get(route('proprietario.clienti-condivisione.sources', $group))->assertForbidden();
        $this->get(route('customers'))->assertOk()->assertDontSee('Riservata');
        $detached = $sharedB->fresh(); $detached->email = 'locale-b@example.invalid'; $detached->save();
        $this->assertEmpty($sharedA->fresh()->email);
    }

    public function test_uscita_richiede_scelta_esplicita_del_pacchetto(): void
    {
        [$user, $a, $b, $c, $sharing] = $this->setupGroup();
        $group = $sharing->configure($user, null, 0, 'Gruppo', [$a->id, $b->id]);
        $this->postJson(route('proprietario.clienti-condivisione.separate', $group), [
            'struttura_id' => $b->id, 'version' => 1, 'conferma' => '1',
        ])->assertUnprocessable()->assertJsonValidationErrors('consegna');
        $this->assertSame([$a->id, $b->id], $sharing->members($group));
    }

}
