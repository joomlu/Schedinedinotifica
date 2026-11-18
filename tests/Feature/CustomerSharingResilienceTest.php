<?php

namespace Tests\Feature;

use App\Models\Customers;
use App\Services\CustomerSharing;
use Illuminate\Support\Facades\DB;
use Tests\Support\StrutturaFixtures;
use Tests\TestCase;

/** Processi distinti, transazioni realmente confermate, solo database effimero attestato. */
class CustomerSharingResilienceTest extends TestCase
{
    use StrutturaFixtures;

    private array $workers = [];

    private function scenario(): array
    {
        config(['customer_sharing.enabled' => true]);
        $owner = $this->ownerFor($this->actor('admin'));
        $user = $this->actor('proprietario', $owner->id);
        $a = $this->structureFor($owner); $b = $this->structureFor($owner);
        $first = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Persona', 'surname' => 'Sintetica',
            'nac_reg' => '1980-01-02', 'country_reg' => 'Italia', 'city_reg' => 'Comune sintetico', 'type_housed' => 'Componente']);
        $sharing = app(CustomerSharing::class);
        $group = $sharing->configure($user, null, 0, 'Gruppo sintetico', [$a->id, $b->id]);
        $second = Customers::withoutGlobalScopes()->where('struttura_id', $b->id)->sole();
        // Il secondo hotel ha una registrazione propria, preservata alla separazione.
        \App\Models\Schedina::forceCreate(['struttura_id' => $b->id, 'customer_id' => $second->id,
            'name' => 'Persona', 'surname' => 'Sintetica', 'scheda' => 'SINT-'.$b->id,
            'arrive' => '2026-06-01', 'departure' => '2026-06-03', 'circuito' => 'schedina', 'is_arrive' => false]);
        return compact('owner', 'user', 'a', 'b', 'first', 'second', 'sharing', 'group');
    }

    private function directory(): string
    {
        $path = storage_path('framework/testing/sharing-'.bin2hex(random_bytes(8)));
        mkdir($path, 0700, true);
        return $path;
    }

    private function worker(string $directory, string $name, array $input): void
    {
        $process = proc_open([PHP_BINARY, base_path('tests/Isolation/customer-sharing-worker.php')],
            [0 => ['pipe', 'r'], 1 => ['file', $directory.'/'.$name.'.stdout', 'w'], 2 => ['file', $directory.'/'.$name.'.stderr', 'w']], $pipes, base_path());
        $this->assertIsResource($process);
        fwrite($pipes[0], json_encode($input + compact('directory', 'name'), JSON_THROW_ON_ERROR)); fclose($pipes[0]);
        $this->workers[] = $process;
        $this->waitFor($directory.'/'.$name.'.ready');
    }

    private function waitFor(string $path): void
    {
        $end = microtime(true) + 30;
        while (!is_file($path) && microtime(true) < $end) usleep(10000);
        $this->assertFileExists($path, 'Coordinamento del processo sintetico scaduto: '.$path);
    }

    private function workerResult(string $directory, string $name): int
    {
        $this->waitFor($directory.'/'.$name.'.result');
        $result = json_decode(file_get_contents($directory.'/'.$name.'.result'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('errore', $result, json_encode($result));
        return $result['status'];
    }

    protected function tearDown(): void
    {
        foreach ($this->workers as $worker) {
            if (proc_get_status($worker)['running']) proc_terminate($worker, 9);
            proc_close($worker);
        }
        parent::tearDown();
    }

    public function test_due_processi_con_versione_uguale_un_solo_aggiornamento_vince(): void
    {
        extract($this->scenario()); $dir = $this->directory();
        $this->worker($dir, 'a', ['action' => 'update', 'customer' => $first->id, 'email' => 'prima@example.invalid']);
        $this->worker($dir, 'b', ['action' => 'update', 'customer' => $second->id, 'email' => 'seconda@example.invalid']);
        touch($dir.'/a.start'); touch($dir.'/b.start');
        $results = [$this->workerResult($dir, 'a'), $this->workerResult($dir, 'b')]; sort($results);
        $this->assertSame([200, 409], $results);
        $this->assertSame($first->fresh()->email, $second->fresh()->email);
        $this->assertContains($first->fresh()->email, ['prima@example.invalid', 'seconda@example.invalid']);
        $this->assertSame(1, DB::table('customer_sharing_operations')->where('group_id', $group)->where('tipo', 'aggiornamento')->count());
    }

    public function test_aggiornamento_attende_separazione_e_prosegue_solo_localmente(): void
    {
        extract($this->scenario()); $dir = $this->directory();
        $this->worker($dir, 'separa', ['action' => 'separate', 'owner' => $owner->id, 'user' => $user->id, 'group' => $group, 'hotel' => $b->id]);
        $this->worker($dir, 'aggiorna', ['action' => 'update', 'customer' => $second->id, 'email' => 'dopo@example.invalid']);
        touch($dir.'/aggiorna.start'); $this->waitFor($dir.'/aggiorna.attempting');
        usleep(200000);
        $this->assertFileDoesNotExist($dir.'/aggiorna.result', 'La scrittura ha superato il lock della separazione.');
        touch($dir.'/separa.release');
        $this->assertSame(200, $this->workerResult($dir, 'separa'));
        $this->assertSame(200, $this->workerResult($dir, 'aggiorna'));
        $this->assertNull($sharing->groupFor($b->id));
        $this->assertSame('dopo@example.invalid', $second->fresh()->email);
        $this->assertEmpty($first->fresh()->email);
        $this->assertSame(0, DB::table('customer_sharing_packages')->where('struttura_id', $b->id)->count());
    }

    public function test_arresto_brutale_dopo_scrittura_parziale_annulla_tutto_e_consente_riprova(): void
    {
        extract($this->scenario()); $dir = $this->directory();
        $before = DB::table('customer_persons')->where('group_id', $group)->sole();
        $this->worker($dir, 'crash', ['action' => 'crash', 'customer' => $first->id, 'email' => 'parziale@example.invalid']);
        touch($dir.'/crash.start'); $this->waitFor($dir.'/crash.partial');
        $worker = $this->workers[array_key_last($this->workers)]; proc_terminate($worker, 9);
        $end = microtime(true) + 10;
        while (proc_get_status($worker)['running'] && microtime(true) < $end) usleep(10000);
        $this->assertFalse(proc_get_status($worker)['running']);
        DB::statement('SET SESSION innodb_lock_wait_timeout = 10');
        $this->assertEmpty($first->fresh()->email); $this->assertEmpty($second->fresh()->email);
        $after = DB::table('customer_persons')->where('id', $before->id)->lockForUpdate()->first();
        $this->assertSame($before->data, $after->data); $this->assertSame($before->version, $after->version);
        $this->assertSame(0, DB::table('customer_sharing_operations')->where('group_id', $group)->where('tipo', 'aggiornamento')->count());
        $retry = $first->fresh(); $retry->email = 'recuperato@example.invalid'; $retry->save();
        $this->assertSame('recuperato@example.invalid', $second->fresh()->email);
        $this->assertSame(1, DB::table('customer_sharing_operations')->where('group_id', $group)->where('tipo', 'aggiornamento')->count());
    }
    public function test_attivazione_interrotta_non_lascia_un_gruppo_parziale_e_la_riprova_riesce(): void
    {
        config(['customer_sharing.enabled' => true]);
        $owner = $this->ownerFor($this->actor('admin'));
        $user = $this->actor('proprietario', $owner->id);
        $a = $this->structureFor($owner); $b = $this->structureFor($owner);
        $customer = Customers::forceCreate(['struttura_id' => $a->id, 'name' => 'Sorgente', 'surname' => 'Integra', 'type_housed' => 'Componente']);
        $before = (array) DB::table('clienti')->where('id', $customer->id)->first();
        $personCount = DB::table('customer_persons')->count();
        $armed = true;
        DB::listen(function ($event) use (&$armed) {
            if ($armed && str_starts_with(strtolower($event->sql), 'insert into `customer_person_links`')) {
                $armed = false;
                throw new \RuntimeException('Guasto sintetico durante attivazione');
            }
        });
        $sharing = app(CustomerSharing::class);
        try {
            $sharing->configure($user, null, 0, 'Da recuperare', [$a->id, $b->id]);
            $this->fail('Guasto non iniettato.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Guasto sintetico durante attivazione', $e->getMessage());
        }
        $this->assertSame(0, DB::table('customer_sharing_groups')->where('proprietario_id', $owner->id)->count());
        $this->assertSame(0, DB::table('customer_sharing_memberships')->whereIn('struttura_id', [$a->id, $b->id])->count());
        $this->assertSame($personCount, DB::table('customer_persons')->count());
        $this->assertSame($before, (array) DB::table('clienti')->where('id', $customer->id)->first());
        $this->assertSame(0, DB::table('clienti')->where('struttura_id', $b->id)->count());
        $group = $sharing->configure($user, null, 0, 'Recuperato', [$a->id, $b->id]);
        $this->assertSame('attivo', DB::table('customer_sharing_groups')->where('id', $group)->value('stato'));
        $this->assertSame(1, DB::table('clienti')->where('struttura_id', $b->id)->count());
    }

}
