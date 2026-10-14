<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('classificazione_tipologia')) {
            Schema::create('classificazione_tipologia', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('classificazione_id');
                $table->unsignedBigInteger('tipologia_struttura_id');
                $table->timestamps();

                $table->foreign('classificazione_id')->references('id')->on('classificazioni')->onDelete('cascade');
                $table->foreign('tipologia_struttura_id')->references('id')->on('tipologie_struttura')->onDelete('cascade');
                $table->unique(['classificazione_id', 'tipologia_struttura_id'], 'class_tipologia_unique');
            });
        }

        if (Schema::hasColumn('classificazioni', 'tipologia_struttura_id')) {
            Schema::table('classificazioni', function (Blueprint $table) {
                $table->dropForeign(['tipologia_struttura_id']);
            });
        }

        $rows = DB::table('classificazioni')->get(['id', 'nome', 'tipologia_struttura_id']);
        $keep = [];
        foreach ($rows as $row) {
            $key = strtolower(trim((string) $row->nome));
            if (!isset($keep[$key])) {
                $keep[$key] = $row->id;
            }
        }

        foreach ($rows as $row) {
            $key = strtolower(trim((string) $row->nome));
            $canonicalId = $keep[$key];
            if (!empty($row->tipologia_struttura_id)) {
                DB::table('classificazione_tipologia')->updateOrInsert(
                    [
                        'classificazione_id' => $canonicalId,
                        'tipologia_struttura_id' => $row->tipologia_struttura_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            if ($row->id !== $canonicalId) {
                DB::table('struttura')->where('classificazione_id', $row->id)->update(['classificazione_id' => $canonicalId]);
            }
        }

        $idsToKeep = array_values($keep);
        if (!empty($idsToKeep)) {
            DB::table('classificazioni')->whereNotIn('id', $idsToKeep)->delete();
        }

        if (Schema::hasColumn('classificazioni', 'tipologia_struttura_id')) {
            Schema::table('classificazioni', function (Blueprint $table) {
                $table->dropColumn('tipologia_struttura_id');
            });
        }

        Schema::table('classificazioni', function (Blueprint $table) {
            $table->unique('nome');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('classificazioni')) {
            Schema::table('classificazioni', function (Blueprint $table) {
                $table->dropUnique('classificazioni_nome_unique');
                if (!Schema::hasColumn('classificazioni', 'tipologia_struttura_id')) {
                    $table->unsignedBigInteger('tipologia_struttura_id')->nullable()->after('nome');
                    $table->foreign('tipologia_struttura_id')->references('id')->on('tipologie_struttura')->nullOnDelete();
                }
            });
        }

        Schema::dropIfExists('classificazione_tipologia');
    }
};
