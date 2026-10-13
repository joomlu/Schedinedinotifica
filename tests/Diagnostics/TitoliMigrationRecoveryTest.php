<?php
namespace Tests\Diagnostics;
use App\Models\Customers;
use App\Models\Schedina;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class TitoliMigrationRecoveryTest extends TestCase
{
    use \Tests\Support\StrutturaFixtures;
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


    public function test_migrazione_conservativa_e_recupero_checkpoint_dopo_ddl_dml(): void
    {
        $a = $this->structureFor(null); $b = $this->structureFor(null);
        DB::table('titolo')->whereNotNull('struttura_id')->delete();
        DB::table('titolo')->insert([
            ['nome' => 'Sig.', 'descrizione' => 'Signore', 'attivo' => 1],
            ['nome' => 'Sig.s', 'descrizione' => 'Signore', 'attivo' => 1],
            ['nome' => 'Sigg.', 'descrizione' => 'Signori', 'attivo' => 1],
        ]);
        Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico', 'type' => 'Sig.']);
        Schedina::forceCreate(['struttura_id' => $b->id, 'name' => 'Sintetico B', 'type' => 'Sigg.']);
        Schema::table('titolo', function ($t) {
            $t->dropForeign(['struttura_id']); $t->dropUnique('titolo_nome_struttura_unique');
            $t->dropUnique('titolo_base_struttura_unique'); $t->dropColumn(['struttura_id', 'base_nome']);
            $t->unique('nome', 'titolo_nome_unique');
        });
        Schema::table('struttura', fn ($t) => $t->dropColumn('titoli_inizializzati_at'));
        DB::table('migrations')->where('migration', '2026_10_14_020000_isolate_titoli_per_struttura')->delete();
        $clients = DB::table('clienti')->orderBy('id')->get()->toJson();
        $sheets = DB::table('schedina')->orderBy('id')->get()->toJson();
        $originals = DB::table('titolo')->orderBy('id')->get()->toJson();
        $snapshot = storage_path('app/titoli-predecessore.sql');
        $options = ['--skip-comments', '--skip-dump-date', '--hex-blob', '--order-by-primary', '--single-transaction', '--no-tablespaces'];
        $this->sqlTool('mariadb-dump', null, $snapshot, $options);
        $hash = hash_file('sha256', $snapshot);
        $migration = require base_path('database/migrations/2026_10_14_020000_isolate_titoli_per_struttura.php');
        $migration->up();
        $this->assertSame(80, DB::table('titolo')->where('struttura_id', $a->id)->count());
        $this->assertSame(81, DB::table('titolo')->where('struttura_id', $b->id)->count());
        $this->assertDatabaseMissing('titolo', ['struttura_id' => $a->id, 'nome' => 'Sig.s']);
        $this->assertSame($clients, DB::table('clienti')->orderBy('id')->get()->toJson());
        $this->assertSame($sheets, DB::table('schedina')->orderBy('id')->get()->toJson());
        $this->assertSame($originals, DB::table('titolo')->whereNull('struttura_id')->orderBy('id')->get(['id','nome','descrizione','attivo','created_at','updated_at'])->toJson());
        $migration->up(); $this->assertSame(81, DB::table('titolo')->where('struttura_id', $b->id)->count());
        try { throw new \RuntimeException('Guasto sintetico dopo DDL/DML Titoli'); } catch (\RuntimeException $e) { $this->assertTrue(Schema::hasColumn('titolo','struttura_id')); }
        $this->sqlTool('mariadb', file_get_contents($snapshot), storage_path('app/titoli-restore.log'));
        DB::purge('mysql'); DB::reconnect('mysql');
        $this->assertFalse(Schema::hasColumn('titolo','struttura_id'));
        $this->assertSame($originals, DB::table('titolo')->orderBy('id')->get()->toJson());
        $after = storage_path('app/titoli-recuperato.sql'); $this->sqlTool('mariadb-dump', null, $after, $options);
        $this->assertSame($hash, hash_file('sha256',$after));
    }
}
