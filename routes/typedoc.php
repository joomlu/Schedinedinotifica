<?php

use Illuminate\Support\Facades\Route;

Route::get('/typedoc', function () {
    return view('typedoc');
})->name('typedoc');
