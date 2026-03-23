<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Struttura;

class StrutturaSeeder extends Seeder
{
    public function run(): void
    {
        Struttura::firstOrCreate(
            ['cir' => 'CIR-123456-K2'],
            [
                'logo'               => 'uploads/loghi/hotel-k2.png',
                'nome_struttura'     => 'Hotel K2',
                'tipologia_generale' => 'Alberghiera',
                'tipologia_struttura'=> 'Hotel',
                'classificazione'    => '3 stelle superior',
                'tipo_apertura'      => 'Stagionale',
                'data_apertura'      => '2024-05-15',
                'data_chiusura'      => '2024-09-30',
                'nazione'            => 'Italia',
                'regione'            => 'Emilia-Romagna',
                'provincia'          => 'RN',
                'città'              => 'Bellaria Igea Marina',
                'logo_città'         => 'uploads/loghi_citta/bellaria-igea-marina.png',
                'zona'               => 'Mare',
                'località'           => 'Igea Marina',
                'indirizzo'          => 'Viale Pinzon',
                'numero_civico'      => '212',
                'cap'                => '47814',
                'latitudine'         => 44.1375000,
                'longitudine'        => 12.4740000,
                'ragione_sociale'    => 'Hotel K2 S.r.l.',
                'partita_iva'        => '01234560987',
                'codice_fiscale'     => '01234560987',
                'cin'                => 'CIN-K2-001',
                'codice_unico'       => 'ABC1234',
                'camere_disponibili' => 63,
                'letti_disponibili'  => 124,
                'letti_agg'          => 35,
                'istat_username'     => 'HOTELK2_ISTAT',
                'istat_password'     => 'istat_password_demo',
                'questura_username'  => 'HOTELK2_QUESTURA',
                'questura_password'  => 'questura_password_demo',
                'telefono'           => '0542 330064',
                'telefono_secondario'=> '0542 330065',
                'fax'                => '0542 330066',
                'email'              => 'info@hotelk2.it',
                'sito_web'           => 'https://www.hotelk2.it',
            ]
        );
    }
}
