<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tipo_documento')) {
            Schema::create('tipo_documento', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('codice', 50)->unique();
                $table->string('descrizione', 191);
                $table->string('rilasciato_da', 100);
                $table->timestamps();
            });
        } else {
            Schema::table('tipo_documento', function (Blueprint $table) {
                if (!Schema::hasColumn('tipo_documento', 'codice')) {
                    $table->string('codice', 50)->unique()->after('id');
                }
                if (!Schema::hasColumn('tipo_documento', 'descrizione')) {
                    $table->string('descrizione', 191)->after('codice');
                }
                if (!Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
                    $table->string('rilasciato_da', 100)->after('descrizione');
                }
                // Assicura l'unicità di codice (Laravel aggiunge l'indice se non esiste)
                if (!Schema::hasColumn('tipo_documento', 'codice')) {
                    $table->unique('codice');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_documento');
    }
};
