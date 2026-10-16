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
        $this->getJson('/clienti/liste-export?q[]=x')->assertUnprocessable();
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
    public function test_upload_sintetico_staging_duplicati_e_file_non_valido(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('super_admin'))->withSession(['struttura_corrente_id' => $s->id]);
        $before = Customers::withoutGlobalScopes()->count();
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('sintetico.csv', "nome;cognome;email\nMario;Sintetico;mario@example.test\nMario;Sintetico;mario@example.test\n;Incompleto;\nLucia;Unica;lucia@example.test\n");
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
        $csv = "nome;cognome;email\nPersona;Sintetica;catena@example.test\nLocale;Sintetico;hotel@example.test\n";
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
        $this->assertSame('Via conservata', $rows[0]->normalized_payload['indirizzo_residenza']);
        $this->assertSame('Comune Inventato', $rows[1]->normalized_payload['comune_residenza']);
        $this->assertSame('needs_review', $rows[1]->status);
    }

}
