<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documento', function (Blueprint $table) {
            if (!Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
                $table->string('rilasciato_da', 100)->nullable()->after('descrizione');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_documento', function (Blueprint $table) {
            if (Schema::hasColumn('tipo_documento', 'rilasciato_da')) {
                $table->dropColumn('rilasciato_da');
            }
        });
    }
};
