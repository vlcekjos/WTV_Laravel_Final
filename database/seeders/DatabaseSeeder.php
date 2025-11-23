<?php

namespace Database\Seeders;

use App\Models\Pub;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Vytvoření uživatelů
        // Použijeme firstOrCreate, aby se nevytvářeli duplicitně, pokud seeder spustíš víckrát
        $user0 = User::firstOrCreate(
            ['email' => 'admin@e'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin'),
                'is_admin' => true,
            ]
        );
        
        $user1 = User::firstOrCreate(
            ['email' => 'uzivatel1@e'],
            [
                'name' => 'Uživatel 1',
                'password' => Hash::make('uzivatel1'),
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'uzivatel2@e'],
            [
                'name' => 'Uživatel 2',
                'password' => Hash::make('uzivatel2'),
            ]
        );

        // 2. Vytvoření hospod
        $pub1 = Pub::firstOrCreate(
            ['name' => 'Restaurace U Mansfelda'], // Podle souřadnic
            [
                'description' => 'Tradiční plzeňská restaurace s výbornou kuchyní a tankovým pivem.',
                'latitude' => 49.7485872,
                'longitude' => 13.3764331,
                'street' => 'Dřevěná 9',
                'city' => 'Plzeň',
            ]
        );

        $pub2 = Pub::firstOrCreate(
            ['name' => 'Šenk Na Parkánu'], // Podle souřadnic
            [
                'description' => 'Hospoda přímo spojená s Pivovarským muzeem. Čepují zde nefiltrovaný Prazdroj.',
                'latitude' => 49.7458767,
                'longitude' => 13.3800058,
                'street' => 'Veleslavínova 4',
                'city' => 'Plzeň',
            ]
        );

        // 3. Vytvoření recenzí (Každý uživatel jednu)
        
        // Uživatel 1 hodnotí Hospodu 1
        Review::updateOrCreate(
            [
                'user_id' => $user1->id,
                'pub_id' => $pub1->id,
            ],
            [
                'rating' => 5,
                'comment' => 'Naprosto skvělé pivo a gulášek jako od maminky! Doporučuji všem.',
            ]
        );

        // Uživatel 2 hodnotí Hospodu 2
        Review::updateOrCreate(
            [
                'user_id' => $user2->id,
                'pub_id' => $pub2->id,
            ],
            [
                'rating' => 4,
                'comment' => 'Krásné prostředí a historie dýchá z každého rohu. Obsluha byla trochu pomalejší, ale jídlo super.',
            ]
        );
    }
}