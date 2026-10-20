<?php
require __DIR__.'/seed-web-checkin-html.php';
// Secondo tenant sintetico: anteprima informativa disponibile, senza cambiare la fixture A.
$b->forceFill(['tipologia_generale'=>'Alberghiera','citta'=>'Bellaria-Igea Marina','tipologia_struttura'=>'Albergo','classificazione'=>'3 stelle'])->save();
\App\Models\TassaDiSoggiorno::forceCreate(['struttura_id'=>$b->id,'tassa_soggiorno'=>1.5,'giorni_massimo'=>6,'inizio'=>'2026-06-01','fine'=>'2026-09-30','max_age_children'=>17,'min_age_adult'=>18]);
$p=\App\Models\Schedina::forceCreate(['struttura_id'=>$b->id,'circuito'=>'web','name'=>'Ospite tassa sintetico','surname'=>'Fixture sezioni','arrive'=>'2026-11-10','departure'=>'2026-11-13','cant_people'=>1,'oa_date_nac'=>'1980-01-01','exent'=>'NO']);
$r=\App\Models\WebCheckinRichiesta::forceCreate(['struttura_id'=>$b->id,'schedina_id'=>$p->id,'codice'=>'SEZIONITASSA'.$p->id,'numero_prenotazione'=>'SEZIONI-TASSA','email'=>'sezioni@example.invalid','nome_referente'=>'Referente tassa sintetico','arrivo'=>$p->arrive,'partenza'=>$p->departure,'quantita_persone'=>1,'numero_adulti'=>1,'numero_bambini'=>0,'token'=>\Illuminate\Support\Str::random(64)]);
app(\App\Services\WebCheckinLink::class)->issue($r);
if (!app(\App\Services\TassaDiSoggiornoService::class)->profiloFiscaleBellaria($b->fresh(),2026)) throw new \RuntimeException('Profilo fiscale della fixture assente');
$f=json_decode(file_get_contents(public_path('web-html-fixture.json')),true);$f['tassa']=route('web_checkin.public.show',$r->token);file_put_contents(public_path('web-html-fixture.json'),json_encode($f));
