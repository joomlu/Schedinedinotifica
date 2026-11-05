<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
            // Identità Struttura
            if (!Schema::hasColumn('struttura', 'logo')) $table->string('logo', 255)->nullable();
            if (!Schema::hasColumn('struttura', 'nome_struttura')) $table->string('nome_struttura', 150);
            if (!Schema::hasColumn('struttura', 'cir')) $table->string('cir', 50)->nullable()->unique();
                // Aggiungi l'indice solo se non esiste già
                $dbName = DB::getDatabaseName();
                $indexExists = DB::select(
                    "SELECT COUNT(1) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                    [$dbName, 'struttura', 'struttura_cir_unique']
                )[0]->count > 0;
                if (!$indexExists) {
                    $table->unique('cir');
                }
            if (!Schema::hasColumn('struttura', 'tipologia_generale')) $table->string('tipologia_generale', 150)->nullable();
            if (!Schema::hasColumn('struttura', 'tipologia_struttura')) $table->string('tipologia_struttura', 150)->nullable();
            if (!Schema::hasColumn('struttura', 'classificazione')) $table->string('classificazione', 100)->nullable();
            // Apertura / Stagionalità
            if (!Schema::hasColumn('struttura', 'tipo_apertura')) $table->enum('tipo_apertura', ['Annuale','Stagionale'])->default('Stagionale');
            if (!Schema::hasColumn('struttura', 'data_apertura')) $table->date('data_apertura')->nullable();
            if (!Schema::hasColumn('struttura', 'data_chiusura')) $table->date('data_chiusura')->nullable();
            // Ubicazione
            if (!Schema::hasColumn('struttura', 'nazione')) $table->string('nazione', 100)->default('Italia');
            if (!Schema::hasColumn('struttura', 'regione')) $table->string('regione', 100);
            if (!Schema::hasColumn('struttura', 'provincia')) $table->string('provincia', 2);
            if (!Schema::hasColumn('struttura', 'città')) $table->string('città', 150);
            if (!Schema::hasColumn('struttura', 'logo_città')) $table->string('logo_città', 255)->nullable();
            if (!Schema::hasColumn('struttura', 'zona')) $table->string('zona', 100)->nullable();
            if (!Schema::hasColumn('struttura', 'località')) $table->string('località', 150)->nullable();
            if (!Schema::hasColumn('struttura', 'indirizzo')) $table->string('indirizzo', 191);
            if (!Schema::hasColumn('struttura', 'numero_civico')) $table->string('numero_civico', 20)->nullable();
            if (!Schema::hasColumn('struttura', 'cap')) $table->string('cap', 10);
            if (!Schema::hasColumn('struttura', 'latitudine')) $table->decimal('latitudine', 10, 7)->nullable();
            if (!Schema::hasColumn('struttura', 'longitudine')) $table->decimal('longitudine', 10, 7)->nullable();
            // Dati fiscali / amministrativi
            if (!Schema::hasColumn('struttura', 'ragione_sociale')) $table->string('ragione_sociale', 191)->nullable();
            if (!Schema::hasColumn('struttura', 'partita_iva')) $table->string('partita_iva', 20)->nullable();
            if (!Schema::hasColumn('struttura', 'codice_fiscale')) $table->string('codice_fiscale', 16)->nullable();
            if (!Schema::hasColumn('struttura', 'cin')) $table->string('cin', 50)->nullable();
            if (!Schema::hasColumn('struttura', 'codice_unico')) $table->string('codice_unico', 7)->nullable();
            // Capacità ricettiva
            if (!Schema::hasColumn('struttura', 'camere_disponibili')) $table->unsignedSmallInteger('camere_disponibili')->nullable();
            if (!Schema::hasColumn('struttura', 'letti_disponibili')) $table->unsignedSmallInteger('letti_disponibili')->nullable();
            if (!Schema::hasColumn('struttura', 'letti_agg')) $table->unsignedSmallInteger('letti_agg')->nullable();
            // Riferimenti istituzionali
            if (!Schema::hasColumn('struttura', 'istat_username')) $table->string('istat_username', 100)->nullable();
            if (!Schema::hasColumn('struttura', 'istat_password')) $table->string('istat_password', 100)->nullable();
            if (!Schema::hasColumn('struttura', 'questura_username')) $table->string('questura_username', 100)->nullable();
            if (!Schema::hasColumn('struttura', 'questura_password')) $table->string('questura_password', 100)->nullable();
            // Contatti
            if (!Schema::hasColumn('struttura', 'telefono')) $table->string('telefono', 20);
            if (!Schema::hasColumn('struttura', 'telefono_secondario')) $table->string('telefono_secondario', 20)->nullable();
            if (!Schema::hasColumn('struttura', 'fax')) $table->string('fax', 20)->nullable();
            if (!Schema::hasColumn('struttura', 'email')) $table->string('email', 191);
            if (!Schema::hasColumn('struttura', 'sito_web')) $table->string('sito_web', 191)->nullable();
        });
        }
    }

    public function down(): void
    {
        // Non si rimuovono colonne in down per sicurezza dati
    }
};
