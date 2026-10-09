<?php

require __DIR__.'/baseline-final-fixtures.php';
$ids = json_decode(file_get_contents(public_path('baseline-final-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$r = \App\Models\WebCheckinRichiesta::where('token', $ids['full'])->firstOrFail();
\App\Models\Schedina::withoutGlobalScopes()->whereKey($r->schedina_id)->update(['relationship' => 'CAPO FAMIGLIA']);
\App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $r->schedina_id)->update(['country_nac' => 'FRANCIA', 'city_nac' => 'FRANCESE', 'country' => 'FRANCIA', 'city' => 'Parigi', 'exent' => 'NO']);
