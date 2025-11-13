<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\College;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test___@example.com',
        ]);
        */

        $koleje = [
            ["nazev" => "Zmijozel", "obrazek" => "zmijozel.png", "barva" => "green"],
            ["nazev" => "Nebelvír", "obrazek" => "lev.png", "barva" => "red"],
            ["nazev" => "Havraspár", "obrazek" => "havran.png", "barva" => "blue"],
            ["nazev" => "Mrzimor", "obrazek" => "jezevec.png", "barva" => "yellow"],
        ];

        foreach($koleje as $kolej) {
            //insert rovnou vklada do db 
            //College::insert();
            //create -> udela instanci, ale nevlozi do db
            $kolejPromenna = College::create($kolej);
            $kolejPromenna->hodnoceni = rand(15, 150);
            $kolejPromenna->save();
        }        
    }
}
