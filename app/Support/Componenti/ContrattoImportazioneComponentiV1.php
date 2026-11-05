<?php

namespace App\Support\Componenti;

final class ContrattoImportazioneComponentiV1
{
    /**
     * Contratto semplificato per il modello cliente: intestazioni in italiano,
     * senza campi tecnici inutili e con compatibilità con i vecchi alias legacy.
     */
    public const COLONNE_FILE = [
        'name' => 'Nome',
        'surname' => 'Cognome',
        'sex' => 'Sesso',
        'country_nac' => 'Nazione nascita',
        'date_nac' => 'Data di nascita',
        'province_nac' => 'Provincia nascita',
        'comune_nac' => 'Comune nascita',
        'city_nac' => 'Cittadinanza',
        'country' => 'Nazione residenza',
        'province' => 'Provincia residenza',
        'city' => 'Comune residenza',
        'typeaway' => 'Tipo via',
        'address' => 'Indirizzo',
        'number' => 'Numero civico',
        'cap' => 'CAP',
    ];

    /**
     * Aliases compatibili con i file già generati dal sistema precedente.
     *
     * @return array<string, array<int, string>>
     */
    public static function aliasIntestazioni(): array
    {
        return [
            'name' => ['Nome', 'nome'],
            'surname' => ['Cognome', 'cognome'],
            'sex' => ['Sesso', 'sesso'],
            'country_nac' => ['Nazione nascita', 'nazione nascita', 'Nazione di nascita'],
            'date_nac' => ['Data di nascita', 'data di nascita', 'Data nascita'],
            'province_nac' => ['Provincia nascita', 'provincia nascita'],
            'comune_nac' => ['Comune nascita', 'Comune di nascita', 'Citta nascita', 'Città nascita'],
            'city_nac' => ['Cittadinanza', 'cittadinanza'],
            'country' => ['Nazione residenza', 'nazione residenza'],
            'province' => ['Provincia residenza', 'provincia residenza'],
            'city' => ['Comune residenza', 'Comune di residenza', 'Citta residenza', 'Città residenza'],
            'typeaway' => ['Tipo via', 'tipo via', 'Tipo via componente'],
            'address' => ['Indirizzo', 'indirizzo', 'Strada', 'strada'],
            'number' => ['Numero civico', 'numero civico', 'Num', 'num', 'Civico', 'civico'],
            'cap' => ['CAP', 'cap', 'CAP residenza', 'Cap residenza'],
        ];
    }

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
