<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('released') && !Schema::hasTable('rilasciato_da')) {
            Schema::rename('released', 'rilasciato_da');
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('rilasciato_da') && !Schema::hasTable('released')) {
            Schema::rename('rilasciato_da', 'released');
        }
    }
};
