<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('strutture');
    }

    public function down(): void
    {
        // Non serve ricreare la tabella vecchia
    }
};
