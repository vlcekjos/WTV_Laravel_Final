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
        // Načteme hospody a k nim 'přibalíme' recenze (seřazené od nejnovější)
        // a také autora recenze (user), abychom mohli zobrazit jeho jméno.
        $pubs = Pub::with(['reviews' => function($query) {
            $query->latest()->with('user:id,name');
        }])->get();

        return view('mapa', compact('pubs')); 
    }
}