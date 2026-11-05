<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se esiste la tabella 'released' e NON esiste ancora 'rilasciato_da',
        // rinominala.
        if (Schema::hasTable('released') && ! Schema::hasTable('rilasciato_da')) {
            Schema::rename('released', 'rilasciato_da');
        }
    }

    public function down(): void
    {
        // Operazione inversa: se esiste 'rilasciato_da' e non esiste 'released',
        // rinominala indietro.
        if (Schema::hasTable('rilasciato_da') && ! Schema::hasTable('released')) {
            Schema::rename('rilasciato_da', 'released');
        }
    }
};
