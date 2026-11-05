<?php

require __DIR__.'/seed-anagrafiche.php';

$extra = \App\Models\TipologiaGenerale::firstOrCreate(['nome' => 'Extra-alberghiera']);
$class = \App\Models\Classificazione::firstOrCreate(['nome' => 'Nessuna']);
foreach (['Agriturismo', 'Affittacamere', 'Locazione turistica imprenditoriale', 'Ostello'] as $nome) {
    $tipo = \App\Models\TipologiaStruttura::create(['nome' => $nome, 'tipologia_generale_id' => $extra->id]);
    $selezione = $nome === 'Agriturismo' ? \App\Models\Classificazione::firstOrCreate(['nome' => '3 girasoli']) : $class;
    $tipo->classificazioni()->attach($selezione->id);
}
