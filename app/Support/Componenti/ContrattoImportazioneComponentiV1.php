<?php

namespace App\Support\Componenti;

final class ContrattoImportazioneComponentiV1
{
    /**
     * Contratto colonne V1 per file import componenti (senza Tipo alloggiato / Esente).
     */
    public const COLONNE_FILE = [
        'name' => 'Nome',
        'surname' => 'Cognome',
        'sex' => 'Sesso',
        'city_nac' => 'Cittadinanza',
        'country_nac' => 'Nazione nascita',
        'date_nac' => 'Data di nascita',
        'province_nac' => 'Provincia nascita',
        'comune_nac' => 'Citta nascita',
        'regione_nac' => 'Regione nascita',
        'cap_nac' => 'CAP nascita',
        'country' => 'Nazione residenza',
        'regione' => 'Regione residenza',
        'province' => 'Provincia residenza',
        'city' => 'Citta residenza',
        'typeaway' => 'Tipo via',
        'address' => 'Strada',
        'number' => 'Num',
        'cap' => 'CAP residenza',
    ];

    public const DEFAULT_EXENT = 'NO';

    private function __construct()
    {
    }

    public static function colonneTemplate(): array
    {
        return self::COLONNE_FILE;
    }

    public static function headersTemplate(): array
    {
        return array_values(self::COLONNE_FILE);
    }

    public static function applicaDefaultImport(array $row, ?callable $tipoAlloggiatoResolver = null): array
    {
        $voceDefault = TipoAlloggiatoCatalogo::voceDefaultComponente($tipoAlloggiatoResolver);

        // I default usano la stessa rappresentazione interna dei componenti manuali.
        $row['relationship'] = (string) $voceDefault['descrizione'];
        $row['exent'] = self::DEFAULT_EXENT;

        return $row;
    }

    public static function metadataDefaultImport(?callable $tipoAlloggiatoResolver = null): array
    {
        $voceDefault = TipoAlloggiatoCatalogo::voceDefaultComponente($tipoAlloggiatoResolver);

        return [
            'relationship_codice' => $voceDefault['codice'] ?? null,
            'relationship_descrizione' => (string) $voceDefault['descrizione'],
            'exent' => self::DEFAULT_EXENT,
        ];
    }
}
