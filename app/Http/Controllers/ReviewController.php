<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // 1. Ověření, zda je uživatel přihlášen
        if (!Auth::check()) {
            return response()->json(['message' => 'Musíte být přihlášen.'], 401);
        }

        // 2. Validace dat
        $validated = $request->validate([
            'pub_id' => 'required|exists:pubs,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        // 3. Uložení recenze
        // Použijeme updateOrCreate, aby uživatel nemohl hodnotit jednu hospodu 2x (pouze upravil svou starou)
        // Pokud chcete povolit více recenzí, použijte Review::create(...)
        $review = Review::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'pub_id' => $validated['pub_id'],
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        return response()->json(['message' => 'Recenze byla uložena!', 'review' => $review], 201);
    }
}