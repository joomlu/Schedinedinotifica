<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('gruppi', 'nome')) {
            Schema::table('gruppi', function (Blueprint $table) {
                $table->string('nome', 100)->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('gruppi', 'nome')) {
            Schema::table('gruppi', function (Blueprint $table) {
                $table->dropColumn('nome');
            });
        }
    }
};
