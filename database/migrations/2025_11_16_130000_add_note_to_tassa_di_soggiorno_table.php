<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (!Schema::hasTable('tassa_di_soggiorno') || Schema::hasColumn('tassa_di_soggiorno', 'note')) {
            return;
        }

        Schema::table('tassa_di_soggiorno', function (Blueprint $table) {
            $table->text('note')->nullable()->after('min_age_adult');
        });
    }
    public function down() {
        if (!Schema::hasTable('tassa_di_soggiorno') || !Schema::hasColumn('tassa_di_soggiorno', 'note')) {
            return;
        }

        Schema::table('tassa_di_soggiorno', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
