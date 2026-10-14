<?php

namespace Tests\Diagnostics;

use App\Models\Customers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CataloghiIndirizziMigrationRecoveryTest extends TestCase
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
        $a = $this->structureFor(null);
        $b = $this->structureFor(null);
        foreach (['rilasciato_da', 'tipo_via'] as $table) {
            DB::table($table)->delete();
        }
        DB::table('rilasciato_da')->insert([['name' => 'Comune', 'attivo' => 1], ['name' => 'Autorita legacy', 'attivo' => 0], ['name' => 'Extra non usato', 'attivo' => 1]]);
        DB::table('tipo_via')->insert([['abbr' => 'V.le', 'nome' => 'Viale storico', 'descrizione' => 'Storica', 'attivo' => 0], ['abbr' => 'Extra', 'nome' => 'Extra non usato', 'descrizione' => 'Extra', 'attivo' => 1]]);
        Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Sintetico', 'rilasciato_reg' => 'Autorita legacy', 'typeaway' => 'Viale storico']);
        $clients = DB::table('clienti')->orderBy('id')->get()->toJson();
        foreach (['rilasciato_da' => 'name', 'tipo_via' => 'abbr'] as $table => $field) {
            Schema::table($table, function ($t) use ($table, $field) {
                $t->dropForeign(['struttura_id']);
                $t->dropUnique($table.'_struttura_unique');
                $t->dropColumn('struttura_id');
                $t->unique($field);
            });
            $originals[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        Schema::table('struttura', fn ($t) => $t->dropColumn('indirizzi_inizializzati_at'));
        DB::table('migrations')->where('migration', '2026_10_14_040000_isolate_cataloghi_indirizzi_per_struttura')->delete();
        $snapshot = storage_path('app/indirizzi-predecessore.sql');
        $options = ['--skip-comments', '--skip-dump-date', '--hex-blob', '--order-by-primary', '--single-transaction', '--no-tablespaces'];
        $this->sqlTool('mariadb-dump', null, $snapshot, $options);
        $hash = hash_file('sha256', $snapshot);
        $migration = require base_path('database/migrations/2026_10_14_040000_isolate_cataloghi_indirizzi_per_struttura.php');
        $migration->up();
        $this->assertDatabaseHas('rilasciato_da', ['struttura_id' => $a->id, 'name' => 'Autorita legacy', 'attivo' => 0]);
        $this->assertDatabaseMissing('rilasciato_da', ['struttura_id' => $b->id, 'name' => 'Autorita legacy']);
        $this->assertDatabaseHas('tipo_via', ['struttura_id' => $a->id, 'nome' => 'Viale storico', 'abbr' => 'V.le', 'attivo' => 0]);
        $this->assertDatabaseMissing('tipo_via', ['struttura_id' => $b->id, 'nome' => 'Viale storico']);
        foreach (['rilasciato_da' => ['id', 'name', 'attivo', 'created_at', 'updated_at'], 'tipo_via' => ['id', 'nome', 'abbr', 'descrizione', 'attivo', 'created_at', 'updated_at']] as $table => $fields) {
            $before = json_decode($originals[$table], true);
            $after = json_decode(DB::table($table)->whereNull('struttura_id')->orderBy('id')->get($fields)->toJson(), true);
            $this->assertEquals($before, $after);
            $this->assertDatabaseMissing($table, ['struttura_id' => $a->id, $table === 'tipo_via' ? 'nome' : 'name' => 'Extra non usato']);
            $counts[$table] = DB::table($table)->count();
        }
        $migration->up();
        foreach ($counts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count());
        }
        $this->assertSame($clients, DB::table('clienti')->orderBy('id')->get()->toJson());
        // Simula l'interruzione dopo la mutazione e recupera l'intero checkpoint coordinato.
        $this->assertTrue(Schema::hasColumn('tipo_via', 'struttura_id'));
        $this->sqlTool('mariadb', file_get_contents($snapshot), storage_path('app/indirizzi-restore.log'));
        DB::purge('mysql');
        DB::reconnect('mysql');
        $this->assertFalse(Schema::hasColumn('tipo_via', 'struttura_id'));
        foreach ($originals as $table => $json) {
            $this->assertSame($json, DB::table($table)->orderBy('id')->get()->toJson());
        }
        $after = storage_path('app/indirizzi-recuperato.sql');
        $this->sqlTool('mariadb-dump', null, $after, $options);
        $this->assertSame($hash, hash_file('sha256', $after));
    }
}
