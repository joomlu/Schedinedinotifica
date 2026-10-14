<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Support\GeoLogoFixtures as Fixture;
use Tests\TestCase;

class GeoLogoIngressoAlternativoTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\StrutturaFixtures;

    public function test_non_admin_non_puo_scrivere_logo_comunale_tramite_dati_struttura(): void
    {
        Storage::fake('public');
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('struttura_user', null, $s->id))->withSession(['struttura_corrente_id' => $s->id]);
        $comune = Fixture::comune();
        $old = Fixture::existingLogo($comune);
        $s->update(['citta' => $comune->nome]);
        foreach (['logo_citta', 'logo_citta_upload'] as $field) {
            try {
                $this->put('/struttura', array_merge($this->validStructurePayload($s), ['citta' => $comune->nome, $field => Fixture::upload('logo.png')]))->assertForbidden();
            } finally {
                Fixture::releaseUploads();
            }
            $this->assertSame('storage/'.$old, $comune->fresh()->logo_citta);
            Storage::disk('public')->assertExists($old);
        }
    }

    public function test_alias_di_upload_admin_valida_effettivamente_il_contenuto(): void
    {
        $s = $this->structureFor(null);
        $this->actingAs($this->actor('admin'))->withSession(['struttura_corrente_id' => $s->id]);
        try {
            $this->put('/struttura', ['logo_citta_upload' => Fixture::upload('logo.php', '<?php echo 1;')])->assertSessionHasErrors('logo_citta_upload');
        } finally {
            Fixture::releaseUploads();
        }
    }
}
