<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalization: we keep the `tassa` table. No action here.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op on rollback as well to avoid destructive side effects.
    }
};
