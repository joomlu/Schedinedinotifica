<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tipologie_generali')) {
            Schema::create('tipologie_generali', function (Blueprint $table) {
                $table->id();
                $table->string('nome', 150)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tipologie_struttura')) {
            Schema::create('tipologie_struttura', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tipologia_generale_id');
                $table->string('nome', 150);
                $table->timestamps();

                $table->foreign('tipologia_generale_id')->references('id')->on('tipologie_generali')->onDelete('cascade');
                $table->unique(['tipologia_generale_id', 'nome']);
            });
        }

        if (!Schema::hasTable('classificazioni')) {
            Schema::create('classificazioni', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tipologia_struttura_id');
                $table->string('nome', 150);
                $table->timestamps();

                $table->foreign('tipologia_struttura_id')->references('id')->on('tipologie_struttura')->onDelete('cascade');
                $table->unique(['tipologia_struttura_id', 'nome']);
            });
        }

        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
                if (!Schema::hasColumn('struttura', 'tipologia_generale_id')) {
                    $table->unsignedBigInteger('tipologia_generale_id')->nullable()->after('tipologia_generale');
                    $table->foreign('tipologia_generale_id')->references('id')->on('tipologie_generali')->nullOnDelete();
                }
                if (!Schema::hasColumn('struttura', 'tipologia_struttura_id')) {
                    $table->unsignedBigInteger('tipologia_struttura_id')->nullable()->after('tipologia_struttura');
                    $table->foreign('tipologia_struttura_id')->references('id')->on('tipologie_struttura')->nullOnDelete();
                }
                if (!Schema::hasColumn('struttura', 'classificazione_id')) {
                    $table->unsignedBigInteger('classificazione_id')->nullable()->after('classificazione');
                    $table->foreign('classificazione_id')->references('id')->on('classificazioni')->nullOnDelete();
                }
            });
        }

        // Seed minimo richiesto
        $generaleId = DB::table('tipologie_generali')->where('nome', 'Alberghiera')->value('id');
        if (!$generaleId) {
            $generaleId = DB::table('tipologie_generali')->insertGetId([
                'nome' => 'Alberghiera',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $strutturaId = DB::table('tipologie_struttura')->where(['tipologia_generale_id' => $generaleId, 'nome' => 'Hotel'])->value('id');
        if (!$strutturaId) {
            $strutturaId = DB::table('tipologie_struttura')->insertGetId([
                'tipologia_generale_id' => $generaleId,
                'nome' => 'Hotel',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $classId = DB::table('classificazioni')->where(['tipologia_struttura_id' => $strutturaId, 'nome' => '3 stelle superior'])->value('id');
        if (!$classId) {
            DB::table('classificazioni')->insert([
                'tipologia_struttura_id' => $strutturaId,
                'nome' => '3 stelle superior',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
                if (Schema::hasColumn('struttura', 'classificazione_id')) {
                    $table->dropForeign(['classificazione_id']);
                    $table->dropColumn('classificazione_id');
                }
                if (Schema::hasColumn('struttura', 'tipologia_struttura_id')) {
                    $table->dropForeign(['tipologia_struttura_id']);
                    $table->dropColumn('tipologia_struttura_id');
                }
                if (Schema::hasColumn('struttura', 'tipologia_generale_id')) {
                    $table->dropForeign(['tipologia_generale_id']);
                    $table->dropColumn('tipologia_generale_id');
                }
            });
        }

        Schema::dropIfExists('classificazioni');
        Schema::dropIfExists('tipologie_struttura');
        Schema::dropIfExists('tipologie_generali');
    }
};
