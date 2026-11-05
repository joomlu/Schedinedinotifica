<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documento', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_documento', 'codice')) {
                $table->string('codice', 50)->after('id');
            }
            if (!Schema::hasColumn('tipo_documento', 'descrizione')) {
                $table->string('descrizione', 191)->after('codice');
            }
            if (!Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
                $table->string('rilasciato_da', 100)->after('descrizione');
            }
        });
        if (Schema::hasTable('tipo_documento')) {
            $dbName = DB::getDatabaseName();
            $indexExists = DB::select(
                "SELECT COUNT(1) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$dbName, 'tipo_documento', 'tipo_documento_codice_unique']
            )[0]->count > 0;
            if (!$indexExists) {
                Schema::table('tipo_documento', function (Blueprint $table) {
                    $table->unique('codice');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('tipo_documento', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
                $table->dropColumn('rilasciato_da');
            }
            if (Schema::hasColumn('tipo_documento', 'descrizione')) {
                $table->dropColumn('descrizione');
            }
            if (Schema::hasColumn('tipo_documento', 'codice')) {
                $table->dropUnique(['codice']);
                $table->dropColumn('codice');
            }
        });
    }
};
