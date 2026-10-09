<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ReleaseSyntheticBackupMigrationAudit extends TestCase
{
    public function test_backup_restore_e_upgrade_delle_due_migration_su_dati_sintetici(): void
    {
        $connection = config('database.connections.mysql');
        $this->assertTrue(app()->environment('testing'));
        $this->assertMatchesRegularExpression('/^test_geo_[a-f0-9]{32}$/', $connection['database']);
        $this->assertSame('127.0.0.1', $connection['host']);
        $this->assertGreaterThanOrEqual(49152, (int) $connection['port']);
        $this->assertSame('fixture', $connection['username']);
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $names = ['2026_10_07_180000_create_tassa_exports_and_receipt_image', '2026_10_09_230000_add_web_checkin_link_lifecycle'];
        foreach (array_reverse($names) as $name) {
            (require database_path('migrations/'.$name.'.php'))->down();
            DB::table('migrations')->where('migration', $name)->delete();
        }
        Schema::create('release_backup_probe', function ($table) {
            $table->integer('id')->primary();
            $table->text('ciphertext');
            $table->date('planned_arrival');
        });
        DB::table('release_backup_probe')->insert(['id' => 1, 'ciphertext' => Crypt::encryptString('credenziale-sintetica'), 'planned_arrival' => '2026-10-10']);
        $before = DB::table('release_backup_probe')->first();
        $history = DB::table('migrations')->orderBy('id')->get()->toJson();
        $finder = new ExecutableFinder;
        $dump = $finder->find('mysqldump');
        $client = $finder->find('mysql');
        $this->assertNotNull($dump);
        $this->assertNotNull($client);
        $arguments = ['--no-defaults', '--protocol=TCP', '--host=127.0.0.1', '--port='.$connection['port'], '--user=fixture'];
        $environment = ['MYSQL_PWD' => $connection['password']];
        $process = new Process(array_merge([$dump], $arguments, ['--single-transaction', '--skip-lock-tables', '--no-tablespaces', $connection['database']]), null, $environment);
        $process->mustRun();
        $snapshot = $process->getOutput();
        $this->assertStringContainsString('release_backup_probe', $snapshot);
        foreach ($names as $name) {
            $this->artisan('migrate', ['--force' => true, '--path' => 'database/migrations/'.$name.'.php'])->assertSuccessful();
        }
        $this->assertTrue(Schema::hasColumn('web_checkin_richieste', 'short_token'));
        $this->assertTrue(Schema::hasTable('tassa_exports'));
        $this->assertSame((array) $before, (array) DB::table('release_backup_probe')->first());
        // Solo la base effimera attestata: simulazione di recupero integrale prima release.
        Schema::dropAllTables();
        $restore = new Process(array_merge([$client], $arguments, [$connection['database']]), null, $environment);
        $restore->setInput($snapshot);
        $restore->mustRun();
        $this->assertSame($history, DB::table('migrations')->orderBy('id')->get()->toJson());
        $this->assertFalse(Schema::hasColumn('web_checkin_richieste', 'short_token'));
        $this->assertFalse(Schema::hasTable('tassa_exports'));
        $this->assertSame((array) $before, (array) DB::table('release_backup_probe')->first());
        $this->assertSame('credenziale-sintetica', Crypt::decryptString(DB::table('release_backup_probe')->value('ciphertext')));
        foreach ($names as $name) {
            $this->artisan('migrate', ['--force' => true, '--path' => 'database/migrations/'.$name.'.php'])->assertSuccessful();
        }
        $this->assertTrue(Schema::hasColumn('tassa_di_soggiorno', 'ricevuta_immagine'));
        $this->assertTrue(Schema::hasColumn('web_checkin_richieste', 'link_expires_at'));
        $this->assertSame(2, DB::table('migrations')->whereIn('migration', $names)->count());
        $this->assertSame((array) $before, (array) DB::table('release_backup_probe')->first());
    }
}
