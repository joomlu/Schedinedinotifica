<?php

namespace Tests\Unit;

use App\Support\Componenti\TipoAlloggiatoCatalogo;
use PHPUnit\Framework\TestCase;

class TipoAlloggiatoCatalogoTest extends TestCase
{
    public function test_catalogo_classifica_capo_e_componenti_per_codice_indipendente_dalle_label(): void
    {
        $catalogo = [
            ['codice' => '16', 'descrizione' => 'DESCR_A'],
            ['codice' => '17', 'descrizione' => 'DESCR_B'],
            ['codice' => '18', 'descrizione' => 'DESCR_C'],
            ['codice' => '19', 'descrizione' => 'DESCR_D'],
            ['codice' => '20', 'descrizione' => 'DESCR_E'],
        ];

        $opzioni = TipoAlloggiatoCatalogo::opzioniCapoSchedina(fn () => $catalogo);
        $codici = array_values(array_map(fn ($item) => (int) $item['codice'], $opzioni));

        $this->assertSame([16, 17, 18], $codici);
    }

    public function test_catalogo_classifica_componenti_per_codice_19_20_indipendente_dalle_label(): void
    {
        $catalogo = [
            ['codice' => '16', 'descrizione' => 'ALFA'],
            ['codice' => '17', 'descrizione' => 'BETA'],
            ['codice' => '18', 'descrizione' => 'GAMMA'],
            ['codice' => '19', 'descrizione' => 'DELTA'],
            ['codice' => '20', 'descrizione' => 'EPSILON'],
        ];

        $opzioni = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo);
        $codici = array_values(array_map(fn ($item) => (int) $item['codice'], $opzioni));

        $this->assertSame([19, 20], $codici);
    }

    public function test_codice_16_valido_solo_per_capo_schedina(): void
    {
        $catalogo = $this->catalogoFixture();

        $capo = TipoAlloggiatoCatalogo::opzioniCapoSchedina(fn () => $catalogo);
        $opzioniComponente = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo);
        $codiciCapo = array_fill_keys(array_map(fn ($item) => (int) $item['codice'], $capo), true);
        $codiciComp = array_fill_keys(array_map(fn ($item) => (int) $item['codice'], $opzioniComponente), true);

        $this->assertArrayHasKey(16, $codiciCapo);
        $this->assertArrayNotHasKey(16, $codiciComp);
    }

    public function test_codice_19_valido_solo_per_componenti(): void
    {
        $catalogo = $this->catalogoFixture();

        $capo = TipoAlloggiatoCatalogo::opzioniCapoSchedina(fn () => $catalogo);
        $opzioniComponente = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo);
        $codiciCapo = array_fill_keys(array_map(fn ($item) => (int) $item['codice'], $capo), true);
        $codiciComp = array_fill_keys(array_map(fn ($item) => (int) $item['codice'], $opzioniComponente), true);

        $this->assertArrayNotHasKey(19, $codiciCapo);
        $this->assertArrayHasKey(19, $codiciComp);
    }

    public function test_matcher_compatibile_con_relationship_numerico_storico(): void
    {
        $catalogo = $this->catalogoFixture();
        $opzione20 = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo)[1];

        $this->assertTrue(TipoAlloggiatoCatalogo::valoreCompatibileConOpzione('20', $opzione20));
        $this->assertTrue(TipoAlloggiatoCatalogo::valoreCompatibileConOpzione(20, $opzione20));
    }

    public function test_matcher_compatibile_con_relationship_testuale_storico(): void
    {
        $catalogo = $this->catalogoFixture();
        $opzione20 = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo)[1];

        $this->assertTrue(TipoAlloggiatoCatalogo::valoreCompatibileConOpzione('MEMBRO GRUPPO', $opzione20));
    }

    public function test_classificazione_non_richiede_mapping_label_hardcoded(): void
    {
        $catalogo = [
            ['codice' => '16', 'descrizione' => 'X1'],
            ['codice' => '17', 'descrizione' => 'X2'],
            ['codice' => '18', 'descrizione' => 'X3'],
            ['codice' => '19', 'descrizione' => 'Y1'],
            ['codice' => '20', 'descrizione' => 'Y2'],
        ];

        $capo = TipoAlloggiatoCatalogo::opzioniCapoSchedina(fn () => $catalogo);
        $comp = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo);

        $this->assertSame([16, 17, 18], array_values(array_map(fn ($item) => (int) $item['codice'], $capo)));
        $this->assertSame([19, 20], array_values(array_map(fn ($item) => (int) $item['codice'], $comp)));
    }

    public function test_select_componente_non_contiene_codici_capo_schedina(): void
    {
        $catalogo = $this->catalogoFixture();
        $opzioniComponente = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo);
        $codici = array_fill_keys(array_map(fn ($item) => (int) $item['codice'], $opzioniComponente), true);

        $this->assertArrayNotHasKey(16, $codici);
        $this->assertArrayNotHasKey(17, $codici);
        $this->assertArrayNotHasKey(18, $codici);
    }

    public function test_select_capo_schedina_non_contiene_codici_componente(): void
    {
        $catalogo = $this->catalogoFixture();
        $opzioniCapo = TipoAlloggiatoCatalogo::opzioniCapoSchedina(fn () => $catalogo);
        $codici = array_fill_keys(array_map(fn ($item) => (int) $item['codice'], $opzioniCapo), true);

        $this->assertArrayNotHasKey(19, $codici);
        $this->assertArrayNotHasKey(20, $codici);
    }

    public function test_select_componente_non_contiene_tipologie_capo_schedina(): void
    {
        $catalogo = $this->catalogoFixture();
        $opzioniComponente = TipoAlloggiatoCatalogo::opzioniComponente(fn () => $catalogo);
        $descrizioni = array_fill_keys(array_map(fn ($item) => $item['descrizione'], $opzioniComponente), true);

        $this->assertArrayNotHasKey('OSPITE SINGOLO', $descrizioni);
        $this->assertArrayNotHasKey('CAPO FAMIGLIA', $descrizioni);
        $this->assertArrayNotHasKey('CAPO GRUPPO', $descrizioni);
    }

    public function test_select_capo_schedina_non_contiene_tipologie_componente(): void
    {
        $catalogo = $this->catalogoFixture();
        $opzioniCapo = TipoAlloggiatoCatalogo::opzioniCapoSchedina(fn () => $catalogo);
        $descrizioni = array_fill_keys(array_map(fn ($item) => $item['descrizione'], $opzioniCapo), true);

        $this->assertArrayNotHasKey('FAMILIARE', $descrizioni);
        $this->assertArrayNotHasKey('MEMBRO GRUPPO', $descrizioni);
    }

    private function catalogoFixture(): array
    {
        return [
            ['codice' => '16', 'descrizione' => 'OSPITE SINGOLO'],
            ['codice' => '17', 'descrizione' => 'CAPO FAMIGLIA'],
            ['codice' => '18', 'descrizione' => 'CAPO GRUPPO'],
            ['codice' => '19', 'descrizione' => 'FAMILIARE'],
            ['codice' => '20', 'descrizione' => 'MEMBRO GRUPPO'],
        ];
    }
}
