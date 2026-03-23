<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 120)->nullable()->after('email');
                $table->unique('username');
            }
            if (!Schema::hasColumn('users', 'display_name')) {
                $table->string('display_name', 120)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono', 40)->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'ruolo_operativo')) {
                $table->string('ruolo_operativo', 30)->nullable()->after('ruolo');
            }
            if (!Schema::hasColumn('users', 'attivo')) {
                $table->boolean('attivo')->default(true)->after('proprietario_id');
            }
            if (!Schema::hasColumn('users', 'ultimo_accesso_at')) {
                $table->timestamp('ultimo_accesso_at')->nullable()->after('attivo');
            }
            if (!Schema::hasColumn('users', 'ultima_uscita_at')) {
                $table->timestamp('ultima_uscita_at')->nullable()->after('ultimo_accesso_at');
            }
            if (!Schema::hasColumn('users', 'ultimo_accesso_ip')) {
                $table->string('ultimo_accesso_ip', 45)->nullable()->after('ultima_uscita_at');
            }
            if (!Schema::hasColumn('users', 'ultimo_logout_ip')) {
                $table->string('ultimo_logout_ip', 45)->nullable()->after('ultimo_accesso_ip');
            }
        });

        DB::table('users')->orderBy('id')->get()->each(function ($user) {
            $username = $user->username ?: $user->email;
            $display = $user->display_name ?: $user->name;
            $role = $user->ruolo_operativo;

            if (!$role && $user->ruolo === 'struttura_user') {
                $role = 'proprietario';
            }

            DB::table('users')->where('id', $user->id)->update([
                'username' => $username,
                'display_name' => $display,
                'ruolo_operativo' => $role,
            ]);
        });

        Schema::create('struttura_accessi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('entrata_at');
            $table->timestamp('uscita_at')->nullable();
            $table->string('ip_entrata', 45)->nullable();
            $table->string('ip_uscita', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['struttura_id', 'entrata_at']);
        });

        Schema::create('struttura_comande', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->unsignedBigInteger('mittente_id');
            $table->unsignedBigInteger('destinatario_id')->nullable();
            $table->string('titolo', 160);
            $table->text('messaggio');
            $table->string('priorita', 20)->default('normale');
            $table->string('stato', 20)->default('da_leggere');
            $table->timestamp('letto_at')->nullable();
            $table->timestamp('chiuso_at')->nullable();
            $table->timestamps();

            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('cascade');
            $table->foreign('mittente_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destinatario_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['struttura_id', 'stato']);
        });

        Schema::create('struttura_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('route_name', 160)->nullable();
            $table->string('metodo', 10)->nullable();
            $table->string('entita_tipo', 80)->nullable();
            $table->string('entita_id', 80)->nullable();
            $table->string('descrizione', 255);
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('struttura_id')->references('id')->on('struttura')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['struttura_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struttura_audit_logs');
        Schema::dropIfExists('struttura_comande');
        Schema::dropIfExists('struttura_accessi');

        Schema::table('users', function (Blueprint $table) {
            foreach (['username'] as $indexColumn) {
                try {
                    $table->dropUnique([$indexColumn]);
                } catch (Throwable $e) {
                }
            }

            foreach (['username', 'display_name', 'telefono', 'ruolo_operativo', 'attivo', 'ultimo_accesso_at', 'ultima_uscita_at', 'ultimo_accesso_ip', 'ultimo_logout_ip'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
