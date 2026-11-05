<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'codice_unico' => fn () => $table->string('codice_unico', 7)->nullable()->after('codice_destinatario'),
                'geo_manual' => fn () => $table->boolean('geo_manual')->default(false)->after('nazione'),
                'latitudine' => fn () => $table->decimal('latitudine', 10, 7)->nullable()->after('geo_manual'),
                'longitudine' => fn () => $table->decimal('longitudine', 10, 7)->nullable()->after('latitudine'),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('users', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['codice_unico', 'geo_manual', 'latitudine', 'longitudine'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
