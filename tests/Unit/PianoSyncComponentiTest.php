<?php

namespace Tests\Unit;

use App\Support\Componenti\PianoSyncComponenti;
use PHPUnit\Framework\TestCase;

class PianoSyncComponentiTest extends TestCase
{
    public function test_componente_esistente_mantiene_lo_stesso_id_nel_piano(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '10', 'name' => 'Mario'],
        ], [10]);

        $this->assertSame([10], $piano->idMantenuti);
        $this->assertArrayHasKey(10, $piano->aggiornamenti);
        $this->assertSame([], $piano->idDaEliminare);
    }

    public function test_componente_esistente_viene_marcato_per_aggiornamento(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '10', 'name' => 'Mario aggiornato'],
        ], [10]);

        $this->assertSame('Mario aggiornato', $piano->aggiornamenti[10]['name']);
        $this->assertSame([], $piano->creazioni);
    }

    public function test_componente_nuovo_senza_id_viene_marcato_per_creazione(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['name' => 'Nuovo', 'surname' => 'Componente'],
        ], []);

        $this->assertCount(1, $piano->creazioni);
        $this->assertSame('Nuovo', $piano->creazioni[0]['name']);
    }

    public function test_componente_rimosso_viene_marcato_per_delete(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '10', 'name' => 'Mario'],
        ], [10, 11]);

        $this->assertSame([11], $piano->idDaEliminare);
    }

    public function test_due_componenti_esistenti_mantengono_i_rispettivi_id(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '10', 'name' => 'Mario'],
            ['id' => '11', 'name' => 'Giulia'],
        ], [10, 11]);

        $this->assertSame([10, 11], $piano->idMantenuti);
        $this->assertArrayHasKey(10, $piano->aggiornamenti);
        $this->assertArrayHasKey(11, $piano->aggiornamenti);
    }

    public function test_id_appartenente_ad_altra_schedina_viene_rifiutato(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '99', 'name' => 'Intruso'],
        ], [10, 11]);

        $this->assertArrayHasKey('componenti.0.id', $piano->errori);
        $this->assertSame([], $piano->aggiornamenti);
    }

    public function test_id_inesistente_viene_rifiutato(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '999', 'name' => 'Ghost'],
        ], [10, 11]);

        $this->assertArrayHasKey('componenti.0.id', $piano->errori);
        $this->assertSame([], $piano->creazioni);
    }

    public function test_submit_con_id_duplicato_viene_rifiutato(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '10', 'name' => 'Mario'],
            ['id' => '10', 'name' => 'Mario duplicato'],
        ], [10]);

        $this->assertArrayHasKey('componenti.1.id', $piano->errori);
    }

    public function test_placeholder_vuoto_non_cancella_componenti_esistenti_quando_la_guardia_e_attiva(): void
    {
        $this->assertTrue(PianoSyncComponenti::devePreservareEsistentiSuPlaceholder(
            2,
            [['id' => '', 'name' => '', 'surname' => '', 'sex' => '']],
            []
        ));
    }

    public function test_delete_all_intenzionale_disattiva_la_guardia_placeholder(): void
    {
        $this->assertFalse(PianoSyncComponenti::devePreservareEsistentiSuPlaceholder(
            2,
            [['id' => '', 'name' => '', 'surname' => '', 'sex' => '']],
            [],
            true
        ));
    }

    public function test_guardia_placeholder_non_scattano_senza_righe_raw(): void
    {
        $this->assertFalse(PianoSyncComponenti::devePreservareEsistentiSuPlaceholder(2, [], []));
    }

    public function test_schedina_vuota_con_placeholder_non_crea_componenti_nel_piano(): void
    {
        $piano = PianoSyncComponenti::costruisci([], []);

        $this->assertSame([], $piano->creazioni);
        $this->assertSame([], $piano->aggiornamenti);
        $this->assertSame([], $piano->idDaEliminare);
    }

    public function test_cross_customer_cross_struttura_restano_bloccati_da_ownership_su_schedina(): void
    {
        $piano = PianoSyncComponenti::costruisci([
            ['id' => '500', 'name' => 'Tentativo cross-tenant'],
        ], [10, 11]);

        $this->assertArrayHasKey('componenti.0.id', $piano->errori);
        $this->assertSame([], $piano->aggiornamenti);
    }

    public function test_il_form_renderizza_hidden_id_del_componente(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/resources/views/schedina/partials/form.blade.php');

        $this->assertIsString($contents);
        $this->assertStringContainsString("componenti[{{ \$index }}][id]", $contents);
        $this->assertStringContainsString('type="hidden"', $contents);
    }

    public function test_il_clone_js_azzera_l_hidden_id_prima_del_riuso(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . '/resources/views/schedina/partials/scripts.blade.php');

        $this->assertIsString($contents);
        $this->assertStringContainsString("if (field.type === 'hidden')", $contents);
        $this->assertStringContainsString("field.value = '';", $contents);
        $this->assertStringContainsString('clearComponenteRow(clone);', $contents);
    }

    public function test_la_metadata_componenti_intenzione_non_viene_persistita_nei_payload_componente(): void
    {
        $schedinaController = file_get_contents(dirname(__DIR__, 2) . '/app/Http/Controllers/SchedinaController.php');
        $arrivalsController = file_get_contents(dirname(__DIR__, 2) . '/app/Http/Controllers/ArrivalsController.php');

        $this->assertIsString($schedinaController);
        $this->assertIsString($arrivalsController);
        $this->assertStringNotContainsString("'componenti_intenzione' =>", $schedinaController);
        $this->assertStringNotContainsString("'componenti_intenzione' =>", $arrivalsController);
    }

    public function test_delete_residuale_rimane_scoped_a_schedina(): void
    {
        $schedinaController = file_get_contents(dirname(__DIR__, 2) . '/app/Http/Controllers/SchedinaController.php');
        $arrivalsController = file_get_contents(dirname(__DIR__, 2) . '/app/Http/Controllers/ArrivalsController.php');

        $this->assertIsString($schedinaController);
        $this->assertIsString($arrivalsController);
        $this->assertStringContainsString("->where('schedina_id', $schedina->id)", $schedinaController);
        $this->assertStringContainsString("->where('schedina_id', $schedina->id)", $arrivalsController);
    }
}