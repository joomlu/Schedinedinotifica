<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cestino_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('entity_type', 100)->index();
            $table->string('entity_class', 191);
            $table->unsignedBigInteger('original_id')->nullable()->index();
            $table->string('title', 191)->nullable();
            $table->string('code', 191)->nullable();
            $table->string('circuito', 50)->nullable()->index();
            $table->string('source', 100)->nullable()->index();
            $table->json('payload');
            $table->timestamp('deleted_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cestino_items');
    }
};
