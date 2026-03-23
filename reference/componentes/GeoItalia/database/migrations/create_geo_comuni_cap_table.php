<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('geo_comuni_cap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_comune_id')->constrained('geo_comuni')->cascadeOnDelete();
            $table->foreignId('geo_cap_id')->constrained('geo_cap')->cascadeOnDelete();
            $table->boolean('principale')->default(false);
            $table->integer('priorita')->default(100);
            $table->string('localita')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['geo_comune_id', 'geo_cap_id', 'localita']);
            $table->index('geo_cap_id');
            $table->index(['geo_comune_id', 'principale', 'priorita']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_comuni_cap');
    }
};
