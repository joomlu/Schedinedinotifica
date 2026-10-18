<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Eseguire da sola nel runtime isolato: verifica DDL reale su schema precedente popolato. */
class WebCheckinMigrationPreservationTest extends TestCase
{
    use \Tests\Support\StrutturaFixtures;

    public function test_upgrade_preserva_tutte_le_righe_precedenti_e_aggiunge_solo_colonne_nullable(): void
    {
        $this->artisan('migrate:fresh', ['--force' => true])->assertExitCode(0);
        $name = '2026_10_18_200000_extend_web_checkin_inviti';
        $migration = require database_path('migrations/'.$name.'.php');
        $migration->down();
        DB::table('migrations')->where('migration', $name)->delete();
        $s = $this->structureFor(null);
        DB::table('web_checkin_modelli')->insert(['struttura_id' => $s->id, 'iniziale' => 'singolo',
            'nome' => 'Personalizzazione preesistente', 'oggetto' => 'Oggetto storico', 'corpo' => 'Corpo storico {{link}}',
            'versione' => 7, 'created_at' => '2026-01-01 10:00:00', 'updated_at' => '2026-01-02 10:00:00']);
        DB::table('web_checkin_comunicazioni')->insert(['struttura_id' => $s->id, 'richiesta_id' => 999,
            'utente_id' => 999, 'idempotenza' => 'sintetica-storica', 'canale' => 'testo', 'stato' => 'preparata',
            'contenuto' => encrypt(['corpo' => 'Storico sintetico riservato']), 'created_at' => '2026-01-01 10:00:00', 'updated_at' => '2026-01-01 10:00:00']);
        $tables = collect(Schema::getTables())->pluck('name')->sort()->values()->all();
        $this->assertCount(66, $tables);
        $before = [];
        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            $before[$table] = ['columns' => $columns, 'rows' => $this->rows($table, $columns),
                'ddl' => array_values((array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`'))[1]];
        }
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->assertSame($tables, collect(Schema::getTables())->pluck('name')->sort()->values()->all());
        $additions = ['struttura' => ['web_checkin_colore'], 'web_checkin_modelli' => ['impostazioni', 'deleted_at'],
            'web_checkin_comunicazioni' => ['nascosta_at', 'tentativo_at', 'accettato_at', 'errore_codice']];
        foreach ($before as $table => $snapshot) {
            if ($table === 'migrations') {
                $this->assertSame($snapshot['rows'], $this->rows($table, $snapshot['columns'], $name));
                continue;
            }
            $this->assertSame(array_merge($snapshot['columns'], $additions[$table] ?? []), Schema::getColumnListing($table), $table);
            $this->assertSame($snapshot['rows'], $this->rows($table, $snapshot['columns']), $table);
            if (isset($additions[$table])) {
                $ddl = array_values((array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`'))[1];
                foreach ($additions[$table] as $column) {
                    $this->assertSame(0, DB::table($table)->whereNotNull($column)->count(), $table.'.'.$column);
                    $definition = DB::selectOne('SELECT DATA_TYPE AS tipo, IS_NULLABLE AS nullable, COLUMN_DEFAULT AS valore FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, $column]);
                    $this->assertSame('YES', $definition->nullable);
                    $this->assertContains($definition->valore, [null, 'NULL']);
                    $this->assertSame(match ($column) { 'impostazioni' => 'text', 'web_checkin_colore', 'errore_codice' => 'varchar', default => 'timestamp' }, $definition->tipo);
                    $ddl = preg_replace('/^  `'.preg_quote($column, '/').'` [^\n]+,\n/m', '', $ddl, 1, $removed);
                    $this->assertSame(1, $removed);
                }
                $this->assertSame($snapshot['ddl'], $ddl, $table.' conserva DDL, indici e contatori precedenti');
            } else {
                $this->assertSame($snapshot['ddl'], array_values((array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`'))[1], $table);
            }
        }
        $this->assertSame(1, DB::table('migrations')->where('migration', $name)->count());
        $this->assertSame(158, DB::table('migrations')->count());
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->assertSame(158, DB::table('migrations')->count());
    }

    private function rows(string $table, array $columns, ?string $excludeMigration = null): array
    {
        $query = DB::table($table)->select($columns);
        if ($excludeMigration !== null) $query->where('migration', '<>', $excludeMigration);
        return $query->get()->map(fn ($row) => json_encode((array) $row, JSON_THROW_ON_ERROR))->sort()->values()->all();
    }
}
