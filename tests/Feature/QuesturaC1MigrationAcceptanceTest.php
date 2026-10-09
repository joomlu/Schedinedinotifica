<?php

namespace Tests\Feature;

use App\Models\Struttura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

// DDL esclusivamente nel database effimero attestato, fuori dalle transazioni dei test.
class QuesturaC1MigrationAcceptanceTest extends TestCase
{
    use StrutturaFixtures;

    protected function tearDown(): void
    {
        try {
            // Il DDL non partecipa alle transazioni di RefreshDatabase. Ricrea
            // soltanto il DB effimero attestato, anche dopo un'asserzione fallita.
            $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
        } finally {
            parent::tearDown();
        }
    }

    public function test_quattro_migration_da_schema_legacy_senza_perdita_dati(): void
    {
        $paths = array_map(fn ($path) => 'database/migrations/'.basename($path), glob(database_path('migrations/*.php')));
        $legacy = array_values(array_filter($paths, fn ($path) => ! str_contains($path, '2026_10_06_')));
        $c1 = array_values(array_diff($paths, $legacy));
        $this->assertCount(4, $c1);
        $this->artisan('migrate:fresh', ['--path' => $legacy, '--force' => true])->assertSuccessful();
        $columns = collect(Schema::getColumns('struttura'))->keyBy('name');
        $this->assertSame('varchar(100)', $columns['questura_password']['type']);
        $this->assertSame('varchar(191)', $columns['questura_wskey']['type']);
        $structures = collect(range(1, 7))->map(fn () => $this->structureFor(null));
        $id = $structures->first()->id;
        DB::table('struttura')->where('id', $id)->update(['questura_username' => 'fixture', 'questura_password' => 'PASSWORD-LEGACY-SINTETICA', 'questura_wskey' => null]);
        $before = DB::table('struttura')->orderBy('id')->get()->map(fn ($row) => collect((array) $row)->except(['questura_password', 'questura_wskey'])->all())->all();
        // Archivi legacy presenti: nessun backfill che abiliti implicitamente il reinvio.
        $tx = DB::table('questura_transmissions')->insertGetId(['struttura_id' => $id, 'mode' => 'send', 'status' => 'uncertain', 'payload' => json_encode(['fixture' => 'legacy']), 'created_at' => now(), 'updated_at' => now()]);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertSame(7, Struttura::count());
        $this->assertSame($before, DB::table('struttura')->orderBy('id')->get()->map(fn ($row) => collect((array) $row)->except(['questura_password', 'questura_wskey'])->all())->all());
        $cipher = DB::table('struttura')->where('id', $id)->value('questura_password');
        $this->assertNotSame('PASSWORD-LEGACY-SINTETICA', $cipher);
        $this->assertSame('PASSWORD-LEGACY-SINTETICA', Struttura::findOrFail($id)->questura_password);
        $this->assertNull(Struttura::findOrFail($id)->questura_wskey);
        $columns = collect(Schema::getColumns('struttura'))->keyBy('name');
        $this->assertSame('text', $columns['questura_password']['type']);
        $this->assertSame('text', $columns['questura_wskey']['type']);
        foreach (['questura_receipts', 'questura_transmission_events', 'questura_send_reservations'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
            $this->assertSame(0, DB::table($table)->count());
        }
        $this->assertNull(DB::table('questura_transmissions')->where('id', $tx)->value('identity_reserved_at'));
        $this->assertSame('uncertain', DB::table('questura_transmissions')->where('id', $tx)->value('status'));
        $this->assertSame(['fixture' => 'legacy'], json_decode(DB::table('questura_transmissions')->where('id', $tx)->value('payload'), true));
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $credentialMigration = require base_path($c1[0]);
        $credentialMigration->up();
        $this->assertSame($cipher, DB::table('struttura')->where('id', $id)->value('questura_password'));
        $schemaBeforeDown = Schema::getColumns('struttura');
        foreach (array_reverse($c1) as $path) {
            (require base_path($path))->down();
        }
        $this->assertSame($schemaBeforeDown, Schema::getColumns('struttura'));
        $this->assertSame($cipher, DB::table('struttura')->where('id', $id)->value('questura_password'));
        $this->assertSame(7, Struttura::count());
        $this->assertSame(1, DB::table('questura_transmissions')->count());
        foreach ($c1 as $path) {
            $this->assertSame(1, DB::table('migrations')->where('migration', basename($path, '.php'))->count());
        }
    }
}
