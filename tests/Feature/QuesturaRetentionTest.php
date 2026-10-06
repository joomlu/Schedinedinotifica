<?php

namespace Tests\Feature;

use App\Models\{QuesturaExport, QuesturaReceipt, QuesturaTransmission, Struttura, Schedina};
use App\Services\{QuesturaRetentionService, QuesturaWebService};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class QuesturaRetentionTest extends TestCase
{
    use RefreshDatabase, StrutturaFixtures;

    public const PDF = "%PDF-1.4\nRICEVUTA SINTETICA\n%%EOF";
    private const TXT = 'ELENCO SINTETICO TEMPORANEO';

    private function copies(Struttura $s, string $status = 'sent'): array
    {
        $day = now()->subDay()->startOfDay();
        $path = 'questura/struttura_'.$s->id.'/fixture.txt';
        Storage::disk('local')->put($path, self::TXT);
        $hash = hash('sha256', self::TXT);
        $export = QuesturaExport::create(['struttura_id' => $s->id, 'dal' => $day, 'al' => $day, 'filename' => 'fixture.txt', 'path' => $path, 'status' => 'generated', 'sha256' => $hash, 'byte_size' => strlen(self::TXT), 'righe_count' => 1, 'schedina_ids' => [901], 'component_ids' => [902], 'created_at' => $day]);
        // Timestamp impostato attraverso query builder per fixture autosufficiente.
        DB::table('questura_exports')->where('id', $export->id)->update(['created_at' => $day]);
        $tx = QuesturaTransmission::create(['struttura_id' => $s->id, 'mode' => 'send', 'status' => $status, 'executed_at' => $day, 'sha256' => $hash, 'byte_size' => strlen(self::TXT), 'righe_count' => 1, 'schedina_ids' => [901], 'component_ids' => [902], 'payload' => ['transport_mode' => 'live', 'txt_base64' => base64_encode(self::TXT)], 'result' => ['name' => 'PERSONALE SINTETICO']]);
        DB::table('questura_transmission_events')->insert(['questura_transmission_id' => $tx->id, 'status' => $status, 'result' => json_encode(['legacy_personal' => 'SINTETICO']), 'created_at' => now()]);
        return [$export->fresh(), $tx];
    }

    private function receipt(Struttura $s, ?int $tx = null): QuesturaReceipt
    {
        return app(QuesturaRetentionService::class)->archiveReceipt($s, now()->subDay(), self::PDF, $tx);
    }

    public function test_scrittura_parziale_fallita_non_lascia_pdf_orfano(): void
    {
        $s = $this->structureFor(null); $real = Storage::disk('local'); $manager = Storage::getFacadeRoot();
        $disk = \Mockery::mock($real);
        $disk->shouldReceive('put')->once()->andReturnUsing(function ($path, $bytes) use ($real) { $real->put($path, substr($bytes, 0, 12)); return false; });
        Storage::shouldReceive('disk')->with('local')->andReturn($disk);
        try { $this->receipt($s); $this->fail('Scrittura incompleta accettata'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(500, $e->getStatusCode()); }
        finally { Storage::swap($manager); }
        $this->assertSame([], $real->allFiles('questura/struttura_'.$s->id.'/ricevute'));
        $this->assertSame(0, QuesturaReceipt::count());
    }

    public function test_errore_db_compensa_pdf_senza_falsa_acquisizione(): void
    {
        $s = $this->structureFor(null);
        QuesturaReceipt::creating(fn () => throw new \RuntimeException('ERRORE DB SINTETICO'));
        try { $this->receipt($s); $this->fail('Errore DB ignorato'); }
        catch (\RuntimeException $e) { $this->assertSame('ERRORE DB SINTETICO', $e->getMessage()); }
        finally { QuesturaReceipt::flushEventListeners(); QuesturaReceipt::clearBootedModels(); }
        $this->assertSame(0, QuesturaReceipt::count());
        $this->assertSame([], Storage::disk('local')->allFiles('questura/struttura_'.$s->id.'/ricevute'));
    }

    public function test_compensazione_fallita_rilevabile_audit_senza_falsa_ricevuta(): void
    {
        $s = $this->structureFor(null); $real = Storage::disk('local'); $manager = Storage::getFacadeRoot();
        $disk = \Mockery::mock($real); $disk->shouldReceive('delete')->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('local')->andReturn($disk);
        QuesturaReceipt::creating(fn () => throw new \RuntimeException('DB SINTETICO'));
        try { $this->receipt($s); $this->fail('Compensazione fallita ignorata'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(503, $e->getStatusCode()); }
        finally { Storage::swap($manager); QuesturaReceipt::flushEventListeners(); QuesturaReceipt::clearBootedModels(); }
        $this->assertSame(0, QuesturaReceipt::count());
        $audit = app(QuesturaRetentionService::class)->auditArchives($s->id);
        $this->assertSame('file_senza_record', $audit[0]['stato']);
        $this->assertCount(1, $real->allFiles('questura/struttura_'.$s->id.'/ricevute'));
        $this->artisan('questura:audit-archivi', ['struttura_id' => $s->id])->expectsOutputToContain('file_senza_record')->assertSuccessful();
        $this->assertSame(0, QuesturaReceipt::count());
    }

    public function test_audit_legacy_calcola_solo_da_byte_presenti_senza_backfill(): void
    {
        $s = $this->structureFor(null); [$export] = $this->copies($s);
        DB::table('questura_exports')->where('id', $export->id)->update(['sha256' => null]);
        $service = app(QuesturaRetentionService::class);
        $row = $service->auditArchives($s->id)[0];
        $this->assertSame('hash_legacy_assente', $row['stato']);
        $this->assertSame(hash('sha256', self::TXT), $row['sha256_osservato']);
        $this->assertNull($export->fresh()->sha256);
        $this->actingAs($this->actor('struttura_user', null, $s->id))->get('/questura/download/storico/'.$export->id)->assertStatus(409);
        Storage::disk('local')->delete($export->path);
        $row = $service->auditArchives($s->id)[0];
        $this->assertSame('file_assente', $row['stato']); $this->assertNull($row['sha256_osservato']);
        $this->assertSame([], $service->auditArchives($s->id + 10000));
        $this->assertNull($export->fresh()->sha256);
    }

    public function test_retention_data_normale_prima_limite_esatto_e_dopo(): void
    {
        Carbon::setTestNow('2026-10-06 12:30:00');
        try {
            $s = $this->structureFor(null); $r = $this->receipt($s); $service = app(QuesturaRetentionService::class);
            $this->assertSame('2031-10-06 12:30:00', $r->retained_until->toDateTimeString());
            $this->assertCount(0, $service->expiredReceipts($s->id, Carbon::parse('2031-10-06 12:29:59')));
            $this->assertCount(1, $service->expiredReceipts($s->id, Carbon::parse('2031-10-06 12:30:00')));
            $this->assertCount(1, $service->expiredReceipts($s->id, Carbon::parse('2031-10-06 12:30:01')));
            $this->assertTrue(Storage::disk('local')->exists($r->path));
            $this->assertNull($r->fresh()->purged_at);
        } finally { Carbon::setTestNow(); }
    }

    public function test_errore_dopo_insert_non_lascia_record_senza_pdf(): void
    {
        $s = $this->structureFor(null);
        QuesturaReceipt::created(fn () => throw new \RuntimeException('ERRORE DOPO INSERT SINTETICO'));
        try { $this->receipt($s); $this->fail('Errore successivo a INSERT ignorato'); }
        catch (\RuntimeException $e) { $this->assertSame('ERRORE DOPO INSERT SINTETICO', $e->getMessage()); }
        finally { QuesturaReceipt::flushEventListeners(); QuesturaReceipt::clearBootedModels(); }
        $this->assertSame(0, QuesturaReceipt::count());
        $this->assertSame([], Storage::disk('local')->allFiles('questura/struttura_'.$s->id.'/ricevute'));
    }

    public function test_senza_ricevuta_payload_e_file_restano(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s);
        try { app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, 999999, 1); $this->fail('Ricevuta assente accettata'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { }
        $this->assertSame(self::TXT, Storage::disk('local')->get($export->path));
        $this->assertNotNull($tx->fresh()->payload); $this->assertNull($tx->fresh()->finalized_at);
    }

    public function test_txt_soltanto_scaricato_non_e_finalizzato(): void
    {
        $s = $this->structureFor(null); [$export] = $this->copies($s);
        $this->actingAs($this->actor('struttura_user', null, $s->id))->get('/questura/download/storico/'.$export->id)->assertOk();
        $this->assertNull($export->fresh()->finalized_at); $this->assertTrue(Storage::disk('local')->exists($export->path));
        $this->post('/questura/txt/'.$export->id.'/ricevuta', ['communication_date' => now()->subDay()->toDateString()])->assertSessionHasErrors('communication_confirmed');
        $this->assertTrue(Storage::disk('local')->exists($export->path));
    }

    public function test_uncertain_anche_con_ricevuta_richiede_riconciliazione(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s, 'uncertain'); $r = $this->receipt($s, $tx->id);
        try { app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1); $this->fail('Esito incerto cancellato'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertNotNull($tx->fresh()->payload); $this->assertTrue(Storage::disk('local')->exists($export->path));
        app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1, true);
        $this->assertNotNull($tx->fresh()->reconciled_at); $this->assertNull($tx->fresh()->payload);
    }

    public function test_finalizzazione_minimizza_file_db_base64_eventi_e_preserva_ricevuta_metadata(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id);
        $test = QuesturaTransmission::create(['struttura_id' => $s->id, 'mode' => 'verify', 'status' => 'unknown', 'sha256' => $tx->sha256, 'payload' => ['txt_base64' => base64_encode(self::TXT)]]);
        app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1);
        $this->assertFalse(Storage::disk('local')->exists($export->path));
        $e = $export->fresh(); $t = $tx->fresh();
        foreach (['path', 'schedina_ids', 'component_ids', 'dal', 'al'] as $field) { $this->assertNull($e->$field); }
        foreach (['payload', 'result', 'schedina_ids', 'component_ids', 'response_detail', 'dal', 'al'] as $field) { $this->assertNull($t->$field); }
        $this->assertNull($test->fresh()->payload);
        $this->assertSame($tx->sha256, $t->sha256); $this->assertSame(strlen(self::TXT), (int) $t->byte_size);
        $this->assertSame(1, (int) $t->righe_count); $this->assertSame('sent', $t->status);
        $this->assertNotNull($t->payload_deleted_at); $this->assertSame($r->id, (int) $t->questura_receipt_id);
        $this->assertStringNotContainsString('PERSONALE', DB::table('questura_transmission_events')->where('questura_transmission_id', $tx->id)->pluck('result')->implode(''));
        $this->assertSame(self::PDF, Storage::disk('local')->get($r->path));
        $this->assertSame($r->sha256, $r->fresh()->sha256);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->get('/questura/download/storico/'.$e->id)->assertStatus(410);
        $this->get('/questura/ws/payload/'.$t->id)->assertStatus(410);
        $this->get('/questura/ricevute/'.$r->id)->assertOk();
        $this->get('/questura')->assertOk()->assertSee('TXT eliminato dopo ricevuta')->assertSee('Payload eliminato dopo ricevuta');
    }

    public function test_idempotenza_non_duplica_eventi_e_non_cambia_scadenza(): void
    {
        $s = $this->structureFor(null); [, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id); $service = app(QuesturaRetentionService::class);
        $service->finalizeTransmission($s->id, $tx->id, $r->id, 1); $time = $tx->fresh()->payload_deleted_at;
        $count = DB::table('questura_transmission_events')->count();
        $service->finalizeTransmission($s->id, $tx->id, $r->id, 1);
        $this->assertSame($count, DB::table('questura_transmission_events')->count());
        $this->assertTrue($time->equalTo($tx->fresh()->payload_deleted_at));
        $this->assertSame($r->retained_until->toDateTimeString(), $r->fresh()->retained_until->toDateTimeString());
    }

    public function test_errore_filesystem_non_dichiara_cancellazione_e_retry_sicuro(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id);
        $manager = Storage::getFacadeRoot();
        $disk = \Mockery::mock(Storage::disk('local'));
        $disk->shouldReceive('delete')->with($export->path)->once()->andReturn(false);
        Storage::shouldReceive('disk')->with('local')->andReturn($disk);
        try { app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1); $this->fail('Cancellazione falsa'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(503, $e->getStatusCode()); }
        finally { Storage::swap($manager); }
        $this->assertTrue(Storage::disk('local')->exists($export->path)); $this->assertNull($tx->fresh()->payload_deleted_at); $this->assertNotNull($tx->fresh()->payload);
        Storage::disk('local')->delete($export->path); // Recupero di crash fra unlink e commit DB.
        app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1);
        $this->assertNotNull($tx->fresh()->payload_deleted_at); $this->assertNull($tx->fresh()->payload);
    }

    public function test_tenant_incompatibile_e_path_traversal_non_cancellano(): void
    {
        $a = $this->structureFor(null); $b = $this->structureFor(null); [$export, $tx] = $this->copies($b); $r = $this->receipt($b, $tx->id);
        $this->actingAs($this->actor('struttura_user', null, $a->id))->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertNotFound();
        $this->post('/questura/txt/'.$export->id.'/ricevuta', ['communication_date' => now()->subDay()->toDateString(), 'communication_confirmed' => 1])->assertNotFound();
        $this->get('/questura/ricevute/'.$r->id)->assertNotFound();
        $this->assertNotNull($tx->fresh()->payload); $this->assertTrue(Storage::disk('local')->exists($export->path));
        DB::table('questura_exports')->where('id', $export->id)->update(['path' => 'questura/struttura_'.$b->id.'/../vietato.txt']);
        try { app(QuesturaRetentionService::class)->finalizeTransmission($b->id, $tx->id, $r->id, 1); $this->fail('Traversal consentito'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertTrue(Storage::disk('local')->exists($export->path));
    }

    public function test_ricevuta_alterata_o_giornata_diversa_blocca_finalizzazione(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id);
        Storage::disk('local')->put($r->path, self::PDF.'ALTERATO');
        try { app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1); $this->fail('PDF alterato accettato'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        Storage::disk('local')->put($r->path, self::PDF);
        DB::table('questura_receipts')->where('id', $r->id)->update(['remote_date' => now()->subDays(2)]);
        try { app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1); $this->fail('Giornata diversa accettata'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertTrue(Storage::disk('local')->exists($export->path));
    }

    public function test_retention_cinque_anni_calendario_e_selezione_scadenze_non_distruttiva(): void
    {
        Carbon::setTestNow('2024-02-29 12:00:00');
        try {
            $s = $this->structureFor(null); $r = $this->receipt($s);
            $this->assertSame('2024-02-29 12:00:00', $r->acquired_at->toDateTimeString());
            $this->assertSame('2029-02-28 12:00:00', $r->retained_until->toDateTimeString());
            $service = app(QuesturaRetentionService::class);
            $this->assertCount(0, $service->expiredReceipts($s->id, Carbon::parse('2029-02-28 11:59:59')));
            $this->assertCount(1, $service->expiredReceipts($s->id, Carbon::parse('2029-02-28 12:00:00')));
            $this->assertCount(0, $service->expiredReceipts($s->id + 1000, Carbon::parse('2030-01-01')));
            $this->artisan('questura:ricevute-scadute', ['struttura_id' => $s->id, '--at' => '2030-01-01'])->expectsOutputToContain('Sola lettura')->assertSuccessful();
            $this->assertTrue(Storage::disk('local')->exists($r->path)); $this->assertNull($r->fresh()->purged_at);
        } finally { Carbon::setTestNow(); }
    }

    public function test_flusso_manual_receipt_download_non_rigenera_e_finalizza(): void
    {
        $s = $this->structureFor(null); [$export] = $this->copies($s);
        $ws = new class extends QuesturaWebService {
            public int $calls = 0;
            public function receipt(Struttura $s, Carbon $date): array { $this->calls++; return ['state' => 'receipt_available', 'bytes' => QuesturaRetentionTest::PDF, 'mime' => 'application/pdf']; }
        };
        $this->app->instance(QuesturaWebService::class, $ws);
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->post('/questura/txt/'.$export->id.'/ricevuta', ['communication_date' => now()->subDay()->toDateString(), 'communication_confirmed' => 1])->assertRedirect();
        $this->assertNull($export->fresh()->path); $this->assertSame(1, $ws->calls);
        $r = QuesturaReceipt::firstOrFail(); $this->assertNull($r->questura_transmission_id);
        $this->get('/questura/ricevute/'.$r->id)->assertOk(); $this->assertSame(1, $ws->calls);
        $this->post('/questura/txt/'.$export->id.'/ricevuta', ['communication_date' => now()->subDay()->toDateString(), 'communication_confirmed' => 1])->assertRedirect();
        $this->assertSame(1, $ws->calls);
    }

    public function test_acquisizione_ws_sent_finalizza_mentre_uncertain_no(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s, 'uncertain');
        $this->app->instance(QuesturaWebService::class, new class extends QuesturaWebService {
            public function receipt(Struttura $s, Carbon $d): array { return ['state' => 'receipt_available', 'bytes' => QuesturaRetentionTest::PDF]; }
        });
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->post('/questura/ws/receipt/'.$tx->id)->assertRedirect();
        $this->assertNotNull($tx->fresh()->payload); $this->assertTrue(Storage::disk('local')->exists($export->path));
        $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1, '_token' => 'errato'])->assertStatus(419);
        $this->assertNotNull($tx->fresh()->payload);
        $this->post('/questura/ws/'.$tx->id.'/finalizza', ['reconciled' => 1])->assertRedirect();
        $this->assertNull($tx->fresh()->payload);
        $b = $this->structureFor(null); [, $sent] = $this->copies($b);
        $this->actingAs($this->actor('struttura_user', null, $b->id))->withSession(['struttura_corrente_id' => $b->id]);
        $this->post('/questura/ws/receipt/'.$sent->id)->assertRedirect(); $this->assertNull($sent->fresh()->payload);
    }

    public function test_copie_diverse_simulazione_e_schedina_pms_non_toccate(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id);
        $unrelated = QuesturaTransmission::create(['struttura_id' => $s->id, 'mode' => 'verify', 'status' => 'simulation', 'sha256' => hash('sha256', 'DIVERSO'), 'payload' => ['txt_base64' => base64_encode('DIVERSO')]]);
        $schedina = new Schedina(['struttura_id' => $s->id, 'scheda' => 'RETENTION SINTETICA', 'surname' => 'Esempio', 'name' => 'Ospite', 'circuito' => 'schedina']); $schedina->save();
        $schedina->update(['name' => 'Modificato']); $raw = $schedina->fresh()->getRawOriginal();
        app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1);
        $this->assertSame($raw, $schedina->fresh()->getRawOriginal()); $this->assertNotNull($unrelated->fresh()->payload);
        $this->assertTrue(Storage::disk('local')->exists($r->path));
    }
    public function test_simulazione_non_puo_finalizzare_con_ricevuta_altrimenti_valida(): void
    {
        $s = $this->structureFor(null); [$export, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id);
        DB::table('questura_transmissions')->where('id', $tx->id)->update(['payload' => json_encode(['transport_mode' => 'simulation', 'txt_base64' => base64_encode(self::TXT)])]);
        try { app(QuesturaRetentionService::class)->finalizeTransmission($s->id, $tx->id, $r->id, 1, true); $this->fail('Simulazione assimilata a invio'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertTrue(Storage::disk('local')->exists($export->path)); $this->assertNotNull($tx->fresh()->payload);
    }

    public function test_ricevuta_non_disponibile_non_finalizza_txt_manual(): void
    {
        $s = $this->structureFor(null); [$export] = $this->copies($s);
        $this->app->instance(QuesturaWebService::class, new class extends QuesturaWebService {
            public function receipt(Struttura $s, Carbon $d): array { return ['state' => 'unavailable']; }
        });
        $this->actingAs($this->actor('struttura_user', null, $s->id));
        $this->post('/questura/txt/'.$export->id.'/ricevuta', ['communication_date' => now()->subDay()->toDateString(), 'communication_confirmed' => 1])->assertRedirect()->assertSessionHasErrors('questura_ws');
        $this->assertTrue(Storage::disk('local')->exists($export->path)); $this->assertNull($export->fresh()->payload_deleted_at);
    }

    public function test_giornata_finalizza_tutti_i_send_certi_ma_preserva_incerto_e_altra_giornata(): void
    {
        $s = $this->structureFor(null); [, $tx] = $this->copies($s); $r = $this->receipt($s, $tx->id);
        $data = ['struttura_id' => $s->id, 'mode' => 'send', 'status' => 'sent', 'executed_at' => now()->subDay(), 'sha256' => hash('sha256', 'ALTRO ELENCO'), 'payload' => ['transport_mode' => 'live', 'txt_base64' => base64_encode('ALTRO ELENCO')]];
        $other = QuesturaTransmission::create($data);
        $uncertain = QuesturaTransmission::create(array_replace($data, ['status' => 'uncertain']));
        $differentDay = QuesturaTransmission::create(array_replace($data, ['executed_at' => now()->subDays(2)]));
        app(QuesturaRetentionService::class)->finalizeDay($s->id, $r->id, 1);
        $this->assertNull($tx->fresh()->payload); $this->assertNull($other->fresh()->payload);
        $this->assertNotNull($uncertain->fresh()->payload); $this->assertNotNull($differentDay->fresh()->payload);
        $this->assertNull($uncertain->fresh()->finalized_at); $this->assertSame($r->id, (int) $other->fresh()->questura_receipt_id);
    }

}
