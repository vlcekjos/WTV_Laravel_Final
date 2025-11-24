<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Musíte být přihlášen.'], 401);
        }

        $validated = $request->validate([
            'pub_id' => 'required|exists:pubs,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

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

    public function destroy(Review $review)
    {
        if (Auth::id() !== $review->user_id && !Auth::user()->isAdmin()) {
            abort(403, 'Nemáte oprávnění smazat tuto recenzi.');
        }

        $review->delete();

        return back()->banner('Recenze byla úspěšně smazána.');
    }
}