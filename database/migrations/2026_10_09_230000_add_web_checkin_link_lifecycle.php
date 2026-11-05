<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_checkin_richieste', function (Blueprint $table) {
            $table->string('short_token', 32)->nullable()->unique();
            $table->dateTime('link_expires_at')->nullable();
            $table->dateTime('link_revoked_at')->nullable();
            $table->dateTime('link_issued_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('web_checkin_richieste', function (Blueprint $table) {
            $table->dropUnique(['short_token']);
            $table->dropColumn(['short_token', 'link_expires_at', 'link_revoked_at', 'link_issued_at']);
        });
    }
};
