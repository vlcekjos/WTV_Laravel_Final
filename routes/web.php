<?php

use Illuminate\Support\Facades\Route; use App\Http\Controllers\MapController; use Illuminate\Support\Facades\Auth; use Illuminate\Http\Request; // Odstranil jsem starý kód 'use App\Models\College;', který se zdál být neaktivní

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- */

// TOTO JE VAŠE NOVÁ ROUTA PRO / S POŽADOVANOU ODHLAŠOVACÍ LOGIKOU 
Route::get('/', function (Request $request) {

// Zkontroluje, zda je uživatel přihlášen
if (Auth::check()) { Auth::guard('web')->logout(); // Odhlásí ho
$request->session()->invalidate(); // Zneplatní jeho session
$request->session()->regenerateToken(); // Vytvoří nový CSRF token 
}

// Poté vždy zobrazí přihlašovací stránku (jak jste měl předtím) 
return view('auth.login');

});

// Tato routa je veřejná pro všechny
Route::get('/mapa', [MapController::class, 'index'])->name('mapa');

// TOTO JE DŮLEŽITÉ: Routy pro přihlášené uživatele (profil, atd.) // Bez tohoto by Jetstream nefungoval správně.
Route::middleware([ 'auth:sanctum', config('jetstream.auth_session'), 'verified', ])->group(function () { // Zde bude v budoucnu váš profil, atd.
});