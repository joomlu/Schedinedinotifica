<?php

if (($argv[1] ?? null) === '--worker') {
    array_splice($argv, 1, 1);
    require_once __DIR__.'/../Support/TestingEnvironment.php';
    \Tests\Support\TestingEnvironment::requireIsolatedRuntime();
    require __DIR__.'/../../vendor/autoload.php';
    $app = require __DIR__.'/../../bootstrap/app.php';
    \Tests\Support\TestingEnvironment::configureApplication($app);
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    try {
        $struttura = \App\Models\Struttura::findOrFail((int) $argv[1]);
        $export = \App\Models\IstatExport::findOrFail((int) $argv[2]);
        \App\Models\IstatTransmission::creating(function () {
            usleep(300000);
        });
        file_put_contents($argv[3].'/ready-'.$argv[4], '1');
        $deadline = microtime(true) + 15;
        while (! is_file($argv[3].'/start') && microtime(true) < $deadline) {
            usleep(10000);
        }
        if (! is_file($argv[3].'/start')) {
            exit(20);
        }
        (new \App\Services\IstatOperationService)->reserve($struttura, $export, 'send', null);
        exit(0);
    } catch (\Illuminate\Validation\ValidationException) {
        exit(10);
    } catch (\Throwable) {
        exit(20);
    }

}

require __DIR__.'/prova-istat.php';

$users = new class
{
    use \Tests\Support\StrutturaFixtures;

    public function make(int $id)
    {
        return $this->actor('struttura_user', null, $id);
    }
};
$user = $users->make($s->id);
$user->update(['username' => 'istat-ui', 'password' => \Illuminate\Support\Facades\Hash::make('Password-istat-fixture-123!')]);
$s->update(['istat_username' => 'UI_SYNTHETIC_USER', 'istat_password' => 'UI_SYNTHETIC_PASSWORD']);
$export = \App\Models\IstatExport::create([
    'struttura_id' => $s->id, 'user_id' => $user->id, 'dal' => $from, 'al' => $to,
    'filename' => 'fixture.xml', 'path' => 'istat/struttura_'.$s->id.'/fixture.enc',
    'sha256' => hash('sha256', $xml), 'encrypted_file' => true,
    'snapshot' => (new \App\Services\IstatSnapshot)->make($a['schedine']),
    'expires_at' => now()->addDays(30), 'schedina_ids' => [$stay->id], 'schedine_count' => 1, 'movimenti_count' => 2,
]);
\Illuminate\Support\Facades\Storage::disk('local')->put($export->path, \Illuminate\Support\Facades\Crypt::encryptString($xml));

$barrier = storage_path('framework/testing/istat-race');
if (! is_dir($barrier)) {
    mkdir($barrier, 0700, true);
}
$workers = [];
for ($i = 0; $i < 2; $i++) {
    $process = proc_open([PHP_BINARY, __FILE__, '--worker', (string) $s->id, (string) $export->id, $barrier, (string) $i],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (! is_resource($process)) {
        throw new \RuntimeException('Worker isolato non avviato.');
    }
    fclose($pipes[0]);
    $workers[] = [$process, $pipes];
}
$deadline = microtime(true) + 15;
while ((! is_file($barrier.'/ready-0') || ! is_file($barrier.'/ready-1')) && microtime(true) < $deadline) {
    usleep(10000);
}
if (! is_file($barrier.'/ready-0') || ! is_file($barrier.'/ready-1')) {
    foreach ($workers as [$process]) {
        proc_terminate($process);
    }
    throw new \RuntimeException('Worker isolati non pronti.');
}
file_put_contents($barrier.'/start', '1');
$codes = [];
foreach ($workers as [$process, $pipes]) {
    stream_get_contents($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $codes[] = proc_close($process);
}
sort($codes);
if ($codes !== [0, 10] || \App\Models\IstatTransmission::where('istat_export_id', $export->id)->count() !== 1
    || \Illuminate\Support\Facades\DB::table('istat_communication_days')->where('struttura_id', $s->id)->count() !== 2) {
    throw new \RuntimeException('Prenotazione concorrente non esclusiva: '.json_encode($codes));
}
echo "PASS: due processi/connessioni MySQL simultanei, una sola prenotazione ISTAT; zero trasporti.\n";
