<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedina')) {
            return;
        }

        Schema::table('schedina', function (Blueprint $table) {
            if (!Schema::hasColumn('schedina', 'customer_privacy_consent')) {
                $table->boolean('customer_privacy_consent')->nullable()->after('customer_anag_observation');
            }
            if (!Schema::hasColumn('schedina', 'customer_privacy_consent_at')) {
                $table->timestamp('customer_privacy_consent_at')->nullable()->after('customer_privacy_consent');
            }
            if (!Schema::hasColumn('schedina', 'customer_marketing_consent')) {
                $table->boolean('customer_marketing_consent')->nullable()->after('customer_privacy_consent_at');
            }
            if (!Schema::hasColumn('schedina', 'customer_marketing_consent_at')) {
                $table->timestamp('customer_marketing_consent_at')->nullable()->after('customer_marketing_consent');
            }
            if (!Schema::hasColumn('schedina', 'customer_communication_consent')) {
                $table->boolean('customer_communication_consent')->nullable()->after('customer_marketing_consent_at');
            }
            if (!Schema::hasColumn('schedina', 'customer_communication_consent_at')) {
                $table->timestamp('customer_communication_consent_at')->nullable()->after('customer_communication_consent');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedina')) {
            return;
        }

        Schema::table('schedina', function (Blueprint $table) {
            foreach ([
                'customer_communication_consent_at',
                'customer_communication_consent',
                'customer_marketing_consent_at',
                'customer_marketing_consent',
                'customer_privacy_consent_at',
                'customer_privacy_consent',
            ] as $column) {
                if (Schema::hasColumn('schedina', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
