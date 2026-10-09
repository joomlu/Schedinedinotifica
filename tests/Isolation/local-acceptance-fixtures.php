<?php

require __DIR__.'/local-closure-fixtures.php';

foreach ([16 => 'OSPITE SINGOLO', 17 => 'CAPOFAMIGLIA', 18 => 'CAPOGRUPPO', 19 => 'FAMILIARE', 20 => 'MEMBRO GRUPPO'] as $code => $label) {
    \App\Models\TipoAlloggiato::firstOrCreate(['codice' => (string) $code], ['descrizione' => $label]);
}
\App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'DE'], ['nome' => 'GERMANIA', 'cittadinanza' => 'TEDESCA', 'is_italia' => false]);
\App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'IT'], ['nome' => 'ITALIA', 'cittadinanza' => 'ITALIANA', 'is_italia' => true]);

\App\Models\TipoVia::firstOrCreate(['nome' => 'Via']);
\App\Models\TipoDocumento::firstOrCreate(['codice' => 'AUDIT-CI'], ['descrizione' => "CARTA DI IDENTITA'"]);
$it = \App\Models\GeoNazione::where('codice_iso2', 'IT')->firstOrFail();
$reg = \App\Models\GeoRegione::firstOrCreate(['geo_nazione_id' => $it->id, 'codice_regione' => '12'], ['nome' => 'Lazio']);
$prov = \App\Models\GeoProvincia::firstOrCreate(['geo_regione_id' => $reg->id, 'sigla' => 'RM'], ['nome' => 'Roma']);
$com = \App\Models\GeoComune::firstOrCreate(['codice_istat' => '990099'], ['geo_provincia_id' => $prov->id, 'nome' => 'Roma']);
$cap = \App\Models\GeoCap::firstOrCreate(['cap' => '00100']);
\App\Models\GeoComuneCap::firstOrCreate(['geo_comune_id' => $com->id, 'geo_cap_id' => $cap->id]);

$web = \App\Models\WebCheckinRichiesta::where('token', str_repeat('c', 64))->firstOrFail();
$structureId = $web->struttura_id;
$auditUser = \App\Models\User::factory()->create(['ruolo' => 'struttura_user', 'ruolo_operativo' => 'proprietario', 'struttura_id' => $structureId, 'username' => 'accettazione-locale', 'password' => \Illuminate\Support\Facades\Hash::make('Password-audit-123!')]);
$parent = \App\Models\Schedina::withoutGlobalScopes()->findOrFail($web->schedina_id);
$parent->forceFill(['name' => 'PRINCIPALE-ACCETTAZIONE', 'surname' => 'Sintetico', 'sex' => 'M', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(2)->toDateString(), 'cant_people' => 2, 'room' => 11, 'beds' => 3, 'relationship' => 'CAPOGRUPPO', 'oa_country' => 'ITALIA', 'oa_region' => 'Lazio', 'oa_prov' => 'RM', 'oa_city' => 'Roma', 'oa_city_nac' => 'ITALIANA', 'oa_date_nac' => '1980-01-01', 'or_country' => 'ITALIA', 'or_region' => 'Lazio', 'or_prov' => 'RM', 'or_city' => 'Roma', 'or_cap' => '00100', 'or_typeaway' => 'Via', 'or_address' => 'Via Sintetica', 'or_num' => '1', 'or_doctype' => "CARTA DI IDENTITA'", 'or_doc' => 'SINTETICO', 'or_published_date' => '2025-01-01', 'or_expire' => '2030-01-01', 'or_published' => 'Comune sintetico', 'or_published_country' => 'ITALIA', 'or_published_city' => 'Roma'])->save();
\App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $parent->id)->update(['country_nac' => 'GERMANIA', 'city_nac' => 'TEDESCA', 'date_nac' => '1980-01-01', 'relationship' => 'MEMBRO GRUPPO', 'exent' => 'NO']);
$web->update(['arrivo' => now()->toDateString(), 'partenza' => now()->addDays(2)->toDateString()]);
$csv = function (array $headers, array $rows): string {
    $f = fopen('php://temp', 'w+');
    fputcsv($f, $headers, ';');
    foreach ($rows as $row) {
        fputcsv($f, array_map(fn ($key) => $row[$key] ?? '', $headers), ';');
    }
    rewind($f);
    $content = stream_get_contents($f);
    fclose($f);

    return $content;
};
$customerHeaders = app(\App\Services\CustomerImportService::class)->templateHeaders();
$customersCsv = $csv($customerHeaders, [['nome' => 'IMPORT-CLIENTE-UNO', 'email' => 'import-uno@example.invalid', 'cognome' => 'Sintetico', 'tipo_cliente' => 'Componente'], ['nome' => 'IMPORT-CLIENTE-DUE', 'email' => 'import-due@example.invalid', 'cognome' => 'Sintetico', 'tipo_cliente' => 'Componente']]);
$componentHeaders = app(\App\Services\ComponentiImportService::class)->headersTemplate();
$componentsCsv = $csv($componentHeaders, [['Nome' => 'IMPORT-COMPONENTE', 'Cognome' => 'Sintetico', 'Sesso' => 'M', 'Nazione nascita' => 'GERMANIA', 'Data di nascita' => '01/01/1980', 'Cittadinanza' => 'TEDESCA', 'Nazione residenza' => 'ITALIA', 'Comune residenza' => 'Roma', 'Provincia residenza' => 'RM', 'CAP' => '00100']]);
$ids = ['user' => $auditUser->id, 'structure' => $structureId, 'parent' => $parent->id, 'web' => $web->id, 'full' => $web->token, 'customerCsv' => $customersCsv, 'componentCsv' => $componentsCsv];
file_put_contents(public_path('local-acceptance-ids.json'), json_encode($ids));
// Sola osservabilità nel checkout attestato effimero: route read-only, dati sintetici,
// accessibile esclusivamente all'operatore della fixture; nessun endpoint aggiunto al progetto.
$auditRoute = <<<'ROUTE'

\Illuminate\Support\Facades\Route::middleware(['web', 'auth'])->get('/audit-local/state', function () {
    $ids = json_decode(file_get_contents(public_path('local-acceptance-ids.json')), true);
    abort_unless(auth()->id() === $ids['user'], 403);
    $p = \App\Models\Schedina::withoutGlobalScopes()->findOrFail($ids['parent']);
    return response()->json([
        'customers' => \App\Models\Customers::withoutGlobalScopes()->where('struttura_id', $ids['structure'])->where('name', 'like', 'IMPORT-CLIENTE-%')->orderBy('id')->get(['id', 'name', 'struttura_id']),
        'components' => \App\Models\Componenti::withoutGlobalScopes()->where('schedina_id', $p->id)->orderBy('id')->get(['id', 'name', 'schedina_id', 'struttura_id', 'date_nac']),
        'parent' => $p->only(['id', 'circuito', 'cant_people', 'struttura_id', 'scheda', 'arrive', 'departure', 'oa_country', 'or_country', 'or_doctype', 'or_typeaway']),
        'web_state' => \App\Models\WebCheckinRichiesta::findOrFail($ids['web'])->stato,
    ]);
});
ROUTE;
$routes = file_get_contents(base_path('routes/web.php'));
$catchAll = "Route::middleware(['auth'])->group(function () {\n    // Catch-all";
if (substr_count($routes, $catchAll) !== 1) {
    throw new \RuntimeException('Osservabilità audit: punto di inserimento non univoco.');
}
file_put_contents(base_path('routes/web.php'), str_replace($catchAll, $auditRoute."\n".$catchAll, $routes));
