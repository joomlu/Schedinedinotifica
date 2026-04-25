<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_import_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedInteger('row_number');
            $table->string('status', 50)->default('draft');
            $table->json('raw_payload');
            $table->json('normalized_payload')->nullable();
            $table->json('notes')->nullable();
            $table->unsignedBigInteger('duplicate_customer_id')->nullable();
            $table->string('duplicate_scope', 50)->nullable();
            $table->unsignedBigInteger('imported_customer_id')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'row_number']);
            $table->index(['batch_id', 'status']);
            $table->index(['duplicate_customer_id']);
            $table->index(['imported_customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_import_rows');
    }
};
