<?php

use App\Models\College;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

/* ZÍKA KÓD
Route::get('/', function () {

    $koleje = College::all();

    $maxHodnota = College::max('hodnoceni');
    $pixelNaBod = 0;
    
    if(0 != $maxHodnota) {
        $pixelNaBod = 150 / $maxHodnota;   
    }    

    return view('welcome', [
        'colleges' => $koleje, 'pomocnaProm' => $pixelNaBod
    ]);
});

Route::view('/video', 'videoHarry')->name("videjko"); 
*/

Route::get('/', function () {
    // Jednoduše vrátíme pohled (view), který používá Jetstream pro přihlášení.
    // Není potřeba žádné přesměrování.
    return view('auth.login');
});

Route::get('/mapa', [MapController::class, 'index'])->name('mapa');



Route::middleware('guest');
