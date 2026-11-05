<?php
require __DIR__.'/seed-geo.php';
foreach (['A', 'B'] as $suffix) {
    $structures[$suffix] = \App\Models\Struttura::forceCreate(['nome_struttura' => 'Hotel Audit '.$suffix, 'tipologia_struttura' => 'Hotel', 'tipo_apertura' => 'Annuale', 'nazione' => 'Italia', 'regione' => 'Lazio', 'provincia' => 'RM', 'citta' => 'Roma', 'indirizzo' => 'Via Test', 'cap' => '00100', 'telefono' => '061111111', 'email' => 'browser@fixture.invalid', 'attiva' => true, 'scadenza_servizio' => now()->addYear()]);
}
\App\Models\User::where('ruolo', 'super_admin')->update(['struttura_id' => $structures['A']->id]);
$other = \Tests\Support\GeoLogoFixtures::user('struttura_user');
$other->update(['struttura_id' => $structures['B']->id]);
file_put_contents(public_path('configurazioni-fixture.json'), json_encode(['a' => $structures['A']->id, 'b' => $structures['B']->id]));
