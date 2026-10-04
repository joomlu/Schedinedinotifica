<?php

namespace Tests\Unit;

use App\Support\Componenti\ContrattoImportazioneComponentiV1;
use App\Support\Componenti\DatiComponenteNormalizzati;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ContrattoImportazioneComponentiV1Test extends TestCase
{
    public function test_contratto_importazione_usa_15_colonne_canoniche_in_ordine_esatto(): void
    {
        $colonne = ContrattoImportazioneComponentiV1::colonneTemplate();

        $this->assertCount(15, $colonne);
        $this->assertSame([
            'name',
            'surname',
            'sex',
            'country_nac',
            'date_nac',
            'province_nac',
            'comune_nac',
            'city_nac',
            'country',
            'province',
            'city',
            'typeaway',
            'address',
            'number',
            'cap',
        ], array_keys($colonne));
        $this->assertSame([
            'Nome',
            'Cognome',
            'Sesso',
            'Nazione nascita',
            'Data di nascita',
            'Provincia nascita',
            'Comune nascita',
            'Cittadinanza',
            'Nazione residenza',
            'Provincia residenza',
            'Comune residenza',
            'Tipo via',
            'Indirizzo',
            'Numero civico',
            'CAP',
        ], array_values($colonne));
    }

    public function test_contratto_importazione_non_contiene_colonna_tipo_alloggiato(): void
    {
        $colonne = ContrattoImportazioneComponentiV1::colonneTemplate();

        $this->assertArrayNotHasKey('relationship', $colonne);
        $this->assertNotContains('Tipo alloggiato', array_values($colonne));
    }

    public function test_contratto_importazione_non_contiene_colonna_esente(): void
    {
        $colonne = ContrattoImportazioneComponentiV1::colonneTemplate();

        $this->assertArrayNotHasKey('exent', $colonne);
        $this->assertNotContains('Esente', array_values($colonne));
    }

    public function test_default_import_tipo_alloggiato_usa_voce_reale_esistente(): void
    {
        $row = ContrattoImportazioneComponentiV1::applicaDefaultImport([], fn () => $this->catalogoFixture());
        $meta = ContrattoImportazioneComponentiV1::metadataDefaultImport(fn () => $this->catalogoFixture());

        $this->assertSame('MEMBRO GRUPPO', $row['relationship']);
        $this->assertSame('20', $meta['relationship_codice']);
        $this->assertSame('MEMBRO GRUPPO', $meta['relationship_descrizione']);
    }

    public function test_default_import_identifica_codice_20_anche_con_descrizione_rinominata(): void
    {
        $catalogo = [
            ['codice' => '16', 'descrizione' => 'DESCR_A'],
            ['codice' => '17', 'descrizione' => 'DESCR_B'],
            ['codice' => '18', 'descrizione' => 'DESCR_C'],
            ['codice' => '19', 'descrizione' => 'DESCR_D'],
            ['codice' => '20', 'descrizione' => 'DESCRIZIONE CAMBIATA'],
        ];

        $row = ContrattoImportazioneComponentiV1::applicaDefaultImport([], fn () => $catalogo);
        $meta = ContrattoImportazioneComponentiV1::metadataDefaultImport(fn () => $catalogo);

        $this->assertSame('DESCRIZIONE CAMBIATA', $row['relationship']);
        $this->assertSame('20', $meta['relationship_codice']);
        $this->assertSame('DESCRIZIONE CAMBIATA', $meta['relationship_descrizione']);
    }

    public function test_default_import_fallisce_esplicitamente_se_codice_20_manca(): void
    {
        $catalogo = [
            ['codice' => '16', 'descrizione' => 'DESCR_A'],
            ['codice' => '17', 'descrizione' => 'DESCR_B'],
            ['codice' => '18', 'descrizione' => 'DESCR_C'],
            ['codice' => '19', 'descrizione' => 'DESCR_D'],
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('codice 20');

        ContrattoImportazioneComponentiV1::applicaDefaultImport([], fn () => $catalogo);
    }

    public function test_default_import_esente_usa_rappresentazione_interna_esistente(): void
    {
        $row = ContrattoImportazioneComponentiV1::applicaDefaultImport([], fn () => $this->catalogoFixture());

        $this->assertSame('NO', $row['exent']);
    }

    public function test_file_non_richiede_codice_questura_per_tipo_alloggiato(): void
    {
        $colonne = ContrattoImportazioneComponentiV1::colonneTemplate();
        $headers = array_map('strtolower', array_values($colonne));

        $this->assertFalse(in_array('codice tipo alloggiato', $headers, true));
        $this->assertFalse(in_array('tipo alloggiato codice', $headers, true));
        $this->assertArrayNotHasKey('relationship_code', $colonne);
    }

    public function test_componente_importato_resta_modificabile_come_componente_normale(): void
    {
        $row = ContrattoImportazioneComponentiV1::applicaDefaultImport([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'sex' => 'M',
            'city_nac' => 'Italiana',
            'country_nac' => 'Italia',
            'date_nac' => '02/10/2026',
            'province_nac' => 'RN',
            'comune_nac' => 'Rimini',
        ], fn () => $this->catalogoFixture());

        $row['relationship'] = 'FAMILIARE';
        $row['exent'] = '400';

        $normalizzate = DatiComponenteNormalizzati::normalizzaRighe([$row]);

        $this->assertSame('FAMILIARE', $normalizzate[0]['relationship']);
        $this->assertSame('400', $normalizzate[0]['exent']);
        $this->assertArrayNotHasKey('import_lock', $normalizzate[0]);
        $this->assertArrayNotHasKey('import_only', $normalizzate[0]);
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
