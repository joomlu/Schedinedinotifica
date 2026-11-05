<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_import_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->unsignedBigInteger('proprietario_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('status', 50)->default('draft');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('duplicate_hotel_rows')->default(0);
            $table->unsignedInteger('duplicate_chain_rows')->default(0);
            $table->unsignedInteger('duplicate_file_rows')->default(0);
            $table->unsignedInteger('needs_review_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['struttura_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_import_batches');
    }
};
