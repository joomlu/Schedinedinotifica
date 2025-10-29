<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If there's a legacy plural table, rename it first
        if (Schema::hasTable('schedinas') && ! Schema::hasTable('schedina')) {
            Schema::rename('schedinas', 'schedina');
        }

        // Create table if missing with all required columns used in code
        if (! Schema::hasTable('schedina')) {
            Schema::create('schedina', function (Blueprint $table) {
                $table->id();
                $table->string('scheda')->nullable();
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('sex')->nullable();
                $table->string('relationship')->nullable();
                $table->string('exent')->nullable();
                $table->string('arrive')->nullable();
                $table->string('departure')->nullable();
                $table->integer('cant_people')->nullable();
                $table->string('room')->nullable();
                $table->string('beds')->nullable();
                $table->text('observation')->nullable();

                // Origin anagrafica (oa_*)
                $table->string('oa_country')->nullable();
                $table->string('oa_city')->nullable();
                $table->string('oa_region')->nullable();
                $table->string('oa_prov')->nullable();
                $table->string('oa_city_nac')->nullable();
                $table->string('oa_date_nac')->nullable();

                // Origin residenza (or_*)
                $table->string('or_country')->nullable();
                $table->string('or_city')->nullable();
                $table->string('or_region')->nullable();
                $table->string('or_prov')->nullable();
                $table->string('or_cap')->nullable();
                $table->string('or_typeaway')->nullable();
                $table->string('or_address')->nullable();
                $table->string('or_num')->nullable();
                $table->string('or_doc')->nullable();
                $table->string('or_doctype')->nullable();
                $table->string('or_published_date')->nullable();
                $table->string('or_expire')->nullable();
                $table->string('or_published')->nullable();
                $table->string('or_published_country')->nullable();

                // Campi aggiunti in update
                $table->string('group')->nullable();
                $table->string('subgroup')->nullable();
                $table->string('subgroup1')->nullable();
                $table->string('type_housed')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();

                $table->boolean('is_arrive')->default(false);

                $table->timestamps();
            });
        } else {
            // Ensure required columns exist (idempotent add)
            Schema::table('schedina', function (Blueprint $table) {
                $cols = Schema::getColumnListing('schedina');
                $addStringIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->string($name)->nullable();
                    }
                };
                $addTextIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->text($name)->nullable();
                    }
                };
                $addBoolIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->boolean($name)->default(false);
                    }
                };
                $addIntIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->integer($name)->nullable();
                    }
                };
                $addBigIntIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->unsignedBigInteger($name)->nullable();
                    }
                };

                $addStringIfMissing('scheda');
                $addStringIfMissing('type');
                $addStringIfMissing('name');
                $addStringIfMissing('surname');
                $addBigIntIfMissing('customer_id');
                $addStringIfMissing('sex');
                $addStringIfMissing('relationship');
                $addStringIfMissing('exent');
                $addStringIfMissing('arrive');
                $addStringIfMissing('departure');
                $addIntIfMissing('cant_people');
                $addStringIfMissing('room');
                $addStringIfMissing('beds');
                $addTextIfMissing('observation');

                foreach (['oa_country','oa_city','oa_region','oa_prov','oa_city_nac','oa_date_nac'] as $c) {
                    $addStringIfMissing($c);
                }
                foreach (['or_country','or_city','or_region','or_prov','or_cap','or_typeaway','or_address','or_num','or_doc','or_doctype','or_published_date','or_expire','or_published','or_published_country'] as $c) {
                    $addStringIfMissing($c);
                }
                foreach (['group','subgroup','subgroup1','type_housed','country','city','region'] as $c) {
                    $addStringIfMissing($c);
                }
                $addBoolIfMissing('is_arrive');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-distruttivo: se esiste 'schedina' ma non 'schedinas', rinomino indietro
        if (Schema::hasTable('schedina') && ! Schema::hasTable('schedinas')) {
            // Best-effort rename back
            Schema::rename('schedina', 'schedinas');
        }
        // Non droppiamo colonne per evitare perdita dati
    }
};
