<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('componenti')) {
            return;
        }

        Schema::table('componenti', function (Blueprint $table) {
            if (!Schema::hasColumn('componenti', 'cap_nac')) {
                $table->string('cap_nac', 20)->nullable()->after('comune_nac');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('componenti') || !Schema::hasColumn('componenti', 'cap_nac')) {
            return;
        }

        Schema::table('componenti', function (Blueprint $table) {
            $table->dropColumn('cap_nac');
        });
    }
};
