<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create 'estructura' table if missing (model uses singular table name)
        if (!Schema::hasTable('estructura')) {
            Schema::create('estructura', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('city')->nullable();
                $table->string('fax')->nullable();
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('cp')->nullable(); // CAP / ZIP
                $table->string('web')->nullable();
                $table->string('cf')->nullable(); // Codice fiscale
                $table->string('piva')->nullable(); // Partita IVA
                $table->date('startact')->nullable();
                $table->string('typology')->nullable();
                $table->date('closeact')->nullable();
                $table->string('clasification')->nullable();
                $table->integer('numshedine')->nullable();
                $table->integer('roomdisp')->nullable();
                $table->string('ref')->nullable();
                $table->integer('beddisp')->nullable();
                $table->string('refpass')->nullable();
                $table->timestamp('updatedbed')->nullable();
                $table->timestamps();
            });
        }

        // Create 'tassa' table if missing (model uses singular table name)
        if (!Schema::hasTable('tassa')) {
            Schema::create('tassa', function (Blueprint $table) {
                $table->id();
                $table->string('tassa_soggiorno')->nullable(); // Consider DECIMAL in a future migration if needed
                $table->integer('giorni_massimo')->nullable();
                $table->date('inizio')->nullable();
                $table->date('fine')->nullable();
                $table->string('province')->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();
                $table->integer('max_age_children')->nullable();
                $table->integer('min_age_adult')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't drop tables if other code depends on them; keep down() conservative.
        // If you need to drop, uncomment below lines.
        // Schema::dropIfExists('estructura');
        // Schema::dropIfExists('tassa');
    }
};
