<?php

use Illuminate\Support\Facades\Route;
use GeoItalia\Http\Controllers\GeoController;

Route::prefix('geo')->group(function () {
    Route::get('/nazioni', [GeoController::class, 'nazioni']);
    Route::get('/regioni', [GeoController::class, 'regioni']);
    Route::get('/province', [GeoController::class, 'province']);
    Route::get('/comuni', [GeoController::class, 'comuni']);
    Route::get('/cap', [GeoController::class, 'cap']);
    Route::get('/resolve', [GeoController::class, 'resolve']);
});
