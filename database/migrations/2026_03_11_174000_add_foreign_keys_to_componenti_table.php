<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('componenti')) {
            return;
        }

        // Align type with schedina.id (int unsigned).
        DB::statement('ALTER TABLE componenti MODIFY schedina_id INT UNSIGNED NULL');

        // Remove orphans before enabling FK constraints.
        $orphansNoSchedina = DB::table('componenti as c')
            ->leftJoin('schedina as s', 's.id', '=', 'c.schedina_id')
            ->whereNull('s.id')
            ->pluck('c.id')
            ->all();
        if (!empty($orphansNoSchedina)) {
            DB::table('componenti')->whereIn('id', $orphansNoSchedina)->delete();
        }

        $orphansCustomerMissing = DB::table('componenti as c')
            ->leftJoin('clienti as cl', 'cl.id', '=', 'c.customer_id')
            ->whereNotNull('c.customer_id')
            ->whereNull('cl.id')
            ->pluck('c.id')
            ->all();
        if (!empty($orphansCustomerMissing)) {
            DB::table('componenti')->whereIn('id', $orphansCustomerMissing)->update(['customer_id' => null]);
        }

        Schema::table('componenti', function (Blueprint $table) {
            if (!$this->hasIndex('componenti', 'componenti_schedina_id_index')) {
                $table->index('schedina_id', 'componenti_schedina_id_index');
            }
            if (!$this->hasIndex('componenti', 'componenti_customer_id_index')) {
                $table->index('customer_id', 'componenti_customer_id_index');
            }
            if (!$this->hasIndex('componenti', 'componenti_struttura_id_index')) {
                $table->index('struttura_id', 'componenti_struttura_id_index');
            }
        });

        Schema::table('componenti', function (Blueprint $table) {
            if (!$this->hasForeignKey('componenti', 'fk_componenti_schedina')) {
                $table->foreign('schedina_id', 'fk_componenti_schedina')
                    ->references('id')
                    ->on('schedina')
                    ->onDelete('cascade');
            }

            if (!$this->hasForeignKey('componenti', 'fk_componenti_customer')) {
                $table->foreign('customer_id', 'fk_componenti_customer')
                    ->references('id')
                    ->on('clienti')
                    ->nullOnDelete();
            }

            if (!$this->hasForeignKey('componenti', 'fk_componenti_struttura')) {
                $table->foreign('struttura_id', 'fk_componenti_struttura')
                    ->references('id')
                    ->on('struttura')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('componenti')) {
            return;
        }

        Schema::table('componenti', function (Blueprint $table) {
            if ($this->hasForeignKey('componenti', 'fk_componenti_schedina')) {
                $table->dropForeign('fk_componenti_schedina');
            }
            if ($this->hasForeignKey('componenti', 'fk_componenti_customer')) {
                $table->dropForeign('fk_componenti_customer');
            }
            if ($this->hasForeignKey('componenti', 'fk_componenti_struttura')) {
                $table->dropForeign('fk_componenti_struttura');
            }
        });
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = "FOREIGN KEY" AND CONSTRAINT_NAME = ? LIMIT 1',
            [$db, $table, $constraint]
        );

        return !empty($rows);
    }

    private function hasIndex(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$db, $table, $index]
        );

        return !empty($rows);
    }
};
