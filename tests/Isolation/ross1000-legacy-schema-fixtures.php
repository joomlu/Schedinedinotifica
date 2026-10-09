<?php
require __DIR__.'/ross1000-navigation-fixtures.php';
\Tests\Support\TestingEnvironment::requireIsolatedRuntime();
foreach (['istat_transmission_events', 'istat_communication_days'] as $table) \Illuminate\Support\Facades\Schema::drop($table);
\Illuminate\Support\Facades\Schema::table('istat_exports', fn (\Illuminate\Database\Schema\Blueprint $table) => $table->dropColumn(['sha256', 'encrypted_file', 'snapshot', 'expires_at', 'minimized_at']));
\Illuminate\Support\Facades\Schema::table('istat_transmissions', fn (\Illuminate\Database\Schema\Blueprint $table) => $table->dropColumn(['idempotency_key', 'attempts', 'reconciled_at']));
\Illuminate\Support\Facades\DB::table('migrations')->where('migration', '2026_10_07_180000_protect_istat_cycle')->delete();
$hook=<<<'ROUTE'
\Illuminate\Support\Facades\Route::middleware(['web','auth'])->post('/audit-ross-schema/upgrade',function(){
    \Tests\Support\TestingEnvironment::requireIsolatedRuntime();
    abort_unless(app()->environment('testing') && auth()->user()->username==='ross-completa',403);
    $code=\Illuminate\Support\Facades\Artisan::call('migrate',['--path'=>'database/migrations/2026_10_07_180000_protect_istat_cycle.php','--force'=>true]);
    return response()->json(['exit'=>$code,'snapshot'=>\Illuminate\Support\Facades\Schema::hasColumn('istat_exports','snapshot')]);
});
ROUTE;
$path=base_path('routes/web.php');$routes=file_get_contents($path);$point="Route::middleware(['auth'])->group(function () {\n    // Catch-all";
if(substr_count($routes,$point)!==1) throw new \RuntimeException('Punto hook isolato non univoco');
file_put_contents($path,str_replace($point,$hook."\n".$point,$routes));
