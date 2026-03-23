<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedina_camere') || !Schema::hasTable('schedina')) {
            return;
        }

        Schema::create('schedina_camere', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedina_id');
            $table->string('numero_camera', 30)->nullable();
            $table->unsignedSmallInteger('posti_letto')->nullable();
            $table->string('note', 255)->nullable();
            $table->string('fonte_camera', 30)->nullable();
            $table->string('camera_esterna_id', 100)->nullable();
            $table->timestamps();

            $table->index('schedina_id');
            $table->index('numero_camera');
            $table->index(['fonte_camera', 'camera_esterna_id']);
            $table->foreign('schedina_id')->references('id')->on('schedina')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina_camere')) {
            Schema::dropIfExists('schedina_camere');
        }
    }
};
