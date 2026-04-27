<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Player::create([
            'name' => 'Noval Saputra',
            'email' => 'noval@example.com',
            'wallet_balance' => 500000
        ]);

        Player::create([
            'name' => 'Dimas Dwi',
            'email' => 'dimas@example.com',
            'wallet_balance' => 300000
        ]);

        Player::create([
            'name' => 'Ivan Arjuna',
            'email' => 'ivan@example.com',
            'wallet_balance' => 750000
        ]);
    }
}
