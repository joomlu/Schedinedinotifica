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
                if (!Schema::hasColumn('struttura', 'istat_codice_struttura')) {
                    $table->string('istat_codice_struttura', 50)->nullable()->after('istat_password');
                }
                if (!Schema::hasColumn('struttura', 'istat_ws_url')) {
                    $table->string('istat_ws_url', 191)->nullable()->after('istat_codice_struttura');
                }
                if (!Schema::hasColumn('struttura', 'istat_ws_simulazione')) {
                    $table->boolean('istat_ws_simulazione')->default(true)->after('istat_ws_url');
                }
            });
        }

        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                if (!Schema::hasColumn('schedina', 'istat_tipo_turismo')) {
                    $table->string('istat_tipo_turismo', 100)->nullable()->after('id_prenotazione_esterna');
                }
                if (!Schema::hasColumn('schedina', 'istat_mezzo_trasporto')) {
                    $table->string('istat_mezzo_trasporto', 100)->nullable()->after('istat_tipo_turismo');
                }
                if (!Schema::hasColumn('schedina', 'istat_canale_prenotazione')) {
                    $table->string('istat_canale_prenotazione', 100)->nullable()->after('istat_mezzo_trasporto');
                }
                if (!Schema::hasColumn('schedina', 'istat_titolo_studio')) {
                    $table->string('istat_titolo_studio', 100)->nullable()->after('istat_canale_prenotazione');
                }
                if (!Schema::hasColumn('schedina', 'istat_professione')) {
                    $table->string('istat_professione', 100)->nullable()->after('istat_titolo_studio');
                }
                if (!Schema::hasColumn('schedina', 'istat_non_turista')) {
                    $table->boolean('istat_non_turista')->default(false)->after('istat_professione');
                }
                if (!Schema::hasColumn('schedina', 'istat_exported_at')) {
                    $table->timestamp('istat_exported_at')->nullable()->after('istat_non_turista');
                }
                if (!Schema::hasColumn('schedina', 'istat_export_count')) {
                    $table->unsignedInteger('istat_export_count')->default(0)->after('istat_exported_at');
                }
                if (!Schema::hasColumn('schedina', 'last_istat_export_id')) {
                    $table->unsignedBigInteger('last_istat_export_id')->nullable()->after('istat_export_count')->index();
                }
                if (!Schema::hasColumn('schedina', 'istat_sent_at')) {
                    $table->timestamp('istat_sent_at')->nullable()->after('last_istat_export_id');
                }
                if (!Schema::hasColumn('schedina', 'istat_send_count')) {
                    $table->unsignedInteger('istat_send_count')->default(0)->after('istat_sent_at');
                }
                if (!Schema::hasColumn('schedina', 'last_istat_transmission_id')) {
                    $table->unsignedBigInteger('last_istat_transmission_id')->nullable()->after('istat_send_count')->index();
                }
            });
        }

        if (!Schema::hasTable('istat_movimenti_giornalieri')) {
            Schema::create('istat_movimenti_giornalieri', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('struttura_id')->index();
                $table->date('giorno')->index();
                $table->boolean('aperta')->nullable();
                $table->boolean('movimento_zero')->nullable();
                $table->unsignedInteger('camere_disponibili')->nullable();
                $table->unsignedInteger('letti_disponibili')->nullable();
                $table->unsignedInteger('camere_occupate')->nullable();
                $table->unsignedInteger('arrivi')->nullable();
                $table->unsignedInteger('partenze')->nullable();
                $table->unsignedInteger('presenti')->nullable();
                $table->json('override_payload')->nullable();
                $table->text('note')->nullable();
                $table->timestamp('confermato_il')->nullable();
                $table->unsignedBigInteger('confermato_da')->nullable()->index();
                $table->timestamps();
                $table->unique(['struttura_id', 'giorno']);
            });
        }

        if (!Schema::hasTable('istat_exports')) {
            Schema::create('istat_exports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('struttura_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->date('dal');
                $table->date('al');
                $table->string('filename', 191);
                $table->string('path', 191);
                $table->unsignedInteger('schedine_count')->default(0);
                $table->unsignedInteger('movimenti_count')->default(0);
                $table->json('schedina_ids')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('istat_transmissions')) {
            Schema::create('istat_transmissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('struttura_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('istat_export_id')->nullable()->index();
                $table->string('mode', 20)->index();
                $table->date('dal')->nullable();
                $table->date('al')->nullable();
                $table->json('schedina_ids')->nullable();
                $table->unsignedInteger('schedine_count')->default(0);
                $table->unsignedInteger('movimenti_count')->default(0);
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
    }

    public function down(): void
    {
        if (Schema::hasTable('schedina')) {
            Schema::table('schedina', function (Blueprint $table) {
                foreach (['last_istat_transmission_id', 'last_istat_export_id'] as $indexed) {
                    if (Schema::hasColumn('schedina', $indexed)) {
                        $table->dropIndex([$indexed]);
                    }
                }
                foreach ([
                    'last_istat_transmission_id', 'istat_send_count', 'istat_sent_at', 'last_istat_export_id',
                    'istat_export_count', 'istat_exported_at', 'istat_professione', 'istat_titolo_studio',
                    'istat_canale_prenotazione', 'istat_mezzo_trasporto', 'istat_tipo_turismo', 'istat_non_turista'
                ] as $column) {
                    if (Schema::hasColumn('schedina', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('istat_transmissions');
        Schema::dropIfExists('istat_exports');
        Schema::dropIfExists('istat_movimenti_giornalieri');

        if (Schema::hasTable('struttura')) {
            Schema::table('struttura', function (Blueprint $table) {
                foreach (['istat_ws_simulazione', 'istat_ws_url', 'istat_codice_struttura'] as $column) {
                    if (Schema::hasColumn('struttura', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
