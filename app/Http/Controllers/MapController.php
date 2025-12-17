<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pub;

class MapController extends Controller
{
    /**
     * Zobrazí hlavní stránku s mapou.
     */
    public function index()
    {
        return view('mapa'); 
    }

    public function apiData()
    {
        // Načítání hospod včetně uživatelů v recenzích (pro zobrazení jména v panelu)
        $pubs = \App\Models\Pub::with('reviews.user')->get();
        
        return response()->json($pubs);
    }
}