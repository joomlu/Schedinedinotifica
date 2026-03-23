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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('group')->nullable();
            $table->string('subgroup')->nullable();
            $table->string('subgroup1')->nullable();
            $table->string('sex')->nullable();
            $table->string('type_housed')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('cap')->nullable();
            $table->string('typeaway')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('observation')->nullable();
            $table->string('country_reg')->nullable();
            $table->string('city_reg')->nullable();
            $table->string('prov_reg')->nullable();
            $table->string('ciudadania_reg')->nullable();
            $table->string('nac_reg')->nullable();
            $table->string('type_doc_reg')->nullable();
            $table->string('num_doc_reg')->nullable();
            $table->string('date_pub_reg')->nullable();
            $table->string('expire_reg')->nullable();
            $table->string('released_reg')->nullable();
            $table->string('observation_reg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
