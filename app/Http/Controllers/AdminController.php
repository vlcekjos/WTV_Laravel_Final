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
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Nemáte oprávnění.');
        }

        if ($user->id === Auth::id()) {
            return back()->dangerBanner('Nemůžete smazat svůj vlastní účet v administraci.');
        }

        $user->delete();

        return back()->banner('Uživatel byl úspěšně smazán.');
    }

    // NOVÁ METODA: ZMĚNA ROLE
    public function toggleUserRole(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Nemáte oprávnění.');
        }

        if ($user->id === Auth::id()) {
            return back()->dangerBanner('Nemůžete odebrat administrátorská práva sami sobě.');
        }

        // Prohození hodnoty booleanu (true -> false, false -> true)
        $user->update([
            'is_admin' => !$user->is_admin
        ]);

        $roleName = $user->is_admin ? 'Admin' : 'Uživatel';
        return back()->banner("Role uživatele {$user->name} byla změněna na: {$roleName}.");
    }

    // Přidání nové hospody
    public function storePub(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Nemáte oprávnění.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        Pub::create($validated);

        return response()->json(['message' => 'Hospoda byla úspěšně vytvořena!'], 201);
    }

    // Úprava hospody
    public function updatePub(Request $request, Pub $pub)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Nemáte oprávnění.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'street' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $pub->update($validated);

        return response()->json(['message' => 'Hospoda byla úspěšně upravena!'], 200);
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