<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedina')) {
            return;
        }

        if (!Schema::hasColumn('schedina', 'or_published_city')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->string('or_published_city', 150)->nullable()->after('or_published_country');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedina')) {
            return;
        }

        if (Schema::hasColumn('schedina', 'or_published_city')) {
            Schema::table('schedina', function (Blueprint $table) {
                $table->dropColumn('or_published_city');
            });
        }
    }
};
