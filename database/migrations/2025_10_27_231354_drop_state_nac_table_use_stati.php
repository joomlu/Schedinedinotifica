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
        // Drop state_nac table - duplicate of stati table
        // stati table is more complete with codice_questura, sigla, cittadinanza, codice_istat
        Schema::dropIfExists('state_nac');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate state_nac table on rollback
        Schema::create('state_nac', function (Blueprint $table) {
            $table->integer('id')->primary()->autoIncrement();
            $table->string('name', 255);
            $table->string('code', 10);
            $table->string('abrev', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }
};
