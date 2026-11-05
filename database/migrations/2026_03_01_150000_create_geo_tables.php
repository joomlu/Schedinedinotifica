<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('geo_nazioni', function (Blueprint $table) {
            $table->id();
            $table->string('codice_iso2', 2)->unique();
            $table->string('nome');
            $table->string('cittadinanza')->nullable();
            $table->boolean('is_italia')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('geo_regioni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_nazione_id')->constrained('geo_nazioni')->cascadeOnDelete();
            $table->string('codice_regione');
            $table->string('nome');
            $table->timestamps();
            $table->unique(['geo_nazione_id', 'codice_regione']);
            $table->index('geo_nazione_id');
        });

        Schema::create('geo_province', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_regione_id')->constrained('geo_regioni')->cascadeOnDelete();
            $table->string('sigla', 3)->unique();
            $table->string('nome');
            $table->string('codice_provincia')->nullable();
            $table->timestamps();
            $table->index('geo_regione_id');
        });

        Schema::create('geo_comuni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_provincia_id')->constrained('geo_province')->cascadeOnDelete();
            $table->string('codice_istat')->unique();
            $table->string('nome');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
            $table->index('geo_provincia_id');
            $table->index('nome');
        });

        Schema::create('geo_cap', function (Blueprint $table) {
            $table->id();
            $table->string('cap', 5)->unique();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('geo_comuni_cap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_comune_id')->constrained('geo_comuni')->cascadeOnDelete();
            $table->foreignId('geo_cap_id')->constrained('geo_cap')->cascadeOnDelete();
            $table->boolean('principale')->default(false);
            $table->integer('priorita')->default(100);
            $table->string('localita')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['geo_comune_id', 'geo_cap_id', 'localita']);
            $table->index('geo_cap_id');
            $table->index(['geo_comune_id', 'principale', 'priorita']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_comuni_cap');
        Schema::dropIfExists('geo_cap');
        Schema::dropIfExists('geo_comuni');
        Schema::dropIfExists('geo_province');
        Schema::dropIfExists('geo_regioni');
        Schema::dropIfExists('geo_nazioni');
    }
};
