<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('struttura', fn (Blueprint $t) => $t->string('web_checkin_colore', 7)->nullable());
        Schema::table('web_checkin_modelli', function (Blueprint $t) {
            $t->text('impostazioni')->nullable();
            $t->softDeletes();
        });
        Schema::table('web_checkin_comunicazioni', function (Blueprint $t) {
            $t->timestamp('nascosta_at')->nullable();
            $t->timestamp('tentativo_at')->nullable();
            $t->timestamp('accettato_at')->nullable();
            $t->string('errore_codice', 40)->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('web_checkin_comunicazioni', fn (Blueprint $t) => $t->dropColumn(['nascosta_at', 'tentativo_at', 'accettato_at', 'errore_codice']));
        Schema::table('web_checkin_modelli', fn (Blueprint $t) => $t->dropColumn(['impostazioni', 'deleted_at']));
        Schema::table('struttura', fn (Blueprint $t) => $t->dropColumn('web_checkin_colore'));
    }
};
