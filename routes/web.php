<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

//
// 1) Health check estable para CI/monitoreo
//
Route::get('/health', fn () => response()->json(['ok' => true]))->name('health');

//
// 2) Archivos adwords: sanitizar y limitar patrón
//
Route::get('/adwords/files/{filename}', function (string $filename) {
    // evita traversal: sample.csv -> sample.csv
    $filename = basename($filename);

    // solo letras, números, guiones, puntos y subrayado (ajusta si necesitás)
    if (! preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
        abort(400, 'Nombre de archivo inválido');
    }

    $path = storage_path('app/uploads/adwords/'.$filename);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '[A-Za-z0-9._-]+')->name('adwords.files');

//
// 3) Auth (Laravel UI está ok en tu stack actual)
//
Auth::routes();

// Home: si redirige, tu test debe esperar 302; si querés 200, ajustá el controller.
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

// Compat: algunas instalaciones de auth redirigen a /home por defecto
Route::get('/home', function () {
    return redirect()->route('root');
})->name('home');

//
// 4) Agrupá rutas de app bajo auth (más claro y seguro)
//
Route::middleware('auth')->group(function () {
    // Admin area (permessi Spatie)
    Route::middleware(['permission:access admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [App\Http\Controllers\Admin\UserAdminController::class, 'index'])->name('users.index');
        Route::post('/users/{id}/role', [App\Http\Controllers\Admin\UserAdminController::class, 'updateRole'])->name('users.updateRole');
    });
    // Authenticated health check
    Route::get('/health-auth', function () {
        $user = auth()->user();

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? null,
            ],
        ]);
    })->name('health.auth');

    // archivo txt (SOLO UNA definición, la de arriba estaba duplicada)
    Route::get('/archivos', [App\Http\Controllers\ArchivosController::class, 'generarArchivoHospedados'])
        ->name('archivos');

    // customers
    Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers');
    Route::get('/newcustomer', [App\Http\Controllers\CustomerController::class, 'new'])->name('newcustomer');
    Route::post('/customer_store', [App\Http\Controllers\CustomerController::class, 'store'])->name('customer.store');
    Route::get('/editcustomer/{id}', [App\Http\Controllers\CustomerController::class, 'edit'])->name('customer.edit');
    Route::put('customer_update/{id}', [App\Http\Controllers\CustomerController::class, 'update'])->name('customer.update');
    Route::get('/customerdestroy/{id}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('customer.destroy');

    // AJAX (podés agregar throttle si querés limitar)
    Route::get('/provinces-by-region', [App\Http\Controllers\LocationController::class, 'provincesByRegion'])->name('provincesByRegion');
    Route::get('/provinces-all', [App\Http\Controllers\LocationController::class, 'provincesAll'])->name('provincesAll');
    Route::get('/cap-by-province', [App\Http\Controllers\LocationController::class, 'capByProvince'])->name('capByProvince');
    Route::get('/cities-by-province', [App\Http\Controllers\LocationController::class, 'citiesByProvince'])->name('citiesByProvince');

    // componenti
    Route::get('/componenti', [App\Http\Controllers\ComponentiController::class, 'index'])->name('componenti');
    Route::get('/newcomponenti/{schedina_id}/{customer_id}', [App\Http\Controllers\ComponentiController::class, 'new'])->name('newcomponenti');
    Route::post('/componenti_store', [App\Http\Controllers\ComponentiController::class, 'store'])->name('componenti.store');
    Route::get('/editcomponenti/{id}', [App\Http\Controllers\ComponentiController::class, 'edit'])->name('componenti.edit');
    Route::put('componenti_update/{id}', [App\Http\Controllers\ComponentiController::class, 'update'])->name('componenti.update');
    Route::get('/componentidestroy/{id}', [App\Http\Controllers\ComponentiController::class, 'destroy'])->name('componenti.destroy');

    // schedina
    Route::get('/schedina', [App\Http\Controllers\SchedinaController::class, 'index'])->name('schedina');
    Route::get('/newschedina', [App\Http\Controllers\SchedinaController::class, 'new'])->name('newschedina');
    Route::post('/schedina_store', [App\Http\Controllers\SchedinaController::class, 'store'])->name('schedina.store');
    Route::get('/editschedina/{id}', [App\Http\Controllers\SchedinaController::class, 'edit'])->name('schedina.edit');
    Route::put('schedina_update/{id}', [App\Http\Controllers\SchedinaController::class, 'update'])->name('schedina.update');
    Route::get('/schedinadestroy/{id}', [App\Http\Controllers\SchedinaController::class, 'destroy'])->name('schedina.destroy');

    // groups
    Route::get('/groups', [App\Http\Controllers\GroupController::class, 'index'])->name('groups');
    Route::post('/group_store', [App\Http\Controllers\GroupController::class, 'store'])->name('group.store');
    Route::put('group_update/{id}', [App\Http\Controllers\GroupController::class, 'update'])->name('group.update');
    Route::get('/groupdestroy/{id}', [App\Http\Controllers\GroupController::class, 'destroy'])->name('group.destroy');

    // Subgroups
    Route::get('/subgroups', [App\Http\Controllers\SubGroupController::class, 'index'])->name('subgroups');
    Route::post('/subgroup_store', [App\Http\Controllers\SubGroupController::class, 'store'])->name('subgroup.store');
    Route::put('subgroup_update/{id}', [App\Http\Controllers\SubGroupController::class, 'update'])->name('subgroup.update');
    Route::get('/subgroupdestroy/{id}', [App\Http\Controllers\SubGroupController::class, 'destroy'])->name('subgroup.destroy');

    // Subgroups1
    Route::get('/subgroups1', [App\Http\Controllers\SubGroup1Controller::class, 'index'])->name('subgroups1');
    Route::post('/subgroup1_store', [App\Http\Controllers\SubGroup1Controller::class, 'store'])->name('subgroup1.store');
    Route::put('/subgroup1_update/{id}', [App\Http\Controllers\SubGroup1Controller::class, 'update'])->name('subgroup1.update');
    Route::get('/subgroup1destroy/{id}', [App\Http\Controllers\SubGroup1Controller::class, 'destroy'])->name('subgroup1.destroy');

    // Title
    Route::get('/titles', [App\Http\Controllers\TitleController::class, 'index'])->name('title');
    Route::post('/title_store', [App\Http\Controllers\TitleController::class, 'store'])->name('title.store');
    Route::put('/title_update/{id}', [App\Http\Controllers\TitleController::class, 'update'])->name('title.update');
    Route::get('/titledestroy/{id}', [App\Http\Controllers\TitleController::class, 'destroy'])->name('title.destroy');

    // typeDoc
    Route::get('/typedoc', [App\Http\Controllers\TypeDocController::class, 'index'])->name('typedoc');
    Route::post('/typedoc_store', [App\Http\Controllers\TypeDocController::class, 'store'])->name('typedoc.store');
    Route::put('/typedoc_update/{id}', [App\Http\Controllers\TypeDocController::class, 'update'])->name('typedoc.update');
    Route::get('/typedocdestroy/{id}', [App\Http\Controllers\TypeDocController::class, 'destroy'])->name('typedoc.destroy');

    // Released
    Route::get('/released', [App\Http\Controllers\ReleasedController::class, 'index'])->name('released');
    Route::post('/released_store', [App\Http\Controllers\ReleasedController::class, 'store'])->name('released.store');
    Route::put('/released_update/{id}', [App\Http\Controllers\ReleasedController::class, 'update'])->name('released.update');
    Route::get('/releaseddestroy/{id}', [App\Http\Controllers\ReleasedController::class, 'destroy'])->name('released.destroy');

    // typestreet
    Route::get('/typestreet', [App\Http\Controllers\TypeStreetController::class, 'index'])->name('typestreet');
    Route::post('/typestreet_store', [App\Http\Controllers\TypeStreetController::class, 'store'])->name('typestreet.store');
    Route::put('/typestreet_update/{id}', [App\Http\Controllers\TypeStreetController::class, 'update'])->name('typestreet.update');
    Route::get('/typestreetdestroy/{id}', [App\Http\Controllers\TypeStreetController::class, 'destroy'])->name('typestreet.destroy');

    // estructura
    Route::get('/estructura', [App\Http\Controllers\EstructuraController::class, 'index'])->name('estructura');
    Route::put('/estructura_update/{id}', [App\Http\Controllers\EstructuraController::class, 'update'])->name('estructura.update');
    Route::put('/tassa_update/{id}', [App\Http\Controllers\EstructuraController::class, 'tasaupdate'])->name('tasa.update');

    // arrivals
    Route::get('/arrivals', [App\Http\Controllers\ArrivalsController::class, 'index'])->name('arrivals');
    Route::get('/new_arrival', [App\Http\Controllers\ArrivalsController::class, 'new'])->name('newarrival');
    Route::put('/arrivals_update/{id}', [App\Http\Controllers\ArrivalsController::class, 'update'])->name('arrivals.update');
    Route::post('/arrivals_store', [App\Http\Controllers\ArrivalsController::class, 'store'])->name('arrival.store');
    Route::get('/search_customers', [App\Http\Controllers\ArrivalsController::class, 'search'])->name('search.customer');
    Route::get('/arrivals_destroy/{id}', [App\Http\Controllers\ArrivalsController::class, 'destroy'])->name('arrivals.destroy');
    Route::post('/arrivals/a_schedina/{id}', [App\Http\Controllers\ArrivalsController::class, 'a_schedina'])->name('a_schedina');

    // Update User Details
    Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');
});

//
// 5) Catch-all SIEMPRE al final del archivo
//
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->where('any', '.*')
    ->name('index');
