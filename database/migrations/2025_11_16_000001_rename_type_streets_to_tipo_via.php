<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esegui le operazioni di migrazione.
     */
    public function up(): void
    {
        Schema::rename('type_streets', 'tipo_via');
    }

    /**
     * Annulla le operazioni di migrazione.
     */
    public function down(): void
    {
        Schema::rename('tipo_via', 'type_streets');
    }
};
