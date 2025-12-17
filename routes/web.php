<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Auth::check() ? redirect()->route('mapa') : view('auth.login');
});

Route::get('/mapa', [MapController::class, 'index'])->name('mapa');

// Recenze - Přidáno middleware auth, aby recenze nemohl posílat/mazat nepřihlášený host
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// --- ADMIN ROUTY ---
Route::middleware(['auth', 'verified'])->group(function () {
    // Smazání a role uživatelů
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::put('/admin/users/{user}/toggle-role', [AdminController::class, 'toggleUserRole'])->name('admin.users.toggle-role');
    
    // Správa hospod
    Route::post('/admin/pubs', [AdminController::class, 'storePub'])->name('admin.pubs.store');
    Route::put('/admin/pubs/{pub}', [AdminController::class, 'updatePub'])->name('admin.pubs.update');
    Route::delete('/admin/pubs/{pub}', [AdminController::class, 'destroyPub'])->name('admin.pubs.destroy');

    // --- API ROUTY PRO DYNAMICKÉ TABULKY ---
    Route::prefix('admin/api')->group(function () {
        Route::get('/reviews', [AdminController::class, 'apiReviews'])->name('api.admin.reviews');
        Route::get('/users', [AdminController::class, 'apiUsers'])->name('api.admin.users');
        Route::get('/pubs', [AdminController::class, 'apiPubs'])->name('api.admin.pubs');
    });
});

// Routa pro JSON data do mapy (veřejná)
Route::get('/api/pubs', [MapController::class, 'apiData'])->name('api.pubs');

// Jetstream defaultní routy (ponecháno pro stabilitu profilu)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () { return redirect()->route('mapa'); })->name('dashboard');
});