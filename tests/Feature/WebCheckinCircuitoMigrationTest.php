<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebCheckinCircuitoMigrationTest extends TestCase
{
    public function test_upgrade_additivo_su_richiesta_precedente_preserva_tutte_le_tabelle_e_righe(): void
    {
        $this->artisan('migrate:fresh',['--force'=>true])->assertSuccessful();
        $name='2026_10_18_220000_complete_web_checkin_circuito';$migration=require database_path('migrations/'.$name.'.php');$migration->down();DB::table('migrations')->where('migration',$name)->delete();
        DB::table('web_checkin_richieste')->insert(['struttura_id'=>9000,'codice'=>'STORICO-SINTETICO','numero_prenotazione'=>'ESTERNA-ORIGINALE','email'=>'sintetico@example.invalid','nome_referente'=>'Referente storico','arrivo'=>'2026-11-10','partenza'=>'2026-11-12','quantita_persone'=>3,'note'=>'NOTA-INVARIATA','token'=>str_repeat('Z',64),'stato'=>'da_inviare']);
        $tables=collect(Schema::getTables())->pluck('name')->sort()->values()->all();$before=[];
        foreach($tables as$table){$cols=Schema::getColumnListing($table);$before[$table]=['columns'=>$cols,'rows'=>DB::table($table)->select($cols)->get()->map(fn($r)=>json_encode((array)$r))->sort()->values()->all(),'ddl'=>array_values((array)DB::selectOne('SHOW CREATE TABLE `'.$table.'`'))[1]];}
        $this->artisan('migrate',['--force'=>true])->assertSuccessful();$added=['numero_adulti','numero_bambini','messaggio_ospite','compilazione_snapshot','conversione_snapshot'];
        foreach($before as$table=>$v){$q=DB::table($table)->select($v['columns']);if($table==='migrations')$q->where('migration','<>',$name);$this->assertSame($v['rows'],$q->get()->map(fn($r)=>json_encode((array)$r))->sort()->values()->all(),$table);
            $this->assertSame(array_merge($v['columns'],$table==='web_checkin_richieste'?$added:[]),Schema::getColumnListing($table));
            if($table==='web_checkin_richieste'){foreach($added as$column){$this->assertSame(0,DB::table($table)->whereNotNull($column)->count());$definition=DB::selectOne('SELECT IS_NULLABLE AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?',[$table,$column]);$this->assertSame('YES',$definition->n);}}
            elseif($table!=='migrations')$this->assertSame($v['ddl'],array_values((array)DB::selectOne('SHOW CREATE TABLE `'.$table.'`'))[1]);
        }
        $this->assertSame(159,DB::table('migrations')->count());$this->assertCount(66,Schema::getTables());$this->artisan('migrate',['--force'=>true])->assertSuccessful();$this->assertSame(159,DB::table('migrations')->count());
    }
}
