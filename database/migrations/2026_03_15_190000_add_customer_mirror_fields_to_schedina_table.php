<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedina', function (Blueprint $table) {
            if (!Schema::hasColumn('schedina', 'customer_type_housed')) {
                $table->string('customer_type_housed', 100)->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('schedina', 'customer_group')) {
                $table->string('customer_group', 191)->nullable()->after('customer_type_housed');
            }
            if (!Schema::hasColumn('schedina', 'customer_subgroup')) {
                $table->string('customer_subgroup', 191)->nullable()->after('customer_group');
            }
            if (!Schema::hasColumn('schedina', 'customer_subgroup1')) {
                $table->string('customer_subgroup1', 191)->nullable()->after('customer_subgroup');
            }
            if (!Schema::hasColumn('schedina', 'customer_email')) {
                $table->string('customer_email', 191)->nullable()->after('customer_subgroup1');
            }
            if (!Schema::hasColumn('schedina', 'customer_phone')) {
                $table->string('customer_phone', 50)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('schedina', 'customer_cellphone')) {
                $table->string('customer_cellphone', 50)->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('schedina', 'customer_fax')) {
                $table->string('customer_fax', 50)->nullable()->after('customer_cellphone');
            }
            if (!Schema::hasColumn('schedina', 'customer_observation')) {
                $table->text('customer_observation')->nullable()->after('customer_fax');
            }
            if (!Schema::hasColumn('schedina', 'customer_anag_observation')) {
                $table->text('customer_anag_observation')->nullable()->after('customer_observation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedina', function (Blueprint $table) {
            foreach ([
                'customer_type_housed',
                'customer_group',
                'customer_subgroup',
                'customer_subgroup1',
                'customer_email',
                'customer_phone',
                'customer_cellphone',
                'customer_fax',
                'customer_observation',
                'customer_anag_observation',
            ] as $column) {
                if (Schema::hasColumn('schedina', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
