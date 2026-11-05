<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clienti', function (Blueprint $table) {
            $table->char('wizard_submission_key', 64)->nullable()->unique();
            $table->char('wizard_submission_digest', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clienti', function (Blueprint $table) {
            $table->dropUnique(['wizard_submission_key']);
            $table->dropColumn(['wizard_submission_key', 'wizard_submission_digest']);
        });
    }
};
