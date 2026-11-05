<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id')->index();
            $table->unsignedBigInteger('opened_by_user_id')->nullable()->index();
            $table->unsignedBigInteger('assigned_admin_id')->nullable()->index();
            $table->string('ticket_code', 32)->unique();
            $table->string('titolo', 180);
            $table->string('categoria', 80)->nullable();
            $table->string('priorita', 20)->default('normale');
            $table->string('stato', 30)->default('aperto');
            $table->string('modulo_riferimento', 80)->nullable();
            $table->text('descrizione');
            $table->timestamp('ultimo_messaggio_at')->nullable();
            $table->string('ultimo_messaggio_da', 20)->nullable();
            $table->timestamp('last_admin_read_at')->nullable();
            $table->timestamp('last_struttura_read_at')->nullable();
            $table->timestamp('chiuso_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id')->index();
            $table->unsignedBigInteger('struttura_id')->index();
            $table->unsignedBigInteger('author_user_id')->index();
            $table->text('messaggio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};
