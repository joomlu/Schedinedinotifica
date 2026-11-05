<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_lead_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crm_lead_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('tipo', 40)->default('nota');
            $table->string('stato', 40)->default('registrata');
            $table->string('titolo', 180);
            $table->text('descrizione')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('crm_lead_id')->references('id')->on('crm_leads')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['crm_lead_id', 'scheduled_at']);
            $table->index(['stato', 'scheduled_at']);
            $table->index(['tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_lead_activities');
    }
};
