<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questura_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('questura_transmission_id')->nullable()->change();
            $table->timestamp('acquired_at')->nullable();
            $table->timestamp('retained_until')->nullable()->index();
            $table->timestamp('purged_at')->nullable();
        });
        foreach (['questura_exports', 'questura_transmissions'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('questura_receipt_id')->nullable()->constrained('questura_receipts')->restrictOnDelete();
                $table->timestamp('finalized_at')->nullable();
                $table->timestamp('payload_deleted_at')->nullable();
                $table->timestamp('reconciled_at')->nullable();
                $table->unsignedBigInteger('reconciled_by')->nullable();
            });
        }
        Schema::table('questura_exports', function (Blueprint $table) {
            $table->string('path', 191)->nullable()->change();
            $table->date('dal')->nullable()->change();
            $table->date('al')->nullable()->change();
            $table->date('communication_date')->nullable();
        });
    }

    public function down(): void
    {
        // Nessun rollback distruttivo di ricevute, audit o minimizzazione.
    }
};
