<?php

require_once __DIR__.'/../Support/TestingEnvironment.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
\Tests\Support\TestingEnvironment::configureApplication($app);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customers;
use App\Models\User;
use App\Services\CustomerSharing;
use Illuminate\Support\Facades\DB;

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$directory = $input['directory'];
if (!is_dir($directory) || !str_starts_with(realpath($directory), getenv('ISOLATED_ROOT').'/')) throw new RuntimeException('Coordinamento fuori dal runtime isolato.');
$prefix = $directory.'/'.$input['name'];
$wait = function (string $file): void {
    $limit = microtime(true) + 30;
    while (!is_file($file)) {
        if (microtime(true) > $limit) throw new RuntimeException('Scadenza del coordinamento sintetico.');
        usleep(10000);
    }
};
try {
    $sharing = app(CustomerSharing::class);
    if ($input['action'] === 'separate') {
        DB::transaction(function () use ($input, $sharing, $prefix, $wait) {
            DB::table('proprietari')->where('id', $input['owner'])->lockForUpdate()->first();
            $sharing->separate(User::findOrFail($input['user']), $input['group'], $input['hotel'], 1, false);
            touch($prefix.'.ready');
            $wait($prefix.'.release');
        });
    } else {
        $customer = Customers::withoutGlobalScopes()->findOrFail($input['customer']);
        touch($prefix.'.ready');
        $wait($prefix.'.start');
        if ($input['action'] === 'crash') {
            DB::listen(function ($event) use ($prefix, $wait) {
                if (str_starts_with(strtolower($event->sql), 'update `customer_persons`')) {
                    touch($prefix.'.partial');
                    $wait($prefix.'.release');
                }
            });
        }
        touch($prefix.'.attempting');
        $customer->email = $input['email'];
        $customer->save();
    }
    file_put_contents($prefix.'.result', json_encode(['status' => 200]));
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    file_put_contents($prefix.'.result', json_encode(['status' => $e->getStatusCode()]));
} catch (Throwable $e) {
    file_put_contents($prefix.'.result', json_encode(['status' => 500, 'errore' => get_class($e).': '.$e->getMessage()]));
    exit(1);
}
