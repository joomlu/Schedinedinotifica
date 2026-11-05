<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$f=new class {
    use \Tests\Support\StrutturaFixtures;
    public function prepara() {
        $s=$this->structureFor(null);
        $s->forceFill(['nome_struttura'=>'Hotel Presenze sintetico','camere_disponibili'=>10,'letti_disponibili'=>20])->save();
        $u=$this->actor('struttura_user',null,$s->id);
        $u->forceFill(['username'=>'presenze-ui','password'=>\Illuminate\Support\Facades\Hash::make('Fixture-presenze-123!')])->save();
        return $s;
    }
};
$s=$f->prepara();
foreach([['2025-12-30','2026-01-01',2],['2026-01-10','2026-01-12',2],['2026-01-31','2026-02-02',1],['2025-12-31','2026-01-03',1]] as [$a,$b,$n]) {
    \App\Models\Schedina::forceCreate(['struttura_id'=>$s->id,'name'=>'Persona sintetica','surname'=>'Presenze','arrive'=>$a,'departure'=>$b,'cant_people'=>$n,'room'=>1,'beds'=>$n,'is_arrive'=>false,'circuito'=>'schedina','oa_date_nac'=>'1980-01-01']);
}
echo "Fixture Presenze sintetiche pronte\n";
