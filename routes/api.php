<?php

use App\Http\Controllers\GeoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('geo')->group(function () {
    Route::get('/nazioni', [GeoController::class, 'nazioni']);
    Route::get('/regioni', [GeoController::class, 'regioni']);
    Route::get('/province', [GeoController::class, 'province']);
    Route::get('/comuni', [GeoController::class, 'comuni']);
    Route::get('/cap', [GeoController::class, 'cap']);
    Route::get('/resolve', [GeoController::class, 'resolve']);
});
