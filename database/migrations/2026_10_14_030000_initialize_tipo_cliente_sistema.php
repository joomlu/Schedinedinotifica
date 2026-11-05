<?php

use App\Services\TipoClienteSistemaService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(TipoClienteSistemaService::class)->initialize();
    }

    public function down(): void
    {
        // I record condivisi e i loro ID non vengono eliminati dal rollback.
    }
};
