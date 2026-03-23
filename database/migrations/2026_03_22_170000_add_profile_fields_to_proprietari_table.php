<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('proprietari')) {
            return;
        }

        Schema::table('proprietari', function (Blueprint $table) {
            $columns = [
                'ragione_sociale' => fn () => $table->string('ragione_sociale', 180)->nullable()->after('telefono'),
                'codice_fiscale' => fn () => $table->string('codice_fiscale', 32)->nullable()->after('ragione_sociale'),
                'partita_iva' => fn () => $table->string('partita_iva', 32)->nullable()->after('codice_fiscale'),
                'codice_destinatario' => fn () => $table->string('codice_destinatario', 32)->nullable()->after('partita_iva'),
                'codice_unico' => fn () => $table->string('codice_unico', 7)->nullable()->after('codice_destinatario'),
                'pec' => fn () => $table->string('pec', 180)->nullable()->after('codice_unico'),
                'indirizzo' => fn () => $table->string('indirizzo', 180)->nullable()->after('pec'),
                'numero_civico' => fn () => $table->string('numero_civico', 20)->nullable()->after('indirizzo'),
                'cap' => fn () => $table->string('cap', 16)->nullable()->after('numero_civico'),
                'citta' => fn () => $table->string('citta', 120)->nullable()->after('cap'),
                'provincia' => fn () => $table->string('provincia', 8)->nullable()->after('citta'),
                'regione' => fn () => $table->string('regione', 120)->nullable()->after('provincia'),
                'nazione' => fn () => $table->string('nazione', 120)->nullable()->after('regione'),
                'geo_manual' => fn () => $table->boolean('geo_manual')->default(false)->after('nazione'),
                'latitudine' => fn () => $table->decimal('latitudine', 10, 7)->nullable()->after('geo_manual'),
                'longitudine' => fn () => $table->decimal('longitudine', 10, 7)->nullable()->after('latitudine'),
                'note_amministrative' => fn () => $table->text('note_amministrative')->nullable()->after('note'),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('proprietari', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('proprietari')) {
            return;
        }

        Schema::table('proprietari', function (Blueprint $table) {
            foreach ([
                'ragione_sociale',
                'codice_fiscale',
                'partita_iva',
                'codice_destinatario',
                'codice_unico',
                'pec',
                'indirizzo',
                'numero_civico',
                'cap',
                'citta',
                'provincia',
                'regione',
                'nazione',
                'geo_manual',
                'latitudine',
                'longitudine',
                'note_amministrative',
            ] as $column) {
                if (Schema::hasColumn('proprietari', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
