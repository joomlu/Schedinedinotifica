<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('stati') && ! Schema::hasColumn('stati', 'denominazione_cittadinanza')) {
            Schema::table('stati', function (Blueprint $table) {
                $table->string('denominazione_cittadinanza')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stati') && Schema::hasColumn('stati', 'denominazione_cittadinanza')) {
            Schema::table('stati', function (Blueprint $table) {
                $table->dropColumn('denominazione_cittadinanza');
            });
        }
    }
};
