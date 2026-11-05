<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class Ross1000BackupRestoreAudit extends TestCase
{
    use StrutturaFixtures;

    public function test_backup_upgrade_mirato_e_recupero_integrale_preservano_dati_e_credenziali_sintetiche(): void
    {
        $connection = config('database.connections.mysql');
        $this->assertTrue(app()->environment('testing'));
        $this->assertMatchesRegularExpression('/^test_geo_[a-f0-9]{32}$/', $connection['database']);
        $this->assertSame('fixture', $connection['username']);
        $this->assertGreaterThanOrEqual(49152, (int) $connection['port']);
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $name = '2026_10_07_180000_protect_istat_cycle';
        Schema::drop('istat_communication_days');
        Schema::drop('istat_transmission_events');
        Schema::table('istat_exports', fn (Blueprint $t) => $t->dropColumn(['sha256', 'encrypted_file', 'snapshot', 'expires_at', 'minimized_at']));
        Schema::table('istat_transmissions', fn (Blueprint $t) => $t->dropColumn(['idempotency_key', 'attempts', 'reconciled_at']));
        Schema::table('struttura', function (Blueprint $t) {
            $t->string('istat_username', 100)->nullable()->change();
            $t->string('istat_password', 100)->nullable()->change();
        });
        DB::table('migrations')->where('migration', $name)->delete();
        $owner = $this->ownerFor($this->actor('admin'));
        $structure = $this->structureFor($owner);
        DB::table('struttura')->where('id', $structure->id)->update(['istat_username' => 'utente-sintetico', 'istat_password' => 'password-sintetica']);
        $other = $this->structureFor($owner);
        $ciphertext = Crypt::encryptString('sintetica');
        DB::table('struttura')->where('id', $other->id)->update(['istat_password' => null]);
        $before = DB::table('struttura')->whereIn('id', [$structure->id, $other->id])->orderBy('id')->get()->toJson();
        $history = DB::table('migrations')->orderBy('id')->get()->toJson();
        $tables = array_column(DB::select('SHOW TABLES'), 'Tables_in_'.$connection['database']);
        $counts = [];
        foreach ($tables as $table) {
            $counts[$table] = DB::table($table)->count();
        }
        $finder = new ExecutableFinder;
        $dump = $finder->find('mysqldump');
        $client = $finder->find('mysql');
        $this->assertNotNull($dump);
        $this->assertNotNull($client);
        $args = ['--no-defaults', '--protocol=TCP', '--host=127.0.0.1', '--port='.$connection['port'], '--user=fixture'];
        $env = ['MYSQL_PWD' => $connection['password']];
        $process = new Process(array_merge([$dump], $args, ['--single-transaction', '--skip-lock-tables', '--no-tablespaces', '--set-gtid-purged=OFF', '--routines', '--events', '--triggers', $connection['database']]), null, $env);
        $process->mustRun();
        $snapshot = $process->getOutput();
        $hash = hash('sha256', $snapshot);
        $this->assertStringContainsString('CREATE TABLE `struttura`', $snapshot);
        $this->assertNotSame($hash, hash('sha256', $snapshot.'corruzione-sintetica'));
        $this->assertSame(0, Artisan::call('migrate', ['--path' => 'database/migrations/'.$name.'.php', '--force' => true]));
        $this->assertTrue(Schema::hasColumn('istat_exports', 'snapshot'));
        $this->assertSame('password-sintetica', Crypt::decryptString(DB::table('struttura')->where('id', $structure->id)->value('istat_password')));
        $this->assertNull(DB::table('struttura')->where('id', $other->id)->value('istat_password'));
        // Un ciphertext valido non entra nel VARCHAR100 storico: si verifica
        // la preservazione dopo l'allargamento, senza inventare quel dato legacy.
        DB::table('struttura')->where('id', $other->id)->update(['istat_password' => $ciphertext]);
        (require database_path('migrations/'.$name.'.php'))->protectCredentials();
        $this->assertSame($ciphertext, DB::table('struttura')->where('id', $other->id)->value('istat_password'));
        $this->assertSame(count(json_decode($history, true)) + 1, DB::table('migrations')->count());
        // Recupero del solo DB sintetico: rimuove esclusivamente le due nuove tabelle,
        // quindi importa il dump completo con DROP/CREATE delle tabelle originarie.
        Schema::drop('istat_communication_days');
        Schema::drop('istat_transmission_events');
        $restore = new Process(array_merge([$client], $args, [$connection['database']]), null, $env);
        $this->assertSame($hash, hash('sha256', $snapshot));
        $restore->setInput($snapshot);
        $restore->mustRun();
        $this->assertSame($before, DB::table('struttura')->whereIn('id', [$structure->id, $other->id])->orderBy('id')->get()->toJson());
        $this->assertSame($history, DB::table('migrations')->orderBy('id')->get()->toJson());
        $this->assertFalse(Schema::hasColumn('istat_exports', 'snapshot'));
        $this->assertFalse(Schema::hasTable('istat_communication_days'));
        foreach ($counts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count(), $table);
        }
        $this->assertNull(DB::table('struttura')->where('id', $other->id)->value('istat_password'));
        $this->assertSame('password-sintetica', DB::table('struttura')->where('id', $structure->id)->value('istat_password'));
    }
}
