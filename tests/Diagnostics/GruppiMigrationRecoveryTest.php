<?php

namespace Tests\Diagnostics;

use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

// Selezione esplicita, runtime effimero esclusivo. Nessun migrate:fresh,
// database quotidiano, credenziale o snapshot operativo in questa prova.
class GruppiMigrationRecoveryTest extends TestCase
{
    use StrutturaFixtures;

    private function sqlTool(string $binary, ?string $input, string $output, array $extra = []): void
    {
        $config = config('database.connections.mysql');
        $this->assertMatchesRegularExpression('/^test_geo_[a-f0-9]{32}$/', $config['database']);
        $command = array_merge(['/opt/schedine-runtime-mariadb-11.8.9/rootfs/usr/bin/'.$binary,
            '--no-defaults', '--protocol=TCP', '--host='.$config['host'], '--port='.$config['port'],
            '--user='.$config['username']], $extra, [$config['database']]);
        $environment = getenv();
        $environment['MYSQL_PWD'] = $config['password'];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $output, 'w'], 2 => ['pipe', 'w']], $pipes, base_path(), $environment);
        $this->assertIsResource($process);
        if ($input !== null) {
            fwrite($pipes[0], $input);
        }
        fclose($pipes[0]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), 'Strumento SQL isolato fallito.');
        chmod($output, 0600);
    }

    public function test_migrazione_reale_su_predecessore_sintetico_e_recupero_dopo_mutazione(): void
    {
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        foreach ([3, 2, 1] as $level) {
            DB::table('gruppi')->whereIn('struttura_id', [$a->id, $b->id])->where('livello', $level)->delete();
        }
        $root = DB::table('gruppi')->insertGetId(['nome' => 'Legacy condiviso', 'livello' => 1, 'tipo' => 'Gruppi I']);
        DB::table('gruppi')->insert(['nome' => 'Legacy figlio', 'livello' => 2, 'tipo' => 'Gruppi II', 'parent_id' => $root]);
        foreach ([$a, $b] as $s) {
            $customer = Customers::forceCreate(['struttura_id' => $s->id, 'name' => 'Solo sintetico', 'group' => 'Legacy condiviso', 'subgroup' => 'Legacy figlio']);
            Schedina::forceCreate(['struttura_id' => $s->id, 'customer_id' => $customer->id, 'name' => 'Capogruppo sintetico', 'customer_group' => 'Legacy condiviso', 'customer_subgroup' => 'Legacy figlio']);
        }
        // Ricostruzione del solo delta del predecessore nello stesso DB attestato.
        Schema::table('gruppi', function ($table) {
            $table->dropForeign('gruppi_padre_struttura_fk');
            $table->dropUnique('gruppi_struttura_identita');
            $table->dropForeign(['struttura_id']);
            $table->dropIndex(['struttura_id', 'livello', 'nome']);
            $table->dropColumn('struttura_id');
        });
        Schema::table('struttura', fn ($t) => $t->dropColumn('gruppi_inizializzati_at'));
        DB::table('migrations')->where('migration', '2026_10_13_210000_isolate_gruppi_per_struttura')->delete();
        $beforeClients = DB::table('clienti')->orderBy('id')->get()->toJson();
        $beforeSheets = DB::table('schedina')->orderBy('id')->get()->toJson();
        $sourceGroups = DB::table('gruppi')->orderBy('id')->get()->toJson();
        $snapshot = storage_path('app/gruppi-predecessore-sintetico.sql');
        $dumpOptions = ['--skip-comments', '--skip-dump-date', '--hex-blob', '--order-by-primary', '--single-transaction', '--no-tablespaces'];
        $this->sqlTool('mariadb-dump', null, $snapshot, $dumpOptions);
        $beforeHash = hash_file('sha256', $snapshot);
        $migration = require base_path('database/migrations/2026_10_13_210000_isolate_gruppi_per_struttura.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumn('gruppi', 'struttura_id'));
        foreach ([$a, $b] as $s) {
            $this->assertSame(137, DB::table('gruppi')->where('struttura_id', $s->id)->count());
            $this->assertDatabaseHas('gruppi', ['struttura_id' => $s->id, 'nome' => 'Legacy figlio']);
        }
        $this->assertSame($beforeClients, DB::table('clienti')->orderBy('id')->get()->toJson());
        $this->assertSame($beforeSheets, DB::table('schedina')->orderBy('id')->get()->toJson());
        $columns = ['id', 'parent_id', 'tipo', 'livello', 'nome', 'descrizione', 'created_at', 'updated_at'];
        // La prova di errore avviene DOPO DDL e DML completate, non prima.
        try {
            throw new \RuntimeException('Guasto sintetico dopo mutazione');
        } catch (\RuntimeException $fault) {
            $this->assertTrue(Schema::hasColumn('gruppi', 'struttura_id'));
        }
        $this->sqlTool('mariadb', file_get_contents($snapshot), storage_path('app/recupero.log'));
        DB::purge('mysql');
        DB::reconnect('mysql');
        $this->assertFalse(Schema::hasColumn('gruppi', 'struttura_id'));
        $this->assertFalse(Schema::hasColumn('struttura', 'gruppi_inizializzati_at'));
        $this->assertSame($sourceGroups, DB::table('gruppi')->orderBy('id')->get()->toJson());
        $this->assertSame($beforeClients, DB::table('clienti')->orderBy('id')->get()->toJson());
        $this->assertSame($beforeSheets, DB::table('schedina')->orderBy('id')->get()->toJson());
        $after = storage_path('app/gruppi-recuperato-sintetico.sql');
        $this->sqlTool('mariadb-dump', null, $after, $dumpOptions);
        $this->assertSame($beforeHash, hash_file('sha256', $after));
    }
}
