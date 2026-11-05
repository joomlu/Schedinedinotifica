<?php

namespace Tests\Support;

use App\Models\GeoComune;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

final class GeoLogoFixtures
{
    private static array $uploads = [];

    public const PASSWORD = 'Password-fixture-123!';

    public static function image(): string
    {
        // PNG sintetico di due pixel per lato: nessun file reale copiato.
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=');
    }

    public static function upload(string $name, ?string $content = null): \Illuminate\Http\UploadedFile
    {
        $handle = tmpfile();
        fwrite($handle, $content ?? self::image());
        self::$uploads[] = $handle;
        return new \Illuminate\Http\UploadedFile(stream_get_meta_data($handle)['uri'], $name, null, null, true);
    }

    public static function releaseUploads(): void
    {
        foreach (self::$uploads as $handle) {
            fclose($handle);
        }
        self::$uploads = [];
    }

    public static function comune(string $name = 'Comune Fixture Logo', string $code = '990001'): GeoComune
    {
        $now = now();
        $country = DB::table('geo_nazioni')->insertGetId(['codice_iso2' => 'ZZ', 'nome' => 'Nazione Fixture', 'created_at' => $now, 'updated_at' => $now]);
        $region = DB::table('geo_regioni')->insertGetId(['geo_nazione_id' => $country, 'codice_regione' => '99', 'nome' => 'Regione Fixture', 'created_at' => $now, 'updated_at' => $now]);
        $province = DB::table('geo_province')->insertGetId(['geo_regione_id' => $region, 'sigla' => 'ZZ', 'nome' => 'Provincia Fixture', 'created_at' => $now, 'updated_at' => $now]);
        return GeoComune::create(['geo_provincia_id' => $province, 'codice_istat' => $code, 'nome' => $name, 'lat' => 1.2, 'lng' => 3.4])->fresh();
    }

    public static function user(string $role): User
    {
        return User::factory()->create([
            'name' => 'Utente Fixture '.$role, 'email' => $role.'@fixture.invalid',
            'username' => 'fixture-'.$role, 'password' => Hash::make(self::PASSWORD),
            'ruolo' => $role, 'attivo' => true,
        ]);
    }

    public static function existingLogo(GeoComune $comune, bool $legacy = false): string
    {
        $path = 'geo_comuni/logo/'.$comune->id.'-fixture.png';
        Storage::disk('public')->put($path, self::image());
        $comune->update([$legacy ? 'logo' : 'logo_citta' => 'storage/'.$path]);
        return $path;
    }
}
