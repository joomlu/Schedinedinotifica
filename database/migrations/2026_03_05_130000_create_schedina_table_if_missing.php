<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedina')) {
            return;
        }

        Schema::create('schedina', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('struttura_id')->nullable();
            $table->string('scheda', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('surname', 100)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('relationship', 50)->nullable();
            $table->boolean('exent')->default(false);
            $table->date('arrive')->nullable();
            $table->date('departure')->nullable();
            $table->unsignedSmallInteger('cant_people')->nullable();
            $table->string('room', 50)->nullable();
            $table->unsignedSmallInteger('beds')->nullable();
            $table->text('observation')->nullable();
            $table->string('oa_country', 100)->nullable();
            $table->string('oa_city', 150)->nullable();
            $table->string('oa_region', 150)->nullable();
            $table->string('oa_prov', 150)->nullable();
            $table->string('oa_city_nac', 150)->nullable();
            $table->date('oa_date_nac')->nullable();
            $table->string('or_country', 100)->nullable();
            $table->string('or_city', 150)->nullable();
            $table->string('or_region', 150)->nullable();
            $table->string('or_prov', 150)->nullable();
            $table->string('or_cap', 10)->nullable();
            $table->string('or_typeaway', 100)->nullable();
            $table->string('or_address', 150)->nullable();
            $table->string('or_num', 20)->nullable();
            $table->string('or_doc', 150)->nullable();
            $table->string('or_doctype', 100)->nullable();
            $table->date('or_published_date')->nullable();
            $table->date('or_expire')->nullable();
            $table->string('or_published', 150)->nullable();
            $table->string('or_published_country', 150)->nullable();
            $table->boolean('is_arrive')->default(false);
            $table->string('fonte_prenotazione', 30)->nullable();
            $table->string('id_prenotazione_esterna', 100)->nullable();
            $table->dateTime('agganciata_il')->nullable();
            $table->unsignedBigInteger('agganciata_da')->nullable();
            $table->timestamps();

            $table->index('struttura_id');
            $table->index('customer_id');
            $table->index('is_arrive');
            $table->index('arrive');
            $table->index('departure');
            $table->index(['fonte_prenotazione', 'id_prenotazione_esterna']);
            $table->index('agganciata_da');
            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('set null');
            $table->foreign('agganciata_da')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina')) {
            Schema::dropIfExists('schedina');
        }
    }
};
