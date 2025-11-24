<?php

namespace App\Http\Controllers;

use App\Models\Pub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // Smazání uživatele
    public function destroyUser(User $user)
    {
        // 1. Kontrola oprávnění
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Nemáte oprávnění.');
        }

        // 2. Ochrana proti smazání sebe sama
        if ($user->id === Auth::id()) {
            return back()->dangerBanner('Nemůžete smazat svůj vlastní účet v administraci.');
        }

        // 3. Smazání
        $user->delete();

        return back()->banner('Uživatel byl úspěšně smazán.');
    }

    // Smazání hospody
    public function destroyPub(Pub $pub)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Nemáte oprávnění.');
        }

        $pub->delete();

        return back()->banner('Hospoda byla úspěšně smazána.');
    }
}