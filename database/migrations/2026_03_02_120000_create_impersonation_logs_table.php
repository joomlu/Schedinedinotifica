<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('impersonation_logs')) {
            Schema::create('impersonation_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('impersonator_id');
                $table->unsignedBigInteger('impersonated_id');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->foreign('impersonator_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('impersonated_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['impersonator_id', 'impersonated_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
    }
};
