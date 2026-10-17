<?php
namespace Tests\Support;
trait SchedinaCataloghiFixtures
{
    private function cataloghiSchedina(int $strutturaId): void
    {
        foreach ([[16,'OSPITE SINGOLO'],[17,'CAPO FAMIGLIA'],[18,'CAPO GRUPPO'],[19,'FAMILIARE'],[20,'MEMBRO GRUPPO']] as [$code,$label]) \App\Models\TipoAlloggiato::firstOrCreate(['codice'=>$code],['descrizione'=>$label,'locked'=>true]);
        $it=\App\Models\GeoNazione::firstOrCreate(['nome'=>'ITALIA','cittadinanza'=>'ITALIANA','codice_iso2'=>'IT','is_italia'=>true]);
        $r=\App\Models\GeoRegione::firstOrCreate(['geo_nazione_id'=>$it->id,'nome'=>'Lazio','codice_regione'=>'12']);
        $p=\App\Models\GeoProvincia::firstOrCreate(['geo_regione_id'=>$r->id,'nome'=>'Roma','sigla'=>'RM','codice_provincia'=>'058']);
        \App\Models\GeoComune::firstOrCreate(['geo_provincia_id'=>$p->id,'nome'=>'Roma','codice_istat'=>'058091']);
        \App\Models\TipoVia::forceCreate(['struttura_id'=>$strutturaId,'nome'=>'Via','abbr'=>'V.','attivo'=>true]);
        \App\Models\RilasciatoDa::forceCreate(['struttura_id'=>$strutturaId,'name'=>'Comune di Roma','attivo'=>true]);
        \App\Models\TipoDocumento::firstOrCreate(['codice'=>'IDENT'],['descrizione'=>"CARTA DI IDENTITA'"]);
    }
}
