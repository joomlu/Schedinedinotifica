<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('groups') && ! Schema::hasTable('gruppi')) {
            Schema::rename('groups', 'gruppi');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gruppi') && ! Schema::hasTable('groups')) {
            Schema::rename('gruppi', 'groups');
        }
    }
};
