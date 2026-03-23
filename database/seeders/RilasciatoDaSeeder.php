<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RilasciatoDaSeeder extends Seeder
{
    public function run(): void
    {
        $autorita = [
            // Identita civile
            'Comune',
            'Comune Capoluogo',
            'Unione di Comuni',
            'Comunita Montana',
            'Municipio',
            'Delegazione Comunale',
            'Ufficio Anagrafe',
            'Ufficio Stato Civile',
            'Anagrafe Centrale',
            'Anagrafe Circoscrizione',

            // Polizia
            'Questura',
            'Polizia di Stato',
            'Carabinieri',
            'Guardia di Finanza',
            'Prefettura',
            'Polizia Municipale',
            'Polizia Locale',
            'Polizia Provinciale',
            'Polizia Stradale',
            'Polizia Ferroviaria',
            'Polizia Postale',
            'Guardia Costiera',
            'Capitaneria di Porto',
            'Vigili del Fuoco',

            // Documenti stradali
            'Motorizzazione Civile',
            'Ufficio Provinciale Motorizzazione',
            'Ministero dei Trasporti',
            'Ufficio Patenti',
            'Ufficio Revisione Veicoli',
            'Centro MCTC',
            'ACI',

            // Documenti internazionali
            'Consolato',
            'Consolato Italiano',
            'Consolato Straniero',
            'Ambasciata',
            'Ambasciata Italiana',
            'Ambasciata Straniera',
            'Ufficio Passaporti',

            // Ministeri
            "Ministero dell'Interno",
            "Ministero degli Esteri",
            "Ministero dei Trasporti",
            "Ministero della Difesa",
            "Ministero della Giustizia",
            "Ministero della Salute",
            "Ministero del Lavoro",
            "Ministero dell'Economia",
            "Ministero dell'Istruzione",

            // Altri enti
            'INPS',
            'INAIL',
            'Agenzia delle Entrate',
            'Agenzia delle Dogane',
            'Agenzia delle Dogane e Monopoli',
            'Camera di Commercio',
            'Registro Imprese',
            'Catasto',
            'Agenzia del Territorio',
            'Autorita Giudiziaria',
            'Tribunale',
            'Procura della Repubblica',
            'Corte di Appello',
            'Giudice di Pace',
            'Corte dei Conti',
            'TAR',
            'Autorita Portuale',
            'Autorita Aeroportuale',
            'Azienda Ospedaliera',
            'ASL',
            'ATS',
            'ARPA',
            'Protezione Civile',
            'Rete Ferroviaria Italiana',
            'Anas',
            'Poste Italiane',
            'Dogana di Frontiera',
        ];

        foreach ($autorita as $nome) {
            \App\Models\RilasciatoDa::updateOrCreate(
                ['name' => $nome],
                ['attivo' => true]
            );
        }
    }
}
