<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class Ross1000LegacySchemaAudit extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public function test_schema_pre_migration_riproduce_snapshot_assente_e_upgrade_isolato_ripristina_la_pagina(): void
    {
        Http::preventStrayRequests();
        $owner = $this->ownerFor($this->actor('admin'));
        $s = $this->structureFor($owner);
        $s->update(['regione' => 'Emilia-Romagna']);
        $user = $this->actor('struttura_user', $owner->id, $s->id);
        $user->update(['ruolo_operativo' => 'proprietario']);
        $this->actingAs($user);
        foreach (['istat_transmission_events', 'istat_communication_days'] as $table) {
            Schema::drop($table);
        }
        Schema::table('istat_exports', fn (Blueprint $table) => $table->dropColumn(['sha256', 'encrypted_file', 'snapshot', 'expires_at', 'minimized_at']));
        Schema::table('istat_transmissions', fn (Blueprint $table) => $table->dropColumn(['idempotency_key', 'attempts', 'reconciled_at']));
        DB::table('migrations')->where('migration', '2026_10_07_180000_protect_istat_cycle')->delete();
        $this->assertFalse(Schema::hasColumn('istat_exports', 'snapshot'));
        $this->get('/struttura')->assertOk()->assertSee('Configurazione Ross1000');
        $this->withoutExceptionHandling();
        try {
            $this->get('/istat-tabella-a');
            $this->fail('Schema incompleto non riprodotto');
        } catch (QueryException $error) {
            $this->assertSame('42S22', (string) $error->getCode());
            $this->assertStringContainsString('snapshot', $error->getMessage());
            $this->assertStringContainsString('istat_exports', $error->getSql());
        }
        $this->assertSame(0, Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_07_180000_protect_istat_cycle.php', '--force' => true]));
        $this->assertTrue(Schema::hasColumn('istat_exports', 'snapshot'));
        $this->assertTrue(Schema::hasTable('istat_transmission_events'));
        $this->get('/istat-tabella-a')->assertOk()->assertSee('Codice struttura Ross1000');
        $this->assertTrue(DB::table('migrations')->where('migration', '2026_10_07_180000_protect_istat_cycle')->exists());
        Http::assertNothingSent();
    }
}
