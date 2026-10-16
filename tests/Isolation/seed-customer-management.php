<?php
require __DIR__.'/seed-customer-wizard.php';
$admin = \Tests\Support\GeoLogoFixtures::user('admin');
$owner = \App\Models\Proprietario::create(['admin_id' => $admin->id, 'nome' => 'Proprietario Browser', 'attivo' => true]);
$s->update(['proprietario_id' => $owner->id]);
$admin->update(['struttura_id' => $s->id]);
$ownerUser = \Tests\Support\GeoLogoFixtures::user('proprietario');
$ownerUser->update(['proprietario_id' => $owner->id, 'struttura_id' => $s->id]);
$c->update(['email' => 'persona@example.test', 'azienda' => 'Azienda Browser completa', 'nota' => str_repeat('Testo lungo sintetico senza dati reali. ', 80)]);
foreach(range(1, 10) as $i) {
 $attrs=$sheet->getAttributes();unset($attrs['id'],$attrs['created_at'],$attrs['updated_at']);
 $attrs['scheda']='STORICO-'.$i;
 \App\Models\Schedina::forceCreate($attrs);
}
