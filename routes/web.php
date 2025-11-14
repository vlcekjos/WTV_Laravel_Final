<?php

use App\Models\College;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

Route::get('/', function () {
    // Jednoduše vrátíme pohled (view), který používá Jetstream pro přihlášení.
    // Není potřeba žádné přesměrování.
    return view('auth.login');
});

Route::get('/mapa', [MapController::class, 'index'])->name('mapa');



Route::middleware('guest');
