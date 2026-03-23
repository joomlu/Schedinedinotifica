<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
            Schema::table('tipo_documento', function (Blueprint $table) {
                $table->string('rilasciato_da', 100)->default('Comune')->after('descrizione');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
            Schema::table('tipo_documento', function (Blueprint $table) {
                $table->dropColumn('rilasciato_da');
            });
        }
    }
};
