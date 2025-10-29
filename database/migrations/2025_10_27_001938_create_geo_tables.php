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
        // Countries (Nazioni)
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('sigla_nazione', 10)->unique(); // IT, FR, etc.
                $table->string('denominazione', 255);
                $table->timestamps();
                $table->index('sigla_nazione');
            });
        }

        // Regions (Regioni)
        if (! Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->id();
                $table->string('codice_regione', 10)->unique(); // 01, 02, etc.
                $table->string('denominazione', 255);
                $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('restrict');
                $table->timestamps();
                $table->index('codice_regione');
            });
        }

        // Provinces (Province)
        if (! Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('sigla_provincia', 10)->unique(); // TO, MI, RN, etc.
                $table->string('denominazione', 255);
                $table->string('codice_regione', 10)->nullable(); // Direct reference for faster filters
                $table->foreignId('region_id')->constrained('regions')->onDelete('restrict');
                $table->timestamps();
                $table->index('sigla_provincia');
                $table->index('codice_regione');
            });
        }

        // Comuni (Cities)
        if (! Schema::hasTable('comuni')) {
            Schema::create('comuni', function (Blueprint $table) {
                $table->id();
                $table->string('codice_istat', 20)->unique(); // 001272, etc.
                $table->string('denominazione', 255);
                $table->string('sigla_provincia', 10)->nullable(); // Direct reference
                $table->foreignId('province_id')->constrained('provinces')->onDelete('restrict');
                $table->timestamps();
                $table->index('codice_istat');
                $table->index('sigla_provincia');
                $table->index('denominazione');
            });
        }

        // CAP (Postal codes - many-to-many with comuni)
        if (! Schema::hasTable('comune_cap')) {
            Schema::create('comune_cap', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comune_id')->constrained('comuni')->onDelete('cascade');
                $table->string('cap', 10);
                $table->string('sigla_provincia', 10)->nullable(); // Denormalized for faster queries
                $table->string('codice_istat', 20)->nullable(); // Denormalized
                $table->timestamps();
                $table->unique(['comune_id', 'cap']);
                $table->index('cap');
                $table->index('sigla_provincia');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comune_cap');
        Schema::dropIfExists('comuni');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }
};
