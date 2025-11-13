<?php

use App\Models\College;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $koleje = College::all();
    //dd($koleje);

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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
