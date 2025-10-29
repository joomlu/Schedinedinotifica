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
        Schema::table('users', function (Blueprint $table) {
            // Role: superadmin, admin (satellite), hotel_owner, hotel_staff
            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['superadmin', 'admin', 'hotel_owner', 'hotel_staff'])
                    ->default('hotel_staff')
                    ->after('password');
            }

            // structure_id: aggiungi colonna se mancante
            if (! Schema::hasColumn('users', 'structure_id')) {
                $table->unsignedBigInteger('structure_id')->nullable()->after('role');
            }
        });

        // Aggiungi FK solo se la tabella `estructura` esiste
        if (Schema::hasTable('estructura')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->foreign('structure_id')->references('id')->on('estructura')->onDelete('cascade');
                } catch (\Throwable $e) {
                    // ignore if FK already exists
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign(['structure_id']);
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('users', 'structure_id')) {
                $table->dropColumn('structure_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
