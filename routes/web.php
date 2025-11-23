<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function (Request $request) {
    // Logika pro odhlášení při návštěvě root URL
    if (Auth::check()) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    return view('auth.login');
});

Route::get('/mapa', [MapController::class, 'index'])->name('mapa');

// API pro ukládání recenzí (z mapy)
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// NOVÁ ROUTA: Mazání recenzí (využívá Model Binding {review})
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Zde jsou routy pro přihlášené (např. dashboard/profil, které řeší Jetstream)
});