<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'schedina' table to match App\Models\Schedina usage
        if (! Schema::hasTable('schedina')) {
            Schema::create('schedina', function (Blueprint $table) {
                $table->id();
                $table->string('scheda')->unique();
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('sex', 10)->nullable();
                $table->string('relationship')->nullable();
                $table->boolean('exent')->default(false);
                // Dates are stored as strings in the controller (d/m/Y)
                $table->string('arrive')->nullable();
                $table->string('departure')->nullable();
                $table->unsignedInteger('cant_people')->nullable();
                $table->string('room')->nullable();
                $table->unsignedInteger('beds')->nullable();
                $table->text('observation')->nullable();
                // OA (origin anagrafica?) fields
                $table->string('oa_country')->nullable();
                $table->string('oa_city')->nullable();
                $table->string('oa_region')->nullable();
                $table->string('oa_prov')->nullable();
                $table->string('oa_city_nac')->nullable();
                $table->string('oa_date_nac')->nullable();
                // OR (residence/document) fields
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
                // Added by later migration in repo; include here directly
                $table->string('or_internal')->nullable();
                // Flags
                $table->tinyInteger('is_arrive')->default(0);
                $table->timestamps();
            });
        }

        // 'componenti' table to match App\Models\Componenti usage
        if (! Schema::hasTable('componenti')) {
            Schema::create('componenti', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedina_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->string('sex', 10)->nullable();
                $table->string('relationship')->nullable();
                $table->boolean('exent')->default(false);
                $table->string('province_nac')->nullable();
                $table->string('city_nac')->nullable();
                $table->string('date_nac')->nullable();
                $table->string('country')->nullable();
                $table->string('regione')->nullable();
                $table->string('typeaway')->nullable();
                $table->string('address')->nullable();
                $table->string('number')->nullable();
                $table->string('cap')->nullable();
                $table->string('province')->nullable();
                $table->string('city')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('componenti');
        Schema::dropIfExists('schedina');
    }
};
