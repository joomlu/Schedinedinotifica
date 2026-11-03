<?php

namespace Tests\Feature;

use App\Models\{CestinoItem, Customers, WebCheckinRichiesta, Schedina, TipoDocumento, User};
use App\Services\CestinoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class CestinoGestioneTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function context(): array
    {
        $owner = $this->ownerFor($this->actor('admin'));
        $mine = $this->structureFor($owner);
        $other = $this->structureFor($owner);
        $this->actingAs($this->actor('struttura_user', $owner->id, $mine->id))
            ->withSession(['struttura_corrente_id' => $mine->id]);
        return [$mine, $other];
    }

    private function archive(int $structure): CestinoItem
    {
        $customer = Customers::forceCreate(['struttura_id' => $structure, 'name' => 'Ospite sintetico', 'surname' => 'Prova']);
        $item = app(CestinoService::class)->archiveModel($customer);
        $customer->delete();
        return $item;
    }

    public function test_dettaglio_leggibile_senza_snapshot_tecnico_o_segreti_e_con_escaping(): void
    {
        [$mine] = $this->context();
        $item = $this->archive($mine->id);
        $item->update(['payload' => ['name' => '<script>SEGNALE</script>', 'surname' => 'Prova', 'password' => 'SEGRETO-SINTETICO', 'token' => 'TOKEN-SINTETICO']]);
        $this->get('/cestino')->assertOk()->assertSee('Dati dell’elemento eliminato')->assertSee('Nome')->assertSee('<script>SEGNALE</script>')
            ->assertDontSee('<script>SEGNALE</script>', false)->assertDontSee('SEGRETO-SINTETICO')->assertDontSee('TOKEN-SINTETICO')->assertDontSee('Snapshot archivio')->assertDontSee('<pre', false);
    }

    public function test_selezione_atomica_confermata_e_limitata_al_tenant(): void
    {
        [$mine, $other] = $this->context();
        $a = $this->archive($mine->id); $b = $this->archive($mine->id); $foreign = $this->archive($other->id);
        $this->delete('/cestino/selezionati', ['ids' => [$a->id, $foreign->id], 'conferma' => 1])->assertNotFound();
        $this->assertDatabaseHas('cestino_items', ['id' => $a->id]);
        $this->delete('/cestino/selezionati', ['ids' => [$a->id]])->assertSessionHasErrors('conferma');
        $this->delete('/cestino/selezionati', ['ids' => [$a->id, $b->id], 'conferma' => 1])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseMissing('cestino_items', ['id' => $a->id]);
        $this->assertDatabaseMissing('cestino_items', ['id' => $b->id]);
        $this->assertDatabaseHas('cestino_items', ['id' => $foreign->id]);
    }

    public function test_svuotamento_comprende_tutte_le_pagine_ma_non_altri_hotel(): void
    {
        [$mine, $other] = $this->context();
        for ($i = 0; $i < 13; $i++) $this->archive($mine->id);
        $foreign = $this->archive($other->id);
        $this->get('/cestino?per_page=25')->assertOk()->assertViewHas('items', fn ($items) => $items->count() === 13);
        $this->delete('/cestino/svuota', ['conferma' => 1])->assertRedirect(route('cestino.index'));
        $this->assertSame(0, CestinoItem::where('struttura_id', $mine->id)->count());
        $this->assertDatabaseHas('cestino_items', ['id' => $foreign->id]);
    }

    public function test_web_checkin_eliminabile_senza_cancellare_la_schedina_attiva(): void
    {
        [$mine] = $this->context();
        $sheet = Schedina::forceCreate(['struttura_id' => $mine->id, 'name' => 'Sintetico', 'circuito' => 'schedina']);
        $item = CestinoItem::create(['struttura_id' => $mine->id, 'entity_class' => WebCheckinRichiesta::class, 'entity_type' => 'Web Check-in', 'original_id' => 999999, 'title' => 'Pratica archiviata', 'payload' => ['schedina_id' => $sheet->id], 'deleted_at' => now()]);
        $communication = \App\Models\WebCheckinComunicazione::create(['struttura_id' => $mine->id, 'richiesta_id' => 999999, 'utente_id' => auth()->id(),
            'idempotenza' => \Illuminate\Support\Str::uuid(), 'canale' => 'testo', 'stato' => 'preparato', 'contenuto' => ['corpo' => 'Solo sintetico']]);
        $this->delete('/cestino/'.$item->id)->assertRedirect();
        $this->assertDatabaseMissing('cestino_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('web_checkin_comunicazioni', ['id' => $communication->id]);
        $this->assertDatabaseHas('schedina', ['id' => $sheet->id]);
    }

    public function test_cataloghi_di_sistema_e_record_originali_attivi_non_vengono_eliminati(): void
    {
        [$mine] = $this->context();
        $this->actingAs($this->actor('super_admin'));
        $protected = CestinoItem::create(['entity_class' => TipoDocumento::class, 'entity_type' => 'Tipo Documento', 'title' => 'Catalogo protetto', 'payload' => [], 'deleted_at' => now()]);
        $mineItem = $this->archive($mine->id);
        $this->delete('/cestino/svuota', ['conferma' => 1])->assertRedirect();
        $this->assertDatabaseHas('cestino_items', ['id' => $protected->id]);
        $this->assertDatabaseMissing('cestino_items', ['id' => $mineItem->id]);
        $user = $this->actor('admin');
        $active = CestinoItem::create(['entity_class' => User::class, 'entity_type' => 'Amministratore', 'original_id' => $user->id, 'payload' => [], 'deleted_at' => now()]);
        $this->delete('/cestino/'.$active->id)->assertStatus(409);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('cestino_items', ['id' => $active->id]);
    }

    public function test_ripristino_e_validazione_selezioni_restano_operativi(): void
    {
        [$mine] = $this->context(); $item = $this->archive($mine->id);
        $this->delete('/cestino/selezionati', ['ids' => [$item->id, $item->id], 'conferma' => 1])->assertSessionHasErrors('ids.0');
        $this->post('/cestino/'.$item->id.'/ripristina')->assertRedirect();
        $this->assertDatabaseMissing('cestino_items', ['id' => $item->id]);
        $this->assertDatabaseHas('clienti', ['struttura_id' => $mine->id, 'name' => 'Ospite sintetico']);
    }
    public function test_date_archiviate_utc_vengono_presentate_nel_giorno_locale_corretto(): void
    {
        [$mine] = $this->context();
        $item = CestinoItem::create(['struttura_id' => $mine->id, 'entity_class' => WebCheckinRichiesta::class,
            'entity_type' => 'Web Check-in', 'title' => 'Data sintetica',
            'payload' => ['arrivo' => '2026-10-15T22:00:00.000000Z', 'partenza' => '2026-10-23T22:00:00.000000Z'], 'deleted_at' => now()]);
        $rows = app(\App\Services\CestinoDettaglio::class)->rows($item);
        $this->assertSame('16/10/2026', $rows[0]['valore']);
        $this->assertSame('24/10/2026', $rows[1]['valore']);
    }

    public function test_snapshot_eliminato_non_puo_essere_ripristinato_da_un_oggetto_obsoleto(): void
    {
        [$mine] = $this->context();
        $item = $this->archive($mine->id);
        app(CestinoService::class)->purgeItem($item);
        try {
            app(CestinoService::class)->restoreItem($item);
            $this->fail('Un archivio eliminato non deve essere ripristinato.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->assertDatabaseMissing('cestino_items', ['id' => $item->id]);
            $this->assertDatabaseMissing('clienti', ['id' => $item->original_id]);
        }
    }

}
