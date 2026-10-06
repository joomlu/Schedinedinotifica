<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['questura_exports', 'questura_transmissions'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->char('sha256', 64)->nullable();
                $table->unsignedBigInteger('byte_size')->nullable();
                $table->string('charset', 20)->nullable();
                $table->json('component_ids')->nullable();
            });
        }
        Schema::table('questura_exports', fn (Blueprint $table) => $table->string('status', 20)->nullable());
        Schema::create('questura_transmission_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questura_transmission_id')->constrained('questura_transmissions')->restrictOnDelete();
            $table->string('status', 20);
            $table->json('result');
            $table->timestamp('created_at');
        });
        Schema::table('questura_transmissions', function (Blueprint $table) {
            $table->char('send_key', 64)->nullable()->unique();
        });
        Schema::create('questura_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('struttura_id')->constrained('struttura')->restrictOnDelete();
            $table->foreignId('questura_transmission_id')->constrained('questura_transmissions')->restrictOnDelete();
            $table->date('remote_date');
            $table->string('filename');
            $table->string('path');
            $table->string('mime', 30);
            $table->char('sha256', 64);
            $table->unsignedBigInteger('byte_size');
            $table->timestamps();
            $table->unique(['struttura_id', 'remote_date']);
        });
    }

    public function down(): void
    {
        // Conserva integralmente gli archivi: rollback applicativo senza perdita dati.
    }
};
