<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'structure_id')) {
                $table->unsignedBigInteger('structure_id')->nullable()->after('updated_at');
            }
        });

        // Add FK if possible (idempotente e sicuro)
        try {
            if (
                Schema::hasTable('customers') &&
                Schema::hasColumn('customers', 'structure_id') &&
                Schema::hasTable('estructura') &&
                Schema::hasColumn('estructura', 'id')
            ) {
                Schema::table('customers', function (Blueprint $table) {
                    // Evita duplicazione FK controllando nome ed esistenza
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $doctrineTable = $sm->listTableDetails('customers');
                    if (! $doctrineTable->hasForeignKey('customers_structure_id_foreign')) {
                        $table->foreign('structure_id')->references('id')->on('estructura')->nullOnDelete();
                    }
                });
            }
        } catch (Throwable $e) {
            // Ignora problemi FK in ambienti dove Doctrine non è disponibile o tipi non combaciano
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        try {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'structure_id')) {
                    // Drop FK se presente
                    try {
                        $table->dropForeign(['structure_id']);
                    } catch (Throwable $e) {
                        // ignora se la FK non esiste
                    }
                    $table->dropColumn('structure_id');
                }
            });
        } catch (Throwable $e) {
            // ignora errori in down
        }
    }
};
