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
        // Rename legacy plural if present
        if (Schema::hasTable('componentis') && ! Schema::hasTable('componenti')) {
            Schema::rename('componentis', 'componenti');
        }

        if (! Schema::hasTable('componenti')) {
            Schema::create('componenti', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedina_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('sex')->nullable();
                $table->string('relationship')->nullable();
                $table->string('exent')->nullable();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('regione')->nullable();
                $table->string('province_nac')->nullable();
                $table->string('cap')->nullable();
                $table->string('typeaway')->nullable();
                $table->string('address')->nullable();
                $table->string('number')->nullable();
                $table->string('city_nac')->nullable();
                $table->string('date_nac')->nullable();
                $table->string('province')->nullable();
                $table->timestamps();

                $table->index('schedina_id');
                $table->index('customer_id');
            });
        } else {
            Schema::table('componenti', function (Blueprint $table) {
                $cols = Schema::getColumnListing('componenti');
                $addStringIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->string($name)->nullable();
                    }
                };
                $addBigIntIfMissing = function (string $name) use (&$cols, $table) {
                    if (! in_array($name, $cols, true)) {
                        $table->unsignedBigInteger($name)->nullable();
                    }
                };

                $addBigIntIfMissing('schedina_id');
                $addBigIntIfMissing('customer_id');
                foreach (['sex','relationship','exent','name','surname','country','city','regione','province_nac','cap','typeaway','address','number','city_nac','date_nac','province'] as $c) {
                    $addStringIfMissing($c);
                }

                // add indexes when adding foreign ids
                if (! in_array('schedina_id', $cols, true)) {
                    $table->index('schedina_id');
                }
                if (! in_array('customer_id', $cols, true)) {
                    $table->index('customer_id');
                }
            });
        }

        // Add foreign keys only if referenced tables exist
        if (Schema::hasTable('componenti')) {
            try {
                Schema::table('componenti', function (Blueprint $table) {
                    if (Schema::hasTable('schedina') && ! $this->hasForeign('componenti', 'componenti_schedina_id_foreign')) {
                        $table->foreign('schedina_id')->references('id')->on('schedina')->nullOnDelete();
                    }
                    if (Schema::hasTable('customers') && ! $this->hasForeign('componenti', 'componenti_customer_id_foreign')) {
                        $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
                    }
                });
            } catch (\Throwable $e) {
                // ignore if FK add fails due to existing state or storage engine
            }
        }
    }

    private function hasForeign(string $table, string $constraint): bool
    {
        try {
            $connection = Schema::getConnection();
            $dbName = $connection->getDatabaseName();
            $result = $connection->selectOne(
                'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
                [$dbName, $table, $constraint]
            );
            return (bool) $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-distruttivo: opzionalmente rinominiamo indietro se serve
        if (Schema::hasTable('componenti') && ! Schema::hasTable('componentis')) {
            // Schema::rename('componenti', 'componentis'); // lasciamo commentato per evitare regressioni
        }
    }
};
