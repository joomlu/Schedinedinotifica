<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
                if (!Schema::hasColumn('struttura', 'questura_wskey')) {
                    $table->string('questura_wskey', 191)->nullable()->after('questura_password');
                }
                if (!Schema::hasColumn('struttura', 'questura_codici')) {
                    $table->string('questura_codici', 191)->nullable()->after('questura_wskey');
                }
                if (!Schema::hasColumn('struttura', 'questura_puk')) {
                    $table->string('questura_puk', 191)->nullable()->after('questura_codici');
                }
            });
        }

        if (!Schema::hasTable('questura_transmissions')) {
            Schema::create('questura_transmissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('struttura_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('questura_export_id')->nullable()->index();
                $table->string('mode', 20)->index();
                $table->string('scope_type', 20)->default('periodo')->index();
                $table->date('dal')->nullable();
                $table->date('al')->nullable();
                $table->json('schedina_ids')->nullable();
                $table->unsignedInteger('schedine_count')->default(0);
                $table->unsignedInteger('righe_count')->default(0);
                $table->string('status', 20)->default('pending')->index();
                $table->string('response_code', 50)->nullable();
                $table->string('response_message', 191)->nullable();
                $table->text('response_detail')->nullable();
                $table->json('payload')->nullable();
                $table->json('result')->nullable();
                $table->string('receipt_filename', 191)->nullable();
                $table->string('receipt_path', 191)->nullable();
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                if (!Schema::hasColumn('schedina', 'questura_sent_at')) {
                    $table->timestamp('questura_sent_at')->nullable()->after('last_questura_export_id');
                }
                if (!Schema::hasColumn('schedina', 'questura_send_count')) {
                    $table->unsignedInteger('questura_send_count')->default(0)->after('questura_sent_at');
                }
                if (!Schema::hasColumn('schedina', 'last_questura_transmission_id')) {
                    $table->unsignedBigInteger('last_questura_transmission_id')->nullable()->after('questura_send_count')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                if (Schema::hasColumn('schedina', 'last_questura_transmission_id')) {
                    $table->dropIndex(['last_questura_transmission_id']);
                    $table->dropColumn('last_questura_transmission_id');
                }
                if (Schema::hasColumn('schedina', 'questura_send_count')) {
                    $table->dropColumn('questura_send_count');
                }
                if (Schema::hasColumn('schedina', 'questura_sent_at')) {
                    $table->dropColumn('questura_sent_at');
                }
            });
        }

        Schema::dropIfExists('questura_transmissions');

        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
                foreach (['questura_puk', 'questura_codici', 'questura_wskey'] as $column) {
                    if (Schema::hasColumn('struttura', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
