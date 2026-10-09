<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\Struttura;
use App\Models\TassaDiSoggiorno;
use App\Models\TassaEsenzione;
use App\Models\TassaExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class TassaBellariaConfigurazioneAutomaticaTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function hotel(): Struttura
    {
        $a = $this->structureFor(null);
        $a->update(['citta' => 'Bellaria-Igea Marina', 'tipologia_struttura' => 'Albergo', 'classificazione' => '3 stelle']);
        $this->actingAs($this->actor('struttura_user', null, $a->id));

        return $a;
    }

    private function ospite(Struttura $a): Schedina
    {
        return Schedina::forceCreate(['struttura_id' => $a->id, 'name' => 'Prova automatica', 'surname' => 'Sintetica', 'arrive' => '2026-06-10', 'departure' => '2026-06-20', 'oa_date_nac' => '1980-01-01', 'exent' => 'NO', 'is_arrive' => 0]);
    }

    public function test_profili_da_una_a_cinque_stelle_senza_configurazione_manuale_o_scritture_get(): void
    {
        $a = $this->hotel();
        $p = $this->ospite($a);
        foreach ([1 => 1, 2 => 1, 3 => 1.5, 4 => 2.5, 5 => 2.5] as $stelle => $tariffa) {
            $a->update(['classificazione' => $stelle.' '.($stelle === 1 ? 'stella' : 'stelle')]);
            $this->get('/tassa_di_soggiorno')->assertOk()->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'automatica' && (float) $d['profilo']['tassa_soggiorno'] === (float) $tariffa)
                ->assertDontSee('name="tassa_soggiorno"', false)->assertDontSee('name="inizio"', false)->assertSee('Modifica nei Dati struttura');
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] == 6 * $tariffa);
            $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 6 * $tariffa);
            $this->get('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertOk()->assertViewHas('totalePeriodo', 6 * $tariffa);
            $csv = $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->assertOk()->getContent();
            $righe = array_map(fn ($r) => str_getcsv($r, ';', '"', ''), array_slice(explode("\n", $csv), 1));
            $this->assertSame(['0', '777'], array_column($righe, 0));
            $this->assertSame(['6', '4'], array_column($righe, 6));
            $this->assertEquals(6 * $tariffa, array_sum(array_map(fn ($r) => $r[6] * $r[7], $righe)));
            $this->assertSame(0, TassaDiSoggiorno::count());
            $this->assertSame(0, TassaEsenzione::count());
        }
        $this->get('/tassa_di_soggiorno/rapporto?mese=7&anno=2026')->assertOk()->assertViewHas('totalePeriodo', 0);
        $this->getJson('/tassa_di_soggiorno/rapporto?mese=7&anno=2027')->assertUnprocessable()->assertJsonValidationErrors('regola_non_disponibile');
    }

    public function test_diagnostica_dati_struttura_mancanti_non_blocca_la_schedina(): void
    {
        foreach ([['citta', null, '', 'Comune non configurato'], ['tipologia_generale', 'tipologia_generale_id', '', 'Tipologia generale non configurata'], ['tipologia_struttura', 'tipologia_struttura_id', '', 'Tipologia struttura non configurata'], ['classificazione', 'classificazione_id', null, 'Classificazione della struttura non configurata']] as [$campo, $id, $valore, $messaggio]) {
            $a = $this->hotel();
            $data = [$campo => $valore];
            if ($id) {
                $data[$id] = null;
            }
            if ($campo === 'tipologia_generale') {
                // Lo schema storico impone un enum non nullo: la sorgente mancante
                // è simulata in memoria senza alterare schema o disabilitare SQL strict.
                Struttura::retrieved(function (Struttura $modello) use ($a, $data) {
                    if ($modello->id === $a->id) {
                        $modello->forceFill($data);
                    }
                });
            } else {
                $a->update($data);
            }
            $p = $this->ospite($a);
            $this->get('/tassa_di_soggiorno')->assertOk()->assertSee($messaggio)->assertSee('Modifica nei Dati struttura');
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertSee($messaggio)->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] === null);
            $this->getJson('/schedine/'.$p->id.'/tassa/print')->assertUnprocessable();
            $this->getJson('/tassa_di_soggiorno/rapporto/csv?mese=7&anno=2026')->assertUnprocessable();
        }
    }

    public function test_categorie_non_certificate_e_catalogo_ufficiale_non_aggirabili(): void
    {
        foreach (['RTA', 'Villaggio turistico'] as $tipo) {
            $a = $this->hotel();
            $a->update(['tipologia_struttura' => $tipo]);
            $p = $this->ospite($a);
            $this->get('/tassa_di_soggiorno')->assertOk()->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'categoria_tassa_non_certificata');
            $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] === null);
            $this->getJson('/tassa_di_soggiorno/rapporto/csv?mese=7&anno=2026')->assertUnprocessable();
            $this->putJson('/tassa_di_soggiorno', ['tassa_soggiorno' => 1.5, 'giorni_massimo' => 6])->assertUnprocessable();
            $this->assertSame(0, TassaDiSoggiorno::count());
        }
        $a = $this->hotel();
        $this->actingAs($this->actor('admin', null, $a->id));
        $this->postJson('/tassa_esenzioni', ['codice' => '999', 'descrizione' => 'Non ufficiale'])->assertForbidden();
        $this->assertSame(0, TassaEsenzione::count());
    }

    public function test_legacy_discordante_preservato_e_configurazione_vuota_derivata_senza_save(): void
    {
        $a = $this->hotel();
        $p = $this->ospite($a);
        $cfg = TassaDiSoggiorno::create(['struttura_id' => $a->id]);
        $prima = $cfg->fresh()->getAttributes();
        $this->get('/tassa_di_soggiorno')->assertOk()->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'automatica');
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 9);
        $this->assertSame($prima, $cfg->fresh()->getAttributes());
        $cfg->update(['tassa_soggiorno' => 1.5, 'giorni_massimo' => 6, 'inizio' => '2026-03-01', 'fine' => '2026-10-01']);
        $prima = $cfg->fresh()->getAttributes();
        $this->get('/tassa_di_soggiorno')->assertOk()->assertViewHas('diagnosi', fn ($d) => $d['stato'] === 'configurazione_legacy_discordante')->assertSee('Configurazione legacy conservata');
        foreach ([6, 7] as $mese) {
            $this->getJson('/tassa_di_soggiorno/rapporto/csv?mese='.$mese.'&anno=2026')->assertUnprocessable();
            $this->postJson('/tassa_di_soggiorno/rapporto/consolida', ['mese' => $mese, 'anno' => 2026])->assertUnprocessable();
        }
        $this->get('/schedine/'.$p->id.'/modifica')->assertOk()->assertViewHas('tassaDettaglio', fn ($d) => $d['totale'] === null);
        $this->assertSame($prima, $cfg->fresh()->getAttributes());
        $this->assertSame(0, TassaExport::count());
    }

    public function test_upload_formati_limite_sostituzione_rimozione_e_tenant(): void
    {
        Storage::fake('local');
        $a = $this->hotel();
        $this->ospite($a);
        foreach (['jpg', 'png', 'webp'] as $estensione) {
            $this->put('/tassa_di_soggiorno', ['ricevuta_foto' => UploadedFile::fake()->image('prova.'.$estensione)])->assertSessionHasNoErrors()->assertRedirect();
            $path = TassaDiSoggiorno::firstOrFail()->ricevuta_immagine;
            $this->assertStringStartsWith('tassa-ricevute/'.$a->id.'/', $path);
            Storage::disk('local')->assertExists($path);
            $this->get('/tassa_di_soggiorno/immagine')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        }
        $path = TassaDiSoggiorno::firstOrFail()->ricevuta_immagine;
        foreach ([UploadedFile::fake()->image('troppo.png')->size(2049), UploadedFile::fake()->create('non-immagine.txt', 10, 'text/plain'), UploadedFile::fake()->create('non-ammesso.svg', 10, 'image/svg+xml')] as $foto) {
            $this->putJson('/tassa_di_soggiorno', ['ricevuta_foto' => $foto])->assertUnprocessable()->assertJsonValidationErrors('ricevuta_foto');
            $this->assertSame($path, TassaDiSoggiorno::firstOrFail()->ricevuta_immagine);
        }
        $csv = $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->getContent();
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertRedirect();
        $export = TassaExport::firstOrFail();
        $this->put('/tassa_di_soggiorno', ['ricevuta_foto' => UploadedFile::fake()->image('sostituzione.png')])->assertRedirect();
        $this->assertNotSame($path, TassaDiSoggiorno::firstOrFail()->ricevuta_immagine);
        Storage::disk('local')->assertExists($path);
        $this->put('/tassa_di_soggiorno', ['ricevuta_senza_immagine' => 1])->assertRedirect();
        $this->assertNull(TassaDiSoggiorno::firstOrFail()->ricevuta_immagine);
        $this->get('/tassa_di_soggiorno/immagine')->assertNotFound();
        $this->get('/tassa_di_soggiorno/immagine?export_id='.$export->id)->assertOk();
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/rapporto/csv?mese=6&anno=2026')->getContent());
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/export/'.$export->id)->getContent());
        $b = $this->hotel();
        $this->get('/tassa_di_soggiorno/immagine?struttura_id='.$a->id)->assertNotFound();
        $this->get('/tassa_di_soggiorno/immagine?export_id='.$export->id)->assertNotFound();
    }

    public function test_storico_versiona_regola_e_non_rivaluta_categoria_corrente(): void
    {
        $a = $this->hotel();
        $p = $this->ospite($a);
        $this->post('/tassa_di_soggiorno/rapporto/consolida', ['mese' => 6, 'anno' => 2026])->assertRedirect();
        $e = TassaExport::firstOrFail();
        $this->assertSame('bellaria-alberghi-2026-v1', $e->snapshot['configurazione']['regola_versione']);
        $this->assertSame('3 stelle', $e->snapshot['configurazione']['regola_categoria']);
        $csv = $e->snapshot['csv'];
        $a->update(['classificazione' => '4 stelle']);
        $this->get('/schedine/'.$p->id.'/tassa/print')->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 15);
        $this->get('/schedine/'.$p->id.'/tassa/print?export_id='.$e->id)->assertOk()->assertViewHas('dettaglio', fn ($d) => $d['totale'] == 9);
        $this->assertSame($csv, $this->get('/tassa_di_soggiorno/export/'.$e->id)->getContent());
        $a->update(['classificazione' => null, 'classificazione_id' => null]);
        $this->get('/tassa_di_soggiorno/rapporto?export_id='.$e->id)->assertOk();
        $this->getJson('/tassa_di_soggiorno/rapporto?mese=6&anno=2026')->assertUnprocessable();
    }
}
