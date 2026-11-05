<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('componenti')) {
            Schema::create('componenti', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('struttura_id')->nullable()->index();
                $table->unsignedBigInteger('schedina_id')->nullable()->index();
                $table->unsignedBigInteger('customer_id')->nullable()->index();

                $table->string('name', 100)->nullable();
                $table->string('surname', 100)->nullable();
                $table->string('sex', 2)->nullable();
                $table->string('relationship', 50)->nullable();
                $table->string('exent', 50)->nullable();

                $table->string('city_nac', 150)->nullable();
                $table->string('province_nac', 150)->nullable();
                $table->date('date_nac')->nullable();

                $table->string('country', 150)->nullable();
                $table->string('regione', 150)->nullable();
                $table->string('province', 150)->nullable();
                $table->string('city', 150)->nullable();
                $table->string('typeaway', 100)->nullable();
                $table->string('address', 150)->nullable();
                $table->string('number', 20)->nullable();
                $table->string('cap', 20)->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('componenti');
    }
};
