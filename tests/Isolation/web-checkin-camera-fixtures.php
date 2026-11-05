<?php

require __DIR__.'/web-checkin-group-fixtures.php';
$ids = json_decode(file_get_contents(public_path('baseline-final-ids.json')), true, 512, JSON_THROW_ON_ERROR);
$r = \App\Models\WebCheckinRichiesta::where('token', $ids['full'])->firstOrFail();
\Illuminate\Support\Facades\DB::table('schedina_camere')->insert(['struttura_id' => $r->struttura_id, 'schedina_id' => $r->schedina_id, 'numero_camera' => '11', 'posti_letto' => 2]);
// Abilitazione del solo campo UI nella copia effimera attestata: nessuna configurazione del repository viene modificata.
$config = config_path('app.php');
$source = file_get_contents($config);
$updated = str_replace("env('CAMERE_REALI_ENABLED', false)", 'true', $source, $count);
if ($count !== 1) {
    throw new \RuntimeException('Configurazione camere del test non riconosciuta');
}
file_put_contents($config, $updated);
\App\Models\Struttura::withoutGlobalScopes()->whereKey($r->struttura_id)->update(['camere_reali_enabled' => true]);
