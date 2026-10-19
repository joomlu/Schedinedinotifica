@php
$labels=['name'=>'Nome','surname'=>'Cognome','sex'=>'Sesso','relationship'=>'Tipo alloggiato','oa_date_nac'=>'Data di nascita','oa_country'=>'Nazione di nascita','oa_region'=>'Regione di nascita','oa_prov'=>'Provincia di nascita','oa_city'=>'Luogo di nascita','oa_city_nac'=>'Cittadinanza','or_country'=>'Nazione di residenza','or_region'=>'Regione','or_prov'=>'Provincia','or_city'=>'Città di residenza','or_cap'=>'CAP','or_typeaway'=>'Tipo via','or_address'=>'Indirizzo','or_num'=>'Numero civico','or_doctype'=>'Tipo documento','or_doc'=>'Numero documento','or_published'=>'Rilasciato da','or_published_date'=>'Data rilascio','or_expire'=>'Scadenza documento','or_published_country'=>'Nazione rilascio','or_published_city'=>'Luogo rilascio','customer_email'=>'Email','customer_cellphone'=>'Cellulare','date_nac'=>'Data di nascita','country_nac'=>'Nazione di nascita','comune_nac'=>'Comune di nascita','province_nac'=>'Provincia di nascita','regione_nac'=>'Regione di nascita','city_nac'=>'Cittadinanza','country'=>'Nazione di residenza','city'=>'Città di residenza','address'=>'Indirizzo','number'=>'Numero civico','cap'=>'CAP'];
@endphp

<section class="border rounded-3 p-3 mb-3"><h3 class="h6">{{ $titolo }}</h3><dl class="row mb-0">
@foreach($labels as $field=>$label)
@if(filled($persona[$field]??null))<div class="col-md-6 col-xl-4 mb-2"><dt class="small text-muted">{{ $label }}</dt><dd class="mb-0 text-break">{{ $persona[$field] }}</dd></div>
@endif

@endforeach
</dl></section>
