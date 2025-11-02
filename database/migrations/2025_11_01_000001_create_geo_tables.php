<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stati', function (Blueprint $table) {
            $table->string('sigla_nazione', 4)->primary();
            $table->string('denominazione_nazione')->index();
            $table->string('denominazione_cittadinanza')->nullable();
        });

        Schema::create('geo_regions', function (Blueprint $table) {
            $table->string('codice_regione', 4)->primary();
            $table->string('denominazione_regione')->index();
            $table->string('tipologia_regione')->nullable();
            $table->string('ripartizione_geografica')->nullable();
        });

        Schema::create('geo_provinces', function (Blueprint $table) {
            $table->string('sigla_provincia', 4)->primary();
            $table->string('denominazione_provincia')->index();
            $table->string('codice_regione', 4)->index();
            $table->string('tipologia_provincia')->nullable();
            $table->foreign('codice_regione')->references('codice_regione')->on('geo_regions')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('geo_cities', function (Blueprint $table) {
            $table->string('codice_istat', 12)->primary();
            $table->string('denominazione_ita')->index();
            $table->string('denominazione_ita_altra')->nullable();
            $table->string('sigla_provincia', 4)->index();
            $table->string('codice_regione', 4)->index();
            $table->string('flag_capoluogo', 4)->nullable();
            $table->string('codice_belfiore', 8)->nullable()->index();
            $table->string('lat', 32)->nullable();
            $table->string('lon', 32)->nullable();
            $table->string('superficie_kmq', 32)->nullable();
            $table->string('codice_sovracomunale', 8)->nullable();
            $table->foreign('sigla_provincia')->references('sigla_provincia')->on('geo_provinces')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('codice_regione')->references('codice_regione')->on('geo_regions')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('geo_caps', function (Blueprint $table) {
            $table->id();
            $table->string('codice_istat', 12)->index();
            $table->string('cap', 10)->index();
            $table->string('sigla_provincia', 4)->index();
            $table->string('denominazione_provincia')->nullable();
            $table->string('codice_regione', 4)->index();
            $table->string('denominazione_regione')->nullable();
            $table->unique(['codice_istat', 'cap']);
            $table->foreign('codice_istat')->references('codice_istat')->on('geo_cities')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_caps');
        Schema::dropIfExists('geo_cities');
        Schema::dropIfExists('geo_provinces');
        Schema::dropIfExists('geo_regions');
        Schema::dropIfExists('stati');
    }
};
