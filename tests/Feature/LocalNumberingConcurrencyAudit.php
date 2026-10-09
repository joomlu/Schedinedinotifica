<?php

namespace Tests\Feature;

use App\Models\Schedina;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

class LocalNumberingConcurrencyAudit extends TestCase
{
    use StrutturaFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        // Solo schema effimero attestato: niente rollback down delle migration.
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
    }

    public static function percorsi(): array
    {
        return [['/arrivi'], ['/schedine']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('percorsi')]
    public function test_due_richieste_parallele_hanno_numeri_distinti_e_persistono_entrambe(string $path): void
    {
        $s = $this->structureFor(null);
        $u = $this->actor('struttura_user', null, $s->id);
        $dir = storage_path('framework/testing/concorrenza-'.bin2hex(random_bytes(4)));
        mkdir($dir, 0700, true);
        DB::disconnect();
        $pids = [];
        for ($i = 0; $i < 2; $i++) {
            $pid = pcntl_fork();
            $this->assertGreaterThanOrEqual(0, $pid);
            if ($pid === 0) {
                try {
                    DB::purge();
                    $this->actingAs($u);
                    $seen = false;
                    DB::listen(function ($query) use (&$seen, $dir, $i) {
                        if (! $seen && str_starts_with($query->sql, 'select `scheda`')) {
                            $seen = true;
                            file_put_contents($dir.'/letto-'.$i, '1');
                            $until = microtime(true) + 1;
                            while ((! file_exists($dir.'/letto-0') || ! file_exists($dir.'/letto-1')) && microtime(true) < $until) {
                                usleep(10000);
                            }
                        }
                    });
                    $response = $this->post($path, ['save_mode' => 'to_arrivi', 'customer_privacy_consent' => 1, 'name' => 'CONCORRENTE-'.$i, 'surname' => 'Sintetico', 'sex' => 'M', 'arrive' => now()->toDateString(), 'departure' => now()->addDays(2)->toDateString(), 'cant_people' => 1, 'room' => $i + 1, 'beds' => 1, 'relationship' => 'OSPITE SINGOLO']);
                    file_put_contents($dir.'/esito-'.$i, json_encode(['status' => $response->status(), 'letto' => $seen]));
                } catch (\Throwable $error) {
                    file_put_contents($dir.'/esito-'.$i, json_encode(['errore' => $error->getMessage()]));
                }
                exit(0);
            }
            $pids[] = $pid;
        }
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertSame(0, pcntl_wexitstatus($status));
        }
        DB::purge();
        foreach ([0, 1] as $i) {
            $result = json_decode(file_get_contents($dir.'/esito-'.$i), true);
            fwrite(STDOUT, '\nCONCORRENZA '.json_encode($result).'\n');
            $this->assertSame(302, $result['status'] ?? null, json_encode($result));
            $this->assertTrue($result['letto']);
        }
        $rows = Schedina::withoutGlobalScopes()->where('struttura_id', $s->id)->get();
        $this->assertCount(2, $rows);
        $this->assertCount(2, $rows->pluck('scheda')->unique());
        $this->assertSame(['CONCORRENTE-0', 'CONCORRENTE-1'], $rows->sortBy('name')->pluck('name')->all());
    }
}
