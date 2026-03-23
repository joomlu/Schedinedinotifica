<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_servizi') || Schema::hasColumn('admin_servizi', 'quantita_default')) {
            return;
        }

        Schema::table('admin_servizi', function (Blueprint $table) {
            $table->unsignedInteger('quantita_default')->default(1)->after('importo');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_servizi') || !Schema::hasColumn('admin_servizi', 'quantita_default')) {
            return;
        }

        Schema::table('admin_servizi', function (Blueprint $table) {
            $table->dropColumn('quantita_default');
        });
    }
};
