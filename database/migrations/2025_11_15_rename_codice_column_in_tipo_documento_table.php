<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = 'tipo_documento';
        $possibleNames = [
            'Codice', 'Codíce', 'Códice', 'ÙCodice', 'ÚCodice', 'codíce', 'códice', 'Ùcodice', 'Úcodice',
            'CODICE', 'CODÍCE', 'CÓDICE', 'ÙCODICE', 'ÚCODICE',
        ];
        foreach ($possibleNames as $oldName) {
            if (Schema::hasColumn($table, $oldName)) {
                // Laravel >=10/DBAL >=4: renameColumn funziona senza chiamate a getDoctrineSchemaManager
                Schema::table($table, function (Blueprint $table) use ($oldName) {
                    $table->renameColumn($oldName, 'codice');
                });
                break;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non si può sapere il nome originale, quindi non si fa nulla qui.
    }
};
