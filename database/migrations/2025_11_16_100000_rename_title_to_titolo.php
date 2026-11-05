<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('title') && !Schema::hasTable('titolo')) {
            Schema::rename('title', 'titolo');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('titolo') && !Schema::hasTable('title')) {
            Schema::rename('titolo', 'title');
        }
    }
};
