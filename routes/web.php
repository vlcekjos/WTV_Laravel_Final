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

Route::get('/', function (Request $request) {
    if (Auth::check()) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    return view('auth.login');
});

Route::get('/mapa', [MapController::class, 'index'])->name('mapa');

// Recenze
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

// --- ADMIN ROUTY ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    
    // NOVÁ ROUTA PRO ZMĚNU ROLE
    Route::put('/admin/users/{user}/toggle-role', [AdminController::class, 'toggleUserRole'])->name('admin.users.toggle-role');
    
    Route::post('/admin/pubs', [AdminController::class, 'storePub'])->name('admin.pubs.store');
    Route::put('/admin/pubs/{pub}', [AdminController::class, 'updatePub'])->name('admin.pubs.update');
    Route::delete('/admin/pubs/{pub}', [AdminController::class, 'destroyPub'])->name('admin.pubs.destroy');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // ...
});