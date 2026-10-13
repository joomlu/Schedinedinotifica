<?php

use App\Services\GruppiStrutturaService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(GruppiStrutturaService::class)->recoverLegacyReferences();
    }

    public function down(): void
    {
        throw new RuntimeException('Recupero Gruppi non reversibile automaticamente: usare il checkpoint coordinato.');
    }
};
