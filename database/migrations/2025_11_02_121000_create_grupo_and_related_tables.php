<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 'grupo'
        if (! Schema::hasTable('grupo')) {
            Schema::create('grupo', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // 'subgrupo'
        if (! Schema::hasTable('subgrupo')) {
            Schema::create('subgrupo', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // 'subgrupo1'
        if (! Schema::hasTable('subgrupo1')) {
            Schema::create('subgrupo1', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        // 'typedoc'
        if (! Schema::hasTable('typedoc')) {
            Schema::create('typedoc', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->timestamps();
            });
        }

        // 'title'
        if (! Schema::hasTable('title')) {
            Schema::create('title', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('title');
        Schema::dropIfExists('typedoc');
        Schema::dropIfExists('subgrupo1');
        Schema::dropIfExists('subgrupo');
        Schema::dropIfExists('grupo');
    }
};
