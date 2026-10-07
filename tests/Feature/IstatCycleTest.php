<?php

namespace Tests\Feature;

use App\Models\Componenti;
use App\Models\GeoNazione;
use App\Models\IstatExport;
use App\Models\Schedina;
use App\Models\Struttura;
use App\Services\IstatTabellaAService;
use App\Services\IstatWebService;
use App\Services\IstatXmlValidator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class IstatCycleTest extends TestCase
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
        return (new IstatTabellaAService)->buildXml($s, Carbon::parse($from), Carbon::parse($to));
    }

    private function xpath(string $xml): \DOMXPath
    {
        return new \DOMXPath((new IstatXmlValidator)->document($xml));
    }

    private function sendData(Struttura $s): array
    {
        return ['dal' => '2026-04-01', 'al' => '2026-04-30', 'preview_hash' => hash('sha256', $this->xml($s))];
    }

    private function configured(): Struttura
    {
        $s = $this->setupStructure();
        $this->stay($s);
        $s->update(['istat_username' => 'FIXTURE_USER', 'istat_password' => 'FIXTURE_SECRET']);
        Http::preventStrayRequests();
        config(['istat.enabled' => true]);

        return $s->fresh();
    }

    private function soap(string $xml, string $success = 'true'): string
    {
        $xp = $this->xpath($xml);
        $days = '';
        foreach ($xp->query('/movimenti/movimento') as $day) {
            $groups = '';
            foreach (['arrivi' => 'arrivo', 'partenze' => 'partenza'] as $group => $tag) {
                $records = '';
                foreach ($xp->query($group.'/'.$tag, $day) as $record) {
                    $id = $xp->evaluate('string(idswh)', $record);
                    $records .= "<$tag><idswh>$id</idswh><successo>$success</successo></$tag>";
                }
                if ($records) {
                    $groups .= "<$group>$records</$group>";
                }
            }
            $days .= '<risultatiGiorno>'.$groups.'</risultatiGiorno>';
        }

        return '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><r:inviaMovimentazioneResponse xmlns:r="http://checkin.ws.service.turismo5.gies.it/"><return>'.$days.'</return></r:inviaMovimentazioneResponse></s:Body></s:Envelope>';
    }

    public function test_credentials_ciphertext_reconfiguration_and_no_flash(): void
    {
        $s = $this->configured();
        $this->assertNotSame('FIXTURE_SECRET', $s->getRawOriginal('istat_password'));
        $this->assertNotSame('FIXTURE_USER', $s->getRawOriginal('istat_username'));
        $this->assertStringNotContainsString('FIXTURE_SECRET', $s->toJson());
        $this->get('/istat-tabella-a?mese=2026-04')->assertOk()->assertDontSee('FIXTURE_SECRET')->assertDontSee('FIXTURE_USER');
        $this->post('/istat-tabella-a/configurazione', ['username' => 'SYNTHETIC_USER', 'password' => 'SYNTHETIC_SECRET', 'codice' => str_repeat('X', 51)])
            ->assertSessionHasErrors('istat_config')->assertSessionMissing('_old_input');
        $this->assertSame('FIXTURE_SECRET', $s->fresh()->istat_password);
        $this->post('/istat-tabella-a/configurazione', ['username' => 'NEW_USER', 'password' => 'NEW_SECRET', 'codice' => 'VALID'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('NEW_SECRET', $s->fresh()->istat_password);
        Http::assertNothingSent();
    }

    public function test_legacy_credentials_conversion_is_idempotent_and_preserves_corrupt_ciphertext(): void
    {
        $s = $this->setupStructure();
        \Illuminate\Support\Facades\DB::table('struttura')->where('id', $s->id)->update(['istat_username' => 'LEGACY_USER', 'istat_password' => 'LEGACY_SECRET']);
        $migration = require database_path('migrations/2026_10_07_180000_protect_istat_cycle.php');
        $migration->protectCredentials();
        $this->assertSame('LEGACY_SECRET', $s->fresh()->istat_password);
        $before = $s->fresh()->getRawOriginal('istat_password');
        $migration->protectCredentials();
        $this->assertSame($before, $s->fresh()->getRawOriginal('istat_password'));
        $broken = base64_encode(json_encode(['iv' => 'broken', 'value' => 'broken', 'mac' => 'broken']));
        \Illuminate\Support\Facades\DB::table('struttura')->where('id', $s->id)->update(['istat_password' => $broken]);
        $migration->protectCredentials();
        $this->assertSame($broken, $s->fresh()->getRawOriginal('istat_password'));
        $ws = new IstatWebService(new IstatTabellaAService);
        $this->assertTrue($ws->credentialsStatus($s->fresh())['blocked']);
        $this->get('/istat-tabella-a?mese=2026-04')->assertOk()->assertDontSee($broken);
    }

    public function test_off_and_missing_credentials_produce_no_istat_writer(): void
    {
        $s = $this->configured();
        Http::fake();
        config(['istat.enabled' => false]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasErrors('istat_ws');
        $this->assertSame(0, IstatExport::count());
        $this->assertSame(0, \App\Models\IstatTransmission::count());
        config(['istat.enabled' => true]);
        $s->update(['istat_password' => null]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasErrors('istat_ws');
        $this->assertSame(0, IstatExport::count());
        Http::assertNothingSent();
    }

    public function test_positive_parser_correlates_records_without_claiming_calendar_acceptance(): void
    {
        $s = $this->configured();
        $xml = $this->xml($s);
        $parser = new \App\Services\IstatResponseParser;
        $result = $parser->parse(200, $this->soap($xml), $xml);
        $this->assertSame('processed', $result['state']);
        $this->assertFalse($result['accepted']);
        $this->assertSame('rejected', $parser->parse(200, $this->soap($xml, 'false'), $xml)['state']);
        foreach (['<response/>', '<s:Fault/>', str_replace('<successo>true</successo>', '<successo/>', $this->soap($xml)), str_replace('<idswh>S', '<idswh>FOREIGN', $this->soap($xml))] as $body) {
            $this->assertSame('uncertain', $parser->parse(200, $body, $xml)['state']);
        }
        $this->assertSame('uncertain', $parser->parse(500, $this->soap($xml), $xml)['state']);
    }

    public function test_changed_preview_blocks_send_and_transport_gets_exact_preview_bytes(): void
    {
        $s = $this->configured();
        $data = $this->sendData($s);
        Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->update(['departure' => '2026-04-03']);
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasErrors('istat_ws');
        Http::assertNothingSent();
        $xml = $this->xml($s);
        Http::fake(['*' => Http::response($this->soap($xml), 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasNoErrors();
        $soap = (new IstatTabellaAService)->buildSoapEnvelope($s, $xml, 'send');
        Http::assertSent(fn ($r) => $r->body() === $soap);
        $this->assertSame('processed', \App\Models\IstatTransmission::first()->status);
    }

    public function test_double_click_overlapping_period_and_timeout_reserve_days(): void
    {
        $s = $this->configured();
        $data = $this->sendData($s);
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;
            throw new \Illuminate\Http\Client\ConnectionException('SYNTHETIC_SECRET');
        });
        $this->post('/istat-tabella-a/ws/send', $data)->assertRedirect();
        $this->assertSame('uncertain', \App\Models\IstatTransmission::first()->status);
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasErrors('istat_ws');
        $this->assertSame(1, $calls);
        $this->assertSame(1, \App\Models\IstatTransmission::count());
        $this->assertSame(30, \Illuminate\Support\Facades\DB::table('istat_communication_days')->count());
        $this->assertStringNotContainsString('SYNTHETIC_SECRET', \App\Models\IstatTransmission::first()->toJson());
    }

    public function test_manual_download_is_not_delivery_and_registration_prevents_duplicate(): void
    {
        $s = $this->configured();
        Http::fake();
        $this->get('/istat-tabella-a/download/xml?mese=2026-04')->assertNotFound();
        $this->assertSame(0, IstatExport::count());
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertOk();
        $export = IstatExport::first();
        $this->assertSame(0, \App\Models\IstatTransmission::count());
        $stored = \Illuminate\Support\Facades\Storage::disk('local')->get($export->path);
        $this->assertStringNotContainsString('<movimenti', $stored);
        $this->assertSame($export->sha256, hash('sha256', (new \App\Services\IstatPayloadStore)->read($export)));
        $this->post('/istat-tabella-a/manuale/'.$export->id)->assertSessionHasErrors('istat_ws');
        $this->post('/istat-tabella-a/manuale/'.$export->id, ['conferma_portale' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('manual_registered', \App\Models\IstatTransmission::first()->status);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasErrors('istat_ws');
        Http::assertNothingSent();
    }

    public function test_changes_and_cancellation_require_recorded_portal_rectification(): void
    {
        $s = $this->configured();
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s));
        $tx = \App\Models\IstatTransmission::first();
        $stay = Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->first();
        $stay->update(['departure' => '2026-04-03']);
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertSessionHasErrors('istat_export');
        $this->post('/istat-tabella-a/riconcilia/'.$tx->id)->assertSessionHasErrors('istat_ws');
        $this->post('/istat-tabella-a/riconcilia/'.$tx->id, ['conferma_portale' => 1])->assertSessionHasNoErrors();
        $this->assertNotNull($tx->fresh()->reconciled_at);
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertOk();
        $this->assertSame(2, IstatExport::count());
        $this->assertSame(0, \Illuminate\Support\Facades\DB::table('istat_communication_days')->count());
    }

    public function test_tenant_and_roles_are_enforced_for_every_new_writer(): void
    {
        $s = $this->configured();
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        foreach (['/istat-tabella-a/configurazione', '/istat-tabella-a/ws/send', '/istat-tabella-a/download/xml'] as $url) {
            $this->post($url.'?sid='.$foreign->id, ['mese' => '2026-04'])->assertForbidden();
        }
        $this->actingAs($this->actor('struttura_user', null, $foreign->id));
        $this->get('/istat-tabella-a?sid='.$s->id)->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_retention_keeps_uncertain_payload_and_preserves_metadata_when_pruned(): void
    {
        $s = $this->configured();
        Http::fake(['*' => Http::response('<malformed/>', 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s));
        $export = IstatExport::first();
        $export->update(['expires_at' => now()->subDay()]);
        $retention = new \App\Services\IstatRetention;
        $this->assertSame(0, $retention->minimize(true));
        $this->post('/istat-tabella-a/riconcilia/'.\App\Models\IstatTransmission::first()->id, ['conferma_portale' => 1]);
        $this->assertSame(1, $retention->minimize(false));
        $this->assertNull($export->fresh()->minimized_at);
        $this->assertSame(1, $retention->minimize(true));
        $this->assertNotNull($export->fresh()->minimized_at);
        $this->assertNotEmpty($export->fresh()->snapshot);
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('local')->exists($export->path));
    }

    public function test_certain_pre_transport_failure_allows_single_controlled_retry(): void
    {
        $s = $this->configured();
        $s->update(['istat_ws_url' => 'https://fixture.invalid']);
        $data = $this->sendData($s);
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $this->post('/istat-tabella-a/ws/send', $data);
        $tx = \App\Models\IstatTransmission::first();
        $this->assertSame('not_delivered', $tx->status);
        Http::assertNothingSent();
        $s->update(['istat_ws_url' => null]);
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasNoErrors();
        $this->assertSame(2, $tx->fresh()->attempts);
        $this->assertSame('processed', $tx->fresh()->status);
        $this->assertSame(1, \App\Models\IstatTransmission::count());
        Http::assertSentCount(1);
    }

    public function test_daily_fallback_is_exactly_one_day_and_source_cancellation_restore_is_visible(): void
    {
        $s = $this->configured();
        Http::fake();
        $response = $this->post('/istat-tabella-a/download/xml', ['dal' => '2026-04-02', 'al' => '2026-04-02'])->assertOk();
        $xpath = $this->xpath($response->getContent());
        $this->assertSame(1, $xpath->query('/movimenti/movimento')->length);
        $this->assertSame('20260402', $xpath->evaluate('string(/movimenti/movimento/data)'));
        $stay = Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->first();
        $before = $stay->getRawOriginal();
        $stay->delete();
        $this->assertSame(0, (new IstatTabellaAService)->analysePeriodo($s, Carbon::parse('2026-04-02'), Carbon::parse('2026-04-02'))['totale_schedine']);
        $stay = Schedina::forceCreate($before);
        $this->assertSame($before['arrive'], $stay->fresh()->getRawOriginal('arrive'));
        $this->assertSame(1, (new IstatTabellaAService)->analysePeriodo($s, Carbon::parse('2026-04-02'), Carbon::parse('2026-04-02'))['totale_schedine']);
        Http::assertNothingSent();
    }

    public function test_crash_pending_and_unique_constraint_prevent_another_operator(): void
    {
        $s = $this->configured();
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04']);
        $export = IstatExport::first();
        $operations = new \App\Services\IstatOperationService;
        $tx = $operations->reserve($s, $export, 'send', null);
        $this->assertSame('pending', $tx->status);
        $this->post('/istat-tabella-a/riconcilia/'.$tx->id, ['conferma_portale' => 1])->assertSessionHasErrors('istat_ws');
        try {
            $operations->reserve($s, $export, 'manual', null);
            $this->fail('Prenotazione parallela ammessa.');
        } catch (ValidationException) {
            $this->assertSame(1, \App\Models\IstatTransmission::count());
        }
        try {
            \Illuminate\Support\Facades\DB::table('istat_communication_days')->insert([
                'struttura_id' => $s->id, 'giorno' => '2026-04-01', 'istat_transmission_id' => $tx->id,
            ]);
            $this->fail('Vincolo univoco assente.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('23000', $e->errorInfo[0]);
        }
    }

    /** @dataProvider postTransmissionChanges */
    public function test_post_transmission_changes_are_detected_and_restore_keeps_duplicate_block(string $change): void
    {
        $s = $this->configured();
        $stay = Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->first();
        if ($change === 'remove_component') {
            $stay->update(['relationship' => '17', 'cant_people' => 2]);
            $this->fixtureComponent($stay);
        }
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasNoErrors();
        if ($change === 'add_component') {
            $stay->update(['relationship' => '17', 'cant_people' => 2]);
            $this->fixtureComponent($stay);
        } elseif ($change === 'remove_component') {
            $stay->componenti()->delete();
            $stay->update(['relationship' => '16', 'cant_people' => 1]);
        } elseif (in_array($change, ['cancel', 'restore'], true)) {
            $original = $stay->fresh()->getRawOriginal();
            $stay->delete();
            if ($change === 'restore') {
                Schedina::forceCreate($original);
            }
        } else {
            $updates = [
                'guest' => ['name' => 'Altro sintetico'], 'arrival' => ['arrive' => '2026-04-01'],
                'departure' => ['departure' => '2026-04-04'], 'extension' => ['departure' => '2026-05-01'],
                'reduction' => ['departure' => '2026-04-01'], 'stay' => ['room' => 2],
            ];
            $stay->update($updates[$change]);
        }
        $analysis = (new IstatTabellaAService)->analysePeriodo($s, Carbon::parse('2026-04-01'), Carbon::parse('2026-04-30'));
        if ($change === 'restore') {
            $this->assertTrue($analysis['valida']);
            $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasErrors('istat_ws');
        } else {
            $this->assertFalse($analysis['valida']);
            $this->assertStringContainsString('rettificare', implode(' ', $analysis['errors']));
            $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertSessionHasErrors('istat_export');
        }
        Http::assertSentCount(1);
        $this->assertSame(1, \App\Models\IstatTransmission::count());
    }

    public static function postTransmissionChanges(): array
    {
        return array_map(fn ($change) => [$change], ['stay', 'guest', 'add_component', 'remove_component', 'arrival', 'departure', 'extension', 'reduction', 'cancel', 'restore']);
    }

    public function test_unchanged_payload_can_be_reimported_only_after_explicit_portal_rectification(): void
    {
        $s = $this->configured();
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $data = $this->sendData($s);
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasNoErrors();
        $tx = \App\Models\IstatTransmission::first();
        $this->post('/istat-tabella-a/riconcilia/'.$tx->id, ['conferma_portale' => 1])->assertSessionHasNoErrors();
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasNoErrors();
        $this->assertSame(2, \App\Models\IstatTransmission::count());
        $this->assertNotSame($tx->idempotency_key, \App\Models\IstatTransmission::latest('id')->first()->idempotency_key);
        Http::assertSentCount(2);
    }

    public function test_guest_changed_after_previous_month_communication_blocks_next_month_departure(): void
    {
        $s = $this->configured();
        Http::fake(['*' => Http::response($this->soap($this->xml($s, '2026-03-01', '2026-03-31')), 200)]);
        $data = ['dal' => '2026-03-01', 'al' => '2026-03-31', 'preview_hash' => hash('sha256', $this->xml($s, '2026-03-01', '2026-03-31'))];
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasNoErrors();
        Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->update(['name' => 'Variazione sintetica']);
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertSessionHasErrors('istat_export');
        Http::assertSentCount(1);
    }

    public function test_preview_is_extracted_from_exact_validated_xml_and_has_no_cache(): void
    {
        $s = $this->configured();
        $xml = $this->xml($s, '2026-03-01', '2026-04-30');
        $records = (new IstatTabellaAService)->previewRecords($xml);
        $this->assertSame(['Arrivo', 'Partenza'], array_column($records, 'tipo'));
        $this->assertSame('100000100', $records[0]['statoresidenza']);
        $this->assertSame('412058091', $records[0]['luogoresidenza']);
        $this->assertSame('20260331', $records[1]['arrivo']);
        $this->assertSame('', $records[1]['nome']);
        $response = $this->get('/istat-tabella-a?dal=2026-03-01&al=2026-04-30')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee(hash('sha256', $xml))->assertSee('Dettaglio dei movimenti da comunicare');
        $this->assertStringNotContainsString('FIXTURE_SECRET', $response->getContent());
    }

    public function test_portal_procedure_actor_and_date_are_visible_without_free_text(): void
    {
        $s = $this->configured();
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s));
        $tx = \App\Models\IstatTransmission::first();
        $this->post('/istat-tabella-a/riconcilia/'.$tx->id, ['conferma_portale' => 1, 'procedura' => 'SYNTHETIC_SECRET'])->assertSessionHasErrors('istat_ws');
        $this->assertNull($tx->fresh()->reconciled_at);
        $this->post('/istat-tabella-a/riconcilia/'.$tx->id, ['conferma_portale' => 1, 'procedura' => 'annullamento_portale'])->assertSessionHasNoErrors();
        $event = \Illuminate\Support\Facades\DB::table('istat_transmission_events')->where('status', 'portal_reconciled')->first();
        $this->assertSame(['procedure' => 'annullamento_portale'], json_decode($event->result, true));
        $this->get('/istat-tabella-a?mese=2026-04')->assertOk()->assertSee('Annullamento sul portale')->assertDontSee('SYNTHETIC_SECRET');
    }

    /** @dataProvider activeCommunicationStates */
    public function test_reconciliation_protects_each_new_communication(string $state): void
    {
        $s = $this->configured();
        Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->update(['arrive' => '2026-04-01']);
        $xml = $this->xml($s);
        $data = $this->sendData($s);
        Http::fake(['*' => Http::response($this->soap($xml), 200)]);
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasNoErrors();
        $first = \App\Models\IstatTransmission::first();
        $this->post('/istat-tabella-a/riconcilia/'.$first->id, ['conferma_portale' => 1])->assertSessionHasNoErrors();
        $stay = Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->first();
        $original = $stay->name;
        $stay->update(['name' => 'Rettifica sintetica A']);
        $this->assertStringContainsString('Rettifica sintetica A', $this->xml($s));
        $stay->update(['name' => $original]);
        $this->post('/istat-tabella-a/ws/send', $data)->assertSessionHasNoErrors();
        $second = \App\Models\IstatTransmission::latest('id')->first();
        $this->assertSame($first->istat_export_id, $second->istat_export_id);
        $this->assertNull($second->reconciled_at);
        if ($state !== 'processed') {
            \Illuminate\Support\Facades\DB::table('istat_transmissions')->where('id', $second->id)->update(['status' => $state]);
        }
        $stay->update(['name' => 'Modifica sintetica B']);
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertSessionHasErrors('istat_export');
        $this->post('/istat-tabella-a/riconcilia/'.$second->id, ['conferma_portale' => 1])->assertSessionHasNoErrors();
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertOk()->assertSee('Modifica sintetica B');
        $this->assertSame($xml, (new \App\Services\IstatPayloadStore)->read(IstatExport::findOrFail($first->istat_export_id)));
        Http::assertSentCount(2);
    }

    public static function activeCommunicationStates(): array
    {
        return [['processed'], ['uncertain'], ['partial']];
    }

    public function test_foreign_communication_and_reconciliation_cannot_change_local_history(): void
    {
        $s = $this->configured();
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasNoErrors();
        $local = \App\Models\IstatTransmission::first();
        $foreign = $this->structureFor($this->ownerFor($this->actor('admin')));
        // Associazione incoerente sintetica: il validator non deve fidarsi del solo export_id.
        $other = \App\Models\IstatTransmission::create([
            'struttura_id' => $foreign->id, 'istat_export_id' => $local->istat_export_id,
            'mode' => 'send', 'status' => 'processed', 'dal' => $local->dal, 'al' => $local->al,
            'reconciled_at' => now(),
        ]);
        $this->post('/istat-tabella-a/riconcilia/'.$other->id, ['conferma_portale' => 1])->assertNotFound();
        Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->update(['name' => 'Variazione sintetica tenant']);
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertSessionHasErrors('istat_export');
        $this->post('/istat-tabella-a/riconcilia/'.$local->id, ['conferma_portale' => 1])->assertSessionHasNoErrors();
        $other->update(['reconciled_at' => null]);
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertOk();
        Http::assertSentCount(1);
    }

    public function test_retry_preserves_origin_and_attributes_current_attempt_to_new_actor(): void
    {
        $s = $this->configured();
        $origin = auth()->id();
        $s->update(['istat_ws_url' => 'https://fixture.invalid']);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasNoErrors();
        $tx = \App\Models\IstatTransmission::first();
        $this->assertSame('not_delivered', $tx->status);
        $retry = $this->actor('proprietario', $s->proprietario_id, $s->id);
        $this->actingAs($retry);
        $s->update(['istat_ws_url' => null]);
        Http::fake(['*' => Http::response($this->soap($this->xml($s)), 200)]);
        $this->post('/istat-tabella-a/ws/send', $this->sendData($s))->assertSessionHasNoErrors();
        $this->assertSame($origin, (int) $tx->fresh()->user_id);
        $this->assertSame(2, (int) $tx->fresh()->attempts);
        $events = \Illuminate\Support\Facades\DB::table('istat_transmission_events')->where('istat_transmission_id', $tx->id)->orderBy('id')->get();
        $this->assertSame(['pending', 'not_delivered', 'pending', 'processed'], $events->pluck('status')->all());
        $this->assertSame([$origin, $origin, $retry->id, $retry->id], $events->pluck('user_id')->map(fn ($id) => (int) $id)->all());
        $this->get('/istat-tabella-a?mese=2026-04')->assertOk()->assertSee('Origine #'.$origin)->assertSee('Tentativo #'.$retry->id);
        Http::assertSentCount(1);
    }

    public function test_manual_history_label_is_distinct_from_web_service(): void
    {
        $s = $this->configured();
        $this->post('/istat-tabella-a/download/xml', ['mese' => '2026-04'])->assertOk();
        $this->post('/istat-tabella-a/manuale/'.IstatExport::first()->id, ['conferma_portale' => 1])->assertSessionHasNoErrors();
        $this->get('/istat-tabella-a?mese=2026-04')->assertOk()->assertSee('<td class="text-nowrap">Consegna sul portale</td>', false);
        $this->assertSame('manual_registered', \App\Models\IstatTransmission::first()->status);
        Http::assertNothingSent();
    }
}
