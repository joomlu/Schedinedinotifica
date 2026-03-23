<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (!Schema::hasColumn('struttura', 'logo_citta')) {
                $table->string('logo_citta', 255)->nullable()->after('logo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            if (Schema::hasColumn('struttura', 'logo_citta')) {
                $table->dropColumn('logo_citta');
            }
        });
    }
};
