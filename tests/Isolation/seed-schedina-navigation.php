<?php
require __DIR__.'/seed-customer-management.php';
$attributes = $sheet->getAttributes();
unset($attributes['id'], $attributes['created_at'], $attributes['updated_at']);
$attributes['circuito'] = 'bozza';
$attributes['name'] = 'BozzaNavigazione';
$attributes['scheda'] = null;
\App\Models\Schedina::forceCreate($attributes);
