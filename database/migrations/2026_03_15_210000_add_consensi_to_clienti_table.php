<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            if (!Schema::hasColumn('clienti', 'privacy_consent')) {
                $table->boolean('privacy_consent')->nullable()->after('observation_reg');
            }
            if (!Schema::hasColumn('clienti', 'privacy_consent_at')) {
                $table->timestamp('privacy_consent_at')->nullable()->after('privacy_consent');
            }
            if (!Schema::hasColumn('clienti', 'marketing_consent')) {
                $table->boolean('marketing_consent')->nullable()->after('privacy_consent_at');
            }
            if (!Schema::hasColumn('clienti', 'marketing_consent_at')) {
                $table->timestamp('marketing_consent_at')->nullable()->after('marketing_consent');
            }
            if (!Schema::hasColumn('clienti', 'communication_consent')) {
                $table->boolean('communication_consent')->nullable()->after('marketing_consent_at');
            }
            if (!Schema::hasColumn('clienti', 'communication_consent_at')) {
                $table->timestamp('communication_consent_at')->nullable()->after('communication_consent');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            foreach ([
                'communication_consent_at',
                'communication_consent',
                'marketing_consent_at',
                'marketing_consent',
                'privacy_consent_at',
                'privacy_consent',
            ] as $column) {
                if (Schema::hasColumn('clienti', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
