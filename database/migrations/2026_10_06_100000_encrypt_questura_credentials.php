<?php

use App\Support\Questura\LegacyCredentials;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preflight completo: nessuna scrittura se anche un solo involucro è ambiguo.
        DB::table('struttura')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                foreach (['questura_password', 'questura_wskey'] as $field) {
                    LegacyCredentials::encrypted($row->{$field});
                }
            }
        });
        Schema::table('struttura', function (Blueprint $table) {
            $table->text('questura_password')->nullable()->change();
            $table->text('questura_wskey')->nullable()->change();
        });
        DB::transaction(function () {
            DB::table('struttura')->orderBy('id')->lockForUpdate()->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('struttura')->where('id', $row->id)->update([
                        'questura_password' => LegacyCredentials::encrypted($row->questura_password),
                        'questura_wskey' => LegacyCredentials::encrypted($row->questura_wskey),
                    ]);
                }
            });
        });
    }

    public function down(): void
    {
        // Rollback non distruttivo: nessun ritorno al plaintext o restringimento colonne.
    }
};
