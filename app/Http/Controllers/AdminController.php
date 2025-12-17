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
            $message = 'Nemůžete smazat svůj vlastní účet v administraci.';
            return request()->wantsJson() 
                ? response()->json(['message' => $message], 422) 
                : back()->dangerBanner($message);
        }

        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Uživatel byl úspěšně smazán.']);
        }

        return back()->banner('Uživatel byl úspěšně smazán.');
    }

    // ZMĚNA ROLE
    public function toggleUserRole(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Nemáte oprávnění.');
        }

        if ($user->id === Auth::id()) {
            $message = 'Nemůžete odebrat administrátorská práva sami sobě.';
            return request()->wantsJson() 
                ? response()->json(['message' => $message], 422) 
                : back()->dangerBanner($message);
        }

        $user->update([
            'is_admin' => !$user->is_admin
        ]);

        $roleName = $user->is_admin ? 'Admin' : 'Uživatel';
        $message = "Role uživatele {$user->name} byla změněna na: {$roleName}.";

        if (request()->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->banner($message);
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

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Hospoda byla úspěšně smazána.']);
        }

        return back()->banner('Hospoda byla úspěšně smazána.');
    }

    public function apiReviews() {
        return response()->json(\App\Models\Review::with(['user:id,name', 'pub:id,name'])->latest()->get());
    }

    public function apiUsers() {
        return response()->json(\App\Models\User::all());
    }

    public function apiPubs() {
        return response()->json(\App\Models\Pub::all());
    }
}