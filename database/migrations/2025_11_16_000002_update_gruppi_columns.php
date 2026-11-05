<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gruppi', function (Blueprint $table) {
            // Forza la presenza della colonna 'nome'
            if (!Schema::hasColumn('gruppi', 'nome')) {
                if (Schema::hasColumn('gruppi', 'name')) {
                    $table->renameColumn('name', 'nome');
                } else {
                    $table->string('nome', 100)->nullable();
                }
            }
            // Forza la presenza della colonna 'descrizione'
            if (!Schema::hasColumn('gruppi', 'descrizione')) {
                if (Schema::hasColumn('gruppi', 'description')) {
                    $table->renameColumn('description', 'descrizione');
                } else {
                    $table->string('descrizione', 255)->nullable();
                }
            }
            if (! Schema::hasColumn('gruppi', 'tipo')) {
                $table->string('tipo', 20)->default('Gruppo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gruppi', function (Blueprint $table) {
            if (!Schema::hasColumn('gruppi', 'nome')) {
                if (Schema::hasColumn('gruppi', 'name')) {
                    $table->renameColumn('name', 'nome');
                } else {
                    $table->string('nome', 100)->after('id');
                }
            }
            if (!Schema::hasColumn('gruppi', 'descrizione')) {
                if (Schema::hasColumn('gruppi', 'description')) {
                    $table->renameColumn('description', 'descrizione');
                } else {
                    $table->string('descrizione', 191)->nullable()->after('nome');
                }
            }
            if (!Schema::hasColumn('gruppi', 'tipo')) {
                $table->string('tipo', 20)->default('Gruppo');
            }
        });
    }
};
