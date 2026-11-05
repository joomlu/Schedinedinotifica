<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';\Tests\Support\TestingEnvironment::configureApplication($app);$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
(new \Database\Seeders\TipoAlloggiatoSeeder())->run();(new \Database\Seeders\TipoDocumentoSeeder())->run();
$s=\App\Models\Struttura::forceCreate(['nome_struttura'=>'Hotel Cataloghi A','tipologia_struttura'=>'Hotel','tipo_apertura'=>'Annuale','nazione'=>'Italia','regione'=>'Lazio','provincia'=>'RM','citta'=>'Roma','indirizzo'=>'Via Prova','cap'=>'00100','telefono'=>'061111111','email'=>'browser@fixture.invalid','attiva'=>true,'scadenza_servizio'=>now()->addYear()]);
$d=$s->getAttributes();unset($d['id'],$d['created_at'],$d['updated_at'],$d['gruppi_inizializzati_at'],$d['titoli_inizializzati_at']);$d['nome_struttura']='Hotel Cataloghi B';$b=\App\Models\Struttura::forceCreate($d);
foreach(['super_admin','admin','proprietario','struttura_user'] as $role){$u=\Tests\Support\GeoLogoFixtures::user($role);$u->update(['struttura_id'=>$role==='struttura_user'?$b->id:$s->id]);}
$item=\App\Models\CestinoItem::create(['entity_class'=>\App\Models\TipoDocumento::class,'entity_type'=>'Tipo Documento','original_id'=>777777,'title'=>'Catalogo archiviato sintetico','payload'=>['id'=>777777,'codice'=>'SYNTH','descrizione'=>'Prova sintetica','locked'=>false],'deleted_at'=>now()]);
file_put_contents(public_path('cataloghi-fixture.json'),json_encode(['tipo_documento'=>\App\Models\TipoDocumento::orderBy('id')->get(['id','codice','descrizione']),'tipo_alloggiato'=>\App\Models\TipoAlloggiato::orderBy('id')->get(['id','codice','descrizione']),'cestino_id'=>$item->id]));
