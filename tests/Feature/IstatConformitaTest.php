<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\GeoNazione;
use App\Models\IstatExport;
use App\Models\IstatMovimentoGiornaliero;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\IstatCodifiche;
use App\Services\IstatTabellaAService;
use App\Services\IstatWebService;
use App\Services\IstatXmlValidator;
use App\Services\QuesturaTxtExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class IstatConformitaTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    private function setupStructure(): Struttura
    {
        GeoNazione::forceCreate(['id' => 106, 'nome' => 'ITALIA', 'cittadinanza' => 'ITALIANA', 'codice_iso2' => 'IT', 'is_italia' => true]);
        GeoNazione::forceCreate(['id' => 9001, 'nome' => 'GERMANIA', 'cittadinanza' => 'TEDESCA', 'codice_iso2' => 'DE', 'is_italia' => false]);
        $owner = $this->ownerFor($this->actor('admin'));
        $structure = $this->structureFor($owner);
        $structure->update(['regione' => 'Emilia-Romagna', 'camere_disponibili' => 10, 'letti_disponibili' => 20, 'istat_codice_struttura' => 'TEST-ISOLATO']);
        $this->actingAs($this->actor('proprietario', $owner->id, $structure->id));
        return $structure->fresh();
    }

    private function stay(Struttura $structure, array $overrides = []): Schedina
    {
        return Schedina::forceCreate(array_replace([
            'struttura_id' => $structure->id, 'scheda' => 'FIXTURE-'.uniqid(), 'circuito' => 'schedina', 'is_arrive' => false,
            'name' => 'Èlia & Test', 'surname' => "D'Àngelo <Fixture>", 'sex' => 'M', 'relationship' => '16',
            'arrive' => '2026-03-31', 'departure' => '2026-04-02', 'cant_people' => 1, 'room' => 1, 'beds' => 1,
            'oa_country' => '106', 'oa_city' => '412058091', 'oa_prov' => 'RM', 'oa_city_nac' => 'ITALIANA', 'oa_date_nac' => '2000-01-01',
            'or_country' => '106', 'or_city' => '412058091', 'or_prov' => 'RM', 'or_region' => 'Lazio',
            'istat_tipo_turismo' => 'Balneare', 'istat_mezzo_trasporto' => 'AUTO', 'istat_canale_prenotazione' => 'Diretta web',
            'istat_titolo_studio' => 'Laurea', 'istat_professione' => 'Professione & fixture',
            'or_doctype' => 'PASSAPORTO', 'or_doc' => 'FIXTURE_DOC', 'or_published_country' => '106', 'or_published_city' => '412058091',
        ], $overrides));
    }

    private function fixtureComponent(Schedina $s, array $overrides = []): Componenti
    {
        return Componenti::forceCreate(array_replace([
            'struttura_id' => $s->struttura_id, 'schedina_id' => $s->id, 'name' => 'Minore Fixture', 'surname' => 'Sintetico', 'sex' => '2',
            'relationship' => '19', 'city_nac' => 'TEDESCA', 'date_nac' => '2020-02-29', 'country_nac' => '9001',
            'country' => '9001', 'city' => 'Berlino', 'province' => '', 'comune_nac' => '', 'province_nac' => '',
        ], $overrides));
    }

    private function xml(Struttura $s, string $from = '2026-04-01', string $to = '2026-04-30'): string
    {
        return (new IstatTabellaAService())->buildXml($s, Carbon::parse($from), Carbon::parse($to));
    }

    private function xpath(string $xml): \DOMXPath
    {
        return new \DOMXPath((new IstatXmlValidator())->document($xml));
    }

    public function test_individual_guests_residence_minors_and_month_boundary(): void
    {
        $s = $this->setupStructure();
        $stay = $this->stay($s, ['cant_people' => 2, 'relationship' => '17']);
        $child = $this->fixtureComponent($stay, ['city_nac' => 'ITALIANA']);
        $service = new IstatTabellaAService();
        $april = $service->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertSame([], $april['errors']);
        $this->assertSame(0, $april['totale_arrivi']);
        $this->assertSame(2, $april['totale_partenze']);
        $this->assertSame(2, $april['totale_presenze']);
        $this->assertSame(1, $april['rows'][0]['presenti_italiani']);
        $this->assertSame(1, $april['rows'][0]['presenti_stranieri']);
        $this->assertSame(0, $april['rows'][1]['presenti']);
        $march = $this->xml($s, '2026-03-01', '2026-03-31');
        $xp = $this->xpath($march);
        $this->assertSame(31, $xp->query('/movimenti/movimento')->length);
        $this->assertSame(2, $xp->query('//arrivi/arrivo')->length);
        $this->assertSame('S'.$stay->id, $xp->evaluate('string(//arrivo[2]/idcapo)'));
        $this->assertSame('C'.$child->id, $xp->evaluate('string(//arrivo[2]/idswh)'));
        $this->assertSame('100000216', $xp->evaluate('string(//arrivo[2]/statoresidenza)'));
        $this->assertSame('100000100', $xp->evaluate('string(//arrivo[2]/cittadinanza)'));
        $this->assertSame('100000100', $xp->evaluate('string(//arrivo[1]/cittadinanza)'));
        $this->assertSame('412058091', $xp->evaluate('string(//arrivo[1]/luogoresidenza)'));
        $this->assertSame('', $xp->evaluate('string(//arrivo[2]/titolostudio)'));
        $this->assertStringContainsString('D\'Àngelo &lt;Fixture&gt;', $march);
        $this->assertStringContainsString('Èlia &amp; Test', $march);
        $this->assertFalse(str_starts_with($march, "\xEF\xBB\xBF"));
        $this->assertSame(0, $xp->query('//persone|//ospite|//provenienza|//presenti|//aperta|//periodoDal')->length);
        $apr = $this->xpath($this->xml($s));
        $this->assertSame(2, $apr->query('//partenza')->length);
        $this->assertSame('20260331', $apr->evaluate('string(//partenza/arrivo)'));
    }

    public function test_group_head_precedes_members_and_foreign_citizen_resident_in_italy_is_italian_residence(): void
    {
        $s = $this->setupStructure();
        $stay = $this->stay($s, ['cant_people' => 2, 'relationship' => '18']);
        $this->fixtureComponent($stay, ['relationship' => '20', 'country' => '106', 'city' => '412058091', 'province' => 'RM', 'regione' => 'Lazio']);
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertSame(2, $a['rows'][0]['presenti_italiani']);
        $this->assertSame(0, $a['rows'][0]['presenti_stranieri']);
        $x = $this->xpath($this->xml($s, '2026-03-01', '2026-03-31'));
        $this->assertSame('18', $x->evaluate('string(//arrivi/arrivo[1]/tipoalloggiato)'));
        $this->assertSame('20', $x->evaluate('string(//arrivi/arrivo[2]/tipoalloggiato)'));
        $this->assertSame('100000216', $x->evaluate('string(//arrivi/arrivo[2]/cittadinanza)'));
        $this->assertSame('100000100', $x->evaluate('string(//arrivi/arrivo[2]/statoresidenza)'));
    }

    public function test_same_day_stay_has_arrival_departure_and_zero_nights(): void
    {
        $s = $this->setupStructure(); $this->stay($s, ['arrive' => '2026-04-02', 'departure' => '2026-04-02']);
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertSame([1, 1, 0], [$a['totale_arrivi'], $a['totale_partenze'], $a['totale_presenze']]);
        $x = $this->xpath($this->xml($s));
        $this->assertSame('1', $x->evaluate('string(//movimento[data="20260402"]/struttura/camereoccupate)'));
        $this->assertSame(1, $x->query('//arrivi/arrivo')->length);
        $this->assertSame(1, $x->query('//partenza')->length);
    }

    public function test_closed_days_and_season_spanning_new_year(): void
    {
        $s = $this->setupStructure();
        $s->update(['tipo_apertura' => 'Stagionale', 'data_apertura' => '2025-11-01', 'data_chiusura' => '2026-02-28']);
        $x = $this->xpath($this->xml($s));
        $this->assertSame(30, $x->query('//struttura[apertura="NO"][camereoccupate="0"][cameredisponibili="0"][lettidisponibili="0"]')->length);
        $jan = $this->xpath($this->xml($s, '2026-01-01', '2026-01-31'));
        $this->assertSame(31, $jan->query('//struttura[apertura="SI"]')->length);
        $this->stay($s, ['arrive' => '2026-04-01']);
        $this->expectException(ValidationException::class);
        $this->xml($s);
    }

    public function test_zero_availability_without_guests_is_valid_but_inconsistent_occupancy_is_blocked(): void
    {
        $s = $this->setupStructure(); $s->update(['camere_disponibili' => 0, 'letti_disponibili' => 0]);
        $x = $this->xpath($this->xml($s));
        $this->assertSame(30, $x->query('//struttura[cameredisponibili="0"][lettidisponibili="0"]')->length);
        $this->stay($s);
        $this->expectException(ValidationException::class); $this->xml($s);
    }

    public function test_missing_dates_are_reported_instead_of_excluded(): void
    {
        $s = $this->setupStructure(); $this->stay($s, ['departure' => null]);
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertSame(1, $a['totale_schedine']);
        $this->assertFalse($a['valida']);
        $this->assertStringContainsString('periodo soggiorno', implode(' ', $a['errors']));
        $this->expectException(ValidationException::class); $this->xml($s);
    }

    /** @dataProvider invalidGuestData */
    public function test_invalid_guest_data_blocks_export(string $field, mixed $value, string $reason): void
    {
        $s = $this->setupStructure(); $this->stay($s, [$field => $value]);
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertFalse($a['valida']);
        $this->assertStringContainsString($reason, implode(' ', $a['errors']));
        $this->expectException(ValidationException::class); $this->xml($s);
    }

    public static function invalidGuestData(): array
    {
        return [
            ['cant_people', 2, 'ospiti nominativi'], ['room', '1.5', 'room'], ['beds', 0, 'beds'],
            ['or_country', 'PAESE_INVENTATO', 'statoresidenza'], ['or_city', 'COMUNE_INVENTATO', 'luogoresidenza'],
            ['or_prov', 'RN', 'provincia'], ['oa_date_nac', '2026-04-03', 'datanascita'], ['sex', 'X', 'sesso'],
            ['name', str_repeat('x', 31), 'nome'], ['istat_tipo_turismo', 'LEISURE', 'tipoturismo'],
            ['istat_mezzo_trasporto', null, 'mezzotrasporto'], ['istat_canale_prenotazione', 'DIRECT', 'canaleprenotazione'],
        ];
    }

    public function test_non_tourists_reservations_other_structures_and_future_records_are_excluded(): void
    {
        $s = $this->setupStructure();
        $this->stay($s, ['istat_non_turista' => true]);
        $this->stay($s, ['circuito' => 'arrivi', 'is_arrive' => true]);
        $this->stay($s, ['arrive' => '2026-05-01', 'departure' => '2026-05-02']);
        $this->stay($this->structureFor(null));
        $x = $this->xpath($this->xml($s));
        $this->assertSame(0, $x->query('//arrivo|//partenza')->length);
        $this->assertSame(30, $x->query('//struttura[apertura="SI"][camereoccupate="0"]')->length);
    }

    public function test_legacy_daily_override_cannot_override_statistics_silently(): void
    {
        $s = $this->setupStructure();
        $override = IstatMovimentoGiornaliero::create(['struttura_id' => $s->id, 'giorno' => '2026-04-01', 'presenti' => 999]);
        $before = $override->fresh()->getRawOriginal();
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertSame(0, $a['rows'][0]['presenti']);
        $this->assertFalse($a['valida']);
        $this->assertSame($before, $override->fresh()->getRawOriginal());
    }

    public function test_xsd_rejects_missing_wrong_and_out_of_order_elements(): void
    {
        $s = $this->setupStructure(); $this->stay($s);
        $xml = $this->xml($s); $v = new IstatXmlValidator(); $v->validate($xml);
        foreach ([str_replace('<apertura>SI</apertura>', '<aperta>true</aperta>', $xml), str_replace('<codice>TEST-ISOLATO</codice>', '', $xml), str_replace('<movimenti>', '<movimenti><periodoDal>2026-04-01</periodoDal>', $xml)] as $bad) {
            try { $v->validate($bad); $this->fail('Lo XSD doveva rifiutare il file.'); }
            catch (ValidationException $e) { $this->assertStringContainsString('XSD', $e->getMessage()); }
        }
    }

    public function test_dtd_is_rejected_without_resolving_external_entities(): void
    {
        $this->expectException(ValidationException::class);
        (new IstatXmlValidator())->document('<!DOCTYPE x [<!ENTITY e SYSTEM "file:///etc/passwd">]><x>&e;</x>');
    }

    public function test_official_mappings_are_independent_of_internal_ids_and_respect_ceased_places(): void
    {
        $this->setupStructure(); $codes = new IstatCodifiche();
        $this->assertSame('100000216', $codes->country('9001'));
        $this->assertSame('100000100', $codes->country('ITALIANA'));
        $this->assertSame('408099001', $codes->comune('Bellaria-Igea Marina', 'RN'));
        $this->assertNull($codes->comune('408040506', 'FO'));
        $this->assertSame('408040506', $codes->comune('408040506', 'FO', true));
        $this->assertNull($codes->country('999999999'));
    }

    public function test_soap_matches_official_xsd_and_verification_never_transmits(): void
    {
        $s = $this->setupStructure(); $this->stay($s);
        $service = new IstatTabellaAService(); $xml = $this->xml($s);
        $s->istat_username = 'FIXTURE_USER'; $s->istat_password = 'FIXTURE_SECRET';
        $soap = $service->buildSoapEnvelope($s, $xml, 'send');
        $doc = (new IstatXmlValidator())->document($soap);
        $operation = $doc->getElementsByTagNameNS(IstatXmlValidator::WS_NAMESPACE, 'inviaMovimentazione')->item(0);
        $op = new \DOMDocument(); $op->appendChild($op->importNode($operation, true));
        $this->assertTrue($op->schemaValidate(base_path('reference/istat/ross1000-er/checkin.xsd')));
        $this->assertStringNotContainsString('FIXTURE_SECRET', $soap);
        $this->assertStringNotContainsString('<modalita>', $soap);
        Http::fake(); $ws = new IstatWebService($service);
        $this->assertSame('validated', $ws->verify($s, $xml, Carbon::now(), Carbon::now())['state']);
        Http::assertNothingSent();
        config(['istat.enabled' => true]);
        Http::fake(['*' => Http::response('<response/>', 200)]);
        $this->assertFalse($ws->send($s, $xml, Carbon::now(), Carbon::now())['accepted']);
        Http::assertSent(fn ($request) => $request->url() === 'https://datiturismo.regione.emilia-romagna.it/ws/checkinV2' && $request->hasHeader('SOAPAction', '""') && str_contains($request->body(), '<movimentazione>'));
    }

    public function test_http_page_download_and_invalid_periods(): void
    {
        $s = $this->setupStructure(); $this->stay($s);
        $this->get('/istat-tabella-a?dal=2026-04-01&al=2026-04-30')->assertOk()->assertSee('Valida XML senza inviare');
        $this->post('/istat-tabella-a/download/xml?dal=2026-04-01&al=2026-04-30')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertSame(1, IstatExport::where('struttura_id', $s->id)->count());
        foreach (['dal=2026-02-30&al=2026-04-30', 'dal=2026-05-01&al=2026-04-30', 'mese=2026-13'] as $q) {
            $this->post('/istat-tabella-a/download/xml?'.$q)->assertRedirect()->assertSessionHasErrors('istat_periodo');
        }
        $this->assertSame(1, IstatExport::where('struttura_id', $s->id)->count());
    }

    public function test_local_validation_without_credentials_and_invalid_download_do_not_transmit(): void
    {
        $s = $this->setupStructure();
        $stay = $this->stay($s);
        Http::fake();
        $this->post('/istat-tabella-a/ws/verify', ['dal' => '2026-04-01', 'al' => '2026-04-30'])->assertRedirect()->assertSessionHasNoErrors();
        Http::assertNothingSent();
        $this->assertSame('validated', \App\Models\IstatTransmission::latest('id')->first()->status);
        $count = IstatExport::count();
        $stay->update(['oa_city_nac' => 'CITTADINANZA_INVENTATA']);
        $this->post('/istat-tabella-a/download/xml?dal=2026-04-01&al=2026-04-30')->assertRedirect()->assertSessionHasErrors('istat_export');
        $this->assertSame($count, IstatExport::count());
    }

    public function test_optional_birth_state_and_invalid_component_are_not_guessed(): void
    {
        $s = $this->setupStructure();
        $stay = $this->stay($s, ['oa_country' => null]);
        $x = $this->xpath($this->xml($s, '2026-03-01', '2026-03-31'));
        $this->assertSame('', $x->evaluate('string(//arrivi/arrivo/statonascita)'));
        $stay->update(['cant_people' => 2, 'relationship' => '17']);
        $this->fixtureComponent($stay, ['country' => 'STATO_INVALIDO']);
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertFalse($a['valida']);
        $this->assertStringContainsString('componente #', implode(' ', $a['errors']));
    }

    public function test_deleted_or_reclassified_guest_requires_controlled_rectification(): void
    {
        $s = $this->setupStructure();
        $stay = $this->stay($s, ['arrive' => '2026-04-01']);
        $xml = $this->xml($s);
        $path = 'istat/struttura_'.$s->id.'/fixture.xml';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, $xml);
        IstatExport::create(['struttura_id' => $s->id, 'dal' => '2026-04-01', 'al' => '2026-04-30', 'filename' => 'fixture.xml', 'path' => $path]);
        $this->assertSame($xml, $this->xml($s));
        $stay->delete();
        $a = (new IstatTabellaAService())->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        $this->assertFalse($a['valida']);
        $this->assertStringContainsString('rettifica controllata', implode(' ', $a['errors']));
        $this->expectException(ValidationException::class); $this->xml($s);
    }

    public function test_questura_bytes_and_source_guest_records_remain_unchanged(): void
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        try {
        $s = $this->setupStructure();
        $stay = $this->stay($s, ['name' => 'Fixture', 'surname' => 'Sintetico', 'oa_city' => 'Roma', 'or_published_city' => 'Roma']);
        \App\Models\TipoDocumento::create(['codice' => 'PASOR', 'descrizione' => 'PASSAPORTO']);
        \App\Models\TipoAlloggiato::create(['codice' => '16', 'descrizione' => 'OSPITE SINGOLO']);
        // Per questa regressione servono anche gli stessi dati geografici letti da Questura.
        $region = \App\Models\GeoRegione::create(['geo_nazione_id' => 106, 'nome' => 'Lazio', 'codice_regione' => '12']);
        $province = \App\Models\GeoProvincia::create(['geo_regione_id' => $region->id, 'nome' => 'Roma', 'sigla' => 'RM']);
        \App\Models\GeoComune::create(['geo_provincia_id' => $province->id, 'nome' => 'Roma', 'codice_istat' => '058091']);
        $stay->update(['or_prov' => 'RM', 'oa_prov' => 'RM']);
        $questura = new QuesturaTxtExportService();
        $before = $questura->buildTxtPerSchedina($stay->fresh()->load('componenti'));
        $record = $stay->fresh()->getRawOriginal();
        $this->xml($s);
        $this->assertSame($record, $stay->fresh()->getRawOriginal());
        $this->assertSame($before, $questura->buildTxtPerSchedina($stay->fresh()->load('componenti')));
        $this->assertNotSame('', $before);
        } finally {
            Carbon::setTestNow();
        }
    }
}
