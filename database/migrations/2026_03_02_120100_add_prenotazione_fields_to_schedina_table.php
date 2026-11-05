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
            $table->string('fonte_prenotazione', 30)->nullable()->after('is_arrive');
            $table->string('id_prenotazione_esterna', 100)->nullable()->after('fonte_prenotazione');
            $table->dateTime('agganciata_il')->nullable()->after('id_prenotazione_esterna');
            $table->unsignedBigInteger('agganciata_da')->nullable()->after('agganciata_il');

            $table->index(['fonte_prenotazione', 'id_prenotazione_esterna']);
            $table->index('agganciata_da');
            $table->foreign('agganciata_da')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schedina')) {
            return;
        }

        Schema::table('schedina', function (Blueprint $table) {
            $table->dropForeign(['agganciata_da']);
            $table->dropIndex(['fonte_prenotazione', 'id_prenotazione_esterna']);
            $table->dropIndex(['agganciata_da']);
            $table->dropColumn(['fonte_prenotazione', 'id_prenotazione_esterna', 'agganciata_il', 'agganciata_da']);
        });
    }
};
