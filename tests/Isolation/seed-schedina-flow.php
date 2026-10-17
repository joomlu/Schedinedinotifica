<?php
require __DIR__.'/seed-schedina-navigation.php';
foreach (['Ada','Bruno'] as $index=>$nome) {
    \App\Models\Customers::forceCreate(['struttura_id'=>$s->id,'name'=>$nome,'surname'=>'OmonimoFlusso','nac_reg'=>$index ? '1985-03-04':'1990-01-02','city'=>'Localita sintetica','privacy_consent'=>false,'type_housed'=>'Ospite']);
}
foreach ([[16,'OSPITE SINGOLO'],[17,'CAPO FAMIGLIA'],[18,'CAPO GRUPPO'],[19,'FAMILIARE'],[20,'MEMBRO GRUPPO']] as [$code,$label]) \App\Models\TipoAlloggiato::firstOrCreate(['codice'=>$code],['descrizione'=>$label,'locked'=>true]);
\App\Models\TipoVia::firstOrCreate(['struttura_id'=>$s->id,'nome'=>'Via'],['abbr'=>'V.','attivo'=>true]);
\App\Models\RilasciatoDa::firstOrCreate(['struttura_id'=>$s->id,'name'=>'Autorita sintetica'],['attivo'=>true]);
\App\Models\Customers::forceCreate(['struttura_id'=>$s->id,'name'=>'Persona','surname'=>'OspiteCompletoFlusso','sex'=>'F','type_housed'=>'Ospite',
 'country'=>'Francia','city'=>'Parigi','typeaway'=>'Via','address'=>'Indirizzo sintetico','number'=>'1',
 'country_reg'=>'Francia','city_reg'=>'Parigi','ciudadania_reg'=>'Francese','nac_reg'=>'1980-01-01',
 'type_doc_reg'=>'PASSAPORTO','num_doc_reg'=>'SINTETICO123','date_pub_reg'=>'2025-01-01','expire_reg'=>'2030-01-01',
 'rilasciato_reg'=>'Autorita sintetica','country_doc_reg'=>'Francia','privacy_consent'=>true,'privacy_consent_at'=>'2025-01-01 12:00:00']);
