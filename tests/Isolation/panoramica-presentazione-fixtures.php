<?php
require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fixture=new class {
    use \Tests\Support\StrutturaFixtures;
    public function prepara(): void
    {
        $s=$this->structureFor(null);
        $class=\App\Models\Classificazione::firstOrCreate(['nome'=>'3 stelle']);
        $class->tipologieStruttura()->syncWithoutDetaching([$s->tipologia_struttura_id]);
        \Illuminate\Support\Facades\Storage::disk('public')->put('panoramica/fixture.png',\Tests\Support\GeoLogoFixtures::image());
        $s->update(['nome_struttura'=>'Hotel panoramica sintetica','citta'=>'Bellaria-Igea Marina','regione'=>'Emilia-Romagna','provincia'=>'RN','cap'=>'47814','classificazione_id'=>$class->id,'classificazione'=>'3 stelle','camere_disponibili'=>18,'letti_disponibili'=>36,'logo'=>'storage/panoramica/fixture.png','logo_citta'=>'storage/panoramica/fixture.png','questura_modalita'=>'txt']);
        $user=$this->actor('struttura_user',null,$s->id);
        $user->update(['username'=>'panoramica-ui','ruolo_operativo'=>'reception','password'=>\Illuminate\Support\Facades\Hash::make('Password-Demo-Fixture-123!')]);
        \App\Models\TassaDiSoggiorno::create(['struttura_id'=>$s->id,'tassa_soggiorno'=>'1.50','giorni_massimo'=>6,'inizio'=>'2026-06-01','fine'=>'2026-09-30','max_age_children'=>17,'min_age_adult'=>18]);
        \App\Models\Schedina::forceCreate(['struttura_id'=>$s->id,'name'=>'Persona','surname'=>'Sintetica','sex'=>'M','arrive'=>'2026-06-01','departure'=>'2026-06-04','cant_people'=>1,'room'=>1,'beds'=>1,'circuito'=>'schedina','is_arrive'=>false,'scheda'=>'S-26001','relationship'=>'OSPITE SINGOLO','exent'=>'NO','oa_date_nac'=>'1984-03-12','or_country'=>'ITALIA','or_city'=>'Bologna']);
    }
};
$fixture->prepara();
echo "Fixture riproducibile: un adulto, tre presenze di giugno e 4,50 euro di imposta. Nessun invio.\n";
