<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_leads', 'struttura_id')) {
                $table->unsignedBigInteger('struttura_id')->nullable()->after('stato');
                $table->foreign('struttura_id')->references('id')->on('struttura')->nullOnDelete();
                $table->index(['struttura_id', 'stato']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            if (Schema::hasColumn('crm_leads', 'struttura_id')) {
                $table->dropForeign(['struttura_id']);
                $table->dropIndex(['struttura_id', 'stato']);
                $table->dropColumn('struttura_id');
            }
        });
    }
};
