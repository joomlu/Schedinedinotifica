<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_lead_activities', function (Blueprint $table) {
            $table->string('direzione', 20)->default('interna')->after('tipo');
            $table->index(['direzione', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('crm_lead_activities', function (Blueprint $table) {
            $table->dropIndex(['direzione', 'scheduled_at']);
            $table->dropColumn('direzione');
        });
    }
};
