<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('struttura', function (Blueprint $table) {
            $table->text('istat_username')->nullable()->change();
            $table->text('istat_password')->nullable()->change();
        });
        $this->protectCredentials();
        Schema::table('istat_exports', function (Blueprint $table) {
            $table->string('sha256', 64)->nullable();
            $table->boolean('encrypted_file')->default(false);
            $table->json('snapshot')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('minimized_at')->nullable();
        });
        Schema::table('istat_transmissions', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('reconciled_at')->nullable();
        });
        Schema::create('istat_communication_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id');
            $table->date('giorno');
            $table->unsignedBigInteger('istat_transmission_id');
            $table->unique(['struttura_id', 'giorno']);
            $table->index('istat_transmission_id');
        });
        Schema::create('istat_transmission_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('struttura_id')->index();
            $table->unsignedBigInteger('istat_transmission_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 30);
            $table->json('result')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function protectCredentials(): void
    {
        DB::table('struttura')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $changes = [];
                foreach (['istat_username', 'istat_password'] as $field) {
                    $value = $row->{$field};
                    if ($value === null || $value === '') {
                        continue;
                    }
                    // Un contenitore cifrato preesistente viene preservato anche se
                    // non decifrabile: non ricifrare il ciphertext come password.
                    $container = json_decode((string) base64_decode($value, true), true);
                    if (is_array($container) && isset($container['iv'], $container['value'], $container['mac'])) {
                        continue;
                    }
                    $changes[$field] = Crypt::encryptString($value);
                }
                if ($changes) {
                    DB::table('struttura')->where('id', $row->id)->update($changes);
                }
            }
        });
    }

    public function down(): void
    {
        // Rollback conservativo: non decifra credenziali e non perde lo storico.
    }
};
