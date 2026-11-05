<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiunta il14ottobre2026: il prefisso ordina la preparazione PRIMA
        // della migrazione originale immutabile, senza falsificare lo storico DB.
        // Installazioni già normalizzate: nessuna DDL o modifica dei dati.
        if (!Schema::hasTable('classificazioni') || !Schema::hasColumn('classificazioni', 'tipologia_struttura_id')) {
            return;
        }
        $names = array_column(Schema::getIndexes('classificazioni'), 'name');
        $composite = 'classificazioni_tipologia_struttura_id_nome_unique';
        if (!in_array($composite, $names, true)) {
            return;
        }
        // Mantiene il supporto al FK mentre viene rimosso il vecchio unique.
        if (!in_array('classificazioni_normalizzazione_tipologia_index', $names, true)) {
            Schema::table('classificazioni', fn (Blueprint $table) => $table->index('tipologia_struttura_id', 'classificazioni_normalizzazione_tipologia_index'));
        }
        Schema::table('classificazioni', fn (Blueprint $table) => $table->dropUnique('classificazioni_tipologia_struttura_id_nome_unique'));
    }

    public function down(): void
    {
        // Recupero coordinato: nessuna ricreazione cieca di vincoli o relazioni.
    }
};
