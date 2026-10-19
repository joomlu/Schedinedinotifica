<?php
namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use App\Services\WebCheckinVersione;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebCheckinConversionConcurrencyAudit extends TestCase
{
    use \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures, \Tests\Support\SchedinaPayloadFixture;

    protected function tearDown(): void
    {
        try {
            $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
            \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
        } finally { parent::tearDown(); }
    }

    public function test_due_conversioni_concorrenti_creano_un_solo_cliente_e_una_sola_schedina(): void
    {
        $this->artisan('migrate:fresh',['--force'=>true])->assertSuccessful();$s=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$s->id));$this->cataloghiSchedina($s->id);
        $p=Schedina::forceCreate(array_replace($this->validSchedinaPayload(),['struttura_id'=>$s->id,'circuito'=>'web','relationship'=>'OSPITE SINGOLO','arrive'=>now()->addDays(5)->toDateString(),'departure'=>now()->addDays(7)->toDateString()]));
        $r=WebCheckinRichiesta::create(['struttura_id'=>$s->id,'schedina_id'=>$p->id,'codice'=>'WC'.\Illuminate\Support\Str::ulid(),'numero_prenotazione'=>'CONCORRENZA-SINTETICA','nome_referente'=>'Sintetico','email'=>'race@example.invalid','arrivo'=>$p->arrive,'partenza'=>$p->departure,'quantita_persone'=>1,'numero_adulti'=>1,'numero_bambini'=>0,'token'=>\Illuminate\Support\Str::random(64),'stato'=>'compilato','compilato_at'=>now()]);
        $version=app(WebCheckinVersione::class)->of($r);$dir=storage_path('framework/testing/conversione-'.bin2hex(random_bytes(4)));mkdir($dir,0700,true);DB::disconnect();$children=[];
        for($i=0;$i<2;$i++){$pid=pcntl_fork();$this->assertGreaterThanOrEqual(0,$pid);if($pid===0){try{DB::purge();$end=microtime(true)+10;while(!file_exists($dir.'/go')&&microtime(true)<$end)usleep(10000);$response=$this->post('/web-checkin/'.$r->id.'/converti',['web_versione_richiesta'=>$version]);file_put_contents($dir.'/result-'.$i,json_encode(['status'=>$response->status(),'errors'=>session('errors')?->all()]));}catch(\Throwable $e){file_put_contents($dir.'/result-'.$i,json_encode(['tipo'=>get_class($e)]));}exit(0);}$children[]=$pid;}
        file_put_contents($dir.'/go','1');foreach($children as$pid){pcntl_waitpid($pid,$status);$this->assertSame(0,pcntl_wexitstatus($status));}DB::purge();
        for($i=0;$i<2;$i++)$this->assertSame(302,json_decode(file_get_contents($dir.'/result-'.$i),true)['status']??null);
        $this->assertDatabaseCount('schedina',1);$this->assertDatabaseCount('clienti',1);$this->assertSame('schedina',$p->fresh()->circuito);$this->assertSame('convertito',$r->fresh()->stato);$this->assertNotNull($r->fresh()->conversione_snapshot);
    }
}
