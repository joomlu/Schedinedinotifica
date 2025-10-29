<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Se la tabella non esiste, creiamola da zero
        if (! Schema::hasTable('comuni')) {
            Schema::create('comuni', function (Blueprint $table) {
                $table->id();
                $table->string('codice_questura', 20)->unique()->comment('Codice comune da Questura');
                $table->string('denominazione', 255)->comment('Nome del comune');
                $table->date('data_fine_validita')->nullable()->comment('Data fine validità per comuni storici');
                $table->string('sigla_provincia', 10)->comment('Sigla provincia (PD, MI, TO, etc.)');
                $table->unsignedBigInteger('province_id')->nullable()->comment('FK to provinces table');
                $table->unsignedBigInteger('region_id')->nullable()->comment('FK to regions table');
                $table->unsignedBigInteger('stato_id')->nullable()->comment('FK to stati table (Italia)');
                $table->timestamps();
                $table->index('denominazione');
                $table->index('sigla_provincia');
                $table->index('data_fine_validita');
            });

            // Aggiungi FKs solo se le tabelle di riferimento esistono
            if (Schema::hasTable('provinces')) {
                Schema::table('comuni', function (Blueprint $table) {
                    $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
                });
            }
            if (Schema::hasTable('regions')) {
                Schema::table('comuni', function (Blueprint $table) {
                    $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
                });
            }
            if (Schema::hasTable('stati')) {
                Schema::table('comuni', function (Blueprint $table) {
                    $table->foreign('stato_id')->references('id')->on('stati')->onDelete('set null');
                });
            }

            return;
        }

        // Se la tabella esiste già, allineiamo lo schema senza dropparla (evitiamo problemi di FK)
        Schema::table('comuni', function (Blueprint $table) {
            if (! Schema::hasColumn('comuni', 'codice_questura')) {
                $table->string('codice_questura', 20)->nullable();
            }
            if (! Schema::hasColumn('comuni', 'denominazione')) {
                $table->string('denominazione', 255)->nullable();
            }
            if (! Schema::hasColumn('comuni', 'data_fine_validita')) {
                $table->date('data_fine_validita')->nullable();
            }
            if (! Schema::hasColumn('comuni', 'sigla_provincia')) {
                $table->string('sigla_provincia', 10)->nullable();
            }
            if (! Schema::hasColumn('comuni', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable();
            }
            if (! Schema::hasColumn('comuni', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable();
            }
            if (! Schema::hasColumn('comuni', 'stato_id')) {
                $table->unsignedBigInteger('stato_id')->nullable();
            }
        });

        // Indici utili (usiamo SQL con IF NOT EXISTS per evitare errori)
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS comuni_denominazione_index ON comuni (denominazione)');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS comuni_sigla_provincia_index ON comuni (sigla_provincia)');
        } catch (\Throwable $e) {
        }
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS comuni_data_fine_validita_index ON comuni (data_fine_validita)');
        } catch (\Throwable $e) {
        }

        // Vincolo di unicità su codice_questura
        if (Schema::hasColumn('comuni', 'codice_questura')) {
            try {
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS comuni_codice_questura_unique ON comuni (codice_questura)');
            } catch (\Throwable $e) {
            }
        }

        // Aggiungi FKs solo se le tabelle di riferimento esistono
        if (Schema::hasTable('provinces')) {
            try {
                Schema::table('comuni', function (Blueprint $table) {
                    $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
                });
            } catch (\Throwable $e) {
            }
        }
        if (Schema::hasTable('regions')) {
            try {
                Schema::table('comuni', function (Blueprint $table) {
                    $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
                });
            } catch (\Throwable $e) {
            }
        }
        if (Schema::hasTable('stati')) {
            try {
                Schema::table('comuni', function (Blueprint $table) {
                    $table->foreign('stato_id')->references('id')->on('stati')->onDelete('set null');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comuni');
    }
};
