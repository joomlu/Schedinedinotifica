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
            if (!Schema::hasColumn('clienti', 'azienda')) {
                $table->string('azienda', 191)->nullable()->after('observation_reg');
            }
            if (!Schema::hasColumn('clienti', 'cap_az')) {
                $table->string('cap_az', 20)->nullable()->after('azienda');
            }
            if (!Schema::hasColumn('clienti', 'cf_az')) {
                $table->string('cf_az', 50)->nullable()->after('cap_az');
            }
            if (!Schema::hasColumn('clienti', 'pi_az')) {
                $table->string('pi_az', 50)->nullable()->after('cf_az');
            }
            if (!Schema::hasColumn('clienti', 'typeaway_az')) {
                $table->string('typeaway_az', 100)->nullable()->after('pi_az');
            }
            if (!Schema::hasColumn('clienti', 'address_az')) {
                $table->string('address_az', 191)->nullable()->after('typeaway_az');
            }
            if (!Schema::hasColumn('clienti', 'number_az')) {
                $table->string('number_az', 20)->nullable()->after('address_az');
            }
            if (!Schema::hasColumn('clienti', 'email_az')) {
                $table->string('email_az', 191)->nullable()->after('number_az');
            }
            if (!Schema::hasColumn('clienti', 'phone_az')) {
                $table->string('phone_az', 50)->nullable()->after('email_az');
            }
            if (!Schema::hasColumn('clienti', 'fax_az')) {
                $table->string('fax_az', 50)->nullable()->after('phone_az');
            }
            if (!Schema::hasColumn('clienti', 'cellphone_az')) {
                $table->string('cellphone_az', 50)->nullable()->after('fax_az');
            }
            if (!Schema::hasColumn('clienti', 'country_az')) {
                $table->string('country_az', 191)->nullable()->after('cellphone_az');
            }
            if (!Schema::hasColumn('clienti', 'city_az')) {
                $table->string('city_az', 191)->nullable()->after('country_az');
            }
            if (!Schema::hasColumn('clienti', 'region_az')) {
                $table->string('region_az', 191)->nullable()->after('city_az');
            }
            if (!Schema::hasColumn('clienti', 'province_az')) {
                $table->string('province_az', 191)->nullable()->after('region_az');
            }
            if (!Schema::hasColumn('clienti', 'sdi_az')) {
                $table->string('sdi_az', 120)->nullable()->after('province_az');
            }
            if (!Schema::hasColumn('clienti', 'website')) {
                $table->string('website', 191)->nullable()->after('sdi_az');
            }
            if (!Schema::hasColumn('clienti', 'desc_az')) {
                $table->text('desc_az')->nullable()->after('website');
            }
            if (!Schema::hasColumn('clienti', 'nota')) {
                $table->text('nota')->nullable()->after('desc_az');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clienti')) {
            return;
        }

        Schema::table('clienti', function (Blueprint $table) {
            $drop = [];
            $cols = [
                'azienda',
                'cap_az',
                'cf_az',
                'pi_az',
                'typeaway_az',
                'address_az',
                'number_az',
                'email_az',
                'phone_az',
                'fax_az',
                'cellphone_az',
                'country_az',
                'city_az',
                'region_az',
                'province_az',
                'sdi_az',
                'website',
                'desc_az',
                'nota',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('clienti', $col)) {
                    $drop[] = $col;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};

