<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se la tabella esiste già, non fare nulla
        if (Schema::hasTable('struttura')) {
            return;
        }

        Schema::create('struttura', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('logo', 255)->nullable();
            $table->string('nome_struttura', 150);
            $table->string('cir', 50)->nullable()->unique();
            $table->enum('tipologia_generale', ["All’aperto",'Alberghiera','Extra-alberghiera']);
            $table->string('tipologia_struttura', 100);
            $table->string('classificazione', 50)->nullable();
            $table->enum('tipo_apertura', ['Annuale','Stagionale'])->default('Stagionale');
            $table->date('data_apertura')->nullable();
            $table->date('data_chiusura')->nullable();
            $table->string('nazione', 100)->default('Italia');
            $table->string('regione', 100);
            $table->string('provincia', 2);
            $table->string('città', 150);
            $table->string('logo_città', 255)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('località', 150)->nullable();
            $table->string('indirizzo', 191);
            $table->string('numero_civico', 20)->nullable();
            $table->string('cap', 10);
            $table->decimal('latitudine', 10, 7)->nullable();
            $table->decimal('longitudine', 10, 7)->nullable();
            $table->string('ragione_sociale', 191)->nullable();
            $table->string('partita_iva', 20)->nullable();
            $table->string('codice_fiscale', 16)->nullable();
            $table->string('cin', 50)->nullable();
            $table->string('codice_unico', 7)->nullable();
            $table->unsignedSmallInteger('camere_disponibili')->nullable();
            $table->unsignedSmallInteger('letti_disponibili')->nullable();
            $table->unsignedSmallInteger('letti_agg')->nullable();
            $table->string('istat_username', 100)->nullable();
            $table->string('istat_password', 100)->nullable();
            $table->string('questura_username', 100)->nullable();
            $table->string('questura_password', 100)->nullable();
            $table->string('telefono', 20);
            $table->string('telefono_secondario', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('email', 191);
            $table->string('sito_web', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struttura');
    }
};
