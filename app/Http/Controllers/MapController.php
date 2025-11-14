<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    /*
     * Zobrazí hlavní stránku s mapou.
     */
    public function index()
    {
        // Vrátí soubor 'resources/views/mapa.blade.php'
        return view('mapa'); 
    }
}