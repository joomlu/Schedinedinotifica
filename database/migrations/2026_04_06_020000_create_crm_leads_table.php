<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code')->unique();
            $table->string('fonte', 40)->default('sito_web');
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->string('stato', 40)->default('nuovo');
            $table->string('struttura', 120);
            $table->string('nome_cognome', 120);
            $table->string('persona_contatto', 120)->nullable();
            $table->string('localita', 120)->nullable();
            $table->string('email', 160);
            $table->string('telefono', 40)->nullable();
            $table->string('cellulare', 40)->nullable();
            $table->string('sito_web', 180)->nullable();
            $table->string('modalita_contatto', 120)->nullable();
            $table->string('preferenza_contatto_label', 180)->nullable();
            $table->dateTime('preferenza_contatto_at')->nullable();
            $table->boolean('qualsiasi_orario')->default(false);
            $table->text('messaggio')->nullable();
            $table->text('note_interne')->nullable();
            $table->dateTime('ultimo_contatto_at')->nullable();
            $table->dateTime('prossimo_contatto_at')->nullable();
            $table->dateTime('chiuso_at')->nullable();
            $table->timestamps();

            $table->foreign('assigned_admin_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['stato', 'assigned_admin_id']);
            $table->index(['prossimo_contatto_at']);
            $table->index(['fonte']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
