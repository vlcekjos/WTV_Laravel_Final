<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController; // <-- DŮLEŽITÉ: Import AdminControlleru
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

// Mazání recenzí (využívá Model Binding {review})
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

// --- ADMIN ROUTY ---
// Tyto routy jsou chráněné, dostupný jen pro přihlášené (ověření isAdmin je pak v Controlleru)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::delete('/admin/pubs/{pub}', [AdminController::class, 'destroyPub'])->name('admin.pubs.destroy');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Zde jsou routy pro přihlášené (např. dashboard/profil, které řeší Jetstream)
});