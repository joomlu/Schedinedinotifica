<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('stati')) {
            $needsRebuild = ! Schema::hasColumn('stati', 'sigla_nazione')
                || ! Schema::hasColumn('stati', 'denominazione_nazione');

            if ($needsRebuild) {
                $suffix = date('YmdHis');
                $legacy = 'stati_legacy_' . $suffix;
                Schema::rename('stati', $legacy);

                Schema::create('stati', function (Blueprint $table) {
                    $table->string('sigla_nazione', 4)->primary();
                    $table->string('denominazione_nazione')->index();
                    $table->string('denominazione_cittadinanza')->nullable();
                });
            }
        } else {
            Schema::create('stati', function (Blueprint $table) {
                $table->string('sigla_nazione', 4)->primary();
                $table->string('denominazione_nazione')->index();
                $table->string('denominazione_cittadinanza')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Non tentiamo di rinominare automaticamente indietro perché il nome contiene timestamp.
        // Ci limitiamo a lasciare la tabella nella forma compatibile.
    }
};
