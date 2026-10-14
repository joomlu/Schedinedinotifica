<?php

namespace Tests\Feature;

use App\Models\GeoComune;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Support\GeoLogoFixtures as Fixture;
use Tests\TestCase;

class GeoLogoAtomicitaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_errore_database_conserva_logo_precedente_e_non_lascia_orfani(): void
    {
        Storage::fake('public');
        $this->actingAs(Fixture::user('admin'));
        $comune = Fixture::comune();
        $old = Fixture::existingLogo($comune);
        $files = Storage::disk('public')->allFiles();
        $dispatcher = GeoComune::getEventDispatcher();
        GeoComune::setEventDispatcher(clone $dispatcher);
        GeoComune::updating(function () {
            throw new \RuntimeException('Errore sintetico persistenza logo');
        });
        try {
            $this->post('/geo/comuni/'.$comune->id.'/logo', ['logo' => Fixture::upload('nuovo.png')])->assertSessionHasErrors('logo');
        } finally {
            GeoComune::setEventDispatcher($dispatcher);
            Fixture::releaseUploads();
        }
        $this->assertSame('storage/'.$old, $comune->fresh()->logo_citta);
        Storage::disk('public')->assertExists($old);
        $this->assertSame($files, Storage::disk('public')->allFiles());
    }

    public function test_logo_precedente_ancora_usato_da_altra_entita_non_viene_eliminato(): void
    {
        Storage::fake('public');
        $this->actingAs(Fixture::user('admin'));
        $a = Fixture::comune();
        $old = Fixture::existingLogo($a);
        $b = GeoComune::create(['geo_provincia_id' => $a->geo_provincia_id, 'nome' => 'Secondo Comune', 'codice_istat' => '990002', 'logo_citta' => 'storage/'.$old]);
        try {
            $this->post('/geo/comuni/'.$a->id.'/logo', ['logo' => Fixture::upload('nuovo.png')])->assertSessionHasNoErrors();
            Storage::disk('public')->assertExists($old);
            $this->assertSame('storage/'.$old, $b->fresh()->logo_citta);
            $this->assertNotSame($b->fresh()->logo_citta, $a->fresh()->logo_citta);
        } finally {
            Fixture::releaseUploads();
        }
    }

    public function test_dimensioni_eccessive_non_sostituiscono_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(Fixture::user('admin'));
        $comune = Fixture::comune();
        $old = Fixture::existingLogo($comune);
        $chunk = fn ($type, $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $png = "\x89PNG\r\n\x1a\n".$chunk('IHDR', pack('NNCCCCC', 4097, 1, 8, 2, 0, 0, 0)).$chunk('IDAT', gzcompress("\0".str_repeat("\0", 4097 * 3))).$chunk('IEND', '');
        try {
            $this->post('/geo/comuni/'.$comune->id.'/logo', ['logo' => Fixture::upload('enorme.png', $png)])->assertSessionHasErrors('logo');
        } finally {
            Fixture::releaseUploads();
        }
        $this->assertSame('storage/'.$old, $comune->fresh()->logo_citta);
        $this->assertSame([$old], Storage::disk('public')->allFiles());
    }
}
