<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\GeoNazione;
use App\Models\Schedina;
use App\Models\TipoDocumento;
use App\Services\QuesturaTxtExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CataloghiQuesturaTxtTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([\Database\Seeders\TipoAlloggiatoSeeder::class, \Database\Seeders\TipoDocumentoSeeder::class]);
        GeoNazione::forceCreate(['id' => 777, 'nome' => 'Francia', 'cittadinanza' => 'Francese', 'codice_iso2' => 'FR', 'is_italia' => false]);
    }

    private function principal(string $tipo = '16'): Schedina
    {
        $s = new Schedina(['id' => 900, 'struttura_id' => 91, 'scheda' => 'SINTETICA', 'relationship' => $tipo, 'arrive' => today()->toDateString(), 'departure' => today()->addDays(2)->toDateString(), 'surname' => 'Esempio', 'name' => 'Persona', 'sex' => 'F', 'oa_date_nac' => '1980-02-29', 'oa_country' => '777', 'oa_city_nac' => 'Francese', 'or_doctype' => 'IDENT', 'or_doc' => 'SINT123', 'or_published_country' => '777']);
        $s->setRelation('componenti', collect());

        return $s;
    }

    public function test_tutti_95_codici_e_descrizioni_generano_lo_stesso_codice_ufficiale(): void
    {
        $service = new QuesturaTxtExportService;
        $s = $this->principal();
        foreach (TipoDocumento::all() as $doc) {
            foreach ([$doc->codice, $doc->descrizione] as $value) {
                $s->or_doctype = $value;
                $txt = $service->buildTxtPerSchedina($s);
                $this->assertSame(168, strlen($txt));
                $this->assertSame($doc->codice, substr($txt, 134, 5));
            }
        }
        foreach (['SYNTH', (string) TipoDocumento::where('codice', 'IDENT')->value('id'), ''] as $value) {
            $s->or_doctype = $value;
            $this->assertFalse($service->analizzaSchedine(collect([$s]))->first()['valida']);
        }
    }

    public function test_cinque_tipologie_obblighi_documenti_e_relazioni_senza_regole_universali(): void
    {
        $service = new QuesturaTxtExportService;
        foreach (['16' => null, '17' => '19', '18' => '20'] as $principal => $member) {
            $s = $this->principal((string) $principal);
            if ($member) {
                $s->setRelation('componenti', collect([new Componenti(['id' => 10, 'struttura_id' => 91, 'relationship' => $member, 'surname' => 'Esempio', 'name' => 'Componente', 'sex' => 'M', 'date_nac' => '2000-01-01', 'country_nac' => '777', 'city_nac' => 'Francese'])]));
            }
            $lines = explode("\r\n", $service->buildTxtPerSchedina($s));
            $this->assertSame((string) $principal, substr($lines[0], 0, 2));
            $this->assertSame('IDENT', substr($lines[0], 134, 5));
            if ($member) {
                $this->assertSame($member, substr($lines[1], 0, 2));
                $this->assertSame(str_repeat(' ', 34), substr($lines[1], 134, 34));
                $this->assertSame(168, strlen($lines[1]));
            }
            $s->or_doctype = '';
            $this->assertFalse($service->analizzaSchedine(collect([$s]))->first()['valida']);
        }
        foreach (['17', '18', '19', '20'] as $tipo) {
            $this->assertFalse($service->analizzaSchedine(collect([$this->principal($tipo)]))->first()['valida']);
        }
    }
}
