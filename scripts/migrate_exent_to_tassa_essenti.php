<?php

require __DIR__.'/../vendor/autoload.php';
use Illuminate\Support\Str;

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting exent -> tassa_essenti migration\n";

$DB = Illuminate\Support\Facades\DB::connection();

// determine target comune id using Estructura->city
$estructura = App\Models\Estructura::first();
$cityName = $estructura->city ?? null;
$comuneId = null;
if ($cityName) {
    $comune = App\Models\Comuni::where('denominazione', $cityName)->first();
    if (! $comune) {
        $comune = App\Models\Comuni::where('denominazione', 'like', "%{$cityName}%")->first();
    }
    if (! $comune) {
        echo "Comune for city '{$cityName}' not found, creating new comune record.\n";
        $comune = App\Models\Comuni::create([
            'denominazione' => $cityName,
            'codice_questura' => 'TEMP_'.strtoupper(substr(md5($cityName), 0, 10)),
        ]);
    }
    $comuneId = $comune->id;
} else {
    $comune = App\Models\Comuni::first();
    if (! $comune) {
        echo "No comuni exist in DB; creating placeholder comune.\n";
        $comune = App\Models\Comuni::create(['name' => 'Unknown', 'code' => null]);
    }
    $comuneId = $comune->id;
}

echo "Using comuni id: {$comuneId} (name: {$comune->name})\n";

// collect distinct exent values from schedina and componenti
$exentsSchedina = collect(
    Illuminate\Support\Facades\DB::table('schedina')
        ->whereNotNull('exent')
        ->whereRaw("TRIM(exent) <> ''")
        ->distinct()
        ->pluck('exent')
)->map(fn ($v) => trim($v));

$exentsComponenti = collect(
    Illuminate\Support\Facades\DB::table('componenti')
        ->whereNotNull('exent')
        ->whereRaw("TRIM(exent) <> ''")
        ->distinct()
        ->pluck('exent')
)->map(fn ($v) => trim($v));

$exents = $exentsSchedina->merge($exentsComponenti)->unique()->values();

if ($exents->isEmpty()) {
    echo "No exent values found in schedina/componenti. Nothing to migrate.\n";
    exit(0);
}

echo 'Found exent values: '.implode(', ', $exents->toArray())."\n";

// backup affected rows
$timestamp = date('Ymd_His');
$backupPath = storage_path('app/backups');
@mkdir($backupPath, 0755, true);
$affectedSchedina = Illuminate\Support\Facades\DB::table('schedina')
    ->whereIn('exent', $exents->toArray())
    ->get();
file_put_contents("{$backupPath}/schedina_exent_backup_{$timestamp}.json", json_encode($affectedSchedina, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$affectedComponenti = Illuminate\Support\Facades\DB::table('componenti')
    ->whereIn('exent', $exents->toArray())
    ->get();
file_put_contents("{$backupPath}/componenti_exent_backup_{$timestamp}.json", json_encode($affectedComponenti, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Backups written to {$backupPath}\n";

// map and create tassa_essenti
$map = [];
foreach ($exents as $val) {
    $cod = Str::slug(mb_substr($val, 0, 40));
    // try to find existing
    $existing = App\Models\TassaEssente::where('comuni_tassa_esenti_id', $comuneId)
        ->where(function ($q) use ($val, $cod) {
            $q->where('cod_esenz', $cod)
                ->orWhere('nome', $val);
        })->first();
    if (! $existing) {
        $created = App\Models\TassaEssente::create([
            'comuni_tassa_esenti_id' => $comuneId,
            'cod_esenz' => $cod,
            'nome' => $val,
            'descrizione' => null,
            'active' => true,
        ]);
        $map[$val] = $created->id;
        echo "Created tassa_essenti: {$created->id} -> {$val}\n";
    } else {
        $map[$val] = $existing->id;
        echo "Found existing tassa_essenti: {$existing->id} -> {$val}\n";
    }
}

// update schedina
$updatedSchedina = 0;
foreach ($map as $val => $tassaId) {
    $u = Illuminate\Support\Facades\DB::table('schedina')->where('exent', $val)->update(['tassa_essente_id' => $tassaId]);
    $updatedSchedina += $u;
}

// update componenti
$updatedComponenti = 0;
foreach ($map as $val => $tassaId) {
    $u = Illuminate\Support\Facades\DB::table('componenti')->where('exent', $val)->update(['tassa_essente_id' => $tassaId]);
    $updatedComponenti += $u;
}

echo "Updated schedina rows: {$updatedSchedina}\n";
echo "Updated componenti rows: {$updatedComponenti}\n";

echo "Migration completed. Verify results.\n";
