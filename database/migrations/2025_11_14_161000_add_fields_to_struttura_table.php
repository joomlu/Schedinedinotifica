<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('city')->nullable();
                $table->string('fax')->nullable();
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('cp')->nullable();
                $table->string('web')->nullable();
                $table->string('cf')->nullable();
                $table->string('piva')->nullable();
                $table->date('startact')->nullable();
                $table->string('typology')->nullable();
                $table->date('closeact')->nullable();
                $table->string('clasification')->nullable();
                $table->integer('numshedine')->nullable();
                $table->integer('roomdisp')->nullable();
                $table->string('ref')->nullable();
                $table->integer('beddisp')->nullable();
                $table->string('refpass')->nullable();
                $table->integer('updatedbed')->nullable();
            });
        }
    }
    public function down(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->dropColumn([
                'name','phone','city','fax','address','email','cp','web','cf','piva','startact','typology','closeact','clasification','numshedine','roomdisp','ref','beddisp','refpass','updatedbed'
            ]);
        });
    }
};
