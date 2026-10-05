<?php

namespace Tests\Feature;

use App\Models\GeoComune;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Support\GeoLogoFixtures as Fixture;
use Tests\TestCase;

class GeoComuneLogoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Fixture::releaseUploads();
        parent::tearDown();
    }

    public static function roles(): array
    {
        return [['super_admin'], ['admin']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_login_rendering_logo_ricerca_e_paginazione(string $role): void
    {
        $user = Fixture::user($role);
        $comune = Fixture::comune();
        $path = Fixture::existingLogo($comune);
        $this->post('/login', ['login' => $user->username, 'password' => Fixture::PASSWORD])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $response = $this->get('/geo/comuni/logo');
        $response->assertOk()->assertSee('Geo Comuni (logo)')->assertSee($comune->nome)
            ->assertSee('Logo '.$comune->nome)->assertSee(asset('storage/'.$path), false)->assertSee('ZZ');
        Storage::disk('public')->assertExists($path);
        for ($i = 2; $i <= 15; $i++) {
            GeoComune::create(['geo_provincia_id' => $comune->geo_provincia_id, 'nome' => 'Zeta Fixture '.$i, 'codice_istat' => '99'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)]);
        }
        $this->get('/geo/comuni/logo?q=990001')->assertOk()->assertSee($comune->nome)->assertDontSee('Zeta Fixture 2');
        $this->get('/geo/comuni/logo?q=Comune+Fixture')->assertOk()->assertSee($comune->nome);
        $this->get('/geo/comuni/logo?q=inesistente')->assertOk()->assertSee('Nessun comune trovato');
        $this->get('/geo/comuni/logo?page=2')->assertOk()->assertDontSee($comune->nome);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_upload_sostituzione_rimozione_preservano_master_data(string $role): void
    {
        $this->actingAs(Fixture::user($role));
        $comune = Fixture::comune();
        $master = $comune->only(['id', 'nome', 'geo_provincia_id', 'codice_istat', 'lat', 'lng']);
        $old = Fixture::existingLogo($comune);
        $url = '/geo/comuni/'.$comune->id.'/logo';
        // Il nome client non deve determinare l’estensione salvata.
        $file = Fixture::upload('logo.txt');
        $this->post($url, ['logo' => $file])->assertSessionHasNoErrors()->assertRedirect();
        $path = substr($comune->fresh()->logo_citta, strlen('storage/'));
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing($old);
        $this->post($url, ['logo' => Fixture::upload('secondo.png')])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('storage/'.$path, $comune->fresh()->logo_citta);
        Storage::disk('public')->assertExists($path);
        $this->get('/geo/comuni/logo')->assertOk()->assertSee(asset('storage/'.$path), false);
        $this->delete($url)->assertRedirect();
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($comune->fresh()->logo_citta);
        $this->assertSame($master, $comune->fresh()->only(array_keys($master)));
        $this->assertSame(1, GeoComune::count());
    }

    public function test_ruoli_non_autorizzati_non_vedono_menu_e_non_scrivono(): void
    {
        $comune = Fixture::comune();
        $path = Fixture::existingLogo($comune);
        foreach (['proprietario', 'struttura_user', 'unknown'] as $role) {
            $this->actingAs(Fixture::user($role));
            $this->get('/geo/comuni/logo')->assertForbidden();
            $this->post('/geo/comuni/'.$comune->id.'/logo', ['logo' => Fixture::upload('logo.png')])->assertForbidden();
            $this->delete('/geo/comuni/'.$comune->id.'/logo')->assertForbidden();
            $this->assertStringNotContainsString('Geo Comuni (logo)', view('layouts.sidebar')->render());
        }
        $this->assertSame('storage/'.$path, $comune->fresh()->logo_citta);
        Storage::disk('public')->assertExists($path);
    }

    public function test_rimozione_logo_legacy_e_file_non_immagine(): void
    {
        $this->actingAs(Fixture::user('admin'));
        $comune = Fixture::comune();
        $old = Fixture::existingLogo($comune, true);
        $url = '/geo/comuni/'.$comune->id.'/logo';
        $this->post($url, ['logo' => Fixture::upload('logo.php')])->assertSessionHasErrors('logo');
        $this->post($url, ['logo' => Fixture::upload('logo.png', '<?php echo 1;')])->assertSessionHasErrors('logo');
        $this->assertSame('storage/'.$old, $comune->fresh()->logo);
        $this->post($url, ['logo' => Fixture::upload('logo.png', Fixture::image().str_repeat('x', 4097 * 1024))])->assertSessionHasErrors('logo');
        $this->delete($url)->assertRedirect();
        $this->assertNull($comune->fresh()->logo);
        $this->assertNull($comune->fresh()->logo_citta);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_csrf_errato_non_modifica_logo(): void
    {
        $this->actingAs(Fixture::user('admin'));
        $comune = Fixture::comune();
        $old = Fixture::existingLogo($comune);
        $this->post('/geo/comuni/'.$comune->id.'/logo', [
            '_token' => 'token-errato', 'logo' => Fixture::upload('logo.png'),
        ])->assertStatus(419);
        $this->assertSame('storage/'.$old, $comune->fresh()->logo_citta);
        Storage::disk('public')->assertExists($old);
    }

    public function test_cancellazione_non_tocca_file_fuori_directory_loghi(): void
    {
        $this->actingAs(Fixture::user('admin'));
        $comune = Fixture::comune();
        foreach (['storage/documenti/fixture.txt', 'storage/geo_comuni/logo/../fixture.txt'] as $path) {
            $file = str_contains($path, '../') ? 'geo_comuni/fixture.txt' : 'documenti/fixture.txt';
            Storage::disk('public')->put($file, 'File fixture da preservare');
            $comune->update(['logo_citta' => $path]);
            $this->delete('/geo/comuni/'.$comune->id.'/logo')->assertRedirect();
            Storage::disk('public')->assertExists($file);
        }
    }

    public function test_anonimo_richiede_login(): void
    {
        $this->get('/geo/comuni/logo')->assertRedirect('/login');
    }
}
