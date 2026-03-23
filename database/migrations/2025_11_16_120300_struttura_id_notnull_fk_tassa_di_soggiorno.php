<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('tassa_di_soggiorno') || !Schema::hasColumn('tassa_di_soggiorno', 'struttura_id')) {
            return;
        }

        $database = Schema::getConnection()->getDatabaseName();
        $constraintExists = DB::table('INFORMATION_SCHEMA.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'tassa_di_soggiorno')
            ->where('CONSTRAINT_NAME', 'tassa_di_soggiorno_struttura_id_foreign')
            ->exists();

        Schema::table('tassa_di_soggiorno', function (Blueprint $table) use ($constraintExists) {
            if (!$constraintExists) {
                $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('cascade');
            }
            $table->unsignedBigInteger('struttura_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tassa_di_soggiorno') || !Schema::hasColumn('tassa_di_soggiorno', 'struttura_id')) {
            return;
        }

        $database = Schema::getConnection()->getDatabaseName();
        $constraintExists = DB::table('INFORMATION_SCHEMA.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'tassa_di_soggiorno')
            ->where('CONSTRAINT_NAME', 'tassa_di_soggiorno_struttura_id_foreign')
            ->exists();

        Schema::table('tassa_di_soggiorno', function (Blueprint $table) use ($constraintExists) {
            if ($constraintExists) {
                $table->dropForeign(['struttura_id']);
            }
            $table->unsignedBigInteger('struttura_id')->nullable(true)->change();
        });
    }
};
