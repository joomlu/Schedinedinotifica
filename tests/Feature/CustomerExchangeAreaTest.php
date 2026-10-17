<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Models\CustomerImportBatch;
use App\Models\CustomerImportRow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerExchangeAreaTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'IT'], ['nome' => 'Italia', 'cittadinanza' => 'Italiana', 'is_italia' => true]);
    }


    public function test_filtri_contatori_export_completo_consensi_e_ritorno(): void
    {
        $s = $this->structureFor(null);
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin'))->withSession(['struttura_corrente_id' => $s->id]);
        foreach (range(1, 18) as $i) Customers::forceCreate(['struttura_id' => $s->id, 'name' => sprintf('Scambio%02d', $i), 'surname' => 'Sintetico', 'email' => 'persona'.$i.'@example.test', 'marketing_consent' => $i <= 2]);
        Customers::forceCreate(['struttura_id' => $other->id, 'name' => 'ScambioSegreto', 'email' => 'segreto@example.test']);
        $this->get('/clienti/liste-export?q=Scambio&page=2')->assertOk()->assertViewHas('totaleFiltrati', 18)->assertViewHas('totaleConEmail', 18)->assertViewHas('totaleMarketing', 2)->assertSee('Scambio18')->assertDontSee('ScambioSegreto');
        $this->assertSame('export', session('clienti_origine.'.$s->id));
        $this->assertSame('2', session('clienti_export.'.$s->id.'.page'));
        $first = Customers::where('struttura_id', $s->id)->firstOrFail();
        $this->get('/clienti/'.$first->id.'/modifica')->assertOk()->assertSee('/clienti/liste-export?q=Scambio&amp;page=2', false);
        $csv = $this->get('/clienti/liste-export/csv?q=Scambio&page=2')->assertOk()->getContent();
        $this->assertCount(19, explode("\n", $csv));
        $this->assertStringNotContainsString('ScambioSegreto', $csv);
        $emails = $this->get('/clienti/liste-export/csv?q=Scambio&mode=email')->assertOk()->getContent();
        $this->assertCount(3, explode("\n", $emails));
        $this->get('/clienti/liste-export?q=Assente')->assertOk()->assertViewHas('totaleFiltrati', 0)->assertSee('Nessun cliente trovato');
        $empty = $this->get('/clienti/liste-export/csv?q=VerificaCsvZeroAssente&mode=general')->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="clienti_export_generale.csv"');
        $this->assertSame('"Codice","Nome","Cognome","Tipo cliente","Nazione","Citta","Gruppo I","Gruppo II","Gruppo III","Email","Telefono","Cellulare","Privacy","Marketing","Comunicazioni","Soggiorni","Ultimo arrivo"', $empty->getContent());
        $this->getJson('/clienti/liste-export?q[]=x')->assertUnprocessable();
    }

    public function test_solo_filtri_visibili_ricerca_tipo_e_stato_legacy_ignorato(): void
    {
        $s = $this->structureFor(null);
        $other = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin'))->withSession(['struttura_corrente_id' => $s->id]);
        foreach (['Ospite', 'Componente', 'Richiesta'] as $type) {
            Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'RicercaNome', 'surname' => 'RicercaCognome', 'email' => 'ricerca@example.test', 'phone' => '051987654', 'cellphone' => '339876543', 'type_housed' => $type]);
        }
        Customers::forceCreate(['struttura_id' => $other->id, 'name' => 'RicercaNome', 'surname' => 'Estraneo', 'type_housed' => 'Ospite']);
        foreach (['RicercaNome', 'RicercaCognome', 'ricerca@example.test', '051987654', '339876543'] as $q) {
            $filters = ['q' => $q, 'tipo_cliente' => 'Ospite'];
            $this->get('/clienti/liste-export?'.http_build_query($filters))->assertOk()->assertViewHas('totaleFiltrati', 1)->assertViewHas('totaleConEmail', 1)->assertDontSee('Estraneo');
            $csv = $this->get('/clienti/liste-export/csv?'.http_build_query($filters))->assertOk()->getContent();
            $this->assertCount(2, explode("\n", $csv));
            $this->assertStringNotContainsString('Estraneo', $csv);
        }
        $legacy = ['country' => 'INESISTENTE', 'city' => 'INESISTENTE', 'group' => 'INESISTENTE', 'subgroup' => 'INESISTENTE', 'subgroup1' => 'INESISTENTE', 'stato' => 'completo', 'privacy_consent' => '1', 'marketing_consent' => '1', 'communication_consent' => '1', 'channel' => 'email', 'has_soggiorni' => 'yes'];
        $this->withSession(['clienti_export.'.$s->id => $legacy + ['q' => 'RicercaNome']]);
        $this->get('/clienti/liste-export?'.http_build_query($legacy + ['q' => 'RicercaNome']))->assertOk()->assertViewHas('totaleFiltrati', 3)->assertDontSee('name="country"', false);
        $this->assertSame(['q' => 'RicercaNome'], session('clienti_export.'.$s->id));
        $csv = $this->get('/clienti/liste-export/csv?'.http_build_query($legacy + ['q' => 'RicercaNome']))->assertOk()->getContent();
        $this->assertCount(4, explode("\n", $csv));
        $this->withSession(['clienti_origine.'.$s->id => 'export', 'clienti_export.'.$s->id => $legacy + ['q' => 'RicercaNome', 'page' => '2']]);
        $id = Customers::where('struttura_id', $s->id)->firstOrFail()->id;
        $this->get('/clienti/'.$id.'/modifica')->assertOk()->assertSee('/clienti/liste-export?q=RicercaNome&amp;page=2', false)->assertDontSee('country=INESISTENTE', false);
        $this->get('/clienti/liste-export')->assertOk()->assertViewHas('totaleFiltrati', 3);
        $this->assertSame([], session('clienti_export.'.$s->id));
        $this->getJson('/clienti/liste-export?tipo_cliente=Inventato')->assertUnprocessable();
    }

    public function test_contatori_import_non_limitati_alla_pagina_e_isolamento_quattro_ruoli(): void
    {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $other = $this->structureFor(null);
        foreach (range(1, 12) as $i) CustomerImportBatch::forceCreate(['struttura_id' => $s->id, 'user_id' => $admin->id, 'original_name' => 'sintetico'.$i.'.csv', 'stored_path' => 'sintetico.csv', 'status' => 'draft', 'total_rows' => 3, 'valid_rows' => 2, 'needs_review_rows' => 1]);
        $foreign = CustomerImportBatch::forceCreate(['struttura_id' => $other->id, 'user_id' => $admin->id, 'original_name' => 'SEGRETO.csv', 'stored_path' => 'segreto.csv', 'status' => 'draft', 'total_rows' => 900]);
        foreach ([$this->actor('super_admin'), $admin, $this->actor('proprietario', $owner->id), $this->actor('struttura_user', null, $s->id)] as $actor) {
            $this->actingAs($actor)->withSession(['struttura_corrente_id' => $s->id]);
            $this->get('/clienti/import?page=2')->assertOk()->assertViewHas('totaleImportazioni', 12)->assertViewHas('totaleRighe', 36)->assertViewHas('totaleValide', 24)->assertViewHas('totaleDaCompletare', 12)->assertDontSee('SEGRETO.csv')->assertSee('Liste, import ed export');
            $this->get('/clienti/liste-export')->assertOk();
            $this->get('/clienti/import/'.$foreign->id)->assertNotFound();
            $this->delete('/clienti/import/'.$foreign->id)->assertNotFound();
            $this->get('/clienti/import/template')->assertOk();
        }
        $this->actingAs($this->actor('struttura_user', null, $s->id))->withSession(['struttura_corrente_id' => $other->id]);
        foreach (['/clienti/import', '/clienti/import/template', '/clienti/liste-export', '/clienti/liste-export/csv'] as $url) $this->get($url.'?sid='.$other->id)->assertForbidden();
        $this->assertDatabaseHas('customer_import_batches', ['id' => $foreign->id]);
    }
    public function test_import_condivide_regole_complete_normalizza_indirizzo_e_rivalida_conferma(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $s = $this->structureFor(null);
        $other = $this->structureFor(null);
        $actor = $this->actor('super_admin');
        $this->actingAs($actor)->withSession(['struttura_corrente_id' => $s->id]);
        \App\Models\TipoVia::withoutGlobalScopes()->firstOrCreate(['struttura_id' => $s->id, 'nome' => 'Via'], ['abbr' => 'V.', 'attivo' => true]);
        \App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'FX'], ['nome' => 'Nazione Sintetica', 'cittadinanza' => 'Sintetica', 'is_italia' => false]);
        $doc = \App\Models\TipoDocumento::firstOrCreate(['codice' => 'FX'], ['descrizione' => 'Documento Sintetico']);
        $service = app(\App\Services\CustomerImportService::class);
        $csv = "nome;cognome;tipo_cliente;email;sesso;data_nascita;nazione_nascita;cittadinanza;indirizzo_residenza\nPersona;Richiesta;Richiesta;persona@example.test;;;;;Via Roma 12/A\nPersona;Componente;Componente;;M;1986-09-12;Nazione Sintetica;Sintetica;\nPersona;Ospite;Ospite;;M;1986-09-12;Nazione Sintetica;Sintetica;\nPersona;Errata;Richiesta;non-email;;;;;Via Roma 22\n";
        $batch = $service->createBatchFromUploadedCsv(\Illuminate\Http\UploadedFile::fake()->createWithContent('completo.csv', $csv), $s, $actor);
        $rows = $batch->rows()->orderBy('id')->get();
        $this->assertSame(['valid', 'valid', 'needs_review', 'needs_review'], $rows->pluck('status')->all(), json_encode($rows->pluck('notes')->all()));
        $this->assertSame('Via', $rows[0]->normalized_payload['tipo_via_strada']);
        $this->assertSame('Roma', $rows[0]->normalized_payload['indirizzo_residenza']);
        $this->assertSame('12/A', $rows[0]->normalized_payload['numero_civico_residenza']);
        $this->assertSame('Via Roma 12/A', $rows[0]->raw_payload['indirizzo_residenza']);
        $this->postJson('/clienti/importati/'.$rows[3]->id.'/conferma')->assertUnprocessable();
        $this->assertNull($rows[3]->fresh()->imported_customer_id);
        $service->updateRowPayload($rows[2], ['tipo_documento' => $doc->descrizione, 'numero_documento' => 'FX123', 'nazione_rilascio_documento' => 'FX']);
        $this->assertSame('valid', $rows[2]->fresh()->status);
        $customer = $service->confirmImportedRow($rows[0]);
        $this->assertSame('Roma', $customer->address);
        $this->assertSame('12/A', $customer->number);
        $this->assertSame('Via', $customer->typeaway);
        $rows[1]->update(['normalized_payload' => array_replace($rows[1]->normalized_payload, ['sesso' => ''])]);
        $this->postJson('/clienti/importati/'.$rows[1]->id.'/conferma')->assertUnprocessable();
        $this->assertNull($rows[1]->fresh()->imported_customer_id);
        $this->actingAs($this->actor('struttura_user', null, $other->id))->withSession(['struttura_corrente_id' => $other->id]);
        $this->postJson('/clienti/importati/'.$rows[2]->id.'/conferma')->assertNotFound();
        $conflict = app(\App\Services\CustomerAddressNormalization::class)->normalize(['address' => 'Via Roma 12', 'number' => '14'], $s->id);
        $this->assertArrayHasKey('number', $conflict['errors']);
        $this->assertSame('14', $conflict['data']['number']);
        $unknown = app(\App\Services\CustomerAddressNormalization::class)->normalize(['address' => 'PercorsoX non catalogato 45'], $other->id);
        $this->assertSame('PercorsoX non catalogato 45', $unknown['data']['address']);
    }

    public function test_upload_sintetico_staging_duplicati_e_file_non_valido(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin'))->withSession(['struttura_corrente_id' => $s->id]);
        $before = Customers::withoutGlobalScopes()->count();
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('sintetico.csv', "nome;cognome;email;tipo_cliente\nMario;Sintetico;mario@example.test;Richiesta\nMario;Sintetico;mario@example.test;Richiesta\n;Incompleto;;Richiesta\nLucia;Unica;lucia@example.test;Richiesta\n");
        $this->post('/clienti/import', ['file_import' => $file])->assertRedirect();
        $batch = CustomerImportBatch::where('struttura_id', $s->id)->firstOrFail();
        $this->assertSame(4, $batch->rows()->count());
        $this->assertSame(2, $batch->rows()->where('status', 'duplicate_file')->count());
        $this->assertSame(1, $batch->rows()->where('status', 'non_importable')->count());
        $this->assertSame($before, Customers::withoutGlobalScopes()->count());
        $this->get('/clienti/import/'.$batch->id)->assertOk()->assertSee('Sintetico');
        $this->postJson('/clienti/import', ['file_import' => \Illuminate\Http\UploadedFile::fake()->create('non-valido.pdf', 1, 'application/pdf')])->assertUnprocessable();
        $this->assertSame(1, CustomerImportBatch::where('struttura_id', $s->id)->count());
        $this->assertSame($before, Customers::withoutGlobalScopes()->count());
        $this->post('/clienti/import/'.$batch->id.'/conferma')->assertRedirect();
        $this->assertSame($before + 1, Customers::withoutGlobalScopes()->count());
        $this->assertSame(1, $batch->rows()->where('status', 'non_importable')->whereNull('imported_customer_id')->count());
    }

    public function test_duplicati_catena_solo_nelle_strutture_autorizzate(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $s = $this->structureFor($owner);
        $sibling = $this->structureFor($owner);
        Customers::forceCreate(['struttura_id' => $sibling->id, 'name' => 'NomePrivato', 'surname' => 'Riservato', 'email' => 'catena@example.test']);
        Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Locale', 'surname' => 'Sintetico', 'email' => 'hotel@example.test']);
        $restricted = $this->actor('struttura_user', null, $s->id);
        $service = app(\App\Services\CustomerImportService::class);
        $csv = "nome;cognome;email;tipo_cliente\nPersona;Sintetica;catena@example.test;Richiesta\nLocale;Sintetico;hotel@example.test;Richiesta\n";
        foreach ([[$restricted, false], [$admin, true]] as [$actor, $visible]) {
            $this->actingAs($actor)->withSession(['struttura_corrente_id' => $s->id]);
            $batch = $service->createBatchFromUploadedCsv(\Illuminate\Http\UploadedFile::fake()->createWithContent('catena.csv', $csv), $s, $actor);
            $row = $batch->rows()->firstOrFail();
            $this->assertSame($visible ? 'duplicate_chain' : 'valid', $row->status);
            $this->assertSame($visible, $row->duplicate_customer_id !== null);
            $this->assertSame('duplicate_hotel', $batch->rows()->reorder('id', 'desc')->firstOrFail()->status);
        }
    }

    public function test_geo_incompleto_cap_univoco_e_originali_non_riconosciuti(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $s = $this->structureFor(null);
        $user = $this->actor('super_admin');
        $this->actingAs($user)->withSession(['struttura_corrente_id' => $s->id]);
        $city = \Tests\Support\GeoLogoFixtures::comune();
        $cap = \App\Models\GeoCap::create(['cap' => '99991']);
        $cap->comuni()->attach($city->id, ['principale' => true, 'priorita' => 1]);
        $csv = "nome;cognome;cap_residenza;indirizzo_residenza;comune_residenza\nPersona;Univoca;99-991;Via conservata;\nPersona;Incerta;88888;Strada originale;Comune Inventato\n";
        $batch = app(\App\Services\CustomerImportService::class)->createBatchFromUploadedCsv(\Illuminate\Http\UploadedFile::fake()->createWithContent('geo.csv', $csv), $s, $user);
        $rows = $batch->rows()->orderBy('id')->get();
        $this->assertSame('99991', $rows[0]->normalized_payload['cap_residenza']);
        $this->assertSame($city->nome, $rows[0]->normalized_payload['comune_residenza']);
        $this->assertSame('ZZ', $rows[0]->normalized_payload['provincia_residenza']);
        $this->assertSame('conservata', $rows[0]->normalized_payload['indirizzo_residenza']);
        $this->assertSame('Via', $rows[0]->normalized_payload['tipo_via_strada']);
        $this->assertSame('Comune Inventato', $rows[1]->normalized_payload['comune_residenza']);
        $this->assertSame('needs_review', $rows[1]->status);
    }

}
