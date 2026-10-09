<?php

namespace Tests\Feature;

use App\Models\Schedina;
use App\Models\WebCheckinRichiesta;
use App\Services\WebCheckinLink;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class WebCheckinLifecycleConcurrencyAudit extends TestCase
{
    use StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
    }

    private function richiesta(): WebCheckinRichiesta
    {
        $s = $this->structureFor(null);
        $p = Schedina::forceCreate(['struttura_id' => $s->id, 'circuito' => 'web', 'name' => 'ORIGINALE', 'surname' => 'Sintetico', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(3)->toDateString(), 'cant_people' => 1]);
        $r = WebCheckinRichiesta::create(['struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'RACE', 'numero_prenotazione' => 'SINTETICA', 'email' => 'race@example.invalid', 'nome_referente' => 'Sintetico', 'arrivo' => now()->toDateString(), 'partenza' => now()->addDays(3)->toDateString(), 'quantita_persone' => 1, 'token' => str_repeat('R', 64), 'stato' => 'da_inviare']);
        app(WebCheckinLink::class)->issue($r);

        return $r;
    }

    public static function races(): array
    {
        return [['revoca'], ['rigenera'], ['conversione']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('races')]
    public function test_salvataggio_gia_iniziato_rivalida_dopo_lock(string $operation): void
    {
        $r = $this->richiesta();
        $u = $this->actor('struttura_user', null, $r->struttura_id);
        $dir = storage_path('framework/testing/race-'.bin2hex(random_bytes(4)));
        mkdir($dir, 0700, true);
        DB::disconnect();
        $pid = pcntl_fork();
        $this->assertGreaterThanOrEqual(0, $pid);
        if ($pid === 0) {
            try {
                DB::purge();
                while (! file_exists($dir.'/go')) {
                    usleep(10000);
                }DB::listen(function ($q) use ($dir) {
                    if (str_contains($q->sql, 'web_checkin_richieste') && str_contains($q->sql, '`token`')) {
                        file_put_contents($dir.'/resolved', '1');
                    }
                });
                $response = $this->post('/checkin/'.$r->token, ['name' => 'SCRITTURA-TARDIVA', 'surname' => 'Sintetico', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(3)->toDateString(), 'cant_people' => 1]);
                file_put_contents($dir.'/result', json_encode(['status' => $response->status(), 'body' => $response->getContent()]));
            } catch (\Throwable $e) {
                file_put_contents($dir.'/result', json_encode(['error' => $e->getMessage()]));
            }exit(0);
        }
        DB::purge();
        DB::beginTransaction();
        \App\Models\Struttura::withoutGlobalScopes()->whereKey($r->struttura_id)->lockForUpdate()->firstOrFail();
        file_put_contents($dir.'/go', '1');
        $end = microtime(true) + 5;
        while (! file_exists($dir.'/resolved') && microtime(true) < $end) {
            usleep(10000);
        }$this->assertFileExists($dir.'/resolved');
        $this->actingAs($u);
        if ($operation === 'conversione') {
            $this->put('/schedine/'.$r->schedina_id, ['save_mode' => 'to_arrivi', 'name' => 'ORIGINALE', 'surname' => 'Sintetico', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(3)->toDateString(), 'cant_people' => 1, 'room' => 1, 'beds' => 1])->assertRedirect();
        } else {
            $this->post('/web-checkin/'.$r->id.'/'.$operation)->assertRedirect();
        }
        DB::commit();
        pcntl_waitpid($pid, $status);
        $this->assertSame(0, pcntl_wexitstatus($status));
        $result = json_decode(file_get_contents($dir.'/result'), true);
        $this->assertSame($operation === 'conversione' ? 200 : 404, $result['status'] ?? null, json_encode($result));
        if ($operation === 'conversione') {
            $this->assertSame('Check-in recibido', $result['body']);
            $this->assertSame('convertito', $r->fresh()->stato);
        }
        $this->assertSame('ORIGINALE', Schedina::withoutGlobalScopes()->find($r->schedina_id)->name);
    }

    public function test_limite_post_concorrente_non_supera20(): void
    {
        $r = $this->richiesta();
        $r->update(['stato' => 'convertito']);
        for ($i = 0; $i < 19; $i++) {
            $this->post('/checkin/'.$r->token)->assertOk();
        }
        $dir = storage_path('framework/testing/quota-'.bin2hex(random_bytes(4)));
        mkdir($dir, 0700, true);
        DB::disconnect();
        $pids = [];
        for ($i = 0; $i < 2; $i++) {
            $pid = pcntl_fork();
            if ($pid === 0) {
                try {
                    DB::purge();
                    while (! file_exists($dir.'/go')) {
                        usleep(10000);
                    }$response = $this->post($i ? '/w/'.$r->short_token : '/checkin/'.$r->token);
                    file_put_contents($dir.'/'.$i, (string) $response->status());
                } catch (\Throwable $e) {
                    file_put_contents($dir.'/'.$i, $e->getMessage());
                }exit(0);
            }$pids[] = $pid;
        }
        file_put_contents($dir.'/go', '1');
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertSame(0, pcntl_wexitstatus($status));
        }DB::purge();
        $statuses = [(int) file_get_contents($dir.'/0'), (int) file_get_contents($dir.'/1')];
        sort($statuses);
        $this->assertSame([200, 429], $statuses);
        $this->assertSame('ORIGINALE', Schedina::withoutGlobalScopes()->find($r->schedina_id)->name);
    }
}
