<?php

namespace Database\Seeders;

use App\Models\GameItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        GameItem::create([
            'name' => '1000 Diamond Mobile Legends',
            'price' => 150000,
            'stock' => 50,
            'category' => 'Currency'
        ]);

        GameItem::create([
            'name' => 'Starlight Member Monthly',
            'price' => 120000,
            'stock' => 20,
            'category' => 'Membership'
        ]);
        
        GameItem::create([
            'name' => 'PUBG 600 UC',
            'price' => 100000,
            'stock' => 100,
            'category' => 'Currency'
        ]);
    }
}
