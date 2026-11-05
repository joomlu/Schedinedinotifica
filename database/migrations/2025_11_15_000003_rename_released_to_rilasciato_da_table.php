<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $oldTable = 'released';
        $newTable = 'rilasciato_da';
        if (Schema::hasTable($oldTable) && ! Schema::hasTable($newTable)) {
            Schema::rename($oldTable, $newTable);
        }
    }

    public function down(): void
    {
        $oldTable = 'released';
        $newTable = 'rilasciato_da';
        if (Schema::hasTable($newTable) && ! Schema::hasTable($oldTable)) {
            Schema::rename($newTable, $oldTable);
        }
    }
};
