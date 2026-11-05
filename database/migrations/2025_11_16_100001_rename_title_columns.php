<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titolo', function (Blueprint $table) {
            if (Schema::hasColumn('titolo', 'name') && !Schema::hasColumn('titolo', 'nome')) {
                $table->renameColumn('name', 'nome');
            }
            if (Schema::hasColumn('titolo', 'Descrizione') && !Schema::hasColumn('titolo', 'descrizione')) {
                $table->renameColumn('Descrizione', 'descrizione');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titolo', function (Blueprint $table) {
            if (Schema::hasColumn('titolo', 'nome') && !Schema::hasColumn('titolo', 'name')) {
                $table->renameColumn('nome', 'name');
            }
            if (Schema::hasColumn('titolo', 'descrizione') && !Schema::hasColumn('titolo', 'Descrizione')) {
                $table->renameColumn('descrizione', 'Descrizione');
            }
        });
    }
};
