<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && !Schema::hasTable('clienti')) {
            Schema::rename('customers', 'clienti');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clienti') && !Schema::hasTable('customers')) {
            Schema::rename('clienti', 'customers');
        }
    }
};
