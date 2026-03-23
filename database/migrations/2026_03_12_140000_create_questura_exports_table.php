<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('questura_exports')) {
            Schema::create('questura_exports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('struttura_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->date('dal');
                $table->date('al');
                $table->string('filename', 191);
                $table->string('path', 191);
                $table->unsignedInteger('schedine_count')->default(0);
                $table->unsignedInteger('righe_count')->default(0);
                $table->json('schedina_ids')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                if (!Schema::hasColumn('schedina', 'questura_exported_at')) {
                    $table->timestamp('questura_exported_at')->nullable()->after('or_published_city');
                }
                if (!Schema::hasColumn('schedina', 'questura_export_count')) {
                    $table->unsignedInteger('questura_export_count')->default(0)->after('questura_exported_at');
                }
                if (!Schema::hasColumn('schedina', 'last_questura_export_id')) {
                    $table->unsignedBigInteger('last_questura_export_id')->nullable()->after('questura_export_count')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                if (Schema::hasColumn('schedina', 'last_questura_export_id')) {
                    $table->dropIndex(['last_questura_export_id']);
                    $table->dropColumn('last_questura_export_id');
                }
                if (Schema::hasColumn('schedina', 'questura_export_count')) {
                    $table->dropColumn('questura_export_count');
                }
                if (Schema::hasColumn('schedina', 'questura_exported_at')) {
                    $table->dropColumn('questura_exported_at');
                }
            });
        }

        Schema::dropIfExists('questura_exports');
    }
};
