<?php
namespace Tests\Diagnostics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class TipoClienteRefreshDiagnosticTest extends TestCase
{
    use RefreshDatabase { migrateDatabases as originalMigrateDatabases; }
    protected function migrateDatabases(): void
    {
        DB::listen(function ($q) {
            if (str_contains($q->sql, 'classificazioni') && !str_contains($q->sql, 'information_schema')) fwrite(STDOUT, 'DDL_DIAGNOSI '.$q->sql."\n");
        });
        $this->originalMigrateDatabases();
    }
    public function test_inizializzazione_con_refresh_invariato(): void
    {
        $this->assertSame(3, DB::table('tipo_cliente')->count());
    }
}
