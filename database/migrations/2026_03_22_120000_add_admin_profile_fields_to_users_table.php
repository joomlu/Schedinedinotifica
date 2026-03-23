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
                'qualifica' => fn () => $table->string('qualifica', 120)->nullable()->after('telefono'),
                'ragione_sociale' => fn () => $table->string('ragione_sociale', 180)->nullable()->after('qualifica'),
                'codice_fiscale' => fn () => $table->string('codice_fiscale', 32)->nullable()->after('ragione_sociale'),
                'partita_iva' => fn () => $table->string('partita_iva', 32)->nullable()->after('codice_fiscale'),
                'codice_destinatario' => fn () => $table->string('codice_destinatario', 32)->nullable()->after('partita_iva'),
                'pec' => fn () => $table->string('pec', 180)->nullable()->after('codice_destinatario'),
                'indirizzo' => fn () => $table->string('indirizzo', 180)->nullable()->after('pec'),
                'numero_civico' => fn () => $table->string('numero_civico', 20)->nullable()->after('indirizzo'),
                'cap' => fn () => $table->string('cap', 16)->nullable()->after('numero_civico'),
                'citta' => fn () => $table->string('citta', 120)->nullable()->after('cap'),
                'provincia' => fn () => $table->string('provincia', 8)->nullable()->after('citta'),
                'regione' => fn () => $table->string('regione', 120)->nullable()->after('provincia'),
                'nazione' => fn () => $table->string('nazione', 120)->nullable()->after('regione'),
                'google_maps_url' => fn () => $table->string('google_maps_url', 500)->nullable()->after('nazione'),
                'compenso_servizio' => fn () => $table->decimal('compenso_servizio', 10, 2)->nullable()->after('google_maps_url'),
                'note_servizio' => fn () => $table->text('note_servizio')->nullable()->after('compenso_servizio'),
                'note_amministrative' => fn () => $table->text('note_amministrative')->nullable()->after('note_servizio'),
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
            $columns = [
                'qualifica',
                'ragione_sociale',
                'codice_fiscale',
                'partita_iva',
                'codice_destinatario',
                'pec',
                'indirizzo',
                'numero_civico',
                'cap',
                'citta',
                'provincia',
                'regione',
                'nazione',
                'google_maps_url',
                'compenso_servizio',
                'note_servizio',
                'note_amministrative',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
