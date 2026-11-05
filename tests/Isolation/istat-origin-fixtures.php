<?php
require __DIR__.'/istat-cycle-fixtures.php';
$origins = new class {
    use \Tests\Support\StrutturaFixtures, \Tests\Support\SchedinaCataloghiFixtures;
    public function assign(\App\Models\Struttura $s): void {
        $admin = $this->actor('admin');
        $owner = $this->ownerFor($admin);
        $admin->update(['username' => 'istat-origin-admin', 'password' => \Illuminate\Support\Facades\Hash::make('Password-istat-fixture-123!')]);
        $s->update(['proprietario_id' => $owner->id, 'nome_struttura' => 'Struttura ISTAT sintetica']);
        $this->cataloghiSchedina($s->id);
        $italia = \App\Models\GeoNazione::where('codice_iso2', 'IT')->firstOrFail();
        $germania = \App\Models\GeoNazione::firstOrCreate(['codice_iso2' => 'DE'], ['nome' => 'GERMANIA', 'cittadinanza' => 'TEDESCA', 'is_italia' => false]);
        \App\Models\Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->whereKey(9000001)->update(['oa_country' => (string) $italia->id, 'oa_city' => 'Roma', 'oa_city_nac' => 'ITALIANA',
            'or_country' => (string) $italia->id, 'or_city' => 'Roma', 'relationship' => 'CAPO FAMIGLIA']);
        \App\Models\Componenti::withoutGlobalScopes()->where('struttura_id', $s->id)->whereKey(9000001)->update(['country_nac' => (string) $germania->id, 'city_nac' => 'TEDESCA', 'country' => (string) $germania->id, 'relationship' => 'FAMILIARE']);
    }
};
$origins->assign($s);

$stay->update(['istat_tipo_turismo' => 'OTHER', 'istat_mezzo_trasporto' => 'AUTO', 'istat_canale_prenotazione' => 'OTA', 'istat_titolo_studio' => 'LAUREA']);

file_put_contents(public_path('istat-origin.json'), json_encode(['struttura_id' => $s->id, 'schedina_id' => 9000001], JSON_THROW_ON_ERROR));
